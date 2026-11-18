<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\LayawayStatus;
use App\Enums\MetalPurchaseStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProductStatus;
use App\Models\CashSession;
use App\Models\Layaway;
use App\Models\MetalPurchase;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Builds the exportable administrative reports and the reporting dashboard.
 *
 * Every report is normalised to the same shape so the CSV, PDF and HTML
 * renderers can consume it without knowing the underlying domain model:
 * a list of column headings, a list of row arrays, a list of totals and the
 * charts that summarise the period.
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
                'description' => 'Ventas realizadas en el período, con cliente, vendedor, piezas y cobro.',
                'dated' => true,
            ],
            'compras' => [
                'title' => 'Reporte de Compras de Metal',
                'description' => 'Compras de metal a clientes, con gramaje, valoración y monto pagado.',
                'dated' => true,
            ],
            'inventario' => [
                'title' => 'Reporte de Inventario',
                'description' => 'Piezas disponibles con su valoración al costo, a precio de venta y margen.',
                'dated' => false,
            ],
            'caja' => [
                'title' => 'Reporte de Caja',
                'description' => 'Turnos de caja del período con arqueo esperado, contado y diferencia.',
                'dated' => true,
            ],
            'apartados' => [
                'title' => 'Reporte de Apartados',
                'description' => 'Apartados del período con abonos, saldo pendiente, vencimientos y estado.',
                'dated' => true,
            ],
        ];
    }

    /**
     * Build the reporting dashboard summary for the given period.
     *
     * @return array{
     *     from: string,
     *     to: string,
     *     metrics: array<int, array{label: string, value: string, hint: string, tone: string, icon: string}>,
     *     charts: array<int, array<string, mixed>>,
     *     topProducts: array<int, array{name: string, pieces: int, total: string}>,
     *     topCustomers: array<int, array{name: string, sales: int, total: string}>,
     *     reportCount: int
     * }
     */
    public function overview(?string $from = null, ?string $to = null): array
    {
        [$fromDate, $toDate] = $this->normaliseRange($from, $to);

        $sales = Sale::query()
            ->completed()
            ->whereDate('created_at', '>=', $fromDate->toDateString())
            ->whereDate('created_at', '<=', $toDate->toDateString())
            ->with(['customer', 'payments', 'items'])
            ->get();

        $purchases = MetalPurchase::query()
            ->whereDate('created_at', '>=', $fromDate->toDateString())
            ->whereDate('created_at', '<=', $toDate->toDateString())
            ->with(['items.metal'])
            ->get();

        $products = Product::query()
            ->inStock()
            ->with(['metal', 'category'])
            ->get();

        $layaways = Layaway::query()->get();

        $cashSessions = CashSession::query()
            ->whereDate('opened_at', '>=', $fromDate->toDateString())
            ->whereDate('opened_at', '<=', $toDate->toDateString())
            ->get();

        $salesTotal = $this->sumMoney($sales, 'total');
        $purchaseTotal = $this->sumMoney($purchases, 'total_amount');
        $purchaseGrams = $this->sumWeight(
            $purchases->flatMap(fn (MetalPurchase $purchase) => $purchase->items),
            'net_weight_g',
        );
        $inventoryValue = $this->sumMoney($products, 'price');
        $activeLayaways = $layaways->filter(fn (Layaway $layaway) => $layaway->isActive());
        $activeBalance = $this->sumMoney($activeLayaways, 'balance');
        $cashDifference = $this->sumMoney($cashSessions, 'difference_total');
        $cashDifferences = $cashSessions
            ->filter(fn (CashSession $session) => bccomp($this->money($session->difference_total), '0', 2) !== 0)
            ->count();

        $averageTicket = $sales->isNotEmpty()
            ? bcdiv($salesTotal, (string) $sales->count(), 2)
            : '0.00';

        $salesSeries = $this->series(
            $fromDate,
            $toDate,
            $sales,
            fn (Sale $sale) => $sale->created_at,
            fn (Sale $sale) => $this->money($sale->total),
        );

        $purchaseSeries = $this->series(
            $fromDate,
            $toDate,
            $purchases,
            fn (MetalPurchase $purchase) => $purchase->created_at,
            fn (MetalPurchase $purchase) => $this->money($purchase->total_amount),
        );

        $inventoryByMetal = [];
        $inventoryByCategory = [];

        foreach ($products as $product) {
            $metal = $product->metal?->name ?? 'Sin metal';
            $category = $product->category?->name ?? 'Sin categoría';

            $inventoryByMetal[$metal] = bcadd(
                $inventoryByMetal[$metal] ?? '0.00',
                $this->money($product->price),
                2,
            );

            $inventoryByCategory[$category] = bcadd(
                $inventoryByCategory[$category] ?? '0.00',
                $this->money($product->price),
                2,
            );
        }

        $layawayCounts = [];

        foreach (LayawayStatus::cases() as $status) {
            $count = $layaways->filter(fn (Layaway $layaway) => $layaway->status === $status)->count();

            if ($count > 0) {
                $layawayCounts[$status->label()] = $count;
            }
        }

        return [
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'metrics' => [
                [
                    'label' => 'Ventas del período',
                    'value' => $this->formatMoney($salesTotal),
                    'hint' => $sales->count().' operaciones',
                    'tone' => 'gold',
                    'icon' => 'sales',
                ],
                [
                    'label' => 'Compras de metal',
                    'value' => $this->formatMoney($purchaseTotal),
                    'hint' => $this->formatWeight($purchaseGrams).' g adquiridos',
                    'tone' => 'info',
                    'icon' => 'purchases',
                ],
                [
                    'label' => 'Ticket promedio',
                    'value' => $this->formatMoney($averageTicket),
                    'hint' => $sales->count().' ventas registradas',
                    'tone' => 'success',
                    'icon' => 'ticket',
                ],
                [
                    'label' => 'Inventario a precio de venta',
                    'value' => $this->formatMoney($inventoryValue),
                    'hint' => $products->count().' piezas en stock',
                    'tone' => 'night',
                    'icon' => 'inventory',
                ],
                [
                    'label' => 'Saldo en apartados activos',
                    'value' => $this->formatMoney($activeBalance),
                    'hint' => $activeLayaways->count().' apartados activos',
                    'tone' => 'warning',
                    'icon' => 'layaways',
                ],
                [
                    'label' => 'Diferencia de caja',
                    'value' => $this->formatMoney($cashDifference),
                    'hint' => $cashDifferences.' turnos con diferencia',
                    'tone' => bccomp($cashDifference, '0', 2) === 0 ? 'success' : 'danger',
                    'icon' => 'cash',
                ],
            ],
            'charts' => [
                $this->chart(
                    'overview-sales-purchases',
                    'Ventas y compras',
                    'Evolución del período',
                    'line',
                    $salesSeries['labels'],
                    [
                        ['label' => 'Ventas', 'data' => $salesSeries['totals'], 'color' => 'gold', 'fill' => true],
                        ['label' => 'Compras', 'data' => $purchaseSeries['totals'], 'color' => 'blue'],
                    ],
                    'money',
                ),
                $this->paymentChart(
                    'overview-payments',
                    $sales->flatMap(fn (Sale $sale) => $sale->payments),
                    'Métodos de pago',
                    'Cobros del período',
                ),
                $this->chart(
                    'overview-inventory',
                    'Inventario por metal',
                    'Valor a precio de venta',
                    'bar',
                    array_keys($this->sortMoneyDesc($inventoryByMetal)),
                    [
                        [
                            'label' => 'Valor',
                            'data' => array_values($this->sortMoneyDesc($inventoryByMetal)),
                            'color' => 'gold',
                        ],
                    ],
                    'money',
                    true,
                ),
                $this->chart(
                    'overview-layaways',
                    'Apartados por estado',
                    'Todos los apartados registrados',
                    'doughnut',
                    array_keys($layawayCounts),
                    [
                        [
                            'label' => 'Apartados',
                            'data' => array_values($layawayCounts),
                            'colors' => ['gold', 'success', 'danger'],
                        ],
                    ],
                    'number',
                ),
            ],
            'topProducts' => $this->topProducts($sales),
            'topCustomers' => $this->topCustomers($sales),
            'reportCount' => count($this->catalogue()),
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
     *     charts: array<int, array<string, mixed>>,
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
            'apartados' => $this->layaways($fromDate, $toDate),
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
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>, charts: array<int, array<string, mixed>>}
     */
    protected function sales(Carbon $from, Carbon $to): array
    {
        $sales = Sale::query()
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->with(['customer', 'user', 'payments', 'items'])
            ->latest('id')
            ->get();

        $count = 0;
        $pieces = 0;
        $subtotal = '0.00';
        $discount = '0.00';
        $total = '0.00';
        $byMethod = [];
        $bySeller = [];
        $byCustomer = [];
        $byProduct = [];

        $rows = $sales->map(function (Sale $sale) use (&$count, &$pieces, &$subtotal, &$discount, &$total, &$byMethod, &$bySeller, &$byCustomer, &$byProduct): array {
            $count++;
            $salePieces = $sale->items->count();
            $pieces += $salePieces;
            $subtotal = bcadd($subtotal, $this->money($sale->subtotal), 2);
            $discount = bcadd($discount, $this->money($sale->discount_total), 2);
            $total = bcadd($total, $this->money($sale->total), 2);

            foreach ($sale->items as $item) {
                $product = $item->name_snapshot;
                $byProduct[$product] = bcadd($byProduct[$product] ?? '0.00', $this->money($item->line_total), 2);
            }

            foreach ($sale->payments as $payment) {
                $method = $payment->method?->label() ?? 'Otro';
                $byMethod[$method] = bcadd($byMethod[$method] ?? '0.00', $this->money($payment->amount), 2);
            }

            $seller = $sale->user?->name ?? '—';
            $bySeller[$seller] = bcadd($bySeller[$seller] ?? '0.00', $this->money($sale->total), 2);

            $customer = $sale->customer?->full_name ?? 'Público general';
            $byCustomer[$customer] = bcadd($byCustomer[$customer] ?? '0.00', $this->money($sale->total), 2);

            $paymentLabels = $sale->payments
                ->map(fn ($payment) => $payment->method?->label() ?? 'Otro')
                ->unique()
                ->implode(' + ');

            return [
                $sale->number,
                $sale->created_at?->format('d/m/Y H:i') ?? '—',
                $customer,
                $seller,
                (string) $salePieces,
                $this->formatMoney($sale->subtotal),
                $this->formatMoney($sale->discount_total),
                $this->formatMoney($sale->total),
                $paymentLabels !== '' ? $paymentLabels : '—',
                $sale->status?->label() ?? '—',
            ];
        })->all();

        $average = $count > 0 ? bcdiv($total, (string) $count, 2) : '0.00';

        $totals = [
            ['label' => 'Ventas', 'value' => (string) $count],
            ['label' => 'Piezas vendidas', 'value' => (string) $pieces],
            ['label' => 'Subtotal', 'value' => $this->formatMoney($subtotal)],
            ['label' => 'Descuentos', 'value' => $this->formatMoney($discount)],
            ['label' => 'Total vendido', 'value' => $this->formatMoney($total)],
            ['label' => 'Ticket promedio', 'value' => $this->formatMoney($average)],
        ];

        foreach ($this->orderedPaymentTotals($byMethod) as $method => $amount) {
            $totals[] = ['label' => 'Cobrado '.$method, 'value' => $this->formatMoney($amount)];
        }

        $series = $this->series(
            $from,
            $to,
            $sales,
            fn (Sale $sale) => $sale->created_at,
            fn (Sale $sale) => $this->money($sale->total),
        );

        $sellers = $this->topTotals($bySeller, 6);
        $products = $this->topTotals($byProduct, 6);
        $productLabels = array_map(
            fn (string $product): string => Str::limit($product, 26, '…'),
            array_keys($products),
        );

        return [
            'columns' => ['Folio', 'Fecha', 'Cliente', 'Vendedor', 'Piezas', 'Subtotal', 'Descuento', 'Total', 'Cobro', 'Estado'],
            'rows' => $rows,
            'totals' => $totals,
            'charts' => [
                $this->chart(
                    'sales-trend',
                    'Tendencia de ventas',
                    'Total vendido por día',
                    'line',
                    $series['labels'],
                    [
                        ['label' => 'Ventas', 'data' => $series['totals'], 'color' => 'gold', 'fill' => true],
                    ],
                    'money',
                ),
                $this->paymentChart(
                    'sales-payments',
                    $sales->flatMap(fn (Sale $sale) => $sale->payments),
                    'Métodos de pago',
                    'Cobros del período',
                ),
                $this->chart(
                    'sales-sellers',
                    'Ventas por vendedor',
                    'Top 6 del período',
                    'bar',
                    array_keys($sellers),
                    [
                        [
                            'label' => 'Total vendido',
                            'data' => array_values($sellers),
                            'color' => 'night',
                        ],
                    ],
                    'money',
                    true,
                ),
                $this->chart(
                    'sales-products',
                    'Productos más vendidos',
                    'Top 6 por importe',
                    'bar',
                    $productLabels,
                    [
                        [
                            'label' => 'Total vendido',
                            'data' => array_values($products),
                            'color' => 'gold',
                        ],
                    ],
                    'money',
                    true,
                ),
            ],
        ];
    }

    /**
     * Metal purchases report within the given range.
     *
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>, charts: array<int, array<string, mixed>>}
     */
    protected function purchases(Carbon $from, Carbon $to): array
    {
        $purchases = MetalPurchase::query()
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->with(['customer', 'user', 'items.metal', 'items.purity'])
            ->latest('id')
            ->get();

        $count = 0;
        $pieces = 0;
        $amount = '0.00';
        $grams = '0.000';
        $appraised = '0.00';
        $byMetal = [];
        $byStatus = [];
        $byCustomer = [];

        $rows = $purchases->map(function (MetalPurchase $purchase) use (&$count, &$pieces, &$amount, &$grams, &$appraised, &$byMetal, &$byStatus, &$byCustomer): array {
            $count++;
            $pieces += $purchase->items->count();
            $amount = bcadd($amount, $this->money($purchase->total_amount), 2);

            $purchaseGrams = '0.000';
            $purchaseAppraised = '0.00';

            foreach ($purchase->items as $item) {
                $purchaseGrams = bcadd($purchaseGrams, $this->weight($item->net_weight_g), 3);
                $purchaseAppraised = bcadd($purchaseAppraised, $this->money($item->appraisedAmount()), 2);

                $metal = $item->metal?->name ?? 'Sin metal';
                $byMetal[$metal] = bcadd($byMetal[$metal] ?? '0.000', $this->weight($item->net_weight_g), 3);
            }

            $grams = bcadd($grams, $purchaseGrams, 3);
            $appraised = bcadd($appraised, $purchaseAppraised, 2);

            $status = $purchase->status?->label() ?? '—';
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            $customer = $purchase->customer?->full_name ?? '—';
            $byCustomer[$customer] = bcadd($byCustomer[$customer] ?? '0.00', $this->money($purchase->total_amount), 2);

            return [
                $purchase->number,
                $purchase->created_at?->format('d/m/Y H:i') ?? '—',
                $customer,
                $purchase->user?->name ?? '—',
                (string) $purchase->items->count(),
                $this->formatWeight($purchaseGrams),
                $this->formatMoney($purchaseAppraised),
                $this->formatMoney($purchase->total_amount),
                $status,
            ];
        })->all();

        $negotiation = bcsub($amount, $appraised, 2);
        $averageGram = bccomp($grams, '0', 3) === 1 ? bcdiv($amount, $grams, 2) : '0.00';
        $averagePurchase = $count > 0 ? bcdiv($amount, (string) $count, 2) : '0.00';

        $totals = [
            ['label' => 'Compras', 'value' => (string) $count],
            ['label' => 'Piezas adquiridas', 'value' => (string) $pieces],
            ['label' => 'Gramos adquiridos', 'value' => $this->formatWeight($grams)],
            ['label' => 'Monto pagado', 'value' => $this->formatMoney($amount)],
            ['label' => 'Valor del sistema', 'value' => $this->formatMoney($appraised)],
            ['label' => 'Diferencia negociada', 'value' => $this->formatMoney($negotiation)],
            ['label' => 'Precio promedio por gramo', 'value' => $this->formatMoney($averageGram)],
            ['label' => 'Ticket promedio', 'value' => $this->formatMoney($averagePurchase)],
        ];

        foreach ($byMetal as $metal => $weight) {
            $totals[] = ['label' => 'Gramos '.$metal, 'value' => $this->formatWeight($weight)];
        }

        $series = $this->series(
            $from,
            $to,
            $purchases,
            fn (MetalPurchase $purchase) => $purchase->created_at,
            fn (MetalPurchase $purchase) => $this->money($purchase->total_amount),
        );

        $customers = $this->topTotals($byCustomer, 6);
        $statusTotals = [];

        foreach (MetalPurchaseStatus::cases() as $status) {
            $total = $byStatus[$status->label()] ?? 0;

            if ($total > 0) {
                $statusTotals[$status->label()] = $total;
            }
        }

        return [
            'columns' => ['Folio', 'Fecha', 'Cliente', 'Registrada por', 'Piezas', 'Peso (g)', 'Valor sistema', 'Monto pagado', 'Estado'],
            'rows' => $rows,
            'totals' => $totals,
            'charts' => [
                $this->chart(
                    'purchases-trend',
                    'Compras de metal',
                    'Monto pagado por día',
                    'bar',
                    $series['labels'],
                    [
                        [
                            'label' => 'Monto pagado',
                            'data' => $series['totals'],
                            'color' => 'gold',
                        ],
                    ],
                    'money',
                ),
                $this->chart(
                    'purchases-metals',
                    'Gramos por metal',
                    'Distribución del período',
                    'doughnut',
                    array_keys($this->sortWeightDesc($byMetal)),
                    [
                        [
                            'label' => 'Gramos',
                            'data' => array_values($this->sortWeightDesc($byMetal)),
                            'colors' => ['gold', 'night', 'blue', 'stone', 'success', 'danger'],
                        ],
                    ],
                    'weight',
                ),
                $this->chart(
                    'purchases-customers',
                    'Compras por cliente',
                    'Top 6 del período',
                    'bar',
                    array_keys($customers),
                    [
                        [
                            'label' => 'Monto pagado',
                            'data' => array_values($customers),
                            'color' => 'night',
                        ],
                    ],
                    'money',
                    true,
                ),
                $this->chart(
                    'purchases-status',
                    'Compras por estado',
                    'Distribución del período',
                    'doughnut',
                    array_keys($statusTotals),
                    [
                        [
                            'label' => 'Compras',
                            'data' => array_values($statusTotals),
                            'colors' => ['warning', 'success', 'danger', 'blue'],
                        ],
                    ],
                    'number',
                ),
            ],
        ];
    }

    /**
     * In-stock inventory report with cost, sale valuation and margin.
     *
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>, charts: array<int, array<string, mixed>>}
     */
    protected function inventory(): array
    {
        $products = Product::query()
            ->inStock()
            ->with(['category', 'metal', 'purity', 'location'])
            ->orderBy('name')
            ->get();

        $count = 0;
        $weight = '0.000';
        $cost = '0.00';
        $price = '0.00';
        $byMetal = [];
        $byCategory = [];
        $byStatus = [];
        $byLocation = [];

        $rows = $products->map(function (Product $product) use (&$count, &$weight, &$cost, &$price, &$byMetal, &$byCategory, &$byStatus, &$byLocation): array {
            $count++;
            $weight = bcadd($weight, $this->weight($product->weight_g), 3);
            $cost = bcadd($cost, $this->money($product->cost), 2);
            $price = bcadd($price, $this->money($product->price), 2);

            $margin = bcsub($this->money($product->price), $this->money($product->cost), 2);
            $marginPercent = bccomp($this->money($product->price), '0', 2) === 1
                ? bcdiv(bcmul($margin, '100', 2), $this->money($product->price), 1)
                : '0.0';

            $metal = $product->metal?->name ?? 'Sin metal';
            $category = $product->category?->name ?? 'Sin categoría';
            $location = $product->location?->name ?? 'Sin ubicación';
            $status = $product->status?->label() ?? '—';

            $byMetal[$metal] = bcadd($byMetal[$metal] ?? '0.00', $this->money($product->price), 2);
            $byCategory[$category] = bcadd($byCategory[$category] ?? '0.00', $this->money($product->price), 2);
            $byLocation[$location] = bcadd($byLocation[$location] ?? '0.00', $this->money($product->price), 2);
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            return [
                $product->sku,
                $product->name,
                $category,
                $metal,
                $product->purity?->label ?? '—',
                $this->formatWeight($product->weight_g),
                $this->formatMoney($product->cost),
                $this->formatMoney($product->price),
                $this->formatMoney($margin),
                $marginPercent.' %',
                $product->location?->name ?? '—',
                $status,
            ];
        })->all();

        $margin = bcsub($price, $cost, 2);
        $marginPercent = bccomp($price, '0', 2) === 1
            ? bcdiv(bcmul($margin, '100', 2), $price, 1)
            : '0.0';
        $averagePrice = $count > 0 ? bcdiv($price, (string) $count, 2) : '0.00';
        $reserved = $products->filter(fn (Product $product) => $product->status === ProductStatus::Reserved)->count();

        $statusTotals = [];

        foreach (ProductStatus::cases() as $status) {
            $total = $byStatus[$status->label()] ?? 0;

            if ($total > 0) {
                $statusTotals[$status->label()] = $total;
            }
        }

        return [
            'columns' => ['SKU', 'Pieza', 'Categoría', 'Metal', 'Pureza', 'Peso (g)', 'Costo', 'Precio', 'Margen', 'Margen %', 'Ubicación', 'Estado'],
            'rows' => $rows,
            'totals' => [
                ['label' => 'Piezas en stock', 'value' => (string) $count],
                ['label' => 'Peso total', 'value' => $this->formatWeight($weight).' g'],
                ['label' => 'Valor al costo', 'value' => $this->formatMoney($cost)],
                ['label' => 'Valor a precio de venta', 'value' => $this->formatMoney($price)],
                ['label' => 'Margen estimado', 'value' => $this->formatMoney($margin)],
                ['label' => 'Margen promedio', 'value' => $marginPercent.' %'],
                ['label' => 'Precio promedio', 'value' => $this->formatMoney($averagePrice)],
                ['label' => 'Piezas apartadas', 'value' => (string) $reserved],
            ],
            'charts' => [
                $this->chart(
                    'inventory-metals',
                    'Valor por metal',
                    'Precio de venta del inventario',
                    'bar',
                    array_keys($this->sortMoneyDesc($byMetal)),
                    [
                        [
                            'label' => 'Valor',
                            'data' => array_values($this->sortMoneyDesc($byMetal)),
                            'color' => 'gold',
                        ],
                    ],
                    'money',
                    true,
                ),
                $this->chart(
                    'inventory-categories',
                    'Valor por categoría',
                    'Distribución del inventario',
                    'doughnut',
                    array_keys($this->sortMoneyDesc($byCategory)),
                    [
                        [
                            'label' => 'Valor',
                            'data' => array_values($this->sortMoneyDesc($byCategory)),
                            'colors' => ['gold', 'night', 'blue', 'stone', 'success', 'danger'],
                        ],
                    ],
                    'money',
                ),
                $this->chart(
                    'inventory-status',
                    'Piezas por estado',
                    'Inventario físico actual',
                    'bar',
                    array_keys($statusTotals),
                    [
                        [
                            'label' => 'Piezas',
                            'data' => array_values($statusTotals),
                            'color' => 'night',
                        ],
                    ],
                    'number',
                ),
                $this->chart(
                    'inventory-locations',
                    'Valor por ubicación',
                    'Precio de venta del inventario',
                    'bar',
                    array_keys($this->sortMoneyDesc($byLocation)),
                    [
                        [
                            'label' => 'Valor',
                            'data' => array_values($this->sortMoneyDesc($byLocation)),
                            'color' => 'blue',
                        ],
                    ],
                    'money',
                    true,
                ),
            ],
        ];
    }

    /**
     * Cash-shift report within the given range.
     *
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>, charts: array<int, array<string, mixed>>}
     */
    protected function cash(Carbon $from, Carbon $to): array
    {
        $sessions = CashSession::query()
            ->whereDate('opened_at', '>=', $from->toDateString())
            ->whereDate('opened_at', '<=', $to->toDateString())
            ->with(['user', 'movements'])
            ->latest('id')
            ->get();

        $count = 0;
        $closed = 0;
        $open = 0;
        $expected = '0.00';
        $counted = '0.00';
        $difference = '0.00';
        $withDifference = 0;
        $expectedByMethod = [
            'Efectivo' => '0.00',
            'Tarjeta' => '0.00',
            'Transferencia' => '0.00',
        ];
        $movementTotals = [
            'Ingreso' => '0.00',
            'Retiro' => '0.00',
        ];

        $rows = $sessions->map(function (CashSession $session) use (&$count, &$closed, &$open, &$expected, &$counted, &$difference, &$withDifference, &$expectedByMethod, &$movementTotals): array {
            $count++;

            if ($session->isOpen()) {
                $open++;
            } else {
                $closed++;
            }

            $expected = bcadd($expected, $this->money($session->expected_cash), 2);
            $counted = bcadd($counted, $this->money($session->counted_cash), 2);
            $difference = bcadd($difference, $this->money($session->difference_total), 2);

            if (bccomp($this->money($session->difference_total), '0', 2) !== 0) {
                $withDifference++;
            }

            $expectedByMethod['Efectivo'] = bcadd($expectedByMethod['Efectivo'], $this->money($session->expected_cash), 2);
            $expectedByMethod['Tarjeta'] = bcadd($expectedByMethod['Tarjeta'], $this->money($session->expected_card), 2);
            $expectedByMethod['Transferencia'] = bcadd($expectedByMethod['Transferencia'], $this->money($session->expected_transfer), 2);

            foreach ($session->movements as $movement) {
                $label = $movement->type?->label() ?? 'Otro';
                $movementTotals[$label] = bcadd($movementTotals[$label] ?? '0.00', $this->money($movement->amount), 2);
            }

            return [
                $session->user?->name ?? '—',
                $session->opened_at?->format('d/m/Y H:i') ?? '—',
                $session->closed_at?->format('d/m/Y H:i') ?? 'Abierto',
                $this->formatMoney($session->opening_amount),
                $this->formatMoney($session->expected_cash),
                $this->formatMoney($session->counted_cash),
                $this->formatMoney($session->difference_total),
                (string) $session->movements->count(),
                $session->status?->label() ?? '—',
            ];
        })->all();

        $chartSessions = $sessions->sortBy('opened_at')->values()->take(-12);
        $chartLabels = $chartSessions
            ->map(fn (CashSession $session) => ($session->opened_at?->format('d/m') ?? '—').' · '.($session->user?->name ?? '—'))
            ->all();

        $movementChart = [];

        foreach (CashMovementType::cases() as $type) {
            $label = $type->label();
            $amount = $movementTotals[$label] ?? '0.00';

            if (bccomp($amount, '0', 2) !== 0) {
                $movementChart[$label] = $amount;
            }
        }

        return [
            'columns' => ['Cajero', 'Apertura', 'Cierre', 'Fondo inicial', 'Efectivo esperado', 'Efectivo contado', 'Diferencia', 'Movimientos', 'Estado'],
            'rows' => $rows,
            'totals' => [
                ['label' => 'Turnos', 'value' => (string) $count],
                ['label' => 'Cerrados', 'value' => (string) $closed],
                ['label' => 'Abiertos', 'value' => (string) $open],
                ['label' => 'Efectivo esperado', 'value' => $this->formatMoney($expected)],
                ['label' => 'Efectivo contado', 'value' => $this->formatMoney($counted)],
                ['label' => 'Diferencia total', 'value' => $this->formatMoney($difference)],
                ['label' => 'Turnos con diferencia', 'value' => (string) $withDifference],
            ],
            'charts' => [
                $this->chart(
                    'cash-sessions',
                    'Arqueo por turno',
                    'Esperado vs contado',
                    'bar',
                    $chartLabels,
                    [
                        [
                            'label' => 'Esperado',
                            'data' => $chartSessions->map(fn (CashSession $session) => $this->money($session->expected_cash))->all(),
                            'color' => 'gold',
                        ],
                        [
                            'label' => 'Contado',
                            'data' => $chartSessions->map(fn (CashSession $session) => $this->money($session->counted_cash))->all(),
                            'color' => 'night',
                        ],
                    ],
                    'money',
                ),
                $this->chart(
                    'cash-methods',
                    'Esperado por método',
                    'Distribución del período',
                    'doughnut',
                    array_keys($this->orderedPaymentTotals($expectedByMethod)),
                    [
                        [
                            'label' => 'Esperado',
                            'data' => array_values($this->orderedPaymentTotals($expectedByMethod)),
                            'colors' => ['gold', 'night', 'blue', 'stone'],
                        ],
                    ],
                    'money',
                ),
                $this->chart(
                    'cash-differences',
                    'Diferencias por turno',
                    'Contado menos esperado',
                    'bar',
                    $chartLabels,
                    [
                        [
                            'label' => 'Diferencia',
                            'data' => $chartSessions->map(fn (CashSession $session) => $this->money($session->difference_total))->all(),
                            'color' => 'danger',
                        ],
                    ],
                    'money',
                ),
                $this->chart(
                    'cash-movements',
                    'Ingresos y retiros',
                    'Movimientos manuales del período',
                    'bar',
                    array_keys($movementChart),
                    [
                        [
                            'label' => 'Monto',
                            'data' => array_values($movementChart),
                            'colors' => array_map(
                                fn (string $label) => $label === 'Ingreso' ? 'success' : 'danger',
                                array_keys($movementChart),
                            ),
                        ],
                    ],
                    'money',
                ),
            ],
        ];
    }

    /**
     * Layaway report within the given range.
     *
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>, totals: array<int, array{label: string, value: string}>, charts: array<int, array<string, mixed>>}
     */
    protected function layaways(Carbon $from, Carbon $to): array
    {
        $layaways = Layaway::query()
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->with(['customer', 'product', 'user'])
            ->latest('id')
            ->get();

        $count = 0;
        $active = 0;
        $completed = 0;
        $cancelled = 0;
        $overdue = 0;
        $total = '0.00';
        $paid = '0.00';
        $balance = '0.00';
        $statusCounts = [];
        $byCustomerBalance = [];
        $aging = [
            'Vencidos' => 0,
            'Vencen hoy' => 0,
            '1 a 7 días' => 0,
            '8 a 30 días' => 0,
            'Más de 30 días' => 0,
            'Sin fecha' => 0,
        ];

        $rows = $layaways->map(function (Layaway $layaway) use (&$count, &$active, &$completed, &$cancelled, &$overdue, &$total, &$paid, &$balance, &$statusCounts, &$byCustomerBalance, &$aging): array {
            $count++;
            $layawayBalance = $this->money($layaway->balance);
            $total = bcadd($total, $this->money($layaway->total_price), 2);
            $paid = bcadd($paid, $this->money($layaway->paid_total), 2);
            $balance = bcadd($balance, $layawayBalance, 2);

            if (bccomp($layawayBalance, '0', 2) === 1) {
                $customer = $layaway->customer?->full_name ?? '—';
                $byCustomerBalance[$customer] = bcadd($byCustomerBalance[$customer] ?? '0.00', $layawayBalance, 2);
            }

            $status = $layaway->status?->label() ?? '—';
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

            if ($layaway->isActive()) {
                $active++;
                $days = $layaway->due_date
                    ? (int) now()->startOfDay()->diffInDays($layaway->due_date->copy()->startOfDay(), false)
                    : null;

                if ($days === null) {
                    $aging['Sin fecha']++;
                } elseif ($days < 0) {
                    $aging['Vencidos']++;
                    $overdue++;
                } elseif ($days === 0) {
                    $aging['Vencen hoy']++;
                } elseif ($days <= 7) {
                    $aging['1 a 7 días']++;
                } elseif ($days <= 30) {
                    $aging['8 a 30 días']++;
                } else {
                    $aging['Más de 30 días']++;
                }
            } elseif ($layaway->isCompleted()) {
                $completed++;
            } elseif ($layaway->isCancelled()) {
                $cancelled++;
            }

            return [
                $layaway->number,
                $layaway->created_at?->format('d/m/Y') ?? '—',
                $layaway->due_date?->format('d/m/Y') ?? '—',
                $layaway->customer?->full_name ?? '—',
                $layaway->product?->name ?? '—',
                $this->formatMoney($layaway->total_price),
                $this->formatMoney($layaway->paid_total),
                $this->formatMoney($layaway->balance),
                $status,
                $layaway->user?->name ?? '—',
            ];
        })->all();

        $average = $count > 0 ? bcdiv($total, (string) $count, 2) : '0.00';
        $customerBalances = $this->topTotals($byCustomerBalance, 6);
        $series = $this->series(
            $from,
            $to,
            $layaways,
            fn (Layaway $layaway) => $layaway->created_at,
            fn () => '1',
            0,
        );

        return [
            'columns' => ['Folio', 'Apertura', 'Vence', 'Cliente', 'Pieza', 'Total', 'Abonado', 'Saldo', 'Estado', 'Vendedor'],
            'rows' => $rows,
            'totals' => [
                ['label' => 'Apartados', 'value' => (string) $count],
                ['label' => 'Activos', 'value' => (string) $active],
                ['label' => 'Completados', 'value' => (string) $completed],
                ['label' => 'Cancelados', 'value' => (string) $cancelled],
                ['label' => 'Total apartado', 'value' => $this->formatMoney($total)],
                ['label' => 'Abonado', 'value' => $this->formatMoney($paid)],
                ['label' => 'Saldo pendiente', 'value' => $this->formatMoney($balance)],
                ['label' => 'Vencidos', 'value' => (string) $overdue],
                ['label' => 'Ticket promedio', 'value' => $this->formatMoney($average)],
            ],
            'charts' => [
                $this->chart(
                    'layaways-status',
                    'Apartados por estado',
                    'Distribución del período',
                    'doughnut',
                    array_keys($statusCounts),
                    [
                        [
                            'label' => 'Apartados',
                            'data' => array_values($statusCounts),
                            'colors' => ['gold', 'success', 'danger'],
                        ],
                    ],
                    'number',
                ),
                $this->chart(
                    'layaways-aging',
                    'Vencimientos de apartados activos',
                    'Días restantes para liquidar',
                    'bar',
                    array_keys($aging),
                    [
                        [
                            'label' => 'Apartados',
                            'data' => array_values($aging),
                            'color' => 'warning',
                        ],
                    ],
                    'number',
                ),
                $this->chart(
                    'layaways-openings',
                    'Aperturas de apartados',
                    'Apartados creados por día',
                    'line',
                    $series['labels'],
                    [
                        [
                            'label' => 'Apartados',
                            'data' => $series['totals'],
                            'color' => 'gold',
                            'fill' => true,
                        ],
                    ],
                    'number',
                ),
                $this->chart(
                    'layaways-balances',
                    'Saldo por cliente',
                    'Top 6 con saldo pendiente',
                    'bar',
                    array_keys($customerBalances),
                    [
                        [
                            'label' => 'Saldo',
                            'data' => array_values($customerBalances),
                            'color' => 'warning',
                        ],
                    ],
                    'money',
                    true,
                ),
            ],
        ];
    }

    /**
     * Aggregate a collection into day or month buckets for a chart.
     *
     * @param  Collection<int, mixed>  $records
     * @param  callable(mixed): mixed  $date
     * @param  callable(mixed): string  $value
     * @return array{labels: array<int, string>, totals: array<int, string>}
     */
    protected function series(Carbon $from, Carbon $to, Collection $records, callable $date, callable $value, int $scale = 2): array
    {
        $monthly = $from->diffInDays($to) + 1 > 62;
        $labels = [];
        $totals = [];

        if ($monthly) {
            for ($cursor = $from->copy()->startOfMonth(); $cursor->lessThanOrEqualTo($to); $cursor->addMonth()) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->format('m/Y');
                $totals[$key] = $this->zero($scale);
            }
        } else {
            for ($cursor = $from->copy(); $cursor->lessThanOrEqualTo($to); $cursor->addDay()) {
                $key = $cursor->toDateString();
                $labels[] = $cursor->format('d/m');
                $totals[$key] = $this->zero($scale);
            }
        }

        foreach ($records as $record) {
            $recordDate = $date($record);

            if (! $recordDate instanceof Carbon) {
                $recordDate = Carbon::parse($recordDate);
            }

            $key = $monthly ? $recordDate->format('Y-m') : $recordDate->toDateString();

            if (! array_key_exists($key, $totals)) {
                continue;
            }

            $totals[$key] = bcadd($totals[$key], (string) $value($record), $scale);
        }

        return [
            'labels' => $labels,
            'totals' => array_values($totals),
        ];
    }

    /**
     * Build a chart definition consumed by resources/js/reports.js.
     *
     * @param  array<int, string>  $labels
     * @param  array<int, array{label: string, data: array<int, string|int|float>, color?: string, colors?: array<int, string>, fill?: bool}>  $datasets
     * @return array<string, mixed>
     */
    protected function chart(
        string $id,
        string $title,
        string $hint,
        string $type,
        array $labels,
        array $datasets,
        string $valueType = 'number',
        bool $horizontal = false,
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'hint' => $hint,
            'type' => $type,
            'labels' => array_values($labels),
            'datasets' => array_map(function (array $dataset): array {
                return [
                    'label' => $dataset['label'],
                    'data' => array_map(fn ($value) => (float) $value, $dataset['data']),
                    'color' => $dataset['color'] ?? 'gold',
                    'colors' => $dataset['colors'] ?? null,
                    'fill' => $dataset['fill'] ?? false,
                ];
            }, $datasets),
            'valueType' => $valueType,
            'horizontal' => $horizontal,
        ];
    }

    /**
     * Build a payment-method doughnut chart.
     *
     * @param  iterable<int, mixed>  $payments
     * @return array<string, mixed>
     */
    protected function paymentChart(string $id, iterable $payments, string $title, string $hint): array
    {
        $totals = [];

        foreach ($payments as $payment) {
            $method = $payment->method?->label() ?? 'Otro';
            $totals[$method] = bcadd($totals[$method] ?? '0.00', $this->money($payment->amount), 2);
        }

        $ordered = $this->orderedPaymentTotals($totals);

        return $this->chart(
            $id,
            $title,
            $hint,
            'doughnut',
            array_keys($ordered),
            [
                [
                    'label' => 'Cobrado',
                    'data' => array_values($ordered),
                    'colors' => ['gold', 'night', 'blue', 'stone'],
                ],
            ],
            'money',
        );
    }

    /**
     * Order payment totals following the enum declaration order.
     *
     * @param  array<string, string>  $totals
     * @return array<string, string>
     */
    protected function orderedPaymentTotals(array $totals): array
    {
        $ordered = [];

        foreach (PaymentMethod::cases() as $method) {
            $label = $method->label();

            if (isset($totals[$label]) && bccomp($totals[$label], '0', 2) !== 0) {
                $ordered[$label] = $totals[$label];
            }
        }

        return $ordered;
    }

    /**
     * Sort monetary totals from highest to lowest.
     *
     * @param  array<string, string>  $totals
     * @return array<string, string>
     */
    protected function sortMoneyDesc(array $totals): array
    {
        uasort($totals, fn (string $a, string $b): int => bccomp($b, $a, 2));

        return $totals;
    }

    /**
     * Sort weight totals from highest to lowest.
     *
     * @param  array<string, string>  $totals
     * @return array<string, string>
     */
    protected function sortWeightDesc(array $totals): array
    {
        uasort($totals, fn (string $a, string $b): int => bccomp($b, $a, 3));

        return $totals;
    }

    /**
     * Return the top totals from an associative array.
     *
     * @param  array<string, string>  $totals
     * @return array<string, string>
     */
    protected function topTotals(array $totals, int $limit, int $scale = 2): array
    {
        uasort($totals, fn (string $a, string $b): int => bccomp($b, $a, $scale));

        return array_slice($totals, 0, $limit, true);
    }

    /**
     * Most sold pieces for the period.
     *
     * @param  Collection<int, Sale>  $sales
     * @return array<int, array{name: string, pieces: int, total: string}>
     */
    protected function topProducts(Collection $sales): array
    {
        $products = [];

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $name = $item->name_snapshot;
                $products[$name] ??= ['name' => $name, 'pieces' => 0, 'total' => '0.00'];
                $products[$name]['pieces']++;
                $products[$name]['total'] = bcadd($products[$name]['total'], $this->money($item->line_total), 2);
            }
        }

        uasort($products, fn (array $a, array $b): int => bccomp($b['total'], $a['total'], 2));

        return array_values(array_map(
            fn (array $product): array => [
                'name' => $product['name'],
                'pieces' => $product['pieces'],
                'total' => $this->formatMoney($product['total']),
            ],
            array_slice($products, 0, 6, true),
        ));
    }

    /**
     * Customers with the highest purchase volume for the period.
     *
     * @param  Collection<int, Sale>  $sales
     * @return array<int, array{name: string, sales: int, total: string}>
     */
    protected function topCustomers(Collection $sales): array
    {
        $customers = [];

        foreach ($sales as $sale) {
            $name = $sale->customer?->full_name ?? 'Público general';
            $customers[$name] ??= ['name' => $name, 'sales' => 0, 'total' => '0.00'];
            $customers[$name]['sales']++;
            $customers[$name]['total'] = bcadd($customers[$name]['total'], $this->money($sale->total), 2);
        }

        uasort($customers, fn (array $a, array $b): int => bccomp($b['total'], $a['total'], 2));

        return array_values(array_map(
            fn (array $customer): array => [
                'name' => $customer['name'],
                'sales' => $customer['sales'],
                'total' => $this->formatMoney($customer['total']),
            ],
            array_slice($customers, 0, 6, true),
        ));
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
     * Sum a monetary column from a collection.
     *
     * @param  iterable<int, mixed>  $records
     */
    protected function sumMoney(iterable $records, string $field): string
    {
        $total = '0.00';

        foreach ($records as $record) {
            $total = bcadd($total, $this->money($record->{$field} ?? null), 2);
        }

        return $total;
    }

    /**
     * Sum a weight column from a collection.
     *
     * @param  iterable<int, mixed>  $records
     */
    protected function sumWeight(iterable $records, string $field): string
    {
        $total = '0.000';

        foreach ($records as $record) {
            $total = bcadd($total, $this->weight($record->{$field} ?? null), 3);
        }

        return $total;
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

    /**
     * Zero value with the requested decimal scale.
     */
    protected function zero(int $scale): string
    {
        return $scale === 0 ? '0' : '0.'.str_repeat('0', $scale);
    }
}
