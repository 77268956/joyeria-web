<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\MetalPurchase;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;

/**
 * Builds the exportable administrative reports.
 *
 * Every report is normalised to the same shape so the CSV, PDF and HTML
 * renderers can consume it without knowing the underlying domain model:
 * a list of column headings, a list of row arrays and a list of totals.
 */
class ReportService
{
    /**
     * The reports that can be produced, keyed by their URL slug.
     *
     * @return array<string, array{title: string, description: string, dated: bool}>
     */
    public function catalogue(): array
    {
        return [
            'ventas' => [
                'title' => 'Reporte de Ventas',
                'description' => 'Ventas realizadas en el período, con cliente, vendedor y estado.',
                'dated' => true,
            ],
            'compras' => [
                'title' => 'Reporte de Compras de Metal',
                'description' => 'Compras de metal a clientes, con gramaje y monto pagado.',
                'dated' => true,
            ],
            'inventario' => [
                'title' => 'Reporte de Inventario',
                'description' => 'Piezas disponibles con su valoración al costo y a precio de venta.',
                'dated' => false,
            ],
            'caja' => [
                'title' => 'Reporte de Caja',
                'description' => 'Turnos de caja del período con arqueo esperado, contado y diferencia.',
                'dated' => true,
            ],
        ];
    }

    /**
     * Build one report normalised for the renderers.
     *
     * @return array{
     *     key: string,
     *     title: string,
     *     description: string,
     *     dated: bool,
     *     columns: array<int, string>,
     *     rows: array<int, array<int, string>>,
     *     totals: array<int, array{label: string, value: string}>,
     *     from: ?string,
     *     to: ?string
     * }|null
     */
    public function build(string $report, ?string $from = null, ?string $to = null): ?array
    {
        $catalogue = $this->catalogue();

        if (! array_key_exists($report, $catalogue)) {
            return null;
        }

        [$fromDate, $toDate] = $this->normaliseRange($from, $to);

        $data = match ($report) {
            'ventas' => $this->sales($fromDate, $toDate),
            'compras' => $this->purchases($fromDate, $toDate),
            'inventario' => $this->inventory(),
            'caja' => $this->cash($fromDate, $toDate),
        };

        return array_merge($catalogue[$report], [
            'key' => $report,
            'from' => $catalogue[$report]['dated'] ? $fromDate->toDateString() : null,
            'to' => $catalogue[$report]['dated'] ? $toDate->toDateString() : null,
        ], $data);
    }

