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

<style>
    .piece-form-section + .piece-form-section {
        padding-top: 1.35rem;
        margin-top: 1.35rem;
        border-top: 1px solid var(--color-linea);
    }

    .piece-form-section-head {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        margin-bottom: 1rem;
    }

    .piece-form-step {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        flex-shrink: 0;
        border-radius: 50%;
        background: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
        font-size: 0.68rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .piece-form-section-head h2 {
        margin: 0 0 0.15rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.02rem;
    }

    .piece-form-section-head p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .piece-form-note {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.65rem 0.8rem;
        margin-top: 0.75rem;
        border-left: 3px solid rgba(184, 154, 94, 0.65);
        border-radius: 3px;
        background: rgba(184, 154, 94, 0.07);
        color: var(--color-piedra);
        font-size: 0.76rem;
        line-height: 1.5;
    }

    .piece-form-note svg {
        flex-shrink: 0;
        margin-top: 1px;
        color: var(--color-dorado);
    }

    .piece-photo-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-top: 0.75rem;
    }

    .piece-photo-thumb {
        position: relative;
        width: 76px;
        height: 76px;
        overflow: hidden;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-crema);
    }

    .piece-photo-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .piece-photo-thumb figcaption {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        padding: 0.16rem 0.2rem;
        background: rgba(18, 22, 31, 0.72);
        color: #fff;
        font-size: 0.56rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-align: center;
        text-transform: uppercase;
    }

    .piece-form-switches {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.6rem;
        margin-bottom: 0;
    }

    .piece-switch {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.75rem 0.85rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-blanco);
        cursor: pointer;
        transition: border-color 0.2s, background-color 0.2s;
    }

    .piece-switch:hover {
        border-color: rgba(184, 154, 94, 0.55);
        background: #fdfbf7;
    }

    .piece-switch input {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        margin-top: 0.16rem;
        accent-color: var(--color-dorado);
    }

    .piece-switch strong {
        display: block;
        margin-bottom: 0.1rem;
        color: var(--color-noche);
        font-size: 0.82rem;
    }

    .piece-switch small {
        display: block;
        color: var(--color-piedra);
        font-size: 0.74rem;
        line-height: 1.4;
    }

    @media (max-width: 700px) {
        .piece-form-switches {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<section class="piece-form-section">
    <div class="piece-form-section-head">
        <span class="piece-form-step">01</span>
        <div>
            <h2>Identificación</h2>
            <p>Nombre comercial y clasificación de la pieza dentro del catálogo.</p>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Nombre de la pieza *</label>
        <input type="text" name="name" value="{{ old('name', $product?->name) }}" class="form-control" required maxlength="150" placeholder="Ej. Anillo solitario oro 18k">
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
</section>

<section class="piece-form-section">
    <div class="piece-form-section-head">
        <span class="piece-form-step">02</span>
        <div>
            <h2>Peso y valuación</h2>
            <p>Datos usados para costear, valuar y fijar el precio de venta de la pieza.</p>
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

    <div class="piece-form-note">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8h.01M11 12h1v4h1" />
        </svg>
        <span>El costo es de uso interno. El precio de venta es el que se muestra en el POS y, si la pieza es pública, en el catálogo web.</span>
    </div>
</section>

<section class="piece-form-section">
    <div class="piece-form-section-head">
        <span class="piece-form-step">03</span>
        <div>
            <h2>Control de inventario</h2>
            <p>Ubicación, estado comercial y códigos de identificación de la pieza.</p>
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

    <div class="piece-form-note">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
        </svg>
        <span>El stock no se modifica desde este formulario. Para sumar o retirar existencias usa la acción «Ajustar existencias», que genera un movimiento auditado.</span>
    </div>
</section>

<section class="piece-form-section">
    <div class="piece-form-section-head">
        <span class="piece-form-step">04</span>
        <div>
            <h2>Publicación y fotografías</h2>
            <p>Contenido visible para clientes y material visual de la pieza.</p>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Descripción (sitio público)</label>
        <textarea name="description" rows="3" class="form-control" placeholder="Texto que se mostrará en el sitio web público">{{ old('description', $product?->description) }}</textarea>
    </div>

    <div class="form-group">
        <label class="form-label">Fotografías</label>
        <input type="file" name="photos[]" class="form-control" accept="image/png,image/jpeg,image/webp" multiple style="height: auto; padding: 0.5rem;">
        <div class="form-hint">
            Hasta 6 imágenes JPG, PNG o WebP de máximo 4 MB cada una.
            @if ($product && $product->photos->isNotEmpty())
                Las fotografías actuales se conservan; las nuevas se agregan al final.
            @endif
        </div>

        @if ($product && $product->photos->isNotEmpty())
            <div class="piece-photo-strip">
                @foreach ($product->photos as $photo)
                    <figure class="piece-photo-thumb">
                        <img src="{{ asset('storage/'.$photo->path) }}" alt="Fotografía actual de {{ $product->name }}">
                        @if ($photo->is_primary)
                            <figcaption>Principal</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </div>

    <div class="piece-form-switches">
        <label class="piece-switch">
            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $product?->is_public ?? true) ? 'checked' : '' }}>
            <span>
                <strong>Visible en el sitio público</strong>
                <small>La pieza puede mostrarse en el catálogo web.</small>
            </span>
        </label>

        <label class="piece-switch">
            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product?->is_featured ?? false) ? 'checked' : '' }}>
            <span>
                <strong>Destacada</strong>
                <small>Se resalta en la selección de piezas del catálogo.</small>
            </span>
        </label>
    </div>
</section>

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
