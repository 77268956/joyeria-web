<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductOrigin;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Http\Requests\Admin\SendToMeltingRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Location;
use App\Models\Metal;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Services\BarcodeGenerator;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProductController extends Controller
{
    public function __construct(protected InventoryService $inventory) {}

    /**
     * Display the internal inventory table with filters and export.
     */
    public function index(Request $request): View
    {
        $products = $this->filteredQuery($request)
            ->with(['category', 'metal', 'purity', 'location', 'primaryPhoto'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'metals' => Metal::orderBy('name')->get(),
            'statuses' => ProductStatus::cases(),
            'origins' => ProductOrigin::cases(),
        ]);
    }

    /**
     * Show the form to register a new piece.
     */
    public function create(): View
    {
        return view('admin.inventory.create', array_merge($this->formData(), [
            'statuses' => ProductStatus::cases(),
            'origins' => ProductOrigin::cases(),
        ]));
    }

    /**
     * Store a newly registered piece and register its entry movement.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $storeId = (int) (Auth::user()->store_id ?? 0);

        try {
            $product = DB::transaction(function () use ($request, $validated, $storeId): Product {
                $product = Product::create([
                    'store_id' => $storeId,
                    'sku' => $validated['sku'] ?? Product::generateSku($storeId),
                    'barcode' => $validated['barcode'] ?? Product::generateBarcode($storeId),
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'category_id' => $validated['category_id'],
                    'metal_id' => $validated['metal_id'],
                    'purity_id' => $validated['purity_id'] ?? null,
                    'weight_g' => $validated['weight_g'],
                    'cost' => $validated['cost'],
                    'price' => $validated['price'],
                    'location_id' => $validated['location_id'] ?? null,
                    'status' => $validated['status'] ?? ProductStatus::Available,
                    'origin' => $validated['origin'] ?? ProductOrigin::New,
                    'is_featured' => $request->boolean('is_featured'),
                    'is_public' => $request->boolean('is_public', true),
                ]);

                $this->storePhotos($request, $product);

                $this->inventory->registerProductEntry($product, null, 'Alta de pieza en inventario');

                return $product;
            });
        } catch (Throwable $exception) {
            return back()->withInput()->withErrors(['error' => 'No se pudo registrar la pieza: '.$exception->getMessage()]);
        }

        activity('inventario')
            ->performedOn($product)
            ->causedBy(Auth::user())
            ->withProperties(['sku' => $product->sku])
            ->log("Pieza registrada: {$product->name}");

        return redirect()->route('admin.inventory.show', $product)
            ->with('status', "La pieza '{$product->name}' se registró correctamente con SKU {$product->sku}.");
    }

    /**
     * Display the detail of a single piece with its movement ledger.
     */
    public function show(Product $product): View
    {
        $product->load(['category', 'metal', 'purity', 'location', 'photos', 'movements.user']);

        return view('admin.inventory.show', [
            'product' => $product,
            'stock' => $product->movements->sum('quantity_delta'),
        ]);
    }

    /**
     * Show the form to edit a piece.
     */
    public function edit(Product $product): View
    {
        return view('admin.inventory.edit', array_merge($this->formData(), [
            'product' => $product->load('photos'),
            'statuses' => ProductStatus::cases(),
            'origins' => ProductOrigin::cases(),
        ]));
    }

    /**
     * Update the piece data.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $previousPrice = (string) $product->price;

        try {
            DB::transaction(function () use ($request, $validated, $product): void {
                $product->update([
                    'sku' => $validated['sku'],
                    'barcode' => $validated['barcode'],
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'category_id' => $validated['category_id'],
                    'metal_id' => $validated['metal_id'],
                    'purity_id' => $validated['purity_id'] ?? null,
                    'weight_g' => $validated['weight_g'],
                    'cost' => $validated['cost'],
                    'price' => $validated['price'],
                    'location_id' => $validated['location_id'] ?? null,
                    'status' => $validated['status'] ?? $product->status,
                    'origin' => $validated['origin'] ?? $product->origin,
                    'is_featured' => $request->boolean('is_featured'),
                    'is_public' => $request->boolean('is_public', true),
                ]);

                $this->storePhotos($request, $product);
            });
        } catch (Throwable $exception) {
            return back()->withInput()->withErrors(['error' => 'No se pudo actualizar la pieza: '.$exception->getMessage()]);
        }

        activity('inventario')
            ->performedOn($product)
            ->causedBy(Auth::user())
            ->withProperties(['sku' => $product->sku, 'price_before' => $previousPrice, 'price_after' => (string) $product->price])
            ->log("Pieza actualizada: {$product->name}");

        return redirect()->route('admin.inventory.show', $product)
            ->with('status', "La pieza '{$product->name}' se actualizó correctamente.");
    }

    /**
     * Soft delete a piece.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        activity('inventario')
            ->performedOn($product)
            ->causedBy(Auth::user())
            ->log("Pieza eliminada: {$name}");

        return redirect()->route('admin.inventory.index')
            ->with('status', "La pieza '{$name}' se eliminó del catálogo.");
    }

    /**
     * Render a printable label with its Code128 barcode.
     */
    public function label(Product $product, BarcodeGenerator $barcode): View
    {
        return view('admin.inventory.label', [
            'product' => $product->load(['metal', 'purity']),
            'barcodeSvg' => $barcode->code128($product->barcode),
        ]);
    }

    /**
     * Export the filtered inventory to a CSV file readable by Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $products = $this->filteredQuery($request)
            ->with(['category', 'metal', 'purity', 'location'])
            ->latest('id')
            ->get();

        $filename = 'inventario_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($products): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'SKU', 'Codigo de barras', 'Nombre', 'Categoria', 'Metal', 'Pureza',
                'Peso (g)', 'Costo', 'Precio', 'Estado', 'Origen', 'Ubicacion', 'Publico',
            ]);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->barcode,
                    $product->name,
                    $product->category?->name,
                    $product->metal?->name,
                    $product->purity?->label,
                    (string) $product->weight_g,
                    (string) $product->cost,
                    (string) $product->price,
                    $product->status?->label(),
                    $product->origin?->label(),
                    $product->location?->name,
                    $product->is_public ? 'Si' : 'No',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Show the form to adjust stock with a mandatory reason.
     */
    public function showAdjustForm(Product $product): View
    {
        $product->load(['metal', 'purity']);

        return view('admin.inventory.adjust', [
            'product' => $product,
            'stock' => $product->movements()->sum('quantity_delta'),
        ]);
    }

    /**
     * Apply a justified stock adjustment to a piece.
     */
    public function adjust(AdjustStockRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        $this->inventory->adjustProduct($product, (int) $validated['quantity_delta'], $validated['reason']);

        activity('inventario')
            ->performedOn($product)
            ->causedBy(Auth::user())
            ->withProperties(['quantity_delta' => (int) $validated['quantity_delta'], 'reason' => $validated['reason']])
            ->log("Ajuste de existencias: {$product->name}");

        return redirect()->route('admin.inventory.show', $product)
            ->with('status', 'El ajuste de existencias se registró y quedó auditado.');
    }

    /**
     * Send a piece to melting.
     */
    public function melt(SendToMeltingRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        $this->inventory->sendProductToMelting($product, $validated['reason']);

        activity('inventario')
            ->performedOn($product)
            ->causedBy(Auth::user())
            ->withProperties(['reason' => $validated['reason']])
            ->log("Pieza enviada a fundición: {$product->name}");

        return redirect()->route('admin.inventory.show', $product)
            ->with('status', 'La pieza se envió a fundición y salió del stock disponible.');
    }

    /**
     * Build the filtered inventory query shared by listing and export.
     */
    protected function filteredQuery(Request $request): Builder
    {
        return Product::query()
            ->search($request->input('search'))
            ->when($request->filled('metal_id'), fn (Builder $query) => $query->where('metal_id', $request->integer('metal_id')))
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('origin'), fn (Builder $query) => $query->where('origin', $request->input('origin')));
    }

    /**
     * Catalog data required by the create and edit forms.
     *
     * @return array{categories: Collection, metals: Collection, locations: Collection}
     */
    protected function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'metals' => Metal::with(['purities' => fn ($query) => $query->orderBy('code')])->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ];
    }

    /**
     * Store every uploaded photo on the public disk.
     */
    protected function storePhotos(Request $request, Product $product): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        $hasPrimary = $product->photos()->where('is_primary', true)->exists();
        $sortOrder = (int) $product->photos()->max('sort_order');

        foreach ($request->file('photos') as $file) {
            $path = $file->store("products/{$product->id}", 'public');

            ProductPhoto::create([
                'product_id' => $product->id,
                'path' => $path,
                'sort_order' => ++$sortOrder,
                'is_primary' => ! $hasPrimary,
                'is_public' => true,
            ]);

            $hasPrimary = true;
        }
    }
}
