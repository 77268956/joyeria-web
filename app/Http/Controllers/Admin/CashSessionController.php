<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CashMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CashMovementRequest;
use App\Http\Requests\Admin\CloseCashSessionRequest;
use App\Http\Requests\Admin\OpenCashSessionRequest;
use App\Models\CashSession;
use App\Services\CashSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;

class CashSessionController extends Controller
{
    public function __construct(protected CashSessionService $cash) {}

    /**
     * Display the current shift, its movements and the closing history.
     */
    public function index(): View
    {
        $user = Auth::user();
        $session = $this->cash->currentSession($user);

        return view('admin.cash.index', [
            'session' => $session?->load(['movements.user', 'user']),
            'expected' => $session ? $this->cash->expectedTotals($session) : null,
            'movementTypes' => CashMovementType::cases(),
            'history' => CashSession::query()
                ->with(['user', 'closedBy', 'supervisor'])
                ->where('status', 'closed')
                ->latest('closed_at')
                ->paginate(10),
        ]);
    }

    /**
     * Open a new shift with the declared opening cash float.
     */
    public function store(OpenCashSessionRequest $request): RedirectResponse
    {
        try {
            $session = $this->cash->open(Auth::user(), (string) $request->validated('opening_amount'));
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        activity('caja')
            ->performedOn($session)
            ->causedBy(Auth::user())
            ->withProperties(['opening_amount' => (string) $session->opening_amount])
            ->log('Apertura de turno de caja');

        return redirect()->route('admin.cash.index')
            ->with('status', 'Turno de caja abierto con '.number_format((float) $session->opening_amount, 2).'.');
    }

    /**
     * Register a justified cash injection or withdrawal.
     */
    public function movement(CashMovementRequest $request): RedirectResponse
    {
        $session = $this->cash->currentSession(Auth::user());

        if ($session === null) {
            return back()->withErrors(['error' => 'No hay un turno de caja abierto.']);
        }

        $validated = $request->validated();

        try {
            $movement = $this->cash->registerMovement(
                $session,
                CashMovementType::from($validated['type']),
                (string) $validated['amount'],
                $validated['reason'],
                Auth::user(),
            );
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        activity('caja')
            ->performedOn($session)
            ->causedBy(Auth::user())
            ->withProperties([
                'type' => $movement->type->value,
                'amount' => (string) $movement->amount,
                'reason' => $movement->reason,
            ])
            ->log('Movimiento de caja registrado');

        return redirect()->route('admin.cash.index')
            ->with('status', 'Movimiento de caja registrado y auditado.');
    }

    /**
     * Close the current shift with the counted amounts (arqueo).
     */
    public function close(CloseCashSessionRequest $request): RedirectResponse
    {
        $session = $this->cash->currentSession(Auth::user());

        if ($session === null) {
            return back()->withErrors(['error' => 'No hay un turno de caja abierto.']);
        }

        $validated = $request->validated();

        try {
            $closed = $this->cash->close(
                $session,
                [
                    'counted_cash' => (string) $validated['counted_cash'],
                    'counted_card' => (string) $validated['counted_card'],
                    'counted_transfer' => (string) $validated['counted_transfer'],
                ],
                Auth::user(),
                $validated['close_note'] ?? null,
                $validated['supervisor_pin'] ?? null,
            );
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['error' => $exception->getMessage()]);
        }

        $log = activity('caja')
            ->performedOn($closed)
            ->causedBy(Auth::user())
            ->withProperties([
                'difference_total' => (string) $closed->difference_total,
                'supervisor_id' => $closed->supervisor_id,
                'supervisor_name' => $closed->supervisor?->name,
            ]);

        if ($closed->supervisor) {
            $log->log("Cierre de turno con diferencia de {$closed->difference_total} autorizado por {$closed->supervisor->name}");
        } else {
            $log->log('Cierre de turno de caja');
        }

        return redirect()->route('admin.cash.index')
            ->with('status', 'Turno cerrado. Diferencia: '.number_format((float) $closed->difference_total, 2).'.');
    }
}
