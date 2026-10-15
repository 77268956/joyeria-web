<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CashMovementType;
use App\Enums\PaymentMethod;
use App\Enums\ProductStatus;
use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\Customer;
use App\Models\LayawayPayment;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\MetalPurchase;
use App\Models\MetalPurchaseItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with key metrics.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $store = $user->store ?? Store::first();

        $productsCount = Product::count();
        $customersCount = Customer::count();
        $usersCount = User::count();

        $salesTodayCount = Sale::query()->completed()->whereDate('created_at', today())->count();
        $salesTodayTotal = $this->money(Sale::query()->completed()->whereDate('created_at', today())->sum('total'));

        $purchasesTodayTotal = $this->money(MetalPurchase::query()->whereDate('created_at', today())->sum('total_amount'));
        $purchasesTodayGrams = $this->weight(
            MetalPurchaseItem::query()
                ->whereHas('metalPurchase', fn ($query) => $query->whereDate('created_at', today()))
                ->sum('net_weight_g'),
        );

        $netCashFlow = $this->netCashFlow();

        $goldQuote = $this->goldQuote();

        $inventoryChart = $this->inventoryByMetal();

        $lowStock = $this->lowStockMetals($store);

        // Latest activity log entries for admin quick view
        $recentActivity = Activity::with('causer')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'user' => $user,
            'store' => $store,
            'productsCount' => $productsCount,
            'customersCount' => $customersCount,
            'usersCount' => $usersCount,
            'salesTodayCount' => $salesTodayCount,
            'salesTodayTotal' => $salesTodayTotal,
            'purchasesTodayTotal' => $purchasesTodayTotal,
            'purchasesTodayGrams' => $purchasesTodayGrams,
            'netCashFlow' => $netCashFlow,
            'goldQuote' => $goldQuote,
            'salesChart' => $this->salesLastSevenDays(),
            'inventoryChart' => $inventoryChart,
            'lowStock' => $lowStock,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Total sales per day for the last seven days, for the dashboard chart.
     *
     * @return array{labels: array<int, string>, totals: array<int, float>}
     */
    protected function salesLastSevenDays(): array
    {
        $summary = Sale::query()
            ->completed()
            ->where('created_at', '>=', today()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $totals = [];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = today()->subDays($daysAgo);

            $labels[] = $date->format('d/m');
            $totals[] = (float) ($summary[$date->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'totals' => $totals];
    }

    /**
     * In-stock weight per metal, for the dashboard doughnut chart.
     *
     * @return array{labels: array<int, string>, grams: array<int, float>}
     */
    protected function inventoryByMetal(): array
    {
        $grams = Product::query()
            ->inStock()
            ->selectRaw('metal_id, SUM(weight_g) as grams')
            ->groupBy('metal_id')
            ->pluck('grams', 'metal_id');

        $names = Metal::query()->pluck('name', 'id');

        $labels = [];
        $values = [];

        foreach ($grams as $metalId => $weight) {
            $labels[] = $names[$metalId] ?? 'Sin metal';
            $values[] = (float) $weight;
        }

        return ['labels' => $labels, 'grams' => $values];
    }

    /**
     * Metals whose available piece count is below the configured threshold.
     *
     * @return array<int, array{name: string, available: int, threshold: int}>
     */
    protected function lowStockMetals(?Store $store): array
    {
        $threshold = (int) (SystemSetting::query()
            ->where('store_id', $store?->id)
            ->value('low_stock_alert_threshold') ?? 5);

        $totals = Product::query()
            ->selectRaw('metal_id, COUNT(*) as total')
            ->groupBy('metal_id')
            ->pluck('total', 'metal_id');

        $available = Product::query()
            ->where('status', ProductStatus::Available->value)
            ->selectRaw('metal_id, COUNT(*) as total')
            ->groupBy('metal_id')
            ->pluck('total', 'metal_id');

        $names = Metal::query()->pluck('name', 'id');

        $lowStock = [];

        foreach ($totals as $metalId => $total) {
            $inStock = (int) ($available[$metalId] ?? 0);

            if ((int) $total > 0 && $inStock < $threshold) {
                $lowStock[] = [
                    'name' => $names[$metalId] ?? 'Sin metal',
                    'available' => $inStock,
                    'threshold' => $threshold,
                ];
            }
        }

        return $lowStock;
    }

    /**
     * Latest market price for pure gold, if any has been recorded.
     *
     * @return array{metal: string, price_per_gram: string, date: ?string}|null
     */
    protected function goldQuote(): ?array
    {
        $gold = Metal::query()->where('code', 'GOLD')->first();

        if ($gold === null) {
            return null;
        }

        /** @var MetalPrice|null $price */
        $price = MetalPrice::query()
            ->where('metal_id', $gold->id)
            ->latest('id')
            ->first();

        if ($price === null) {
            return null;
        }

        return [
            'metal' => $gold->name,
            'price_per_gram' => $price->price_per_gram,
            'date' => $price->price_date?->format('d/m/Y'),
        ];
    }

    /**
     * Net cash moved today: cash sales + cash layaway instalments + in - out.
     */
    protected function netCashFlow(): string
    {
        $cashSales = Payment::query()
            ->whereDate('created_at', today())
            ->where('method', PaymentMethod::Cash->value)
            ->whereHas('sale', fn ($query) => $query->where('status', SaleStatus::Completed->value))
            ->sum('amount');

        $cashLayaways = LayawayPayment::query()
            ->whereDate('created_at', today())
            ->where('method', PaymentMethod::Cash->value)
            ->whereHas('layaway')
            ->sum('amount');

        $cashIn = CashMovement::query()
            ->whereDate('created_at', today())
            ->where('type', CashMovementType::In->value)
            ->sum('amount');

        $cashOut = CashMovement::query()
            ->whereDate('created_at', today())
            ->where('type', CashMovementType::Out->value)
            ->sum('amount');

        $net = bcadd($this->money($cashSales), $this->money($cashLayaways), 2);
        $net = bcadd($net, $this->money($cashIn), 2);

        return bcsub($net, $this->money($cashOut), 2);
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
