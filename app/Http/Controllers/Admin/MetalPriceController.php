<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetManualMetalPriceRequest;
use App\Http\Requests\Admin\UpdatePurchaseSettingsRequest;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\PurchaseSetting;
use App\Models\Store;
use App\Services\MetalPriceService;
use App\Services\PricingCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class MetalPriceController extends Controller
{
    public function __construct(protected MetalPriceService $prices, protected PricingCalculator $calculator) {}

    /**
     * Show the current metal prices and their full history.
     */
    public function index(Request $request): View
    {
        $metals = Metal::with(['purities' => fn ($query) => $query->orderBy('code')])
            ->where('is_precious', true)
            ->orderBy('code')
            ->get();

        $current = $metals->mapWithKeys(
            fn (Metal $metal): array => [$metal->code => $this->prices->currentForMetal($metal)]
        );

        // Indicative price per gram for each purity, derived solely through PricingCalculator.
        $purityPrices = $metals->mapWithKeys(function (Metal $metal) use ($current): array {
            $price = $current[$metal->code] ?? null;

            if ($price === null) {
                return [$metal->code => collect()];
            }

            return [$metal->code => $metal->purities->mapWithKeys(
                fn ($purity): array => [$purity->code => $this->calculator->metalValue('1', (string) $price->price_per_gram, (string) $purity->fineness)]
            )];
        });

        $history = MetalPrice::with(['metal', 'setBy'])
            ->when($request->filled('metal_id'), fn ($query) => $query->where('metal_id', $request->integer('metal_id')))
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.prices.index', [
            'metals' => $metals,
            'current' => $current,
            'purityPrices' => $purityPrices,
            'history' => $history,
            'store' => $this->currentStore(),
        ]);
    }

    /**
     * Pull fresh spot prices from the configured provider.
     */
    public function refresh(): RedirectResponse
    {
        $store = $this->currentStore();

        try {
            $result = $this->prices->refreshAll($store);
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => 'No se pudo consultar el proveedor: '.$exception->getMessage()]);
        }

        if ($result['updated'] === 0) {
            return back()->withErrors(['error' => 'No se pudo actualizar ningún precio. Se conserva el último precio válido; revise la configuración del proveedor.']);
        }

        activity('precios')
            ->causedBy(Auth::user())
            ->withProperties($result)
            ->log('Actualización automática del precio del metal bajo demanda');

        $message = "Se actualizaron {$result['updated']} precio(s) desde el proveedor.";

        if ($result['failed'] !== []) {
            $message .= ' Sin actualizar: '.implode(', ', $result['failed']).'.';
        }

        return back()->with('status', $message);
    }

    /**
     * Fix a metal price manually with a mandatory, audited reason.
     */
    public function setManual(SetManualMetalPriceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $store = $this->currentStore();
        $metal = Metal::findOrFail($validated['metal_id']);

        $price = $this->prices->setManual(
            $store,
            $metal,
            (string) $validated['price_per_gram'],
            $validated['reason'],
            Auth::user(),
        );

        activity('precios')
            ->performedOn($price)
            ->causedBy(Auth::user())
            ->withProperties([
                'metal' => $metal->code,
                'price_per_gram' => (string) $price->price_per_gram,
                'reason' => $validated['reason'],
            ])
            ->log("Precio fijado manualmente para {$metal->name}");

        return back()->with('status', "El precio de {$metal->name} se fijó manualmente y quedó auditado.");
    }

    /**
     * Show the purchase-discount settings per metal and purity.
     */
    public function purchaseSettings(): View
    {
        $metals = Metal::with(['purities' => fn ($query) => $query->orderBy('code')])
            ->where('is_precious', true)
            ->orderBy('code')
            ->get();

        $settings = PurchaseSetting::get()->keyBy(
            fn (PurchaseSetting $setting): string => $setting->metal_id.'-'.($setting->purity_id ?? 'default')
        );

        return view('admin.prices.purchase-settings', [
            'metals' => $metals,
            'settings' => $settings,
            'store' => $this->currentStore(),
        ]);
    }

    /**
     * Persist the purchase-discount settings (owner-only).
     */
    public function updatePurchaseSettings(UpdatePurchaseSettingsRequest $request): RedirectResponse
    {
        $store = $this->currentStore();
        $discounts = $request->validated()['discounts'];
        $userId = Auth::id();

        DB::transaction(function () use ($store, $discounts, $userId): void {
            foreach ($discounts as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                [$metalId, $purityId] = array_pad(explode('-', (string) $key, 2), 2, 'default');
                $purityId = $purityId === 'default' || $purityId === '' ? null : (int) $purityId;

                PurchaseSetting::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'metal_id' => (int) $metalId,
                        'purity_id' => $purityId,
                    ],
                    [
                        'discount_percent' => $value,
                        'updated_by' => $userId,
                    ],
                );
            }
        });

        activity('precios')
            ->causedBy(Auth::user())
            ->log('Actualización de los descuentos de compra de metal');

        return back()->with('status', 'Los descuentos de compra se actualizaron correctamente.');
    }

    /**
     * Resolve the store the current user operates in.
     */
    protected function currentStore(): Store
    {
        $storeId = Auth::user()?->store_id;

        return ($storeId ? Store::find($storeId) : null) ?? Store::orderBy('id')->firstOrFail();
    }
}
