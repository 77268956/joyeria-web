<?php

namespace App\Http\Middleware;

use App\Services\CashSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks sales operations until the cashier has an open cash shift.
 */
class EnsureCashSessionIsOpen
{
    public function __construct(protected CashSessionService $cash) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $this->cash->currentSession($user) === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Debe abrir un turno de caja para operar.',
                ], 409);
            }

            return redirect()->route('admin.cash.index')
                ->withErrors(['error' => 'Debe abrir un turno de caja para usar el punto de venta.']);
        }

        return $next($request);
    }
}