    /**
     * Sales report within the given range.
     *
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>}
     */
    protected function sales(Carbon $from, Carbon $to): array
    {
        $sales = Sale::query()
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->with(['customer', 'user', 'payments'])
            ->latest('id')
            ->get();

        $count = 0;
        $subtotal = '0.00';
        $discount = '0.00';
        $total = '0.00';
        $byMethod = [];

        $rows = $sales->map(function (Sale $sale) use (&$count, &$subtotal, &$discount, &$total, &$byMethod): array {
            $count++;
            $subtotal = bcadd($subtotal, $this->money($sale->subtotal), 2);
            $discount = bcadd($discount, $this->money($sale->discount_total), 2);
            $total = bcadd($total, $this->money($sale->total), 2);

            foreach ($sale->payments as $payment) {
                $method = $payment->method->label();
                $byMethod[$method] = bcadd($byMethod[$method] ?? '0.00', $this->money($payment->amount), 2);
            }

            return [
                $sale->number,
                $sale->created_at?->format('d/m/Y H:i') ?? '—',
                $sale->customer?->full_name ?? 'Público general',
                $sale->user?->name ?? '—',
                $this->formatMoney($sale->subtotal),
                $this->formatMoney($sale->discount_total),
                $this->formatMoney($sale->total),
                $sale->status?->label() ?? '—',
            ];
        })->all();

        $totals = [
            ['label' => 'Ventas', 'value' => (string) $count],
            ['label' => 'Subtotal', 'value' => $this->formatMoney($subtotal)],
            ['label' => 'Descuentos', 'value' => $this->formatMoney($discount)],
            ['label' => 'Total vendido', 'value' => $this->formatMoney($total)],
        ];

        foreach ($byMethod as $method => $amount) {
            $totals[] = ['label' => 'Cobrado '.$method, 'value' => $this->formatMoney($amount)];
        }

        return [
            'columns' => ['Folio', 'Fecha', 'Cliente', 'Vendedor', 'Subtotal', 'Descuento', 'Total', 'Estado'],
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * Metal purchases report within the given range.
     *
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>}
     */
    protected function purchases(Carbon $from, Carbon $to): array
    {
        $purchases = MetalPurchase::query()
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->with(['customer', 'user', 'items.metal'])
            ->latest('id')
            ->get();

        $count = 0;
        $amount = '0.00';
        $grams = '0.000';
        $byMetal = [];

        $rows = $purchases->map(function (MetalPurchase $purchase) use (&$count, &$amount, &$grams, &$byMetal): array {
            $count++;
            $amount = bcadd($amount, $this->money($purchase->total_amount), 2);

            $purchaseGrams = '0.000';

            foreach ($purchase->items as $item) {
                $purchaseGrams = bcadd($purchaseGrams, $this->weight($item->net_weight_g), 3);

                $metal = $item->metal?->name ?? 'Sin metal';
                $byMetal[$metal] = bcadd($byMetal[$metal] ?? '0.000', $this->weight($item->net_weight_g), 3);
            }

            $grams = bcadd($grams, $purchaseGrams, 3);

            return [
                $purchase->number,
                $purchase->created_at?->format('d/m/Y H:i') ?? '—',
                $purchase->customer?->full_name ?? '—',
                $purchase->user?->name ?? '—',
                $this->formatWeight($purchaseGrams),
                $this->formatMoney($purchase->total_amount),
                $purchase->status?->label() ?? '—',
            ];
        })->all();

        $totals = [
            ['label' => 'Compras', 'value' => (string) $count],
            ['label' => 'Monto pagado', 'value' => $this->formatMoney($amount)],
            ['label' => 'Gramos adquiridos', 'value' => $this->formatWeight($grams)],
        ];

        foreach ($byMetal as $metal => $weight) {
            $totals[] = ['label' => 'Gramos '.$metal, 'value' => $this->formatWeight($weight)];
        }

        return [
            'columns' => ['Folio', 'Fecha', 'Cliente', 'Registrada por', 'Peso (g)', 'Monto', 'Estado'],
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * In-stock inventory report with cost and sale valuation.
     *
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>}
     */
    protected function inventory(): array
    {
        $products = Product::query()
            ->inStock()
            ->with(['category', 'metal', 'purity'])
            ->orderBy('name')
            ->get();

        $count = 0;
        $cost = '0.00';
        $price = '0.00';

        $rows = $products->map(function (Product $product) use (&$count, &$cost, &$price): array {
            $count++;
            $cost = bcadd($cost, $this->money($product->cost), 2);
            $price = bcadd($price, $this->money($product->price), 2);

            return [
                $product->sku,
                $product->name,
                $product->category?->name ?? '—',
                $product->metal?->name ?? '—',
                $product->purity?->label ?? '—',
                $this->formatWeight($product->weight_g),
                $this->formatMoney($product->cost),
                $this->formatMoney($product->price),
                $product->status?->label() ?? '—',
            ];
        })->all();

        $margin = bcsub($price, $cost, 2);

        return [
            'columns' => ['SKU', 'Pieza', 'Categoría', 'Metal', 'Pureza', 'Peso (g)', 'Costo', 'Precio', 'Estado'],
            'rows' => $rows,
            'totals' => [
                ['label' => 'Piezas en stock', 'value' => (string) $count],
                ['label' => 'Valor al costo', 'value' => $this->formatMoney($cost)],
                ['label' => 'Valor a precio de venta', 'value' => $this->formatMoney($price)],
                ['label' => 'Margen estimado', 'value' => $this->formatMoney($margin)],
            ],
        ];
    }

    /**
     * Cash-shift report within the given range.
     *
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>}
     */
    protected function cash(Carbon $from, Carbon $to): array
    {
        $sessions = CashSession::query()
            ->whereDate('opened_at', '>=', $from->toDateString())
            ->whereDate('opened_at', '<=', $to->toDateString())
            ->with('user')
            ->latest('id')
            ->get();

        $count = 0;
        $expected = '0.00';
        $counted = '0.00';
        $difference = '0.00';

        $rows = $sessions->map(function (CashSession $session) use (&$count, &$expected, &$counted, &$difference): array {
            $count++;
            $expected = bcadd($expected, $this->money($session->expected_cash), 2);
            $counted = bcadd($counted, $this->money($session->counted_cash), 2);
            $difference = bcadd($difference, $this->money($session->difference_total), 2);

            return [
                $session->user?->name ?? '—',
                $session->opened_at?->format('d/m/Y H:i') ?? '—',
                $session->closed_at?->format('d/m/Y H:i') ?? 'Abierto',
                $this->formatMoney($session->opening_amount),
                $this->formatMoney($session->expected_cash),
                $this->formatMoney($session->counted_cash),
                $this->formatMoney($session->difference_total),
                $session->status?->label() ?? '—',
            ];
        })->all();

        return [
            'columns' => ['Cajero', 'Apertura', 'Cierre', 'Fondo inicial', 'Efectivo esperado', 'Efectivo contado', 'Diferencia', 'Estado'],
            'rows' => $rows,
            'totals' => [
                ['label' => 'Turnos', 'value' => (string) $count],
                ['label' => 'Efectivo esperado', 'value' => $this->formatMoney($expected)],
                ['label' => 'Efectivo contado', 'value' => $this->formatMoney($counted)],
                ['label' => 'Diferencia total', 'value' => $this->formatMoney($difference)],
            ],
        ];
    }

    /**
     * Normalise the requested range, defaulting to the current month.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function normaliseRange(?string $from, ?string $to): array
    {
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->startOfMonth();
        $toDate = $to ? Carbon::parse($to)->startOfDay() : now()->startOfDay();

        if ($toDate->lessThan($fromDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return [$fromDate, $toDate];
    }

    /**
     * Format a decimal money string for display.
     */
    protected function formatMoney(string|int|float|null $value): string
    {
        return number_format((float) $this->money($value), 2);
    }

    /**
     * Format a decimal weight string for display.
     */
    protected function formatWeight(string|int|float|null $value): string
    {
        return number_format((float) $this->weight($value), 3);
    }

    /**
     * Normalise a monetary value to a two-decimal string without floats.
     */
    protected function money(string|int|float|null $value): string
    {
        return bcadd((string) ($value ?? '0'), '0', 2);
    }

    /**
     * Normalise a weight to a three-decimal string without floats.
     */
    protected function weight(string|int|float|null $value): string
    {
        return bcadd((string) ($value ?? '0'), '0', 3);
    }
}
