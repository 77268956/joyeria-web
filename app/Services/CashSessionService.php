<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\CashSessionStatus;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\LayawayPayment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Owns the cash-shift lifecycle: opening, justified movements and closing
 * with a method-by-method reconciliation (arqueo).
 */
class CashSessionService
{
    /**
     * The open shift of a cashier, if any.
     */
    public function currentSession(User $user): ?CashSession
    {
        return CashSession::query()
            ->where('user_id', $user->id)
            ->open()
            ->latest('id')
            ->first();
    }

    /**
     * Open a new shift with a declared opening cash float.
     */
    public function open(User $user, string $openingAmount): CashSession
    {
        if (bccomp($openingAmount, '0', 2) < 0) {
            throw new InvalidArgumentException('El efectivo inicial no puede ser negativo.');
        }

        return DB::transaction(function () use ($user, $openingAmount): CashSession {
            if ($this->currentSession($user) !== null) {
                throw new RuntimeException('Ya existe un turno de caja abierto para este usuario.');
            }

            return CashSession::create([
                'store_id' => $user->store_id,
                'user_id' => $user->id,
                'status' => CashSessionStatus::Open,
                'opened_at' => now(),
                'opening_amount' => $this->money($openingAmount),
            ]);
        });
    }

    /**
     * Register a justified cash injection or withdrawal.
     */
    public function registerMovement(CashSession $session, CashMovementType $type, string $amount, string $reason, User $user): CashMovement
    {
        if (bccomp($amount, '0', 2) <= 0) {
            throw new InvalidArgumentException('El monto del movimiento debe ser mayor a cero.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo del movimiento es obligatorio.');
        }

        if (! $session->isOpen()) {
            throw new RuntimeException('No se pueden registrar movimientos en un turno cerrado.');
        }

        return CashMovement::create([
            'store_id' => $session->store_id,
            'cash_session_id' => $session->id,
            'type' => $type,
            'amount' => $this->money($amount),
            'reason' => $reason,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Compute the expected cash, card and transfer totals of a shift.
     *
     * Only non-voided sales feed the totals; opening float and justified
     * movements affect cash exclusively.
     *
     * @return array{cash: string, card: string, transfer: string}
     */
    public function expectedTotals(CashSession $session): array
    {
        $payments = Payment::query()
            ->where('cash_session_id', $session->id)
            ->whereHas('sale', fn (Builder $query) => $query->where('status', SaleStatus::Completed->value))
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $movements = CashMovement::query()
            ->where('cash_session_id', $session->id)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $layawayPayments = LayawayPayment::query()
            ->where('cash_session_id', $session->id)
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $cash = bcadd($this->money($session->opening_amount), $this->money($payments[PaymentMethod::Cash->value] ?? '0'), 2);
        $cash = bcadd($cash, $this->money($layawayPayments[PaymentMethod::Cash->value] ?? '0'), 2);
        $cash = bcadd($cash, $this->money($movements[CashMovementType::In->value] ?? '0'), 2);
        $cash = bcsub($cash, $this->money($movements[CashMovementType::Out->value] ?? '0'), 2);

        return [
            'cash' => $cash,
            'card' => bcadd(
                $this->money($payments[PaymentMethod::Card->value] ?? '0'),
                $this->money($layawayPayments[PaymentMethod::Card->value] ?? '0'),
                2,
            ),
            'transfer' => bcadd(
                $this->money($payments[PaymentMethod::Transfer->value] ?? '0'),
                $this->money($layawayPayments[PaymentMethod::Transfer->value] ?? '0'),
                2,
            ),
        ];
    }

    /**
     * Close a shift, recording the counted amounts and the difference.
     *
     * If there is a discrepancy (difference != 0), a note and a supervisor PIN
     * (Gerente or Dueño) are required.
     *
     * @param  array{counted_cash: string, counted_card: string, counted_transfer: string}  $counted
     */
    public function close(CashSession $session, array $counted, User $user, ?string $note = null, ?string $supervisorPin = null): CashSession
    {
        if (! $session->isOpen()) {
            throw new RuntimeException('El turno ya está cerrado.');
        }

        return DB::transaction(function () use ($session, $counted, $user, $note, $supervisorPin): CashSession {
            $expected = $this->expectedTotals($session);

            $countedCash = $this->money($counted['counted_cash']);
            $countedCard = $this->money($counted['counted_card']);
            $countedTransfer = $this->money($counted['counted_transfer']);

            $difference = bcadd(
                bcadd(
                    bcsub($countedCash, $expected['cash'], 2),
                    bcsub($countedCard, $expected['card'], 2),
                    2,
                ),
                bcsub($countedTransfer, $expected['transfer'], 2),
                2,
            );

            $supervisor = null;

            if (bccomp($difference, '0', 2) !== 0) {
                if (blank($note)) {
                    throw new InvalidArgumentException('La nota de cierre es obligatoria cuando existe una diferencia.');
                }

                if (! empty($supervisorPin)) {
                    $supervisor = app(DiscountAuthorizationService::class)->authorizeByPin($supervisorPin, $session->store_id);
                } elseif (! $user->can('products.edit_price')) {
                    throw new InvalidArgumentException('El cierre con diferencia requiere el PIN de un supervisor (Gerente o Dueño).');
                } else {
                    $supervisor = $user;
                }
            }

            $session->forceFill([
                'status' => CashSessionStatus::Closed,
                'closed_at' => now(),
                'closed_by' => $user->id,
                'supervisor_id' => $supervisor?->id,
                'expected_cash' => $expected['cash'],
                'expected_card' => $expected['card'],
                'expected_transfer' => $expected['transfer'],
                'counted_cash' => $countedCash,
                'counted_card' => $countedCard,
                'counted_transfer' => $countedTransfer,
                'difference_total' => $difference,
                'close_note' => $note,
            ])->save();

            return $session->refresh();
        });
    }

    /**
     * Normalise a monetary value to a two-decimal string without floats.
     */
    protected function money(string|int|float|null $value): string
    {
        return bcadd((string) ($value ?? '0'), '0', 2);
    }
}
