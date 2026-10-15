{{--
 * Shared piece form used by create and edit screens.
 * Expects: $categories, $metals, $locations, $statuses, $origins and an optional $product.
--}}
@php
    $product = $product ?? null;
    $puritiesByMetal = $metals->mapWithKeys(function ($metal) {
        return [$metal->id => $metal->purities->map(fn ($purity) => [
            'id' => $purity->id,
            'code' => $purity->code,
            'label' => $purity->label,
        ])->values()];
    });
@endphp

<div class="grid-2">
    <div class="form-group" style="grid-column: 1 / -1;">
        <label class="form-label">Nombre de la pieza *</label>
        <input type="text" name="name" value="{{ old('name', $product?->name) }}" class="form-control" required maxlength="150" placeholder="Ej. Anillo solitario oro 18k">
    </div>
</div>

<div class="grid-3">
    <div class="form-group">
        <label class="form-label">Categoría *</label>
        <select name="category_id" class="form-select" required>
            <option value="">Selecciona…</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ (string) old('category_id', $product?->category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">Metal *</label>
        <select name="metal_id" id="metal_id" class="form-select" required>
            <option value="">Selecciona…</option>
            @foreach ($metals as $metal)
                <option value="{{ $metal->id }}" {{ (string) old('metal_id', $product?->metal_id) === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">Pureza</label>
        <select name="purity_id" id="purity_id" class="form-select">
            <option value="">—</option>
        </select>
        <div class="form-hint">No aplica en acero o fantasía.</div>
    </div>
</div>

<div class="grid-3">
    <div class="form-group">
        <label class="form-label">Peso (g) *</label>
        <input type="number" step="0.001" min="0.001" name="weight_g" value="{{ old('weight_g', $product?->weight_g) }}" class="form-control" required>
    </div>

    <div class="form-group">
        <label class="form-label">Costo (interno) *</label>
        <input type="number" step="0.01" min="0" name="cost" value="{{ old('cost', $product?->cost) }}" class="form-control" required>
    </div>

    <div class="form-group">
        <label class="form-label">Precio de venta *</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product?->price) }}" class="form-control" required>
    </div>
</div>

<div class="grid-3">
    <div class="form-group">
        <label class="form-label">Ubicación</label>
        <select name="location_id" class="form-select">
            <option value="">—</option>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" {{ (string) old('location_id', $product?->location_id) === (string) $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">Estado</label>
        <select name="status" class="form-select">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" {{ old('status', $product?->status?->value ?? 'available') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">Origen</label>
        <select name="origin" class="form-select">
            @foreach ($origins as $origin)
                <option value="{{ $origin->value }}" {{ old('origin', $product?->origin?->value ?? 'new') === $origin->value ? 'selected' : '' }}>{{ $origin->label() }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid-2">
    <div class="form-group">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $product?->sku) }}" class="form-control" maxlength="40" placeholder="Se genera automáticamente si se deja vacío">
    </div>

    <div class="form-group">
        <label class="form-label">Código de barras</label>
        <input type="text" name="barcode" value="{{ old('barcode', $product?->barcode) }}" class="form-control" maxlength="60" placeholder="Se genera automáticamente si se deja vacío">
    </div>
</div>

<div class="form-group">
    <label class="form-label">Descripción (sitio público)</label>
    <textarea name="description" rows="3" class="form-control" placeholder="Texto que se mostrará en el sitio web público">{{ old('description', $product?->description) }}</textarea>
</div>

<div class="form-group">
    <label class="form-label">Fotografías</label>
    <input type="file" name="photos[]" class="form-control" accept="image/png,image/jpeg,image/webp" multiple style="height: auto; padding: 0.5rem;">
    <div class="form-hint">Hasta 6 imágenes JPG, PNG o WebP de máximo 4 MB cada una.</div>

    @if ($product && $product->photos->isNotEmpty())
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.75rem;">
            @foreach ($product->photos as $photo)
                <img src="{{ asset('storage/'.$photo->path) }}" alt="Foto" style="width: 64px; height: 64px; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-linea);">
            @endforeach
        </div>
    @endif
</div>

<div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
    <label class="form-check">
        <input type="checkbox" name="is_public" value="1" {{ old('is_public', $product?->is_public ?? true) ? 'checked' : '' }}>
        <span>Visible en el sitio público</span>
    </label>
    <label class="form-check">
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product?->is_featured ?? false) ? 'checked' : '' }}>
        <span>Destacada</span>
    </label>
</div>

@push('scripts')
<script>
    const puritiesByMetal = @json($puritiesByMetal);
    const selectedPurity = "{{ (string) old('purity_id', $product?->purity_id) }}";

    function syncPurities() {
        const metalId = document.getElementById('metal_id').value;
        const puritySelect = document.getElementById('purity_id');
        const purities = puritiesByMetal[metalId] || [];

        puritySelect.innerHTML = '<option value="">—</option>';
        purities.forEach(function (purity) {
            const option = document.createElement('option');
            option.value = purity.id;
            option.textContent = purity.label || purity.code;
            if (String(purity.id) === selectedPurity) {
                option.selected = true;
            }
            puritySelect.appendChild(option);
        });
    }

    document.getElementById('metal_id').addEventListener('change', syncPurities);
    syncPurities();
</script>
@endpush
