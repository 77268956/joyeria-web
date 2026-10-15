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

        return view('admin.layaways.create', [
            'products' => Product::query()
                ->where('status', ProductStatus::Available->value)
                ->with(['metal', 'purity'])
                ->orderBy('name')
                ->get(),
            'customers' => Customer::orderBy('full_name')->get(['id', 'full_name', 'document_type', 'document_number']),
            'defaultDueDate' => now()->addDays((int) $settings->layaway_max_days)->toDateString(),
            'maxDays' => (int) $settings->layaway_max_days,
            'selectedProductId' => $request->integer('product_id') ?: null,
            'paymentMethods' => PaymentMethod::cases(),
        ]);
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
