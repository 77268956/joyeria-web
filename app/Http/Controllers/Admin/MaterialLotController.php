<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MaterialLotStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustLotWeightRequest;
use App\Http\Requests\Admin\SendToMeltingRequest;
use App\Http\Requests\Admin\StoreMaterialLotRequest;
use App\Models\Location;
use App\Models\MaterialLot;
use App\Models\Metal;
use App\Services\InventoryService;
use App\Traits\BuildsActivityChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MaterialLotController extends Controller
{
    use BuildsActivityChanges;

    public function __construct(protected InventoryService $inventory) {}

    /**
     * Display the raw-material lots.
     */
    public function index(Request $request): View
    {
        $query = MaterialLot::query()
            ->when($request->filled('search'), fn (Builder $query) => $query->where('code', 'like', '%'.$request->input('search').'%'))
            ->when($request->filled('metal_id'), fn (Builder $query) => $query->where('metal_id', $request->integer('metal_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')));

        $lots = (clone $query)
            ->with(['metal', 'purity', 'location'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'count' => (clone $query)->count(),
            'open_count' => (clone $query)->where('status', MaterialLotStatus::Open->value)->count(),
            'open_weight' => (clone $query)->where('status', MaterialLotStatus::Open->value)->sum('current_weight_g'),
            'melting_count' => (clone $query)->where('status', MaterialLotStatus::SentToMelting->value)->count(),
        ];

        return view('admin.inventory.lots.index', [
            'lots' => $lots,
            'metals' => Metal::orderBy('name')->get(),
            'statuses' => MaterialLotStatus::cases(),
            'summary' => $summary,
        ]);
    }

    /**
     * Show the form to register a new lot.
     */
    public function create(): View
    {
        return view('admin.inventory.lots.create', [
            'metals' => Metal::with(['purities' => fn ($query) => $query->orderBy('code')])->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new raw-material lot and register its opening weight.
     */
    public function store(StoreMaterialLotRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $storeId = (int) (Auth::user()->store_id ?? 0);

        $lot = $this->inventory->createMaterialLot([
            'store_id' => $storeId,
            'code' => $validated['code'] ?? MaterialLot::generateCode($storeId),
            'metal_id' => $validated['metal_id'],
            'purity_id' => $validated['purity_id'],
            'initial_weight_g' => $validated['initial_weight_g'],
            'location_id' => $validated['location_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        activity('inventario')
            ->performedOn($lot)
            ->causedBy(Auth::user())
            ->event('created')
            ->withChanges($this->activityCreatedChanges($lot))
            ->log("Lote de materia prima registrado: {$lot->code}");

        return redirect()->route('admin.inventory.lots.show', $lot)
            ->with('status', "El lote '{$lot->code}' se registró con {$lot->initial_weight_g} g.");
    }

    /**
     * Display a lot with its weight movement ledger.
     */
    public function show(MaterialLot $lot): View
    {
        $lot->load(['metal', 'purity', 'location', 'movements.user']);

        return view('admin.inventory.lots.show', ['lot' => $lot]);
    }

    /**
     * Apply a justified weight adjustment to a lot.
     */
    public function adjustWeight(AdjustLotWeightRequest $request, MaterialLot $lot): RedirectResponse
    {
        $validated = $request->validated();
        $snapshot = $this->activitySnapshot($lot);

        $this->inventory->adjustLotWeight($lot, (string) $validated['weight_delta_g'], $validated['reason']);

        activity('inventario')
            ->performedOn($lot)
            ->causedBy(Auth::user())
            ->event('adjusted')
            ->withChanges($this->activityUpdatedChanges($lot, $snapshot))
            ->withProperties(['weight_delta_g' => (string) $validated['weight_delta_g'], 'reason' => $validated['reason']])
            ->log("Ajuste de peso de lote: {$lot->code}");

        return redirect()->route('admin.inventory.lots.show', $lot)
            ->with('status', 'El ajuste de peso se registró y quedó auditado.');
    }

    /**
     * Send a lot to melting, zeroing its weight.
     */
    public function melt(SendToMeltingRequest $request, MaterialLot $lot): RedirectResponse
    {
        $validated = $request->validated();
        $snapshot = $this->activitySnapshot($lot);

        $this->inventory->sendLotToMelting($lot, $validated['reason']);

        activity('inventario')
            ->performedOn($lot)
            ->causedBy(Auth::user())
            ->event('sent_to_melting')
            ->withChanges($this->activityUpdatedChanges($lot, $snapshot))
            ->withProperties(['reason' => $validated['reason']])
            ->log("Lote enviado a fundición: {$lot->code}");

        return redirect()->route('admin.inventory.lots.show', $lot)
            ->with('status', 'El lote se envió a fundición.');
    }
}
