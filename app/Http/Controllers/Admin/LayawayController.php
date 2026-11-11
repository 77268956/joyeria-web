<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LayawayStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelLayawayRequest;
use App\Http\Requests\Admin\StoreLayawayPaymentRequest;
use App\Http\Requests\Admin\StoreLayawayRequest;
use App\Models\Customer;
use App\Models\Layaway;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Services\CashSessionService;
use App\Services\LayawayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class LayawayController extends Controller
{
    public function __construct(
        protected LayawayService $layaways,
        protected CashSessionService $cash,
    ) {}

    /**
     * List the store's layaways with filters.
     */
    public function index(Request $request): View
    {
        $layaways = Layaway::query()
            ->when($request->filled('search'), fn ($query) => $query->where('number', 'like', '%'.$request->input('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->with(['customer', 'product'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.layaways.index', [
            'layaways' => $layaways,
            'statuses' => LayawayStatus::cases(),
        ]);
    }

    /**
     * Show the form to open a new layaway.
     */
    public function create(Request $request): View
    {
        $settings = SystemSetting::firstOrCreate(['store_id' => $request->user()->store_id]);

        $selectedId = (int) old('product_id', $request->integer('product_id') ?: 0);

        $selectedProduct = $selectedId > 0
            ? Product::query()
                ->where('status', ProductStatus::Available->value)
                ->with(['metal', 'purity', 'primaryPhoto'])
                ->find($selectedId)
            : null;

        $selectedCustomer = Customer::find((int) old('customer_id', 0));

        return view('admin.layaways.create', [
            'defaultDueDate' => now()->addDays((int) $settings->layaway_max_days)->toDateString(),
            'maxDays' => (int) $settings->layaway_max_days,
            'selectedProduct' => $selectedProduct ? $this->productPayload($selectedProduct) : null,
            'selectedCustomer' => $selectedCustomer ? [
                'id' => $selectedCustomer->id,
                'full_name' => $selectedCustomer->full_name,
                'document' => $selectedCustomer->document_type.' '.$selectedCustomer->document_number,
                'phone' => $selectedCustomer->phone,
            ] : null,
            'hasAvailableProducts' => Product::query()->where('status', ProductStatus::Available->value)->exists(),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    /**
     * Search available pieces by name, SKU or barcode for the layaway form.
     */
    public function search(Request $request): JsonResponse
    {
        $products = Product::query()
            ->search($request->string('q')->trim()->value())
            ->where('status', ProductStatus::Available->value)
            ->with(['metal', 'purity', 'primaryPhoto'])
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(fn (Product $product): array => $this->productPayload($product));

        return response()->json(['products' => $products]);
    }

    /**
     * Serializable shape of a piece used by the picker and the search endpoint.
     *
     * @return array<string, mixed>
     */
    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'metal' => $product->metal?->name,
            'purity' => $product->purity?->label,
            'weight_g' => (string) $product->weight_g,
            'price' => (string) $product->price,
            'photo_url' => $product->primaryPhoto?->url,
        ];
    }

    /**
     * Register a layaway and reserve the piece.
     */
    public function store(StoreLayawayRequest $request): RedirectResponse
    {
        $user = $request->user();
        $session = $this->cash->currentSession($user);

        if ($session === null) {
            return back()->withInput()->with('error', 'Debe abrir un turno de caja para registrar apartados.');
        }

        try {
            $layaway = $this->layaways->create($user, $session, $request->validated());
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        activity('apartados')
            ->performedOn($layaway)
            ->causedBy($user)
            ->withProperties([
                'number' => $layaway->number,
                'total' => (string) $layaway->total_price,
            ])
            ->log("Apartado registrado: {$layaway->number}");

        return redirect()->route('admin.layaways.show', $layaway)
            ->with('status', "El apartado {$layaway->number} se registró correctamente.");
    }

    /**
     * Show a layaway with its instalments.
     */
    public function show(Layaway $layaway): View
    {
        $layaway->load([
            'customer',
            'product.metal',
            'product.purity',
            'user:id,name',
            'completedSale',
            'payments.user:id,name',
        ]);

        return view('admin.layaways.show', [
            'layaway' => $layaway,
            'paymentMethods' => PaymentMethod::cases(),
            'canComplete' => $layaway->isActive() && $layaway->isFullyPaid(),
        ]);
    }

    /**
     * Register a partial payment against the layaway.
     */
    public function addPayment(StoreLayawayPaymentRequest $request, Layaway $layaway): RedirectResponse
    {
        $user = $request->user();
        $session = $this->cash->currentSession($user);

        if ($session === null) {
            return back()->with('error', 'Debe abrir un turno de caja para registrar abonos.');
        }

        try {
            $layaway = $this->layaways->addPayment($layaway, $session, $user, $request->validated());
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('apartados')
            ->performedOn($layaway)
            ->causedBy($user)
            ->withProperties(['amount' => (string) $request->validated('amount')])
            ->log("Abono registrado al apartado: {$layaway->number}");

        return back()->with('status', 'Abono registrado. Saldo pendiente: $ '.number_format((float) $layaway->balance, 2));
    }

    /**
     * Deliver a fully paid layaway and generate its sale.
     */
    public function complete(Request $request, Layaway $layaway): RedirectResponse
    {
        $user = $request->user();
        $session = $this->cash->currentSession($user);

        if ($session === null) {
            return back()->with('error', 'Debe abrir un turno de caja para entregar apartados.');
        }

        try {
            $layaway = $this->layaways->complete($layaway, $session, $user);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('apartados')
            ->performedOn($layaway)
            ->causedBy($user)
            ->log("Apartado entregado y convertido en venta: {$layaway->number}");

        return redirect()->route('admin.layaways.show', $layaway)
            ->with('status', "El apartado {$layaway->number} fue entregado correctamente.");
    }

    /**
     * Cancel an active layaway, releasing the piece.
     */
    public function cancel(CancelLayawayRequest $request, Layaway $layaway): RedirectResponse
    {
        try {
            $layaway = $this->layaways->cancel($layaway, $request->user(), $request->validated('reason'));
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('apartados')
            ->performedOn($layaway)
            ->causedBy($request->user())
            ->withProperties(['reason' => $request->validated('reason')])
            ->log("Apartado cancelado: {$layaway->number}");

        return redirect()->route('admin.layaways.show', $layaway)
            ->with('status', "El apartado {$layaway->number} fue cancelado y la pieza volvió al inventario.");
    }
}
