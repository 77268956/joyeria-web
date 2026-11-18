<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VoidSaleRequest;
use App\Models\Sale;
use App\Models\Store;
use App\Services\CashSessionService;
use App\Services\SaleService;
use App\Traits\BuildsActivityChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class SaleController extends Controller
{
    use BuildsActivityChanges;

    public function __construct(
        protected SaleService $sales,
        protected CashSessionService $cash,
    ) {}

    /**
     * List the sales of the store with filters.
     */
    public function index(Request $request): View
    {
        $scope = $request->string('scope')->value();
        $session = null;

        if ($scope === 'shift') {
            $session = $this->cash->currentSession($request->user());

            if ($session === null) {
                $scope = 'today';
            }
        }

        $query = Sale::query()
            ->when($session !== null, fn (Builder $query) => $query->where('cash_session_id', $session->id))
            ->when($scope === 'today', fn (Builder $query) => $query->whereDate('created_at', today()))
            ->when($request->filled('search'), fn (Builder $query) => $query->where('number', 'like', '%'.$request->input('search').'%'))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('date'), fn (Builder $query) => $query->whereDate('created_at', $request->input('date')));

        $summary = (clone $query)
            ->reorder()
            ->selectRaw(
                'COUNT(*) as sales_count, '.
                'COALESCE(SUM(CASE WHEN status = ? THEN total ELSE 0 END), 0) as sales_total, '.
                'COUNT(CASE WHEN status = ? THEN 1 END) as completed_count, '.
                'COUNT(CASE WHEN status = ? THEN 1 END) as voided_count',
                [SaleStatus::Completed->value, SaleStatus::Completed->value, SaleStatus::Voided->value]
            )
            ->first();

        $sales = $query
            ->with(['customer', 'user'])
            ->withCount('items')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sales.index', [
            'sales' => $sales,
            'statuses' => SaleStatus::cases(),
            'scope' => $scope,
            'session' => $session,
            'summary' => [
                'count' => (int) ($summary->sales_count ?? 0),
                'total' => (string) ($summary->sales_total ?? '0.00'),
                'completed_count' => (int) ($summary->completed_count ?? 0),
                'voided_count' => (int) ($summary->voided_count ?? 0),
            ],
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
        $sale->load(['items.product', 'payments', 'customer', 'user', 'voidedBy']);

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
        $snapshot = $this->activitySnapshot($sale);

        try {
            $sale = $this->sales->void($sale, $request->validated('reason'), Auth::user());
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        activity('ventas')
            ->performedOn($sale)
            ->causedBy(Auth::user())
            ->event('voided')
            ->withChanges($this->activityUpdatedChanges($sale, $snapshot))
            ->withProperties(['reason' => $request->validated('reason')])
            ->log("Venta anulada: {$sale->number}");

        return redirect()->route('admin.sales.show', $sale)
            ->with('status', "La venta {$sale->number} fue anulada y las piezas volvieron al inventario.");
    }
}
