<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DiscountAuthorizationStatus;
use App\Http\Controllers\Controller;
use App\Models\DiscountAuthorization;
use App\Services\CashSessionService;
use App\Services\DiscountAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class DiscountAuthorizationController extends Controller
{
    public function __construct(
        protected DiscountAuthorizationService $authorizations,
    ) {}

    /**
     * Validate a supervisor PIN for in-person POS authorization.
     */
    public function validatePin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:4'],
        ]);

        try {
            $supervisor = $this->authorizations->authorizeByPin(
                $request->input('pin'),
                Auth::user()->store_id,
            );

            return response()->json([
                'valid' => true,
                'supervisor_name' => $supervisor->name,
                'supervisor_id' => $supervisor->id,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'valid' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Create a remote discount authorization request (seller).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'sale_data' => ['required', 'array'],
            'subtotal' => ['required', 'numeric', 'min:0.01'],
            'discount_amount' => ['required', 'numeric', 'min:0.01'],
            'discount_percent' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $session = app(CashSessionService::class)->currentSession($user);

        if ($session === null) {
            return response()->json(['message' => 'Debe tener un turno de caja abierto.'], 409);
        }

        try {
            $authorization = $this->authorizations->requestRemote(
                $user,
                $session,
                $request->input('sale_data'),
                $request->input('subtotal'),
                $request->input('discount_amount'),
                $request->input('discount_percent'),
                $request->input('reason'),
            );

            activity('descuentos')
                ->performedOn($authorization)
                ->causedBy($user)
                ->withProperties([
                    'discount_percent' => $request->input('discount_percent'),
                    'discount_amount' => $request->input('discount_amount'),
                ])
                ->log("Solicitud de descuento del {$request->input('discount_percent')}%");

            return response()->json([
                'id' => $authorization->id,
                'status' => $authorization->status->value,
                'expires_at' => $authorization->expires_at->toISOString(),
            ], 201);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Check the status of a pending authorization (seller polls this).
     */
    public function status(DiscountAuthorization $authorization): JsonResponse
    {
        // Auto-expire stale pending requests
        if (
            $authorization->status === DiscountAuthorizationStatus::Pending
            && $authorization->expires_at->isPast()
        ) {
            $authorization->forceFill([
                'status' => DiscountAuthorizationStatus::Expired,
                'resolved_at' => now(),
            ])->save();
        }

        return response()->json([
            'id' => $authorization->id,
            'status' => $authorization->status->value,
            'authorized_by_name' => $authorization->authorizer?->name,
            'rejection_reason' => $authorization->rejection_reason,
        ]);
    }

    /**
     * List pending authorizations for the current store (supervisor view).
     */
    public function pending(): JsonResponse
    {
        $user = Auth::user();

        $pending = $this->authorizations->pendingForStore($user->store_id);

        return response()->json([
            'authorizations' => $pending->map(fn (DiscountAuthorization $auth): array => [
                'id' => $auth->id,
                'requester_name' => $auth->requester->name,
                'discount_percent' => (string) $auth->discount_percent,
                'discount_amount' => (string) $auth->discount_amount,
                'subtotal' => (string) $auth->subtotal,
                'reason' => $auth->reason,
                'created_at' => $auth->created_at->diffForHumans(),
                'expires_at' => $auth->expires_at->toISOString(),
            ]),
        ]);
    }

    /**
     * Approve a pending authorization (supervisor).
     */
    public function approve(DiscountAuthorization $authorization): JsonResponse
    {
        try {
            $this->authorizations->approve($authorization, Auth::user());

            activity('descuentos')
                ->performedOn($authorization)
                ->causedBy(Auth::user())
                ->log("Descuento del {$authorization->discount_percent}% aprobado");

            return response()->json(['status' => 'approved']);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Reject a pending authorization (supervisor).
     */
    public function reject(Request $request, DiscountAuthorization $authorization): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->authorizations->reject($authorization, Auth::user(), $request->input('reason'));

            activity('descuentos')
                ->performedOn($authorization)
                ->causedBy(Auth::user())
                ->log("Descuento del {$authorization->discount_percent}% rechazado");

            return response()->json(['status' => 'rejected']);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Badge count of pending authorizations (for nav bell).
     */
    public function count(): JsonResponse
    {
        return response()->json([
            'count' => $this->authorizations->pendingCount(Auth::user()->store_id),
        ]);
    }
}
