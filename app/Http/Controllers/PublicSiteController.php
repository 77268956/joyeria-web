<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\MetalPurity;
use App\Models\Product;
use App\Models\Store;
use App\Services\PricingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only controllers for the public marketing site.
 *
 * These views must never expose confidential data (cost, margin, provider).
 * Valuation is delegated to {@see PricingCalculator}; this class only
 * orchestrates queries and responses.
 */
class PublicSiteController extends Controller
{
    public function __construct(protected PricingCalculator $calculator) {}

    /**
     * Brand landing page with a full-bleed featured piece.
     */
    public function home(): View
    {
        $store = $this->currentStore();

        $featured = Product::query()
            ->when($store, fn ($query) => $query->where('store_id', $store->id))
            ->publiclyVisible()
            ->with(['metal', 'category', 'primaryPhoto'])
            ->orderByDesc('is_featured')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('public.home', [
            'featured' => $featured,
            'hero' => $featured->first(),
        ]);
    }

    /**
     * Public catalogue with metal and category filters.
     */
    public function catalog(Request $request): View
    {
        $store = $this->currentStore();

        $selectedMetal = $request->string('metal')->toString() ?: null;
        $selectedCategory = $request->string('category')->toString() ?: null;

        $products = Product::query()
            ->when($store, fn ($query) => $query->where('store_id', $store->id))
            ->publiclyVisible()
            ->with(['metal', 'category', 'primaryPhoto'])
            ->when($selectedMetal, function ($query) use ($selectedMetal) {
                $query->whereHas('metal', fn ($metal) => $metal->where('code', $selectedMetal));
            })
            ->when($selectedCategory, function ($query) use ($selectedCategory) {
                $query->whereHas('category', fn ($category) => $category->where('slug', $selectedCategory));
            })
            ->orderByDesc('is_featured')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('public.catalog', [
            'products' => $products,
            'metals' => Metal::query()->orderBy('name')->get(),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedMetal' => $selectedMetal,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    /**
     * Public detail of a single piece with its photo gallery.
     */
    public function product(Product $product): View
    {
        abort_unless($product->is_public && $product->status === ProductStatus::Available, 404);

        $product->load([
            'metal',
            'purity',
            'category',
            'photos' => fn ($query) => $query->where('is_public', true)->orderBy('sort_order'),
        ]);

        return view('public.product-detail', [
            'product' => $product,
            'whatsappUrl' => $this->whatsappUrl(
                'Hola, me interesa la pieza '.$product->name.' ('.$product->sku.').'
            ),
        ]);
    }

    /**
     * Estimated gold buy-back calculator.
     */
    public function sellGold(): View
    {
        $metals = Metal::query()
            ->where('is_precious', true)
            ->with(['purities' => fn ($query) => $query->where('is_active', true)->orderBy('code')])
            ->orderBy('name')
            ->get();

        $metalsJson = $metals->map(fn (Metal $metal): array => [
            'id' => $metal->id,
            'name' => $metal->name,
            'purities' => $metal->purities->map(fn ($purity): array => [
                'id' => $purity->id,
                'label' => $purity->label,
            ])->values()->all(),
        ])->values()->all();

        return view('public.sell-gold', [
            'metals' => $metals,
            'metalsJson' => $metalsJson,
            'goldPricePerGram' => $this->pricePerGram('GOLD'),
        ]);
    }

    /**
     * Quote an estimated amount for a weight and purity (no internal discount).
     */
    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metal_id' => ['required', 'integer', 'exists:metals,id'],
            'purity_id' => ['required', 'integer', 'exists:metal_purities,id'],
            'weight_g' => ['required', 'numeric', 'gt:0', 'max:100000'],
        ]);

        $purity = MetalPurity::findOrFail($validated['purity_id']);
        $pricePerGram = $this->pricePerGram(Metal::findOrFail($validated['metal_id'])->code);

        if ($pricePerGram === null) {
            return response()->json([
                'message' => 'Aún no hay una cotización del día para este metal. Visítanos o contáctanos por WhatsApp.',
            ], 422);
        }

        $amount = $this->calculator->metalValue(
            (string) $validated['weight_g'],
            $pricePerGram,
            (string) $purity->fineness,
        );

        return response()->json([
            'amount' => $amount,
            'price_per_gram' => $pricePerGram,
            'fineness' => (string) $purity->fineness,
            'purity' => $purity->label,
        ]);
    }

    /**
     * Store location, hours and map.
     */
    public function store(): View
    {
        return view('public.store');
    }

    /**
     * Contact page with the inquiry form.
     */
    public function contact(): View
    {
        return view('public.contact');
    }

    /**
     * Persist a visitor inquiry.
     */
    public function submitContact(StoreContactMessageRequest $request): RedirectResponse
    {
        $store = $this->currentStore();

        abort_if($store === null, 503, 'El sitio no está disponible en este momento.');

        ContactMessage::create([
            'store_id' => $store->id,
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'message' => $request->validated('message'),
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->route('public.contact')
            ->with('contact_status', '¡Gracias por escribirnos! Te responderemos a la brevedad.');
    }

    /**
     * The store that backs the public site.
     */
    protected function currentStore(): ?Store
    {
        return Store::publicSite();
    }

    /**
     * Current price per gram of a metal, or null when none is recorded.
     */
    protected function pricePerGram(string $metalCode): ?string
    {
        $store = $this->currentStore();
        $metal = Metal::query()->where('code', strtoupper($metalCode))->first();

        if ($store === null || $metal === null) {
            return null;
        }

        $price = MetalPrice::query()
            ->where('store_id', $store->id)
            ->where('metal_id', $metal->id)
            ->latest('created_at')
            ->latest('id')
            ->first();

        return $price?->price_per_gram;
    }

    /**
     * Build a WhatsApp deep link using the store's configured number.
     */
    protected function whatsappUrl(string $text): ?string
    {
        $number = $this->currentStore()?->systemSettings?->whatsapp_number;

        if (blank($number)) {
            return null;
        }

        return 'https://wa.me/'.preg_replace('/\D+/', '', $number).'?text='.rawurlencode($text);
    }
}
