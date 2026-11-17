@extends('layouts.admin')

@section('title', 'Nueva Compra de Metales')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .purchase-create-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-height: 0;
    }

    .purchase-create-page .page-header {
        flex-shrink: 0;
        margin-bottom: 0.8rem;
    }

    .purchase-create-page > .alert {
        flex-shrink: 0;
        max-height: 25vh;
        margin-bottom: 0.8rem;
        overflow-y: auto;
    }

    #purchase-form {
        display: flex;
        flex: 1;
        min-height: 0;
    }

    .purchase-create-layout {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 1rem;
        align-items: stretch;
        width: 100%;
        min-height: 0;
    }

    .purchase-create-main {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
        min-height: 0;
        padding-right: 0.25rem;
        overflow-y: auto;
    }

    .purchase-create-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        padding-right: 0.25rem;
        overflow-y: auto;
    }

    .purchase-section {
        padding: 1rem 1.15rem;
        margin-bottom: 0;
    }

    .purchase-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
    }

    .purchase-section-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 0;
        font-size: 1.12rem;
    }

    .purchase-step {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background-color: var(--color-crema);
        color: var(--color-dorado);
        font-family: var(--font-sans);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .picker-wrap {
        position: relative;
    }

    .picker-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        left: 0;
        z-index: 80;
        max-height: 260px;
        padding: 0.35rem;
        overflow-y: auto;
        background-color: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        box-shadow: 0 8px 24px rgba(18, 22, 31, 0.12);
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
        font: inherit;
        text-align: left;
        cursor: pointer;
        transition: background-color 0.15s, border-color 0.15s;
    }

    .picker-chip:hover {
        background-color: var(--color-crema);
        border-color: var(--color-dorado);
    }

    .picker-chip-body {
        flex: 1;
        min-width: 0;
    }

    .picker-chip-title {
        overflow: hidden;
        color: var(--color-noche);
        font-size: 0.85rem;
        font-weight: 600;
        line-height: 1.2;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .picker-chip-meta {
        margin-top: 0.1rem;
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.75rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .picker-chip-action {
        flex-shrink: 0;
        color: var(--color-dorado);
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .picker-empty {
        padding: 0.5rem 0.65rem;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .picker-clear-btn {
        flex-shrink: 0;
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
        margin-top: 0.6rem;
        padding: 0.55rem 0.75rem;
        border: 1px solid var(--color-dorado);
        border-radius: 4px;
        background-color: var(--color-crema);
    }

    .customer-selected.is-visible {
        display: flex;
    }

    .customer-selected-content {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .customer-selected-icon {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: 1px solid var(--color-linea);
        border-radius: 50%;
        background-color: var(--color-blanco);
        color: var(--color-dorado);
    }

    .customer-selected-name {
        overflow: hidden;
        color: var(--color-noche);
        font-size: 0.92rem;
        font-weight: 600;
        line-height: 1.2;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .customer-selected-meta {
        margin-top: 0.15rem;
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.78rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .purchase-item {
        padding: 0.9rem 1rem 1rem;
        margin-bottom: 0.9rem;
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        background-color: #FCFBF8;
    }

    .purchase-item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.7rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed var(--color-linea);
    }

    .purchase-item-head strong {
        font-family: var(--font-serif);
        font-size: 1rem;
    }

    .purchase-item .form-group {
        margin-bottom: 0.7rem;
    }

    .purchase-item-scale {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin: 0.2rem 0 0.8rem;
    }

    .purchase-item-quote {
        padding-top: 0.6rem;
        border-top: 1px dashed var(--color-linea);
        color: var(--color-piedra);
        font-size: 0.85rem;
    }

    .purchase-negotiation {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 170px minmax(0, 1fr);
        gap: 0.75rem;
        align-items: end;
        margin-top: 0.7rem;
        padding: 0.7rem 0.8rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-crema);
    }

    .purchase-negotiation .form-group {
        margin-bottom: 0;
    }

    .purchase-negotiation .form-label {
        font-size: 0.66rem;
    }

    .purchase-negotiation-stat {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .purchase-negotiation-stat span {
        color: var(--color-piedra);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .purchase-negotiation-stat strong {
        color: var(--color-noche);
        font-size: 1rem;
        font-variant-numeric: tabular-nums;
    }

    .purchase-negotiation-stat:last-child {
        text-align: right;
    }

    .purchase-negotiation-stat strong.is-positive {
        color: #2F6B3D;
    }

    .purchase-negotiation-stat strong.is-negative {
        color: var(--color-error);
    }

    .purchase-summary-breakdown {
        display: grid;
        gap: 0.25rem;
        padding-top: 0.55rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .purchase-summary-breakdown > div {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .purchase-summary-breakdown strong {
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .purchase-summary-breakdown strong.is-positive {
        color: #2F6B3D;
    }

    .purchase-summary-breakdown strong.is-negative {
        color: var(--color-error);
    }

    .purchase-summary {
        padding: 1rem 1.15rem;
        margin-bottom: 0;
    }

    .purchase-summary-label {
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .purchase-summary-total {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.55rem 0;
        border-bottom: 1px solid var(--color-linea);
    }

    .purchase-summary-total strong {
        color: var(--color-noche);
        font-size: 1.75rem;
        font-weight: 600;
    }

    .purchase-summary-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-top: 0.55rem;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .purchase-summary .btn {
        width: 100%;
        margin-top: 0.9rem;
    }

    .purchase-checklist {
        padding: 0.9rem 1.1rem;
        margin-bottom: 0;
        background-color: #FAF8F3;
    }

    .purchase-checklist h2 {
        margin: 0 0 0.6rem;
        font-family: var(--font-serif);
        font-size: 0.98rem;
    }

    .purchase-checklist ul {
        display: grid;
        gap: 0.4rem;
        margin: 0;
        padding: 0;
        color: var(--color-piedra);
        font-size: 0.78rem;
        list-style: none;
    }

    .purchase-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
    }

    .purchase-checklist li::before {
        content: "";
        flex-shrink: 0;
        width: 5px;
        height: 5px;
        margin-top: 0.4rem;
        border-radius: 50%;
        background-color: var(--color-dorado);
    }

    @media (max-width: 1024px) {
        .purchase-create-page,
        #purchase-form,
        .purchase-create-layout {
            display: block;
            min-height: auto;
        }

        .purchase-create-main,
        .purchase-create-side {
            display: flex;
            min-height: auto;
            padding-right: 0;
            overflow: visible;
        }

        .purchase-create-side {
            margin-top: 1rem;
        }
    }

    @media (max-width: 720px) {
        .purchase-section-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .purchase-negotiation {
            grid-template-columns: 1fr;
        }

        .purchase-negotiation-stat:last-child {
            text-align: left;
        }
    }
</style>

<div class="purchase-create-page">
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Nueva compra de metales</h1>
        <p class="page-subtitle">Identifica al cliente, registra cada pieza con su foto y confirma la valuación del día.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<form id="purchase-form" action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="purchase-create-layout">
        <div class="purchase-create-main">
            <div class="card purchase-section">
                <div class="purchase-section-head">
                    <h2 class="purchase-section-title font-serif"><span class="purchase-step">01</span> Identificación del cliente</h2>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="purchase-customer-search">Cliente <span style="color: var(--color-error);">*</span></label>
                        <input type="hidden" name="customer_id" id="customer_id" value="{{ $selectedCustomer['id'] ?? '' }}">

                        <div class="picker-wrap" id="customer-search-wrap">
                            <input type="text" id="purchase-customer-search" class="form-control" placeholder="Buscar cliente por nombre, documento o teléfono..." autocomplete="off">
                            <div class="picker-dropdown" id="customer-search-results" style="display: none;"></div>
                        </div>
                        <p id="customer-error" class="muted" style="display: none; color: var(--color-error); margin-top: 0.35rem; font-size: 0.8rem;">Selecciona un cliente de la búsqueda.</p>

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

                        <p class="form-hint">El cliente debe estar registrado con su documento. <a href="{{ route('admin.customers.create') }}" target="_blank">Registrar cliente</a>.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="payout_method">Método de pago <span style="color: var(--color-error);">*</span></label>
                        <select name="payout_method" id="payout_method" class="form-select" required>
                            <option value="cash" {{ old('payout_method') === 'cash' ? 'selected' : '' }}>Efectivo (egresa de caja)</option>
                            <option value="transfer" {{ old('payout_method') === 'transfer' ? 'selected' : '' }}>Transferencia</option>
                        </select>
                        <p class="form-hint">El efectivo egresa de la caja del turno actual.</p>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="notes">Notas</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" maxlength="2000" placeholder="Detalles relevantes de la compra">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="card purchase-section">
                <div class="purchase-section-head">
                    <h2 class="purchase-section-title font-serif"><span class="purchase-step">02</span> Piezas compradas</h2>
                    <button type="button" class="btn btn-outline btn-sm" id="add-item">Añadir pieza</button>
                </div>

                @include('admin.partials.scale-widget')

                <div id="items"></div>

                <p class="muted" style="font-size: 0.82rem; margin: 0.25rem 0 0;">
                    Cada pieza requiere foto obligatoria. El peso puede tomarse de la balanza o justificarse como manual.
                </p>
            </div>
        </div>

        <aside class="purchase-create-side">
            <div class="card purchase-summary">
                <span class="purchase-summary-label">Resumen de la compra</span>
                <div class="purchase-summary-total">
                    <span class="muted" style="font-size: 0.85rem;">Total acordado</span>
                    <strong class="font-serif num" id="grand-total">$ 0.00</strong>
                </div>
                <div class="purchase-summary-breakdown">
                    <div>
                        <span>Valuación del sistema</span>
                        <strong class="num" id="system-total">$ 0.00</strong>
                    </div>
                    <div>
                        <span>Negociación</span>
                        <strong class="num" id="negotiation-difference">$ 0.00</strong>
                    </div>
                </div>
                <div class="purchase-summary-meta">
                    <span id="items-count">0 piezas</span>
                    <span id="payout-summary">Efectivo</span>
                </div>

                <p id="approval-notice" class="muted" style="display: none; margin: 0.75rem 0 0; font-size: 0.8rem;"></p>
                <p id="form-error" class="alert alert-error" style="display: none; margin: 0.75rem 0 0;"></p>

                <button type="submit" class="btn btn-dark" id="submit-purchase">Registrar compra</button>
            </div>

            <div class="card purchase-checklist">
                <h2>Antes de registrar</h2>
                <ul>
                    <li>Cliente identificado con documento.</li>
                    <li>Foto por cada pieza recibida.</li>
                    <li>Peso de balanza o captura manual justificada.</li>
                    <li>Precio acordado con el cliente registrado por pieza.</li>
                    <li>Destino definido: ocasión o fundición.</li>
                </ul>
            </div>
        </aside>
    </div>
</form>
</div>
@endsection

@push('scripts')
@vite('resources/js/scale.js')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const quoteUrl = @json(route('admin.purchases.quote'));
    const metals = @json($metalsJson);
    const approvalLimit = @json($approvalLimit);
    const canApprove = @json($canApprove);
    const customerSearchUrl = @json(route('admin.customers.search'));
    const initialCustomer = @json($selectedCustomer);

    const el = (id) => document.getElementById(id);
    const money = (n) => '$ ' + (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);

    const customer = el('customer_id');
    const customerSearchInput = el('purchase-customer-search');
    const customerResults = el('customer-search-results');
    const customerSearchWrap = el('customer-search-wrap');
    const customerSelected = el('customer-selected');
    const customerSelectedName = el('customer-selected-name');
    const customerSelectedMeta = el('customer-selected-meta');
    const customerError = el('customer-error');
    let selectedCustomer = initialCustomer;
    let customerSearchTimer = null;
    let customerSearchController = null;

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

    function hideCustomerResults() {
        customerResults.innerHTML = '';
        customerResults.style.display = 'none';
    }

    function selectCustomer(data) {
        selectedCustomer = data;
        customerSearchInput.value = '';
        customerError.style.display = 'none';
        hideCustomerResults();
        refreshCustomer();
    }

    function clearCustomer() {
        selectedCustomer = null;
        refreshCustomer();
        hideCustomerResults();
        customerSearchInput.value = '';
        customerSearchInput.focus();
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

    function searchCustomers(term) {
        if (! term) {
            hideCustomerResults();
            return;
        }

        if (customerSearchController) {
            customerSearchController.abort();
        }
        customerSearchController = new AbortController();

        fetch(customerSearchUrl + '?q=' + encodeURIComponent(term), {
            headers: { 'Accept': 'application/json' },
            signal: customerSearchController.signal,
        })
            .then((response) => response.json())
            .then((data) => {
                customerResults.innerHTML = '';

                if (! data.customers || data.customers.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'picker-empty';
                    empty.textContent = 'Sin clientes coincidentes. Puedes registrar uno nuevo.';
                    customerResults.appendChild(empty);
                    customerResults.style.display = 'block';
                    return;
                }

                data.customers.forEach((item) => {
                    customerResults.appendChild(buildCustomerChip(item));
                });
                customerResults.style.display = 'block';
            })
            .catch(() => {});
    }

    if (customerSearchInput) {
        customerSearchInput.addEventListener('input', (event) => {
            clearTimeout(customerSearchTimer);
            customerSearchTimer = setTimeout(() => searchCustomers(event.target.value.trim()), 200);
        });

        customerSearchInput.addEventListener('focus', () => {
            const value = customerSearchInput.value.trim();
            if (value) {
                searchCustomers(value);
            }
        });

        customerSearchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                hideCustomerResults();
            }

            if (event.key === 'Enter') {
                const firstResult = customerResults.querySelector('.picker-chip');
                if (firstResult) {
                    event.preventDefault();
                    firstResult.click();
                }
            }
        });

        document.addEventListener('click', (event) => {
            if (customerSearchWrap && ! customerSearchWrap.contains(event.target)) {
                hideCustomerResults();
            }
        });
    }

    el('customer-clear').addEventListener('click', clearCustomer);
    refreshCustomer();

    function metalOptions() {
        return metals.map((m) => '<option value="' + m.id + '">' + m.name + '</option>').join('');
    }

    function purityOptions(metalId) {
        const metal = metals.find((m) => String(m.id) === String(metalId));
        if (!metal) {
            return '<option value="">—</option>';
        }
        return metal.purities.map((p) => '<option value="' + p.id + '">' + p.code + '</option>').join('');
    }

    function createRow() {
        const row = document.createElement('div');
        row.className = 'purchase-item';
        row.innerHTML =
            '<div class="purchase-item-head">' +
                '<strong>Pieza <span class="item-index"></span></strong>' +
                '<button type="button" class="btn btn-danger btn-sm" data-remove>Quitar</button>' +
            '</div>' +
            '<div class="grid-2">' +
                '<div class="form-group"><label class="form-label">Metal</label><select class="form-select" data-field="metal_id">' + metalOptions() + '</select></div>' +
                '<div class="form-group"><label class="form-label">Quilataje / Pureza</label><select class="form-select" data-field="purity_id"></select></div>' +
            '</div>' +
            '<div class="form-group"><label class="form-label">Descripción</label><input type="text" class="form-control" data-field="description" maxlength="150" placeholder="Ej: Cadena rota 18k"></div>' +
            '<div class="grid-3">' +
                '<div class="form-group"><label class="form-label">Peso bruto (g)</label><input type="number" step="0.001" min="0.001" class="form-control" data-field="gross_weight_g"></div>' +
                '<div class="form-group"><label class="form-label">Peso neto (g)</label><input type="number" step="0.001" min="0.001" class="form-control" data-field="net_weight_g"></div>' +
                '<div class="form-group"><label class="form-label">Origen del peso</label><select class="form-select" data-field="weight_source"><option value="scale">Balanza</option><option value="manual">Manual</option></select></div>' +
            '</div>' +
            '<div class="purchase-item-scale">' +
                '<button type="button" class="btn btn-outline btn-sm" data-take-scale>Tomar peso de la balanza</button>' +
                '<span class="muted" data-scale-note style="font-size: 0.78rem;"></span>' +
            '</div>' +
            '<div class="grid-2">' +
                '<div class="form-group" style="margin-bottom: 0;"><label class="form-label">Destino</label><select class="form-select" data-field="destination"><option value="used_inventory">Inventario de ocasión</option><option value="melting">Materia prima (fundición)</option></select></div>' +
                '<div class="form-group" style="margin-bottom: 0;"><label class="form-label">Foto de la pieza</label><input type="file" class="form-control" data-field="photo" accept="image/*"></div>' +
            '</div>' +
            '<div class="purchase-item-quote item-quote">Completa metal, pureza y pesos para calcular la valuación.</div>' +
            '<div class="purchase-negotiation" data-negotiation>' +
                '<div class="purchase-negotiation-stat"><span>Valuación sistema</span><strong data-system-amount>$ 0.00</strong></div>' +
                '<div class="form-group"><label class="form-label">Precio acordado</label><input type="number" step="0.01" min="0" max="999999.99" inputmode="decimal" class="form-control num" data-field="negotiated_amount" placeholder="= valuación" title="Vacío = valuación del sistema"></div>' +
                '<div class="purchase-negotiation-stat"><span>Diferencia</span><strong data-negotiation-difference>$ 0.00</strong></div>' +
            '</div>';

        const metalSelect = row.querySelector('[data-field="metal_id"]');
        const puritySelect = row.querySelector('[data-field="purity_id"]');
        puritySelect.innerHTML = purityOptions(metalSelect.value);

        metalSelect.addEventListener('change', () => {
            puritySelect.innerHTML = purityOptions(metalSelect.value);
            scheduleQuote(row);
        });

        ['purity_id', 'gross_weight_g', 'net_weight_g'].forEach((field) => {
            row.querySelector('[data-field="' + field + '"]').addEventListener('input', () => scheduleQuote(row));
            row.querySelector('[data-field="' + field + '"]').addEventListener('change', () => scheduleQuote(row));
        });

        const negotiatedInput = row.querySelector('[data-field="negotiated_amount"]');

        negotiatedInput.addEventListener('input', () => {
            row.dataset.negotiatedTouched = '1';
            updateNegotiation(row);
            recalcTotal();
        });

        const grossInput = row.querySelector('[data-field="gross_weight_g"]');
        const netInput = row.querySelector('[data-field="net_weight_g"]');
        const sourceSelect = row.querySelector('[data-field="weight_source"]');
        const scaleNote = row.querySelector('[data-scale-note]');

        const markManual = () => {
            sourceSelect.value = 'manual';
            scaleNote.textContent = 'Captura manual del peso.';
        };

        grossInput.addEventListener('input', markManual);
        netInput.addEventListener('input', markManual);

        row.querySelector('[data-take-scale]').addEventListener('click', () => {
            const reading = window.JoyeriaScale ? window.JoyeriaScale.lastReading : null;

            if (reading === null || reading === undefined) {
                scaleNote.textContent = 'Sin lectura de balanza. Conéctala o escribe el peso a mano.';
                return;
            }

            const value = Number(reading).toFixed(3);
            grossInput.value = value;
            netInput.value = value;
            sourceSelect.value = 'scale';
            scaleNote.textContent = 'Peso capturado de la balanza.';
            scheduleQuote(row);
        });

        row.querySelector('[data-remove]').addEventListener('click', () => {
            row.remove();
            reindex();
            recalcTotal();
        });

        el('items').appendChild(row);
        reindex();
        return row;
    }

    let timers = new WeakMap();

    function scheduleQuote(row) {
        clearTimeout(timers.get(row));
        timers.set(row, setTimeout(() => quoteRow(row), 350));
    }

    function updateNegotiation(row) {
        const appraised = Number(row.dataset.appraised) || 0;
        const input = row.querySelector('[data-field="negotiated_amount"]');
        const raw = input.value.trim();
        const agreed = raw === '' ? appraised : Number(raw) || 0;
        const difference = agreed - appraised;
        const differenceEl = row.querySelector('[data-negotiation-difference]');

        row.querySelector('[data-system-amount]').textContent = money(appraised);
        differenceEl.textContent = (difference > 0 ? '+ ' : difference < 0 ? '- ' : '') + money(Math.abs(difference));
        differenceEl.classList.toggle('is-positive', difference > 0);
        differenceEl.classList.toggle('is-negative', difference < 0);
    }

    function rowAmount(row) {
        const appraised = Number(row.dataset.appraised) || 0;
        const raw = row.querySelector('[data-field="negotiated_amount"]').value.trim();

        return raw === '' ? appraised : Number(raw) || 0;
    }

    function quoteRow(row) {
        const metalId = row.querySelector('[data-field="metal_id"]').value;
        const purityId = row.querySelector('[data-field="purity_id"]').value;
        const gross = row.querySelector('[data-field="gross_weight_g"]').value;
        const net = row.querySelector('[data-field="net_weight_g"]').value;
        const box = row.querySelector('.item-quote');
        const negotiatedInput = row.querySelector('[data-field="negotiated_amount"]');

        if (!metalId || !purityId || !gross || !net) {
            row.dataset.appraised = '0';
            box.textContent = 'Completa metal, pureza y pesos para calcular la valuación.';
            updateNegotiation(row);
            recalcTotal();
            return;
        }

        fetch(quoteUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ metal_id: metalId, purity_id: purityId, gross_weight_g: gross, net_weight_g: net }),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo valuar la pieza.');
                }

                row.dataset.appraised = data.appraised_amount || data.amount;
                box.innerHTML =
                    'Ley ' + data.fineness_snapshot + ' · ' + money(data.price_per_gram_snapshot) + '/g · ' +
                    'descuento ' + data.discount_percent_snapshot + '%';

                if (! row.dataset.negotiatedTouched) {
                    negotiatedInput.value = Number(row.dataset.appraised).toFixed(2);
                }

                updateNegotiation(row);
                recalcTotal();
            })
            .catch((exception) => {
                row.dataset.appraised = '0';
                box.textContent = exception.message;
                updateNegotiation(row);
                recalcTotal();
            });
    }

    function reindex() {
        const rows = el('items').querySelectorAll('.purchase-item');
        rows.forEach((row, index) => {
            row.querySelector('.item-index').textContent = (index + 1);
            row.querySelectorAll('[data-field]').forEach((field) => {
                field.name = 'items[' + index + '][' + field.dataset.field + ']';
            });
        });

        el('items-count').textContent = rows.length + (rows.length === 1 ? ' pieza' : ' piezas');
    }

    function recalcTotal() {
        const rows = el('items').querySelectorAll('.purchase-item');
        let total = 0;
        let appraisedTotal = 0;

        rows.forEach((row) => {
            total += rowAmount(row);
            appraisedTotal += Number(row.dataset.appraised) || 0;
        });

        const difference = total - appraisedTotal;
        const differenceEl = el('negotiation-difference');

        el('grand-total').textContent = money(total);
        el('system-total').textContent = money(appraisedTotal);
        differenceEl.textContent = (difference > 0 ? '+ ' : difference < 0 ? '- ' : '') + money(Math.abs(difference));
        differenceEl.classList.toggle('is-positive', difference > 0);
        differenceEl.classList.toggle('is-negative', difference < 0);

        const notice = el('approval-notice');
        if (Number(total) > Number(approvalLimit) && !canApprove) {
            notice.textContent = 'Este monto supera el límite de ' + money(approvalLimit) + '. La compra quedará pendiente de aprobación de gerencia.';
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
        }
    }

    el('add-item').addEventListener('click', createRow);

    el('payout_method').addEventListener('change', () => {
        el('payout-summary').textContent = el('payout_method').value === 'cash' ? 'Efectivo' : 'Transferencia';
    });

    el('purchase-form').addEventListener('submit', (event) => {
        const error = el('form-error');
        error.style.display = 'none';

        if (! customer.value) {
            event.preventDefault();
            customerError.style.display = 'block';
            customerSearchInput.focus();
            return;
        }

        if (el('items').querySelectorAll('.purchase-item').length === 0) {
            event.preventDefault();
            error.textContent = 'Agrega al menos una pieza.';
            error.style.display = 'block';
        }
    });

    createRow();
})();
</script>
@endpush
