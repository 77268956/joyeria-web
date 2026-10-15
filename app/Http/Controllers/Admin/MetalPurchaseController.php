<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MetalPurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustMetalPurchaseRequest;
use App\Http\Requests\Admin\ApproveMetalPurchaseRequest;
use App\Http\Requests\Admin\RejectMetalPurchaseRequest;
use App\Http\Requests\Admin\StoreMetalPurchaseRequest;
use App\Models\Customer;
use App\Models\Metal;
use App\Models\MetalPurchase;
use App\Models\SystemSetting;
use App\Services\CashSessionService;
use App\Services\MetalPurchaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class MetalPurchaseController extends Controller
{
    public function __construct(
        protected MetalPurchaseService $purchases,
        protected CashSessionService $cash,
    ) {}

    /**
     * List the store's metal purchases with filters.
     */
    public function index(Request $request): View
    {
        $purchases = MetalPurchase::query()
            ->with(['customer:id,full_name', 'user:id,name'])
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = '%'.$request->input('search').'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('number', 'like', $term)
                        ->orWhereHas('customer', fn ($query) => $query->where('full_name', 'like', $term));
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.purchases.index', [
            'purchases' => $purchases,
            'statuses' => MetalPurchaseStatus::cases(),
        ]);
    }

    /**
     * Show the three-step purchase form.
     */
    public function create(): View
    {
        $user = Auth::user();
        $settings = SystemSetting::firstOrCreate(['store_id' => $user->store_id]);

        $metals = Metal::with(['purities' => fn ($query) => $query->where('is_active', true)->orderBy('code')])
            ->where('is_precious', true)
            ->orderBy('code')
            ->get();

        $metalsJson = $metals->map(fn (Metal $metal): array => [
            'id' => $metal->id,
            'name' => $metal->name,
            'purities' => $metal->purities->map(fn ($purity): array => [
                'id' => $purity->id,
                'code' => $purity->code,
                'fineness' => (string) $purity->fineness,
            ])->values()->all(),
        ])->values()->all();

        return view('admin.purchases.create', [
            'customers' => Customer::orderBy('full_name')->get(['id', 'full_name', 'document_type', 'document_number']),
            'metalsJson' => $metalsJson,
            'approvalLimit' => (string) $settings->purchase_approval_limit,
            'canApprove' => $user->can('purchases.approve'),
        ]);
    }

    /**
     * Register a purchase (pending approval when above the limit).
     */
    public function store(StoreMetalPurchaseRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $session = $this->cash->currentSession($user);

        if ($session === null) {
            return back()->withInput()->with('error', 'Debe abrir un turno de caja para comprar metales.');
        }

        try {
            $purchase = $this->purchases->create($user, $session, $request->validated());
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        activity('compras')
            ->performedOn($purchase)
            ->causedBy($user)
            ->withProperties([
                'number' => $purchase->number,
                'total' => (string) $purchase->total_amount,
                'status' => $purchase->status->value,
            ])
            ->log("Compra de metales registrada: {$purchase->number}");

        $message = $purchase->isPaid()
            ? "La compra {$purchase->number} se registró y liquidó correctamente."
            : "La compra {$purchase->number} quedó pendiente de aprobación por gerencia.";

        return redirect()->route('admin.purchases.show', $purchase)->with('status', $message);
    }

    /**
     * Price a single piece for the live breakdown in the form.
     */
    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metal_id' => ['required', 'integer', 'exists:metals,id'],
            'purity_id' => ['required', 'integer', 'exists:metal_purities,id'],
            'gross_weight_g' => ['required', 'numeric', 'gt:0'],
            'net_weight_g' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            $line = $this->purchases->quote(
                Auth::user()->store_id,
                (int) $validated['metal_id'],
                (int) $validated['purity_id'],
                (string) $validated['gross_weight_g'],
                (string) $validated['net_weight_g'],
            );
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($line);
    }

    /**
     * Show a purchase with its pieces and adjustments.
     */
    public function show(MetalPurchase $metalPurchase): View
    {
        $metalPurchase->load([
            'customer',
            'user:id,name',
            'approvedBy:id,name',
            'items.metal',
            'items.purity',
            'items.product',
            'items.materialLot',
            'adjustments.user:id,name',
        ]);

        return view('admin.purchases.show', [
            'purchase' => $metalPurchase,
            'canApprove' => Auth::user()->can('purchases.approve'),
        ]);
    }

    /**
     * Approve a pending purchase.
     */
    public function approve(ApproveMetalPurchaseRequest $request, MetalPurchase $metalPurchase): RedirectResponse
    {
        try {
            $this->purchases->approve(
                $metalPurchase,
                Auth::user(),
                $request->validated()['approval_note'] ?? null,
            );
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('compras')
            ->performedOn($metalPurchase)
            ->causedBy(Auth::user())
            ->log("Compra aprobada: {$metalPurchase->number}");

        return back()->with('status', "La compra {$metalPurchase->number} fue aprobada y liquidada.");
    }

    /**
     * Reject a pending purchase.
     */
    public function reject(RejectMetalPurchaseRequest $request, MetalPurchase $metalPurchase): RedirectResponse
    {
        try {
            $this->purchases->reject(
                $metalPurchase,
                Auth::user(),
                $request->validated()['reason'],
            );
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('compras')
            ->performedOn($metalPurchase)
            ->causedBy(Auth::user())
            ->log("Compra rechazada: {$metalPurchase->number}");

        return back()->with('status', "La compra {$metalPurchase->number} fue rechazada.");
    }

    /**
     * Show the adjustment form for a paid purchase.
     */
    public function adjustForm(MetalPurchase $metalPurchase): View
    {
        $metalPurchase->load(['customer', 'adjustments.user:id,name']);

        return view('admin.purchases.adjust', ['purchase' => $metalPurchase]);
    }

    /**
     * Store an audited adjustment of the purchase total.
     */
    public function adjust(AdjustMetalPurchaseRequest $request, MetalPurchase $metalPurchase): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->purchases->adjust(
                $metalPurchase,
                Auth::user(),
                (string) $validated['total_amount'],
                $validated['reason'],
            );
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('compras')
            ->performedOn($metalPurchase)
            ->causedBy(Auth::user())
            ->withProperties(['reason' => $validated['reason']])
            ->log("Compra ajustada: {$metalPurchase->number}");

        return redirect()->route('admin.purchases.show', $metalPurchase)
            ->with('status', "La compra {$metalPurchase->number} fue ajustada y quedó auditada.");
    }

    /**
     * Download the legal purchase receipt as a PDF.
     */
    public function receipt(MetalPurchase $metalPurchase): Response
    {
        $metalPurchase->load(['store', 'customer', 'user', 'approvedBy', 'items.metal', 'items.purity']);

        $settings = SystemSetting::firstOrCreate(['store_id' => $metalPurchase->store_id]);

        return Pdf::loadView('admin.purchases.receipt', [
            'purchase' => $metalPurchase,
            'disclaimer' => $metalPurchase->notes ?: $settings->default_purchase_disclaimer,
        ])->setPaper('letter')->download("compra-{$metalPurchase->number}.pdf");
    }
}
