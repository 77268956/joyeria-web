<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Services\CashSessionService;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class PosController extends Controller
{
    public function __construct(
        protected SaleService $sales,
        protected CashSessionService $cash,
    ) {}

    /**
     * Display the point-of-sale terminal.
     */
    public function index(): View
    {
        $user = Auth::user();
        $settings = SystemSetting::firstOrCreate(['store_id' => $user->store_id]);

        return view('admin.pos.index', [
            'session' => $this->cash->currentSession($user),
            'customers' => Customer::orderBy('full_name')->get(['id', 'full_name', 'document_type', 'document_number']),
            'maxDiscountPercent' => (string) $settings->max_discount_percent,
            'canOverrideDiscount' => $user->can('products.edit_price'),
        ]);
    }

    /**
     * Search available pieces by barcode, SKU or name.
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->string('q')->trim()->value();

        $products = Product::query()
            ->search($term)
            ->where('status', ProductStatus::Available->value)
            ->with(['metal', 'purity'])
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'metal' => $product->metal?->name,
                'purity' => $product->purity?->label,
                'weight_g' => (string) $product->weight_g,
                'price' => (string) $product->price,
            ]);

        return response()->json(['products' => $products]);
    }

    /**
     * Register a sale and return the redirect target for the ticket.
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        $user = Auth::user();
        $session = $this->cash->currentSession($user);

        if ($session === null) {
            return response()->json(['message' => 'Debe abrir un turno de caja para vender.'], 409);
        }

        try {
            $sale = $this->sales->create($user, $session, $request->validated());
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        activity('ventas')
            ->performedOn($sale)
            ->causedBy($user)
            ->withProperties([
                'number' => $sale->number,
                'total' => (string) $sale->total,
                'items' => $sale->items->count(),
            ])
            ->log("Venta registrada: {$sale->number}");

        return response()->json([
            'sale_id' => $sale->id,
            'number' => $sale->number,
            'total' => (string) $sale->total,
            'ticket_url' => route('admin.sales.ticket', $sale),
        ]);
    }
}
