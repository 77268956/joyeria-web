<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\CashSessionStatus;
use App\Enums\MetalPurchaseStatus;
use App\Enums\PaymentMethod;
use App\Enums\PayoutMethod;
use App\Enums\SaleStatus;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\LayawayPayment;
use App\Models\MetalPurchase;
use App\Models\Payment;
use App\Models\Sale;
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
     * Chronological record of every operation that moved money during a shift.
     *
     * Used by the cash view to show the day's history of the open shift,
     * including sales, layaway instalments, transfer payouts of metal
     * purchases and justified cash movements.
     *
     * @return array{
     *     sales: array<int, array{time: string, at: int, number: string, customer: string, total: string, status: string, is_voided: bool, payments: array<int, array{method: string, method_value: string|null, amount: string}>}>,
     *     layaway_payments: array<int, array{time: string, at: int, number: string, customer: string, method: string, method_value: string|null, amount: string}>,
     *     purchase_payouts: array<int, array{time: string, number: string, customer: string, amount: string, status: string}>,
     *     movements: array<int, array{time: string, at: int, type: string, reason: string, user: string, amount: string, is_in: bool}>,
     *     totals: array{sales_count: int, sales_total: string, layaway_total: string, purchases_transfer: string, cash_in: string, cash_out: string}
     * }
     */
    public function dayHistory(CashSession $session): array
    {
        $sales = Sale::query()
            ->where('cash_session_id', $session->id)
            ->with(['customer', 'payments'])
            ->orderBy('created_at')
            ->get();

        $layawayPayments = LayawayPayment::query()
            ->where('cash_session_id', $session->id)
            ->with(['layaway.customer'])
            ->orderBy('created_at')
            ->get();

        $purchasePayouts = MetalPurchase::query()
            ->where('cash_session_id', $session->id)
            ->where('payout_method', PayoutMethod::Transfer->value)
            ->whereIn('status', [MetalPurchaseStatus::Paid->value, MetalPurchaseStatus::Adjusted->value])
            ->with('customer')
            ->orderBy('created_at')
            ->get();

        $movements = CashMovement::query()
            ->where('cash_session_id', $session->id)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $salesCount = 0;
        $salesTotal = '0.00';

        $salesRows = $sales->map(function (Sale $sale) use (&$salesCount, &$salesTotal): array {
            $isVoided = $sale->status === SaleStatus::Voided;

            if (! $isVoided) {
                $salesCount++;
                $salesTotal = bcadd($salesTotal, $this->money($sale->total), 2);
            }

            return [
                'time' => $sale->created_at?->format('H:i') ?? '—',
                'at' => $sale->created_at?->timestamp ?? 0,
                'number' => $sale->number,
                'customer' => $sale->customer?->full_name ?? 'Público general',
                'total' => $this->money($sale->total),
                'status' => $sale->status?->label() ?? '—',
                'is_voided' => $isVoided,
                'payments' => $sale->payments->map(fn (Payment $payment): array => [
                    'method' => $payment->method?->label() ?? '—',
                    'method_value' => $payment->method?->value,
                    'amount' => $this->money($payment->amount),
                ])->all(),
            ];
        })->all();

        $layawayTotal = '0.00';

        $layawayRows = $layawayPayments->map(function (LayawayPayment $payment) use (&$layawayTotal): array {
            $layawayTotal = bcadd($layawayTotal, $this->money($payment->amount), 2);

            return [
                'time' => $payment->created_at?->format('H:i') ?? '—',
                'at' => $payment->created_at?->timestamp ?? 0,
                'number' => $payment->layaway?->number ?? '—',
                'customer' => $payment->layaway?->customer?->full_name ?? '—',
                'method' => $payment->method?->label() ?? '—',
                'method_value' => $payment->method?->value,
                'amount' => $this->money($payment->amount),
            ];
        })->all();

        $purchaseTotal = '0.00';

        $purchaseRows = $purchasePayouts->map(function (MetalPurchase $purchase) use (&$purchaseTotal): array {
            $purchaseTotal = bcadd($purchaseTotal, $this->money($purchase->total_amount), 2);

            return [
                'time' => $purchase->created_at?->format('H:i') ?? '—',
                'number' => $purchase->number,
                'customer' => $purchase->customer?->full_name ?? '—',
                'amount' => $this->money($purchase->total_amount),
                'status' => $purchase->status?->label() ?? '—',
            ];
        })->all();

        $cashIn = '0.00';
        $cashOut = '0.00';

        $movementRows = $movements->map(function (CashMovement $movement) use (&$cashIn, &$cashOut): array {
            $isIn = $movement->type === CashMovementType::In;

            if ($isIn) {
                $cashIn = bcadd($cashIn, $this->money($movement->amount), 2);
            } else {
                $cashOut = bcadd($cashOut, $this->money($movement->amount), 2);
            }

            return [
                'time' => $movement->created_at?->format('H:i') ?? '—',
                'at' => $movement->created_at?->timestamp ?? 0,
                'type' => $movement->type?->label() ?? '—',
                'reason' => $movement->reason,
                'user' => $movement->user?->name ?? '—',
                'amount' => $this->money($movement->amount),
                'is_in' => $isIn,
            ];
        })->all();

        return [
            'sales' => $salesRows,
            'layaway_payments' => $layawayRows,
            'purchase_payouts' => $purchaseRows,
            'movements' => $movementRows,
            'totals' => [
                'sales_count' => $salesCount,
                'sales_total' => $salesTotal,
                'layaway_total' => $layawayTotal,
                'purchases_transfer' => $purchaseTotal,
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
            ],
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
