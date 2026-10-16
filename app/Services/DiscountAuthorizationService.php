<?php

namespace App\Services;

use App\Enums\DiscountAuthorizationStatus;
use App\Models\CashSession;
use App\Models\DiscountAuthorization;
use App\Models\Scopes\StoreScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;

/**
 * Handles the hybrid discount authorization workflow.
 *
 * Two paths are supported:
 * 1. **In-person PIN** – A supervisor enters their 4-digit PIN on the
 *    seller's screen, the sale is completed immediately.
 * 2. **Remote request** – The seller creates a pending authorization that
 *    a supervisor can approve from their own device. The seller's POS
 *    polls until the request is resolved.
 */
class DiscountAuthorizationService
{
    /**
     * Validate a supervisor PIN and return the authorising user.
     *
     * @throws InvalidArgumentException if the PIN is invalid or belongs to an unauthorised user
     */
    public function authorizeByPin(string $pin, int $storeId): User
    {
        if (trim($pin) === '') {
            throw new InvalidArgumentException('El PIN de supervisor es obligatorio.');
        }

        $supervisors = User::where('is_active', true)
            ->whereNotNull('supervisor_pin')
            ->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereHas('roles', fn ($q) => $q->where('name', 'Dueño'));
            })
            ->get();

        foreach ($supervisors as $user) {
            if (Hash::check($pin, $user->supervisor_pin) && $user->can('products.edit_price')) {
                return $user;
            }
        }

        throw new InvalidArgumentException('PIN incorrecto o el usuario no tiene permiso para autorizar descuentos.');
    }

    /**
     * Create a pending remote authorization request.
     *
     * @param  array<string, mixed>  $saleData  Frozen cart + payment snapshot
     */
    public function requestRemote(
        User $seller,
        CashSession $session,
        array $saleData,
        string $subtotal,
        string $discountAmount,
        string $discountPercent,
        string $reason,
    ): DiscountAuthorization {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del descuento es obligatorio.');
        }

        return DiscountAuthorization::create([
            'store_id' => $session->store_id,
            'requested_by' => $seller->id,
            'cash_session_id' => $session->id,
            'status' => DiscountAuthorizationStatus::Pending,
            'sale_snapshot' => $saleData,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'discount_percent' => $discountPercent,
            'reason' => $reason,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    /**
     * Approve a pending authorization.
     */
    public function approve(DiscountAuthorization $authorization, User $supervisor): DiscountAuthorization
    {
        return DB::transaction(function () use ($authorization, $supervisor): DiscountAuthorization {
            $locked = DiscountAuthorization::lockForUpdate()->find($authorization->id);

            if ($locked === null || ! $locked->isPending()) {
                throw new RuntimeException('La solicitud ya no está pendiente o ha expirado.');
            }

            if (! $supervisor->can('products.edit_price')) {
                throw new RuntimeException('No tienes permiso para autorizar descuentos.');
            }

            $locked->forceFill([
                'status' => DiscountAuthorizationStatus::Approved,
                'authorized_by' => $supervisor->id,
                'resolved_at' => now(),
            ])->save();

            return $locked->refresh();
        });
    }

    /**
     * Reject a pending authorization.
     */
    public function reject(DiscountAuthorization $authorization, User $supervisor, string $reason): DiscountAuthorization
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del rechazo es obligatorio.');
        }

        return DB::transaction(function () use ($authorization, $supervisor, $reason): DiscountAuthorization {
            $locked = DiscountAuthorization::lockForUpdate()->find($authorization->id);

            if ($locked === null || ! $locked->isPending()) {
                throw new RuntimeException('La solicitud ya no está pendiente o ha expirado.');
            }

            $locked->forceFill([
                'status' => DiscountAuthorizationStatus::Rejected,
                'authorized_by' => $supervisor->id,
                'rejection_reason' => $reason,
                'resolved_at' => now(),
            ])->save();

            return $locked->refresh();
        });
    }

    /**
     * Mark an approved authorization as consumed by a sale.
     */
    public function markUsed(DiscountAuthorization $authorization): void
    {
        if (! $authorization->isUsable()) {
            throw new RuntimeException('La autorización no es válida o ya fue utilizada.');
        }

        $authorization->forceFill([
            'status' => DiscountAuthorizationStatus::Used,
        ])->save();
    }

    /**
     * Retrieve pending authorizations for a user (Dueño sees all stores; other supervisors see their store).
     *
     * @return Collection<int, DiscountAuthorization>
     */
    public function pendingForUser(User $user): Collection
    {
        $query = DiscountAuthorization::withoutGlobalScope(StoreScope::class)
            ->where('status', DiscountAuthorizationStatus::Pending)
            ->where('expires_at', '>', now())
            ->with(['requester', 'store'])
            ->orderByDesc('created_at');

        if (! $user->hasRole('Dueño')) {
            $query->where('store_id', $user->store_id);
        }

        return $query->get();
    }

    /**
     * Count pending authorizations for a user.
     */
    public function pendingCountForUser(User $user): int
    {
        $query = DiscountAuthorization::withoutGlobalScope(StoreScope::class)
            ->where('status', DiscountAuthorizationStatus::Pending)
            ->where('expires_at', '>', now());

        if (! $user->hasRole('Dueño')) {
            $query->where('store_id', $user->store_id);
        }

        return $query->count();
    }

    /**
     * Retrieve pending authorizations for a store (supervisor view).
     *
     * @return Collection<int, DiscountAuthorization>
     */
    public function pendingForStore(int $storeId): Collection
    {
        return DiscountAuthorization::withoutGlobalScope(StoreScope::class)
            ->where('store_id', $storeId)
            ->where('status', DiscountAuthorizationStatus::Pending)
            ->where('expires_at', '>', now())
            ->with(['requester', 'store'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Count pending authorizations for a store (badge count).
     */
    public function pendingCount(int $storeId): int
    {
        return DiscountAuthorization::withoutGlobalScope(StoreScope::class)
            ->where('store_id', $storeId)
            ->where('status', DiscountAuthorizationStatus::Pending)
            ->where('expires_at', '>', now())
            ->count();
    }
}
