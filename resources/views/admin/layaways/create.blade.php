@extends('layouts.admin')

@section('title', 'Nuevo Apartado')

@section('body_class', 'full-viewport')

@push('styles')
<style>
    .layaway-create-page {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        gap: 0.85rem;
    }
    .layaway-create-page .page-header { margin-bottom: 0; flex-shrink: 0; }
    .layaway-create-page .alert { margin-bottom: 0; flex-shrink: 0; }

    .layaway-create-page .layaway-grid {
        flex: 1;
        min-height: 0;
        align-items: stretch;
    }
    .layaway-create-page .layaway-grid > .card {
        padding: 1.25rem 1.5rem 0;
        margin-bottom: 0;
        overflow-y: auto;
        min-height: 0;
    }
    .layaway-create-page .layaway-summary { padding-bottom: 1.25rem; display: flex; flex-direction: column; }

    .layaway-submit-bar {
        position: sticky;
        bottom: 0;
        margin: 0.9rem -1.5rem 0;
        padding: 0.9rem 1.5rem;
        background-color: var(--color-blanco);
        border-top: 1px solid var(--color-linea);
    }

    @media (max-width: 1024px) {
        .layaway-create-page { display: block; }
        .layaway-create-page .page-header { margin-bottom: 1.5rem; }
        .layaway-create-page .alert { margin-bottom: 1.5rem; }
        .layaway-create-page .layaway-grid { display: block; }
        .layaway-create-page .layaway-grid > .card {
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            overflow: visible;
        }
        .layaway-submit-bar {
            position: static;
            margin: 1.25rem 0 0;
            padding: 0;
            background: none;
            border-top: none;
        }
    }

    .layaway-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(300px, 1fr);
        gap: 1rem;
    }

    .layaway-create-page .form-group { margin-bottom: 0.95rem; }
    .layaway-create-page .form-control,
    .layaway-create-page .form-select { height: 40px; }
    .layaway-create-page .layaway-section-title { margin-bottom: 0.85rem; }

    .layaway-section { margin-bottom: 1.35rem; }
    .layaway-section:last-of-type { margin-bottom: 0; }

    .layaway-section-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-family: var(--font-serif);
        font-size: 1.12rem;
        font-weight: 600;
        color: var(--color-noche);
        margin-bottom: 1rem;
    }
    .layaway-step {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background-color: var(--color-noche);
        color: var(--color-hueso);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-sans);
        font-size: 0.72rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    .picker-wrap { position: relative; }
    .picker-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background-color: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        box-shadow: 0 8px 24px rgba(18, 22, 31, 0.12);
        max-height: 280px;
        overflow-y: auto;
        z-index: 50;
        padding: 0.35rem;
    }
    .picker-chip {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        width: 100%;
        margin-bottom: 0.25rem;
        padding: 0.45rem 0.65rem;
        border: 1px solid transparent;
        border-radius: 4px;
        background-color: transparent;
        text-align: left;
        cursor: pointer;
        font: inherit;
        transition: background-color 0.15s, border-color 0.15s;
    }
    .picker-chip:hover {
        background-color: var(--color-crema);
        border-color: var(--color-dorado);
    }
    .picker-chip-body { flex: 1; min-width: 0; }
    .picker-chip-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--color-noche);
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .picker-chip-meta {
        font-size: 0.75rem;
        color: var(--color-piedra);
        margin-top: 0.1rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .picker-chip-action {
        font-size: 0.75rem;
        color: var(--color-dorado);
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .picker-empty {
        font-size: 0.8rem;
        padding: 0.5rem 0.65rem;
        color: var(--color-piedra);
    }
    .product-result-thumb {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-crema);
        color: var(--color-dorado);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-family: var(--font-serif);
        font-size: 1rem;
    }
    .product-result-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .product-result-price {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--color-noche);
        white-space: nowrap;
    }
    .picker-clear-btn {
        height: 26px;
        padding: 0 0.65rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
        color: var(--color-piedra);
        font-family: var(--font-sans);
        font-size: 0.74rem;
        font-weight: 500;
        cursor: pointer;
        flex-shrink: 0;
        transition: border-color 0.15s, color 0.15s, background-color 0.15s;
    }
    .picker-clear-btn:hover {
        border-color: var(--color-error);
        color: var(--color-error);
        background-color: #FDEDED;
    }

    .customer-selected {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.75rem;
        padding: 0.55rem 0.75rem;
        border: 1px solid var(--color-dorado);
        border-radius: 4px;
        background-color: var(--color-crema);
    }
    .customer-selected.is-visible { display: flex; }
    .customer-selected-content { display: flex; align-items: center; gap: 0.65rem; min-width: 0; }
    .customer-selected-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid var(--color-linea);
        background-color: var(--color-blanco);
        color: var(--color-dorado);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .customer-selected-name {
        font-weight: 600;
        font-size: 0.92rem;
        color: var(--color-noche);
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .customer-selected-meta {
        font-size: 0.78rem;
        color: var(--color-piedra);
        margin-top: 0.15rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-preview {
        display: none;
        align-items: center;
        gap: 0.9rem;
        margin-top: 0.75rem;
        padding: 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-crema);
    }
    .product-preview.is-visible { display: flex; }
    .product-preview-media {
        width: 76px;
        height: 76px;
        flex-shrink: 0;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: var(--color-dorado);
        font-family: var(--font-serif);
        font-size: 1.7rem;
    }
    .product-preview-media img { width: 100%; height: 100%; object-fit: cover; }
    .product-preview-body { flex: 1; min-width: 0; }
    .product-preview-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--color-noche);
        line-height: 1.25;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .product-preview-meta {
        font-size: 0.78rem;
        color: var(--color-piedra);
        margin-top: 0.2rem;
    }
    .product-preview-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.45rem;
        flex-shrink: 0;
    }
    .product-preview-price {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--color-noche);
        white-space: nowrap;
    }

    .quick-amounts { display: flex; gap: 0.4rem; flex-wrap: wrap; margin-top: 0.45rem; }
    .quick-amount-btn {
        height: 28px;
        padding: 0 0.8rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
        color: var(--color-piedra);
        font-family: var(--font-sans);
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.02em;
        cursor: pointer;
        transition: border-color 0.15s, color 0.15s, background-color 0.15s;
    }
    .quick-amount-btn:hover:not(:disabled) {
        border-color: var(--color-dorado);
        color: var(--color-dorado);
        background-color: rgba(184, 154, 94, 0.08);
    }
    .quick-amount-btn:disabled { opacity: 0.45; cursor: not-allowed; }

    .amount-warning {
        display: none;
        align-items: center;
        gap: 0.4rem;
        margin-top: 0.5rem;
        color: var(--color-error);
        font-size: 0.8rem;
        font-weight: 500;
    }
    .amount-warning.is-visible { display: flex; }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 1rem;
    }
    .progress-track {
        height: 8px;
        border-radius: 4px;
        background-color: var(--color-crema);
        overflow: hidden;
        margin: 0.55rem 0 0.25rem;
    }
    .progress-fill {
        height: 100%;
        width: 0%;
        background-color: var(--color-dorado);
        border-radius: 4px;
        transition: width 0.25s ease;
    }
    .progress-label {
        font-size: 0.75rem;
        color: var(--color-piedra);
        text-align: right;
    }
