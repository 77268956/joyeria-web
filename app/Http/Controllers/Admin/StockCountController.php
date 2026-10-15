<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Enums\StockCountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordStockCountRequest;
use App\Http\Requests\Admin\StoreStockCountRequest;
use App\Models\Product;
use App\Models\StockCount;
use App\Services\StockCountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class StockCountController extends Controller
{
    public function __construct(protected StockCountService $counts) {}

    /**
     * List the store's physical inventory counts.
     */
    public function index(): View
    {
        $counts = StockCount::query()
            ->with('startedBy:id,name')
            ->withCount('items')
            ->latest('id')
            ->paginate(20);

        return view('admin.stock-counts.index', [
            'counts' => $counts,
            'statuses' => StockCountStatus::cases(),
        ]);
    }

    /**
     * Open a new physical count.
     */
    public function store(StoreStockCountRequest $request): RedirectResponse
    {
        try {
            $count = $this->counts->start($request->user(), $request->validated('notes'));
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('inventario')
            ->performedOn($count)
            ->causedBy($request->user())
            ->log("Conteo físico iniciado #{$count->id}");

        return redirect()->route('admin.stock-counts.show', $count)
            ->with('status', 'El conteo físico se inició con el inventario actual.');
    }

    /**
     * Show a count with its pieces and discrepancies.
     */
    public function show(StockCount $stockCount): View
    {
        $stockCount->load([
            'items.product.metal',
            'items.product.purity',
            'startedBy:id,name',
            'finishedBy:id,name',
            'appliedBy:id,name',
        ]);

        $items = $stockCount->items;

        $candidates = collect();

        if ($stockCount->isOpen()) {
            $candidates = Product::query()
                ->whereIn('status', [ProductStatus::Available->value, ProductStatus::Reserved->value])
                ->whereNotIn('id', $items->pluck('product_id'))
                ->orderBy('name')
                ->get(['id', 'name', 'sku']);
        }

        return view('admin.stock-counts.show', [
            'count' => $stockCount,
            'candidates' => $candidates,
            'stats' => [
                'expected' => $items->where('expected', true)->count(),
                'counted' => $items->whereNotNull('found')->count(),
                'missing' => $items->where('expected', true)->where('found', false)->count(),
                'surplus' => $items->where('expected', false)->where('found', true)->count(),
                'pending' => $items->whereNull('found')->count(),
            ],
        ]);
    }

    /**
     * Record the physically found state of the pieces.
     */
    public function record(RecordStockCountRequest $request, StockCount $stockCount): RedirectResponse
    {
        try {
            $this->counts->record($stockCount, $request->validated('items'));
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', 'El conteo se guardó correctamente.');
    }

    /**
     * Finish the count, freezing the recorded differences.
     */
    public function finish(Request $request, StockCount $stockCount): RedirectResponse
    {
        try {
            $this->counts->finish($stockCount, $request->user());
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('inventario')
            ->performedOn($stockCount)
            ->causedBy($request->user())
            ->log("Conteo físico finalizado #{$stockCount->id}");

        return back()->with('status', 'El conteo quedó finalizado. Revísalo y aplícalo para ajustar el inventario.');
    }

    /**
     * Apply the count, adjusting the inventory for every difference.
     */
    public function apply(Request $request, StockCount $stockCount): RedirectResponse
    {
        try {
            $this->counts->apply($stockCount, $request->user());
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        activity('inventario')
            ->performedOn($stockCount)
            ->causedBy($request->user())
            ->log("Conteo físico aplicado #{$stockCount->id}");

        return redirect()->route('admin.stock-counts.show', $stockCount)
            ->with('status', 'El conteo fue aplicado y las diferencias ajustaron el inventario.');
    }
}
