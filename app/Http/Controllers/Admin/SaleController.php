<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VoidSaleRequest;
use App\Models\Sale;
use App\Models\Store;
use App\Services\SaleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class SaleController extends Controller
{
    public function __construct(protected SaleService $sales) {}

    /**
     * List the sales of the store with filters.
     */
    public function index(Request $request): View
    {
        $sales = Sale::query()
            ->when($request->filled('search'), fn (Builder $query) => $query->where('number', 'like', '%'.$request->input('search').'%'))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('date'), fn (Builder $query) => $query->whereDate('created_at', $request->input('date')))
            ->with(['customer', 'user'])
            ->withCount('items')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sales.index', [
            'sales' => $sales,
            'statuses' => SaleStatus::cases(),
        ]);
    }

    /**
     * Display a sale with its lines and payments.
     */
    public function show(Sale $sale): View
    {
        $sale->load(['items.product', 'payments', 'customer', 'user', 'voidedBy']);

        return view('admin.sales.show', ['sale' => $sale]);
    }

    /**
     * Render a printable thermal ticket or an A4 invoice.
     */
    public function ticket(Request $request, Sale $sale): View
    {
        $sale->load(['items', 'payments', 'customer', 'user']);

        $format = $request->input('format', 'ticket');

        return view($format === 'invoice' ? 'admin.sales.invoice' : 'admin.sales.ticket', [
            'sale' => $sale,
            'store' => $sale->store ?? Store::first(),
            'settings' => Store::first()?->systemSettings,
        ]);
    }

    /**
     * Void a sale, returning its pieces to stock.
     */
    public function void(VoidSaleRequest $request, Sale $sale): RedirectResponse
    {
        try {
            $this->sales->void($sale, $request->validated('reason'), Auth::user());
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        activity('ventas')
            ->performedOn($sale)
            ->causedBy(Auth::user())
            ->withProperties(['reason' => $request->validated('reason')])
            ->log("Venta anulada: {$sale->number}");

        return redirect()->route('admin.sales.show', $sale)
            ->with('status', "La venta {$sale->number} fue anulada y las piezas volvieron al inventario.");
    }
}