</style>
@endpush

@section('content')
<div class="layaway-create-page">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Nuevo Apartado</h1>
            <p class="page-subtitle">Reserva una pieza con un abono inicial y una fecha límite de pago.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.layaways.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="layaway-grid">
    <div class="card">
        <form action="{{ route('admin.layaways.store') }}" method="POST" id="layaway-form">
            @csrf

            <div class="layaway-section">
                <h2 class="layaway-section-title"><span class="layaway-step">1</span> Pieza y cliente</h2>

                <div class="form-group">
                    <label class="form-label" for="layaway-product-search">Pieza a apartar <span style="color: var(--color-error);">*</span></label>
                    <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id', $selectedProduct['id'] ?? '') }}">

                    @if (! $hasAvailableProducts)
                        <p class="muted" style="font-size: 0.9rem;">No hay piezas disponibles para apartar en este momento.</p>
                    @else
                        <div class="picker-wrap" id="product-search-wrap">
                            <input type="text" id="layaway-product-search" class="form-control" placeholder="Buscar pieza por nombre, SKU o código de barras..." autocomplete="off" autofocus>
                            <div class="picker-dropdown" id="product-search-results" style="display: none;"></div>
                        </div>
                    @endif
                    @error('product_id') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror

                    <div class="product-preview" id="product-preview">
                        <div class="product-preview-media" id="preview-media"></div>
                        <div class="product-preview-body">
                            <div class="product-preview-name" id="preview-name"></div>
                            <div class="product-preview-meta" id="preview-meta"></div>
                        </div>
                        <div class="product-preview-right">
                            <div class="product-preview-price num" id="preview-price"></div>
                            <button type="button" class="picker-clear-btn" id="preview-clear">✕ Quitar</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="layaway-customer-search">Cliente <span style="color: var(--color-error);">*</span></label>
                    <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $selectedCustomer['id'] ?? '') }}">

                    <div class="picker-wrap" id="customer-search-wrap">
                        <input type="text" id="layaway-customer-search" class="form-control" placeholder="Buscar cliente por nombre, documento o teléfono..." autocomplete="off">
                        <div class="picker-dropdown" id="customer-search-results" style="display: none;"></div>
                    </div>
                    @error('customer_id') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror

                    <div class="customer-selected" id="customer-selected">
                        <div class="customer-selected-content">
                            <div class="customer-selected-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div style="min-width: 0;">
                                <div class="customer-selected-name" id="customer-selected-name"></div>
                                <div class="customer-selected-meta" id="customer-selected-meta"></div>
                            </div>
                        </div>
                        <button type="button" class="picker-clear-btn" id="customer-clear">✕ Quitar</button>
                    </div>

                    @can('customers.manage')
                        <p class="form-hint">¿Cliente nuevo? <a href="{{ route('admin.customers.create') }}" target="_blank" style="color: var(--color-dorado);">Regístralo aquí</a> y vuelve a esta pantalla.</p>
                    @endcan
                </div>
            </div>

            <div class="layaway-section">
                <h2 class="layaway-section-title"><span class="layaway-step">2</span> Condiciones del apartado</h2>

                <div class="form-group">
                    <label class="form-label" for="due_date">Fecha límite <span style="color: var(--color-error);">*</span></label>
                    <input type="date" name="due_date" id="due_date" class="form-control num" value="{{ old('due_date', $defaultDueDate) }}" min="{{ now()->toDateString() }}" max="{{ now()->addDays($maxDays)->toDateString() }}" required>
                    @error('due_date') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                    <p class="form-hint">Plazo máximo: {{ $maxDays }} días (hasta el {{ now()->addDays($maxDays)->locale('es')->isoFormat('LL') }}).</p>
                </div>
            </div>

            <div class="layaway-section">
                <h2 class="layaway-section-title"><span class="layaway-step">3</span> Abono inicial</h2>

                <div class="form-group">
                    <label class="form-label" for="initial_amount">Monto del abono <span style="color: var(--color-error);">*</span></label>
                    <input type="number" step="0.01" min="0" name="initial_amount" id="initial_amount" class="form-control num" value="{{ old('initial_amount', '0.00') }}" required>
                    @error('initial_amount') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                    <div class="quick-amounts">
                        <button type="button" class="quick-amount-btn" data-ratio="0.25" disabled>25%</button>
                        <button type="button" class="quick-amount-btn" data-ratio="0.5" disabled>50%</button>
                        <button type="button" class="quick-amount-btn" data-ratio="0.75" disabled>75%</button>
                        <button type="button" class="quick-amount-btn" data-ratio="1" disabled>Precio total</button>
                    </div>
                    <div class="amount-warning" id="amount-warning">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>El abono inicial no puede superar el precio de la pieza.</span>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="initial_method">Método del abono inicial</label>
                        <select name="initial_method" id="initial_method" class="form-select">
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->value }}" {{ old('initial_method', 'cash') === $method->value ? 'selected' : '' }}>{{ $method->label() }}</option>
                            @endforeach
                        </select>
                        @error('initial_method') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reference">Referencia (opcional)</label>
                        <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference') }}" maxlength="80" placeholder="N.º de operación, transferencia...">
                        @error('reference') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="layaway-submit-bar">
                <button type="submit" class="btn btn-gold" id="layaway-submit" style="width: 100%;">Registrar apartado</button>
                <p class="form-hint" id="submit-hint" style="text-align: center; margin-top: 0.55rem; margin-bottom: 0;">La pieza quedará reservada y el abono ingresará a la caja activa.</p>
            </div>
        </form>
    </div>

    <div class="card layaway-summary">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 0.9rem;">Resumen</h2>

        <div id="summary-empty">
            <p class="muted" style="font-size: 0.9rem;">Selecciona una pieza para ver el resumen del apartado.</p>
        </div>

        <div id="summary-body" style="display: none;">
            <div style="display: grid; gap: 0.65rem;">
                <div class="summary-row">
                    <span class="muted">Pieza</span>
                    <strong id="sum-piece" style="font-size: 0.9rem; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 65%;"></strong>
                </div>
                <div class="summary-row">
                    <span class="muted">Precio de la pieza</span>
                    <span class="num" id="sum-total">$ 0.00</span>
                </div>
                <div class="summary-row">
                    <span class="muted">Abono inicial</span>
                    <span class="num" id="sum-initial">$ 0.00</span>
                </div>
                <div class="progress-track"><div class="progress-fill" id="progress-fill"></div></div>
                <p class="progress-label" id="progress-label">0% abonado</p>
                <div class="summary-row" style="border-top: 1px solid var(--color-linea); padding-top: 0.65rem;">
                    <strong class="font-serif">Saldo pendiente</strong>
                    <strong class="font-serif num" style="font-size: 1.3rem;" id="sum-balance">$ 0.00</strong>
                </div>
                <div class="summary-row" style="margin-top: 0.15rem;">
                    <span class="muted">Vence</span>
                    <span class="num" id="sum-due">—</span>
                </div>
                <div class="summary-row">
                    <span class="muted">Plazo</span>
                    <span id="sum-days">—</span>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const productSearchUrl = '{{ route('admin.layaways.search') }}';
    const customerSearchUrl = '{{ route('admin.customers.search') }}';
    const initialProduct = @json($selectedProduct);
    const initialCustomer = @json($selectedCustomer);

    const form = document.getElementById('layaway-form');
    const submit = document.getElementById('layaway-submit');
    const submitHint = document.getElementById('submit-hint');
    const submitLabel = submit ? submit.textContent : '';

    const product = document.getElementById('product_id');
    const customer = document.getElementById('customer_id');
    const dueDate = document.getElementById('due_date');
    const initial = document.getElementById('initial_amount');

    const productSearchWrap = document.getElementById('product-search-wrap');
    const productSearchInput = document.getElementById('layaway-product-search');
    const productResults = document.getElementById('product-search-results');

    const customerSearchWrap = document.getElementById('customer-search-wrap');
    const customerSearchInput = document.getElementById('layaway-customer-search');
    const customerResults = document.getElementById('customer-search-results');
    const customerSelected = document.getElementById('customer-selected');
    const customerSelectedName = document.getElementById('customer-selected-name');
    const customerSelectedMeta = document.getElementById('customer-selected-meta');
    const customerClear = document.getElementById('customer-clear');

    const preview = document.getElementById('product-preview');
    const previewMedia = document.getElementById('preview-media');
    const previewName = document.getElementById('preview-name');
    const previewMeta = document.getElementById('preview-meta');
    const previewPrice = document.getElementById('preview-price');
    const previewClear = document.getElementById('preview-clear');

    const warning = document.getElementById('amount-warning');
    const quickButtons = document.querySelectorAll('.quick-amount-btn');

    const summaryEmpty = document.getElementById('summary-empty');
    const summaryBody = document.getElementById('summary-body');
    const sumPiece = document.getElementById('sum-piece');
    const sumTotal = document.getElementById('sum-total');
    const sumInitial = document.getElementById('sum-initial');
    const sumBalance = document.getElementById('sum-balance');
    const progressFill = document.getElementById('progress-fill');
    const progressLabel = document.getElementById('progress-label');
    const sumDue = document.getElementById('sum-due');
    const sumDays = document.getElementById('sum-days');

    let selected = initialProduct;
    let selectedCustomer = initialCustomer;
    const searchState = { product: null, customer: null };

    const money = (n) => '$ ' + (Math.max(0, Number(n) || 0)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function price() {
        return selected ? Number(selected.price) || 0 : 0;
    }

    function formatWeight(weight) {
        const value = String(weight || '').replace(/0+$/, '').replace(/\.$/, '');

        return value ? value + ' g' : '';
    }

    function metaText(data) {
        return [
            data.sku,
            [data.metal, data.purity].filter(Boolean).join(' '),
            formatWeight(data.weight_g),
        ].filter(Boolean).join(' · ') || 'Sin detalles';
    }

    function fillThumb(element, data) {
        element.textContent = '';

        if (data.photo_url) {
            const img = document.createElement('img');
            img.src = data.photo_url;
            img.alt = '';
            element.appendChild(img);
        } else {
            element.textContent = (data.name || '?').charAt(0).toUpperCase();
        }
    }

    function runPickerSearch(url, key, term, resultsBox, stateKey, buildChip, emptyMessage) {
        if (! term) {
            resultsBox.innerHTML = '';
            resultsBox.style.display = 'none';
            return;
        }

        if (searchState[stateKey]) {
            searchState[stateKey].abort();
        }
        searchState[stateKey] = new AbortController();

        fetch(url + '?q=' + encodeURIComponent(term), {
            headers: { 'Accept': 'application/json' },
            signal: searchState[stateKey].signal,
        })
            .then((response) => response.json())
            .then((data) => {
                resultsBox.innerHTML = '';

                if (! data[key] || data[key].length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'picker-empty';
                    empty.textContent = emptyMessage;
                    resultsBox.appendChild(empty);
                    resultsBox.style.display = 'block';
                    return;
                }

                data[key].forEach((item) => {
                    resultsBox.appendChild(buildChip(item));
                });
                resultsBox.style.display = 'block';
            })
            .catch(() => {});
    }

    function wirePicker(input, wrap, resultsBox, url, key, stateKey, buildChip, emptyMessage) {
        if (! input) {
            return;
        }

        let debounce = null;

        input.addEventListener('input', (event) => {
            clearTimeout(debounce);
            debounce = setTimeout(() => runPickerSearch(url, key, event.target.value.trim(), resultsBox, stateKey, buildChip, emptyMessage), 200);
        });

        input.addEventListener('focus', () => {
            const value = input.value.trim();

            if (value) {
                runPickerSearch(url, key, value, resultsBox, stateKey, buildChip, emptyMessage);
            }
        });

        document.addEventListener('click', (event) => {
            if (wrap && ! wrap.contains(event.target)) {
                resultsBox.style.display = 'none';
            }
        });
    }

    function refreshProduct() {
        if (! selected) {
            product.value = '';
            preview.classList.remove('is-visible');
            summaryEmpty.style.display = 'block';
            summaryBody.style.display = 'none';
            quickButtons.forEach((btn) => { btn.disabled = true; });
            return;
        }

        product.value = selected.id;
        preview.classList.add('is-visible');
        summaryEmpty.style.display = 'none';
        summaryBody.style.display = 'block';

        previewName.textContent = selected.name || '';
        previewMeta.textContent = metaText(selected);
        previewPrice.textContent = money(selected.price);
        sumPiece.textContent = selected.name || '';
        fillThumb(previewMedia, selected);

        quickButtons.forEach((btn) => { btn.disabled = false; });
    }

    function selectProduct(data) {
        selected = data;
        productResults.innerHTML = '';
        productResults.style.display = 'none';
        refreshProduct();
        refreshAmount();
        refreshSubmit();
    }

    function clearProduct() {
        selected = null;
        refreshProduct();
        refreshAmount();
        refreshSubmit();

        if (productSearchInput) {
            productSearchInput.value = '';
            productSearchInput.focus();
        }
    }

    function buildProductChip(data) {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'picker-chip';

        const thumb = document.createElement('div');
        thumb.className = 'product-result-thumb';
        fillThumb(thumb, data);

        const body = document.createElement('div');
        body.className = 'picker-chip-body';

        const name = document.createElement('div');
        name.className = 'picker-chip-title';
        name.textContent = data.name || '';

        const meta = document.createElement('div');
        meta.className = 'picker-chip-meta';
        meta.textContent = metaText(data);

        body.appendChild(name);
        body.appendChild(meta);

        const priceEl = document.createElement('div');
        priceEl.className = 'product-result-price num';
        priceEl.textContent = money(data.price);

        chip.appendChild(thumb);
        chip.appendChild(body);
        chip.appendChild(priceEl);

        chip.addEventListener('click', () => selectProduct(data));

        return chip;
    }

    function customerMetaText(data) {
        return [data.document, data.phone].filter(Boolean).join(' · ') || 'Sin detalles de contacto';
    }

    function refreshCustomer() {
        if (! selectedCustomer) {
            customer.value = '';
            customerSelected.classList.remove('is-visible');
            return;
        }

        customer.value = selectedCustomer.id;
        customerSelected.classList.add('is-visible');
        customerSelectedName.textContent = selectedCustomer.full_name || '';
        customerSelectedMeta.textContent = customerMetaText(selectedCustomer);
    }

    function selectCustomer(data) {
        selectedCustomer = data;
        customerResults.innerHTML = '';
        customerResults.style.display = 'none';
        refreshCustomer();
        refreshSubmit();
    }

    function clearCustomer() {
        selectedCustomer = null;
        refreshCustomer();
        refreshSubmit();

        if (customerSearchInput) {
            customerSearchInput.value = '';
            customerSearchInput.focus();
        }
    }

    function buildCustomerChip(data) {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'picker-chip';

        const icon = document.createElement('div');
        icon.className = 'customer-selected-icon';
        icon.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';

        const body = document.createElement('div');
        body.className = 'picker-chip-body';

        const name = document.createElement('div');
        name.className = 'picker-chip-title';
        name.textContent = data.full_name || '';

        const meta = document.createElement('div');
        meta.className = 'picker-chip-meta';
        meta.textContent = customerMetaText(data);

        body.appendChild(name);
        body.appendChild(meta);

        const action = document.createElement('span');
        action.className = 'picker-chip-action';
        action.textContent = 'Elegir →';

        chip.appendChild(icon);
        chip.appendChild(body);
        chip.appendChild(action);

        chip.addEventListener('click', () => selectCustomer(data));

        return chip;
    }

    wirePicker(productSearchInput, productSearchWrap, productResults, productSearchUrl, 'products', 'product', buildProductChip, 'Sin piezas coincidentes.');
    wirePicker(customerSearchInput, customerSearchWrap, customerResults, customerSearchUrl, 'customers', 'customer', buildCustomerChip, 'Sin clientes coincidentes. Puedes registrar uno nuevo.');

    if (previewClear) {
        previewClear.addEventListener('click', clearProduct);
    }

    if (customerClear) {
        customerClear.addEventListener('click', clearCustomer);
    }

    function refreshAmount() {
        const total = price();
        const paid = Math.max(0, Number(initial.value) || 0);
        const balance = Math.max(0, total - paid);
        const percent = total > 0 ? Math.min(100, (paid / total) * 100) : 0;

        sumTotal.textContent = money(total);
        sumInitial.textContent = money(paid);
        sumBalance.textContent = money(balance);
        progressFill.style.width = percent.toFixed(1) + '%';
        progressLabel.textContent = percent.toFixed(0) + '% abonado';

        const exceeds = total > 0 && paid > total;

        initial.style.borderColor = exceeds ? 'var(--color-error)' : '';
        initial.setAttribute('max', total > 0 ? total.toFixed(2) : '');
        warning.classList.toggle('is-visible', exceeds);
    }

    function refreshDue() {
        if (! dueDate.value) {
            sumDue.textContent = '—';
            sumDays.textContent = '—';
            return;
        }

        const due = new Date(dueDate.value + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const days = Math.round((due - today) / 86400000);

        sumDue.textContent = due.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
        sumDays.textContent = days <= 0 ? 'Vence hoy' : days + (days === 1 ? ' día' : ' días');
    }

    function refreshSubmit() {
        let ready = Boolean(product && product.value) && Boolean(customer && customer.value) && Boolean(dueDate.value) && initial.value !== '';

        if (price() > 0 && (Number(initial.value) || 0) > price()) {
            ready = false;
        }

        submit.disabled = ! ready;
        submitHint.textContent = 'La pieza quedará reservada y el abono ingresará a la caja activa.';
    }

    if (dueDate) {
        dueDate.addEventListener('input', () => {
            refreshDue();
            refreshSubmit();
        });
    }

    if (initial) {
        initial.addEventListener('input', () => {
            refreshAmount();
            refreshSubmit();
        });
    }

    quickButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const total = price();

            if (! total) {
                return;
            }

            initial.value = (total * Number(btn.dataset.ratio)).toFixed(2);
            initial.dispatchEvent(new Event('input'));
            initial.focus();
        });
    });

    if (form && submit) {
        form.addEventListener('submit', () => {
            if (submit.disabled) {
                return false;
            }

            submit.disabled = true;
            submit.textContent = 'Registrando...';
            setTimeout(() => {
                submit.disabled = false;
                submit.textContent = submitLabel;
            }, 8000);
        });
    }

    refreshProduct();
    refreshCustomer();
    refreshAmount();
    refreshDue();
    refreshSubmit();
})();
</script>
@endpush
