@extends('layouts.admin')

@section('title', 'Punto de Venta')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .pos-page {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        gap: 0.85rem;
    }

    .pos-header {
        margin-bottom: 0;
    }

    .pos-header .page-title {
        font-size: 1.5rem;
    }

    .pos-header .page-subtitle {
        font-size: 0.82rem;
        margin-top: 0.1rem;
    }

    .pos-main {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        gap: 0.85rem;
        flex: 1;
        min-height: 0;
    }

    .pos-card-title {
        font-size: 1.05rem;
        margin-bottom: 0.6rem;
        padding-bottom: 0.45rem;
        border-bottom: 1px solid var(--color-linea);
        flex-shrink: 0;
    }

    .pos-search-card,
    .pos-cart-card {
        display: flex;
        flex-direction: column;
        min-height: 0;
        min-width: 0;
        padding: 0.9rem 1rem;
        margin-bottom: 0;
    }

    .pos-results {
        margin-top: 0.6rem;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
    }

    .pos-filters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 0.5rem;
        margin-top: 0.6rem;
        flex-shrink: 0;
    }

    .pos-filters .form-select,
    .pos-filters .form-control {
        height: 36px;
        font-size: 0.85rem;
        min-width: 0;
    }

    .pos-cards {
        margin-top: 0.75rem;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(165px, 1fr));
        gap: 0.75rem;
        align-content: start;
    }

    .pos-product-card {
        display: flex;
        flex-direction: column;
        text-align: left;
        background-color: #fff;
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        padding: 0;
        overflow: hidden;
        cursor: pointer;
        transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
        font: inherit;
        color: inherit;
    }

    .pos-product-card:hover {
        border-color: var(--color-dorado);
        box-shadow: 0 4px 14px rgba(184, 154, 94, 0.18);
        transform: translateY(-2px);
    }

    .pos-product-card.is-added {
        opacity: 0.5;
        cursor: not-allowed;
        border-color: var(--color-exito);
        box-shadow: none;
        transform: none;
    }

    .pos-product-media {
        height: 96px;
        background-color: var(--color-crema);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .pos-product-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pos-product-placeholder {
        color: var(--color-dorado);
        font-family: var(--font-serif);
        font-size: 1.6rem;
    }

    .pos-product-badge {
        position: absolute;
        top: 6px;
        left: 6px;
        background-color: var(--color-dorado);
        color: #fff;
        font-size: 0.62rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 2px 6px;
        border-radius: 3px;
    }

    .pos-product-body {
        padding: 0.55rem 0.65rem 0.65rem;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }

    .pos-product-cat {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--color-piedra);
    }

    .pos-product-name {
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1.2;
        color: var(--color-noche);
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .pos-product-meta {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.35rem;
        margin-top: 0.15rem;
    }

    .pos-product-meta span:first-child {
        font-size: 0.72rem;
        color: var(--color-piedra);
    }

    .pos-product-price {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--color-noche);
        white-space: nowrap;
    }

    .pos-cards-empty {
        grid-column: 1 / -1;
        text-align: center;
        color: var(--color-piedra);
        padding: 2rem 1rem;
        font-size: 0.9rem;
    }

    .pos-cart-table {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: auto;
    }

    .pos-cart-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: var(--color-crema);
    }

    .pos-totals {
        margin-top: 0.75rem;
        display: grid;
        gap: 0.4rem;
        flex-shrink: 0;
    }

    /* --- Selector de Cliente en Vista Principal --- */
    .pos-cart-customer-section {
        background-color: var(--color-crema);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        padding: 0.6rem 0.75rem;
        margin-bottom: 0.75rem;
        position: relative;
        flex-shrink: 0;
    }

    .pos-cart-customer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.35rem;
    }

    .pos-cart-customer-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-noche);
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .pos-cart-customer-new {
        font-size: 0.75rem;
        color: var(--color-dorado);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.15s;
    }
    .pos-cart-customer-new:hover {
        text-decoration: underline;
        color: var(--color-noche);
    }

    .pos-cart-customer-search-wrap {
        position: relative;
    }

    .pos-cart-customer-search-wrap .form-control {
        height: 36px;
        font-size: 0.85rem;
        background-color: #fff;
    }

    .pos-cart-customer-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        box-shadow: 0 8px 24px rgba(18, 22, 31, 0.12);
        max-height: 220px;
        overflow-y: auto;
        z-index: 50;
        padding: 0.35rem;
    }

    .pos-cart-customer-dropdown .pos-result-chip {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-bottom: 0.25rem;
        padding: 0.45rem 0.65rem;
        border: 1px solid transparent;
        border-radius: 4px;
        background: transparent;
        text-align: left;
        cursor: pointer;
        font: inherit;
        transition: background-color 0.15s, border-color 0.15s;
    }
    .pos-cart-customer-dropdown .pos-result-chip:hover {
        background-color: var(--color-crema);
        border-color: var(--color-dorado);
    }

    .pos-cart-customer-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border: 1px solid var(--color-dorado);
        border-radius: 4px;
        padding: 0.4rem 0.65rem;
    }

    .pos-cart-customer-card-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 0;
    }

    .pos-cart-customer-badge-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: var(--color-crema);
        color: var(--color-dorado);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pos-cart-customer-fullname {
        display: block;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--color-noche);
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pos-cart-customer-meta {
        font-size: 0.74rem;
        display: block;
        line-height: 1.2;
    }

    .pos-cart-customer-remove {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
        height: 28px;
        flex-shrink: 0;
    }

    /* --- Modal de Cobro (Diseño fiel a la imagen) --- */
    #modal-charge .modal {
        max-width: 880px;
        width: 95%;
        border-radius: 6px;
        box-shadow: 0 16px 40px rgba(18, 22, 31, 0.22);
        border: 1px solid var(--color-linea);
        overflow: hidden;
        background-color: #fff;
    }

    #modal-charge .modal-header {
        background-color: #EDE8DF;
        border-bottom: 1px solid var(--color-linea);
        padding: 1.15rem 1.75rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    #modal-charge .modal-header h3 {
        font-family: var(--font-serif);
        font-size: 1.45rem;
        margin: 0;
        color: var(--color-noche);
        letter-spacing: -0.01em;
        line-height: 1.2;
    }

    #modal-charge .modal-header .muted {
        font-size: 0.82rem;
        color: var(--color-piedra);
        margin-top: 0.2rem;
        display: block;
    }

    #modal-charge .modal-body {
        padding: 1.5rem 1.75rem;
        background-color: #fff;
    }

    .pos-modal-section-tag {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-piedra);
        margin-bottom: 0.85rem;
    }

    .pos-modal-info-summary {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        margin-bottom: 1.35rem;
    }

    .pos-modal-info-row {
        font-size: 0.95rem;
        color: var(--color-noche);
        display: flex;
        align-items: baseline;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .pos-modal-info-label {
        font-weight: 700;
        color: var(--color-noche);
    }

    .pos-modal-info-val {
        font-weight: 500;
        color: #1e2430;
    }

    .pos-modal-change-link {
        font-size: 0.78rem;
        color: var(--color-dorado);
        background: none;
        border: none;
        padding: 0;
        margin-left: 0.35rem;
        cursor: pointer;
        text-decoration: underline;
    }

    .pos-modal-method-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--color-noche);
        margin-bottom: 0.6rem;
    }

    .pos-modal-methods-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.85rem;
        margin-bottom: 1.35rem;
    }

    .pos-method-btn {
        height: 48px;
        border: 1px solid var(--color-linea);
        background-color: #fff;
        border-radius: 4px;
        font-size: 0.92rem;
        font-weight: 500;
        color: var(--color-noche);
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pos-method-btn:hover {
        border-color: var(--color-dorado);
        background-color: var(--color-hueso);
    }

    .pos-method-btn.active {
        border: 2px solid var(--color-dorado);
        background-color: #FAF7F0;
        font-weight: 700;
        color: var(--color-noche);
        box-shadow: 0 2px 8px rgba(184, 154, 94, 0.18);
    }

    /* Grilla de campos: Pagos (Izquierda) vs Descuento (Derecha) */
    .pos-modal-fields-row {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(0, 0.95fr);
        gap: 1.5rem;
        align-items: start;
        margin-top: 0.5rem;
        padding-bottom: 0.75rem;
    }

    .pos-payment-subhead {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 34px;
        gap: 0.45rem;
        margin-bottom: 0.4rem;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-piedra);
    }

    .pos-payments-box {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .pos-payment-input-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 34px;
        gap: 0.45rem;
        align-items: center;
    }

    .pos-payment-input-row .form-control {
        height: 38px;
        font-size: 0.88rem;
    }

    .pos-payment-del {
        height: 38px;
        width: 34px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        line-height: 1;
        color: var(--color-piedra);
        border-color: var(--color-linea);
    }
    .pos-payment-del:hover {
        color: var(--color-error);
        border-color: var(--color-error);
        background-color: #FDF2F2;
    }

    .pos-discount-subhead {
        display: grid;
        grid-template-columns: 1fr 1.35fr;
        gap: 0.5rem;
        margin-bottom: 0.4rem;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-piedra);
    }

    .pos-discount-input-row {
        display: grid;
        grid-template-columns: 1fr 1.35fr;
        gap: 0.5rem;
        align-items: center;
    }

    .pos-discount-input-row .form-control {
        height: 38px;
        font-size: 0.88rem;
    }

    /* Footer de Cobro */
    .pos-charge-footer-bar {
        border-top: 1px solid var(--color-linea);
        padding-top: 1.15rem;
        margin-top: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pos-footer-totals {
        display: flex;
        align-items: baseline;
        gap: 1.5rem;
        font-size: 0.95rem;
    }

    .pos-footer-total-item {
        color: var(--color-noche);
    }

    .pos-footer-total-val {
        font-weight: 700;
        font-size: 1.05rem;
    }

    .pos-footer-actions {
        display: flex;
        gap: 0.65rem;
    }

    .pos-footer-actions .btn {
        height: 42px;
        padding: 0 1.5rem;
        font-size: 0.92rem;
        font-weight: 600;
        border-radius: 4px;
    }

    @media (max-width: 1024px) {
        .pos-page {
            display: block;
        }

        .pos-main {
            display: block;
        }

        .pos-search-card,
        .pos-cart-card {
            margin-bottom: 1rem;
        }

        .pos-results,
        .pos-cards,
        .pos-cart-table {
            overflow: visible;
            max-height: none;
        }

        .pos-cart-table thead th {
            position: static;
        }

        .pos-modal-fields-row {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
    }

    @media (max-width: 640px) {
        .pos-modal-methods-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .pos-payment-subhead,
        .pos-payment-input-row {
            grid-template-columns: 1fr 1fr;
        }

        .pos-discount-subhead,
        .pos-discount-input-row {
            grid-template-columns: 1fr;
        }

        .pos-charge-footer-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .pos-footer-actions {
            width: 100%;
        }

        .pos-footer-actions .btn {
            flex: 1;
        }
    }
</style>

<div class="pos-page">
    <div class="page-header pos-header">
        <div>
            <h1 class="page-title font-serif">Punto de Venta</h1>
            <p class="page-subtitle">
                Escanea o busca una pieza, arma el carrito y cobra.
                @if ($session)
                    Turno abierto desde {{ $session->opened_at?->format('H:i') }}.
                @else
                    <span style="color: var(--color-error);">No hay un turno de caja abierto.</span>
                @endif
            </p>
        </div>
        <div class="page-actions">
            @can('purchases.create')
                <a href="{{ route('admin.purchases.create') }}" class="btn btn-outline btn-sm">Comprar materiales</a>
            @endcan
            @can('layaways.manage')
                <a href="{{ route('admin.layaways.create') }}" class="btn btn-outline btn-sm">Apartar pieza</a>
            @endcan
            <a href="{{ route('admin.cash.index') }}" class="btn btn-outline btn-sm">Caja</a>
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="pos-main">
        <div class="card pos-search-card">
            <label class="form-label" for="pos-search">Buscar pieza (código de barras, SKU o nombre)</label>
            <input type="text" id="pos-search" class="form-control" placeholder="Escanea o escribe y presiona Enter" autocomplete="off" autofocus>

            <div class="pos-filters">
                <select id="filter-metal" class="form-select">
                    <option value="">Todos los metales</option>
                    @foreach ($metals as $metal)
                        <option value="{{ $metal->id }}">{{ $metal->name }}</option>
                    @endforeach
                </select>
                <select id="filter-category" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="number" id="filter-price-min" class="form-control" min="0" step="0.01" placeholder="Precio mín.">
                <input type="number" id="filter-price-max" class="form-control" min="0" step="0.01" placeholder="Precio máx.">
                <button type="button" class="btn btn-outline btn-sm" id="filter-clear">Limpiar</button>
            </div>

            <div id="pos-results" class="pos-cards"></div>
        </div>

        <div class="card pos-cart-card">
            <h2 class="font-serif pos-card-title">Carrito</h2>

            <!-- Selector de Cliente en Vista Principal (desde el inicio) -->
            <div class="pos-cart-customer-section">
                <div class="pos-cart-customer-header">
                    <span class="pos-cart-customer-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Cliente
                    </span>
                    <a href="{{ route('admin.customers.create') }}" class="pos-cart-customer-new" target="_blank" title="Registrar un nuevo cliente">+ Nuevo</a>
                </div>

                <input type="hidden" id="customer_id" value="">

                <!-- Input de búsqueda de clientes con autocompletado desplegable -->
                <div id="main-customer-search-wrap" class="pos-cart-customer-search-wrap">
                    <input type="text" id="main-customer-search" class="form-control" placeholder="Buscar cliente por nombre, documento o teléfono..." autocomplete="off">
                    <div id="main-customer-results" class="pos-cart-customer-dropdown" style="display: none;"></div>
                </div>

                <!-- Tarjeta con el cliente seleccionado -->
                <div id="main-customer-selected" class="pos-cart-customer-card" style="display: none;">
                    <div class="pos-cart-customer-card-content">
                        <div class="pos-cart-customer-badge-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <div style="min-width: 0;">
                            <strong id="main-customer-name" class="pos-cart-customer-fullname"></strong>
                            <span id="main-customer-details" class="pos-cart-customer-meta muted"></span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm pos-cart-customer-remove" id="main-customer-clear" title="Quitar cliente">✕ Quitar</button>
                </div>
            </div>

            <div class="table-wrap pos-cart-table">
                <table class="data-table" id="cart-table">
                    <thead>
                        <tr>
                            <th>Pieza</th>
                            <th class="text-right">Precio</th>
                            <th style="width: 110px;">Descuento</th>
                            <th class="text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr id="cart-empty"><td colspan="5" class="muted" style="text-align: center; padding: 1.25rem;">El carrito está vacío.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pos-totals">
                <div style="display: flex; justify-content: space-between;">
                    <span class="muted">Subtotal</span>
                    <span class="num" id="sum-subtotal">$ 0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="muted">Descuento total</span>
                    <span class="num" id="sum-discount">$ 0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--color-linea); padding-top: 0.5rem;">
                    <strong class="font-serif" style="font-size: 1.1rem;">Total</strong>
                    <strong class="font-serif num" style="font-size: 1.3rem;" id="sum-total">$ 0.00</strong>
                </div>
            </div>

            <button type="button" class="btn btn-gold" id="open-charge" style="margin-top: 0.75rem; width: 100%; height: 46px; font-size: 1rem;">Cobrar</button>

            <p id="cart-error" class="alert alert-error" style="display: none; margin-top: 0.75rem;"></p>
        </div>
    </div>
</div>

<!-- Modal de Cobro -->
<div id="modal-charge" class="modal-backdrop" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h3 class="font-serif">Cobro</h3>
                <span class="muted">Cliente, descuento y pagos antes de registrar la venta</span>
            </div>
            <button type="button" class="modal-close" id="btn-close-charge">✕</button>
        </div>

        <div class="modal-body">
            <div class="pos-modal-section-tag">PAGOS</div>

            <!-- Resumen superior: Cliente y Productos -->
            <div class="pos-modal-info-summary">
                <div class="pos-modal-info-row">
                    <span class="pos-modal-info-label">Cliente:</span>
                    <span id="modal-customer-display" class="pos-modal-info-val">Público general</span>
                    <button type="button" id="modal-change-customer-btn" class="pos-modal-change-link" title="Cambiar cliente en la pantalla principal">(cambiar)</button>
                </div>
                <div class="pos-modal-info-row">
                    <span class="pos-modal-info-label">Producto:</span>
                    <span id="modal-products-display" class="pos-modal-info-val">Sin piezas</span>
                </div>
            </div>

            <!-- Botones de Método de Pago (Efectivo, Tarjeta, Transferencia, Otro) -->
            <div class="pos-modal-method-title">Metodo de pago:</div>
            <div class="pos-modal-methods-grid">
                <button type="button" class="pos-method-btn active" data-method-btn="cash">Efectivo</button>
                <button type="button" class="pos-method-btn" data-method-btn="card">Tarjeta</button>
                <button type="button" class="pos-method-btn" data-method-btn="transfer">Transferencia</button>
                <button type="button" class="pos-method-btn" data-method-btn="other">Otro</button>
            </div>

            <!-- Fila de Campos: Pagos (Izquierda) y Descuentos (Derecha) -->
            <div class="pos-modal-fields-row">
                <!-- Columna Izquierda: Pagos -->
                <div>
                    <div class="pos-payment-subhead">
                        <span>Monto</span>
                        <span>Recibido</span>
                        <span>Referencia</span>
                        <span></span>
                    </div>
                    <div id="payments" class="pos-payments-box"></div>
                    <button type="button" class="btn btn-outline btn-sm" id="add-payment" style="margin-top: 0.5rem; font-size: 0.78rem;">+ Agregar otro método</button>
                </div>

                <!-- Columna Derecha: Descuentos -->
                <div>
                    <div class="pos-discount-subhead">
                        <span>Descuento global</span>
                        <span>Motivo del descuento</span>
                    </div>
                    <div class="pos-discount-input-row">
                        <input type="number" step="0.01" min="0" id="discount_total" class="form-control" value="0.00" placeholder="0.00">
                        <input type="text" id="discount_reason" class="form-control" maxlength="255" placeholder="Obligatorio si hay desc">
                    </div>

                    <div id="discount-warning" style="display: none; background-color: #FEF3C7; border-left: 4px solid var(--color-aviso); color: #92400E; padding: 0.6rem 0.85rem; border-radius: 4px; font-size: 0.8rem; margin-top: 0.65rem; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span>⚠️</span>
                            <span id="discount-warning-text">El descuento supera el límite autorizado para tu rol. Requerirá autorización.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con totales y botones de acción -->
            <div class="pos-charge-footer-bar">
                <div class="pos-footer-totals">
                    <div class="pos-footer-total-item">
                        <span>Total pagado:</span>
                        <span class="num pos-footer-total-val" id="sum-paid">$ 0.00</span>
                    </div>
                    <div class="pos-footer-total-item">
                        <span id="pay-status" class="pos-footer-total-val" style="color: var(--color-error);">Faltante: $ 0.00</span>
                    </div>
                    @if (! $canOverrideDiscount)
                        <div class="muted" style="font-size: 0.75rem;">
                            (Límite desc: {{ number_format((float) $maxDiscountPercent, 2) }}%)
                        </div>
                    @endif
                </div>

                <div class="pos-footer-actions">
                    <button type="button" class="btn btn-outline" id="btn-cancel-charge">Cancelar</button>
                    <button type="button" class="btn btn-gold" id="confirm-sale">Registrar venta</button>
                </div>
            </div>

            <p id="pos-error" class="alert alert-error" style="display: none; margin-top: 0.75rem;"></p>
        </div>
    </div>
</div>

<!-- Modal de Autorización Híbrida de Descuento (PIN Mostrador o Remoto) -->
<div id="modal-supervisor-auth" class="modal-backdrop" style="display: none;">
    <div class="modal" style="max-width: 480px; max-height: 92vh; overflow-y: auto;">
        <div class="modal-header">
            <div>
                <h3 class="font-serif" style="font-size: 1.25rem; margin: 0; color: var(--color-noche);">Autorización de Descuento</h3>
                <span class="muted" style="font-size: 0.78rem;">Se requiere aprobación de Gerencia para este descuento</span>
            </div>
            <button type="button" class="modal-close" id="btn-close-auth-modal">✕</button>
        </div>

        <div class="modal-body">
            <!-- Resumen de montos -->
            <div style="background-color: var(--color-crema); border-radius: 4px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; border: 1px solid var(--color-linea);">
                <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.25rem;">
                    <span class="muted">Subtotal:</span>
                    <strong class="num" id="auth-subtotal">$ 0.00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.25rem; color: var(--color-aviso);">
                    <span>Descuento solicitado:</span>
                    <strong class="num" id="auth-discount">$ 0.00 (0%)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.95rem; font-weight: 600; border-top: 1px solid var(--color-linea); padding-top: 0.35rem; margin-top: 0.35rem;">
                    <span>Total a pagar:</span>
                    <strong class="font-serif num" style="font-size: 1.25rem; color: var(--color-noche);" id="auth-total">$ 0.00</strong>
                </div>
            </div>

            <!-- Selector de Modo: PIN Presencial vs Solicitud Remota -->
            <div style="display: flex; border-bottom: 1px solid var(--color-linea); margin-bottom: 1.25rem;">
                <button type="button" id="tab-pin" style="flex: 1; padding: 0.6rem 0.5rem; border: none; background: none; font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid var(--color-dorado); cursor: pointer; color: var(--color-noche);">
                    🔑 PIN Presencial (Mostrador)
                </button>
                <button type="button" id="tab-remote" style="flex: 1; padding: 0.6rem 0.5rem; border: none; background: none; color: var(--color-piedra); font-size: 0.85rem; cursor: pointer;">
                    📱 Solicitud Remota
                </button>
            </div>

            <!-- Panel 1: PIN Presencial -->
            <div id="panel-pin">
                <p style="font-size: 0.85rem; color: var(--color-piedra); margin-bottom: 1rem; text-align: center;">
                    Si el Gerente o Administrador está presente, solicita que ingrese su <strong>PIN de 4 dígitos</strong>:
                </p>
                <div style="text-align: center; margin-bottom: 1rem;">
                    <input type="password" id="supervisor-pin" maxlength="4" pattern="[0-9]*" inputmode="numeric" placeholder="••••" autocomplete="off" style="width: 150px; height: 50px; font-size: 1.8rem; text-align: center; letter-spacing: 0.4em; border: 2px solid var(--color-dorado); border-radius: 6px; outline: none; margin: 0 auto; display: block; font-family: monospace;">
                </div>
                <p id="pin-error" style="color: var(--color-error); font-size: 0.85rem; text-align: center; display: none; margin-bottom: 0.75rem;"></p>
                <button type="button" id="btn-submit-pin" class="btn btn-gold" style="width: 100%; height: 42px;">
                    Autorizar y Cobrar Venta
                </button>
            </div>

            <!-- Panel 2: Solicitud Remota -->
            <div id="panel-remote" style="display: none;">
                <div id="remote-initial">
                    <p style="font-size: 0.85rem; color: var(--color-piedra); margin-bottom: 1rem;">
                        Envía una solicitud en tiempo real al panel del Gerente para que la apruebe a distancia:
                    </p>
                    <button type="button" id="btn-send-remote" class="btn btn-dark" style="width: 100%; height: 42px;">
                        Enviar solicitud a Gerencia
                    </button>
                </div>

                <div id="remote-waiting" style="display: none; text-align: center; padding: 1.5rem 0.5rem;">
                    <div style="width: 42px; height: 42px; border: 3px solid rgba(184, 154, 94, 0.25); border-top-color: var(--color-dorado); border-radius: 50%; animation: spin-loader 0.9s linear infinite; margin: 0 auto 0.75rem;"></div>
                    <div id="remote-timer-display" style="font-family: monospace; font-size: 1.8rem; font-weight: 700; color: var(--color-dorado); letter-spacing: 2px; margin-bottom: 0.35rem;">03:00</div>
                    <strong style="display: block; font-size: 1.05rem; color: var(--color-noche); margin-bottom: 0.35rem;">Esperando respuesta de Gerencia...</strong>
                    <p class="muted" style="font-size: 0.82rem; margin-bottom: 1rem;">La solicitud se envió al panel del supervisor. Si no se aprueba en 3 minutos, la solicitud expirará.</p>
                    <button type="button" id="btn-cancel-remote" class="btn btn-outline btn-sm">Cancelar espera</button>
                </div>

                <div id="remote-expired" style="display: none; text-align: center; padding: 1rem 0;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #FFF4E5; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 0.75rem;">⏱</div>
                    <strong style="color: #D97706; font-size: 1rem; display: block;">Tiempo de Espera Expirado (3 min)</strong>
                    <p class="muted" style="font-size: 0.85rem; margin: 0.5rem 0 1rem;">Gerencia no respondió en el tiempo límite. Puedes volver a enviar la solicitud o negociar otro importe.</p>
                    <button type="button" id="btn-retry-expired" class="btn btn-outline btn-sm">Cerrar y reintentar</button>
                </div>

                <div id="remote-rejected" style="display: none; text-align: center; padding: 1rem 0;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #FDEDED; color: var(--color-error); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 0.75rem;">✕</div>
                    <strong style="color: var(--color-error); font-size: 1rem; display: block;">Solicitud Rechazada por Gerencia</strong>
                    <p id="rejected-reason-text" class="muted" style="font-size: 0.85rem; margin: 0.5rem 0 1rem;"></p>
                    <button type="button" id="btn-retry-auth" class="btn btn-outline btn-sm">Ajustar descuento</button>
                </div>

                <div id="remote-approved" style="display: none; text-align: center; padding: 1.25rem 0;">
                    <div style="width: 46px; height: 46px; border-radius: 50%; background-color: #EDF7EE; color: var(--color-exito); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 0.75rem;">✓</div>
                    <strong style="color: var(--color-exito); font-size: 1.1rem; display: block;" id="approved-by-label">¡Descuento Aprobado!</strong>
                    <p class="muted" style="font-size: 0.85rem; margin-top: 0.35rem;">Procesando venta en este momento...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const routes = {
        search: @json(route('admin.pos.search')),
        store: @json(route('admin.pos.store')),
        validatePin: @json(route('admin.pos.authorization.pin')),
        requestRemote: @json(route('admin.pos.authorization.store')),
        checkStatus: @json(url('admin/pos/autorizacion')),
        customerSearch: @json(route('admin.customers.search')),
    };

    const maxDiscountPercent = Number(@json((float) $maxDiscountPercent)) || 10;
    const canOverrideDiscount = Boolean(@json($canOverrideDiscount));

    let approvedAuthorizationId = null;
    let supervisorPinCode = null;
    let activePollingInterval = null;
    let countdownInterval = null;
    let currentAuthRequestId = null;

    const cart = [];
    let searchController = null;

    const el = (id) => document.getElementById(id);
    const money = (n) => '$ ' + (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);

    function renderCart() {
        const body = el('cart-body');
        body.innerHTML = '';

        if (cart.length === 0) {
            body.innerHTML = '<tr id="cart-empty"><td colspan="5" class="muted" style="text-align: center; padding: 1.25rem;">El carrito está vacío.</td></tr>';
            recalc();
            return;
        }

        cart.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + item.name + '<div class="form-hint">' + item.sku + '</div></td>' +
                '<td class="text-right num">' + money(item.price) + '</td>' +
                '<td><input type="number" step="0.01" min="0" class="form-control" style="height:34px;" value="' + item.discount.toFixed(2) + '" data-index="' + index + '"></td>' +
                '<td class="text-right num">' + money(item.price - item.discount) + '</td>' +
                '<td class="text-right"><button type="button" class="btn btn-danger btn-sm" data-remove="' + index + '">×</button></td>';
            body.appendChild(tr);
        });

        body.querySelectorAll('[data-index]').forEach((input) => {
            input.addEventListener('input', (event) => {
                const index = Number(event.target.dataset.index);
                cart[index].discount = Math.max(0, Number(event.target.value) || 0);
                resetAuthorizations();
                renderCart();
            });
        });

        body.querySelectorAll('[data-remove]').forEach((button) => {
            button.addEventListener('click', () => {
                cart.splice(Number(button.dataset.remove), 1);
                resetAuthorizations();
                renderCart();
            });
        });

        recalc();
    }

    function resetAuthorizations() {
        approvedAuthorizationId = null;
        supervisorPinCode = null;
        if (activePollingInterval) {
            clearInterval(activePollingInterval);
            activePollingInterval = null;
        }
    }

    function recalc() {
        const subtotal = cart.reduce((sum, item) => sum + item.price, 0);
        const lineDiscounts = cart.reduce((sum, item) => sum + item.discount, 0);
        const globalDiscount = Math.max(0, Number(el('discount_total').value) || 0);
        const discountTotal = lineDiscounts + globalDiscount;
        const total = Math.max(0, subtotal - discountTotal);

        el('sum-subtotal').textContent = money(subtotal);
        el('sum-discount').textContent = money(discountTotal);
        el('sum-total').textContent = money(total);

        // Auto-fill first payment row amount with the total (if only one row and empty)
        const rows = paymentRows();
        if (rows.length === 1) {
            const amountInput = rows[0].querySelector('[data-amount]');
            if (!amountInput.value || Number(amountInput.value) === 0) {
                amountInput.value = total.toFixed(2);
            }
        }

        // Warning banner for seller when discount exceeds limit
        const discountPercent = subtotal > 0 ? (discountTotal / subtotal) * 100 : 0;
        const warning = el('discount-warning');
        if (discountPercent > maxDiscountPercent && !canOverrideDiscount && !approvedAuthorizationId && !supervisorPinCode) {
            warning.style.display = 'flex';
            el('discount-warning-text').textContent = 'El descuento (' + discountPercent.toFixed(1) + '%) supera tu límite permitido (' + maxDiscountPercent + '%). Requerirá autorización de Gerencia.';
        } else {
            warning.style.display = 'none';
        }

        updatePaid(total);
    }

    function paymentRows() {
        return Array.from(el('payments').querySelectorAll('[data-payment]'));
    }

    let activePaymentRow = null;

    function setMethodButtonActive(method) {
        document.querySelectorAll('[data-method-btn]').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.methodBtn === method);
        });
    }

    function setRowMethod(row, method) {
        const methodInput = row.querySelector('[data-method]');
        if (methodInput) {
            methodInput.value = method;
        }
        setMethodButtonActive(method);
    }

    function updatePaid(total) {
        let paid = 0;
        paymentRows().forEach((row) => {
            paid += Number(row.querySelector('[data-amount]').value) || 0;
        });

        el('sum-paid').textContent = money(paid);

        const diff = Math.round((paid - total) * 100) / 100;
        const status = el('pay-status');
        if (diff === 0) {
            status.textContent = 'Pago exacto';
            status.style.color = 'var(--color-exito)';
        } else if (diff < 0) {
            status.textContent = 'Faltante: ' + money(-diff);
            status.style.color = 'var(--color-error)';
        } else {
            status.textContent = 'Cambio: ' + money(diff);
            status.style.color = 'var(--color-info)';
        }
    }

    function addPaymentRow(method = 'cash', amount = null) {
        const row = document.createElement('div');
        row.setAttribute('data-payment', '');
        row.className = 'pos-payment-input-row';

        // Si no se especifica monto, calcular el faltante restante
        if (amount === null) {
            let currentPaid = 0;
            paymentRows().forEach((r) => {
                currentPaid += Number(r.querySelector('[data-amount]').value) || 0;
            });
            const remaining = Math.max(0, currentTotal() - currentPaid);
            amount = remaining > 0 ? remaining.toFixed(2) : currentTotal().toFixed(2);
        }

        row.innerHTML =
            '<input type="hidden" data-method value="' + (method || 'cash') + '">' +
            '<input type="number" step="0.01" min="0" class="form-control" data-amount placeholder="Monto" value="' + (amount ? Number(amount).toFixed(2) : '') + '">' +
            '<input type="number" step="0.01" min="0" class="form-control" data-received placeholder="Recibido">' +
            '<input type="text" class="form-control" data-reference placeholder="Referencia">' +
            '<button type="button" class="btn btn-outline btn-sm pos-payment-del" data-drop title="Eliminar pago">×</button>';

        row.querySelectorAll('input').forEach((input) => {
            input.addEventListener('focus', () => {
                activePaymentRow = row;
                const m = row.querySelector('[data-method]').value;
                setMethodButtonActive(m);
            });
        });

        row.querySelector('[data-amount]').addEventListener('input', () => updatePaid(currentTotal()));
        row.querySelector('[data-received]').addEventListener('input', () => updatePaid(currentTotal()));
        row.querySelector('[data-drop]').addEventListener('click', () => {
            if (paymentRows().length > 1) {
                row.remove();
            } else {
                row.querySelector('[data-amount]').value = currentTotal().toFixed(2);
                row.querySelector('[data-received]').value = '';
                row.querySelector('[data-reference]').value = '';
                setRowMethod(row, 'cash');
            }
            updatePaid(currentTotal());
        });

        el('payments').appendChild(row);
        activePaymentRow = row;
        setMethodButtonActive(method || 'cash');
    }

    // Botones de selección rápida de Método de Pago
    document.querySelectorAll('[data-method-btn]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const selectedMethod = btn.dataset.methodBtn;
            const rows = paymentRows();
            let targetRow = null;
            if (rows.length === 0) {
                addPaymentRow(selectedMethod, currentTotal());
                return;
            }
            targetRow = activePaymentRow && rows.includes(activePaymentRow) ? activePaymentRow : rows[rows.length - 1];
            setRowMethod(targetRow, selectedMethod);

            if (selectedMethod === 'cash') {
                targetRow.querySelector('[data-received]').focus();
            } else {
                targetRow.querySelector('[data-reference]').focus();
            }
            updatePaid(currentTotal());
        });
    });

    function currentTotal() {
        const raw = el('sum-total').textContent.replace(/[^0-9.]/g, '');
        return Number(raw) || 0;
    }

    function addProductChip(product) {
        if (cart.some((item) => item.id === product.id)) {
            el('cart-error').textContent = 'Esa pieza ya está en el carrito.';
            el('cart-error').style.display = 'flex';
            return;
        }

        cart.push({
            id: product.id,
            name: product.name,
            sku: product.sku + (product.metal ? ' · ' + product.metal : ''),
            price: Number(product.price),
            discount: 0,
        });

        el('cart-error').style.display = 'none';
        resetAuthorizations();
        renderCart();
        renderAddedState();
    }

    function renderAddedState() {
        el('pos-results').querySelectorAll('[data-product-id]').forEach((card) => {
            const inCart = cart.some((item) => item.id === Number(card.dataset.productId));
            card.classList.toggle('is-added', inCart);
            card.disabled = inCart;
        });
    }

    function buildSearchQuery(term) {
        const params = new URLSearchParams();
        if (term) {
            params.set('q', term);
        }
        const metal = el('filter-metal').value;
        const category = el('filter-category').value;
        const priceMin = el('filter-price-min').value;
        const priceMax = el('filter-price-max').value;
        if (metal) {
            params.set('metal_id', metal);
        }
        if (category) {
            params.set('category_id', category);
        }
        if (priceMin) {
            params.set('price_min', priceMin);
        }
        if (priceMax) {
            params.set('price_max', priceMax);
        }
        return params.toString();
    }

    function productCard(product) {
        const card = document.createElement('button');
        card.type = 'button';
        card.className = 'pos-product-card';
        card.dataset.productId = product.id;

        const media = product.photo_url
            ? '<img src="' + product.photo_url + '" alt="" loading="lazy">'
            : '<span class="pos-product-placeholder">' + (product.name || '?').charAt(0) + '</span>';
        const badge = product.is_featured ? '<span class="pos-product-badge">Destacada</span>' : '';
        const spec = [product.purity || product.metal, (product.weight_g ? product.weight_g.replace(/0+$/, '').replace(/\.$/, '') + ' g' : '')]
            .filter(Boolean)
            .join(' · ');

        card.innerHTML =
            '<div class="pos-product-media">' + media + badge + '</div>' +
            '<div class="pos-product-body">' +
                '<span class="pos-product-cat">' + (product.category || '') + '</span>' +
                '<span class="pos-product-name">' + product.name + '</span>' +
                '<div class="pos-product-meta">' +
                    '<span>' + spec + '</span>' +
                    '<span class="pos-product-price">' + money(product.price) + '</span>' +
                '</div>' +
            '</div>';

        card.addEventListener('click', () => addProductChip(product));
        return card;
    }

    function runSearch(term) {
        if (searchController) {
            searchController.abort();
        }
        searchController = new AbortController();

        const query = buildSearchQuery(term);

        fetch(routes.search + (query ? '?' + query : ''), {
            headers: { 'Accept': 'application/json' },
            signal: searchController.signal,
        })
            .then((response) => response.json())
            .then((data) => {
                const box = el('pos-results');
                box.innerHTML = '';

                if (!data.products || data.products.length === 0) {
                    box.innerHTML = '<p class="pos-cards-empty">Sin resultados. Ajusta la búsqueda o los filtros.</p>';
                    return;
                }

                data.products.forEach((product) => box.appendChild(productCard(product)));
                renderAddedState();
            })
            .catch(() => {});
    }

    let debounce = null;
    el('pos-search').addEventListener('input', (event) => {
        clearTimeout(debounce);
        debounce = setTimeout(() => runSearch(event.target.value.trim()), 250);
    });
    el('pos-search').addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch(event.target.value.trim());
        }
    });

    ['filter-metal', 'filter-category'].forEach((id) => {
        el(id).addEventListener('change', () => runSearch(el('pos-search').value.trim()));
    });

    let priceDebounce = null;
    ['filter-price-min', 'filter-price-max'].forEach((id) => {
        el(id).addEventListener('input', () => {
            clearTimeout(priceDebounce);
            priceDebounce = setTimeout(() => runSearch(el('pos-search').value.trim()), 300);
        });
    });

    el('filter-clear').addEventListener('click', () => {
        el('filter-metal').value = '';
        el('filter-category').value = '';
        el('filter-price-min').value = '';
        el('filter-price-max').value = '';
        runSearch(el('pos-search').value.trim());
    });

    runSearch('');

    el('discount_total').addEventListener('input', () => {
        resetAuthorizations();
        recalc();
    });
    el('add-payment').addEventListener('click', () => addPaymentRow());

    // --- Modal de Cobro ---
    function openChargeModal() {
        const error = el('cart-error');
        error.style.display = 'none';

        if (cart.length === 0) {
            error.textContent = 'Agrega al menos una pieza al carrito.';
            error.style.display = 'flex';
            return;
        }

        // Sincronizar Cliente en el Modal
        const custName = el('main-customer-name').textContent.trim();
        const custId = el('customer_id').value;
        if (custId && custName) {
            el('modal-customer-display').textContent = custName;
        } else {
            el('modal-customer-display').textContent = 'Público general';
        }

        // Sincronizar Resumen de Productos en el Modal
        const productNames = cart.map((item) => item.name);
        el('modal-products-display').textContent = productNames.length > 0 ? productNames.join(', ') : 'Sin piezas';

        // Asegurar fila de pago inicial con el total
        const rows = paymentRows();
        if (rows.length === 0) {
            addPaymentRow('cash', currentTotal());
        } else if (rows.length === 1 && (!rows[0].querySelector('[data-amount]').value || Number(rows[0].querySelector('[data-amount]').value) === 0)) {
            rows[0].querySelector('[data-amount]').value = currentTotal().toFixed(2);
        }

        const freshRows = paymentRows();
        const activeMethod = freshRows[0] ? freshRows[0].querySelector('[data-method]').value : 'cash';
        setMethodButtonActive(activeMethod);

        el('modal-charge').style.display = 'flex';
        recalc();

        // Enfoque inmediato al campo adecuado (recibido si es efectivo, referencia si tarjeta)
        const primaryRow = paymentRows()[0];
        if (primaryRow) {
            if (activeMethod === 'cash') {
                primaryRow.querySelector('[data-received]').focus();
            } else {
                primaryRow.querySelector('[data-reference]').focus();
            }
        }
    }

    function closeChargeModal() {
        el('modal-charge').style.display = 'none';
    }

    el('open-charge').addEventListener('click', openChargeModal);
    el('btn-close-charge').addEventListener('click', closeChargeModal);
    el('btn-cancel-charge').addEventListener('click', closeChargeModal);
    el('modal-charge').addEventListener('click', (event) => {
        if (event.target === el('modal-charge')) {
            closeChargeModal();
        }
    });

    // Enlace para cambiar cliente desde el modal
    const changeCustomerBtn = el('modal-change-customer-btn');
    if (changeCustomerBtn) {
        changeCustomerBtn.addEventListener('click', () => {
            closeChargeModal();
            clearCustomer();
        });
    }

    // --- Búsqueda y Selección de Cliente en la Vista Principal (desde el inicio) ---
    let customerController = null;
    let customerDebounce = null;

    function selectCustomer(customer) {
        el('customer_id').value = customer.id;
        el('main-customer-search-wrap').style.display = 'none';
        el('main-customer-results').innerHTML = '';
        el('main-customer-results').style.display = 'none';

        el('main-customer-name').textContent = customer.full_name;
        el('main-customer-details').textContent = (customer.document || '') + (customer.phone ? ' · ' + customer.phone : '');
        el('main-customer-selected').style.display = 'flex';

        el('modal-customer-display').textContent = customer.full_name;
    }

    function clearCustomer() {
        el('customer_id').value = '';
        el('main-customer-selected').style.display = 'none';
        el('main-customer-search-wrap').style.display = 'block';
        el('main-customer-search').value = '';
        el('main-customer-results').innerHTML = '';
        el('main-customer-results').style.display = 'none';
        el('main-customer-search').focus();

        el('modal-customer-display').textContent = 'Público general';
    }

    el('main-customer-clear').addEventListener('click', clearCustomer);

    function runCustomerSearch(term) {
        const resultsBox = el('main-customer-results');
        if (!term) {
            resultsBox.innerHTML = '';
            resultsBox.style.display = 'none';
            return;
        }

        if (customerController) {
            customerController.abort();
        }
        customerController = new AbortController();

        fetch(routes.customerSearch + '?q=' + encodeURIComponent(term), {
            headers: { 'Accept': 'application/json' },
            signal: customerController.signal,
        })
            .then((response) => response.json())
            .then((data) => {
                resultsBox.innerHTML = '';

                if (!data.customers || data.customers.length === 0) {
                    resultsBox.innerHTML = '<div class="muted" style="font-size:0.8rem; padding:0.5rem 0.65rem;">Sin clientes coincidentes. Puedes registrar uno con "+ Nuevo".</div>';
                    resultsBox.style.display = 'block';
                    return;
                }

                data.customers.forEach((customer) => {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'pos-result-chip';
                    chip.innerHTML =
                        '<div>' +
                            '<strong style="font-size:0.85rem; color:var(--color-noche);">' + customer.full_name + '</strong><br>' +
                            '<small class="muted" style="font-size:0.75rem;">' + (customer.document || '') + (customer.phone ? ' · ' + customer.phone : '') + '</small>' +
                        '</div>' +
                        '<span style="font-size:0.75rem; color:var(--color-dorado); font-weight:600;">Elegir →</span>';

                    chip.addEventListener('click', () => selectCustomer(customer));
                    resultsBox.appendChild(chip);
                });
                resultsBox.style.display = 'block';
            })
            .catch(() => {});
    }

    el('main-customer-search').addEventListener('input', (event) => {
        clearTimeout(customerDebounce);
        customerDebounce = setTimeout(() => runCustomerSearch(event.target.value.trim()), 200);
    });

    el('main-customer-search').addEventListener('focus', () => {
        const val = el('main-customer-search').value.trim();
        if (val) {
            runCustomerSearch(val);
        }
    });

    // Cerrar dropdown al hacer click afuera
    document.addEventListener('click', (event) => {
        const searchWrap = el('main-customer-search-wrap');
        if (searchWrap && !searchWrap.contains(event.target)) {
            const results = el('main-customer-results');
            if (results) {
                results.style.display = 'none';
            }
        }
    });

    // --- Modal de Autorización Híbrida ---
    function openAuthModal(subtotal, discountTotal, total, percent) {
        el('auth-subtotal').textContent = money(subtotal);
        el('auth-discount').textContent = money(discountTotal) + ' (' + percent.toFixed(1) + '%)';
        el('auth-total').textContent = money(total);

        el('supervisor-pin').value = '';
        el('pin-error').style.display = 'none';

        // Switch to PIN tab by default
        switchAuthTab('pin');

        el('remote-initial').style.display = 'block';
        el('remote-waiting').style.display = 'none';
        el('remote-approved').style.display = 'none';
        el('remote-rejected').style.display = 'none';
        el('remote-expired').style.display = 'none';

        el('modal-supervisor-auth').style.display = 'flex';
        el('supervisor-pin').focus();
    }

    function stopPollingAndCountdown() {
        if (activePollingInterval) {
            clearInterval(activePollingInterval);
            activePollingInterval = null;
        }
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    }

    function closeAuthModal() {
        el('modal-supervisor-auth').style.display = 'none';
        stopPollingAndCountdown();
    }

    function switchAuthTab(tab) {
        if (tab === 'pin') {
            el('panel-pin').style.display = 'block';
            el('panel-remote').style.display = 'none';
            el('tab-pin').style.borderBottom = '2px solid var(--color-dorado)';
            el('tab-pin').style.fontWeight = '600';
            el('tab-pin').style.color = 'var(--color-noche)';
            el('tab-remote').style.borderBottom = 'none';
            el('tab-remote').style.fontWeight = '400';
            el('tab-remote').style.color = 'var(--color-piedra)';
            el('supervisor-pin').focus();
        } else {
            el('panel-pin').style.display = 'none';
            el('panel-remote').style.display = 'block';
            el('tab-remote').style.borderBottom = '2px solid var(--color-dorado)';
            el('tab-remote').style.fontWeight = '600';
            el('tab-remote').style.color = 'var(--color-noche)';
            el('tab-pin').style.borderBottom = 'none';
            el('tab-pin').style.fontWeight = '400';
            el('tab-pin').style.color = 'var(--color-piedra)';
        }
    }

    el('tab-pin').addEventListener('click', () => switchAuthTab('pin'));
    el('tab-remote').addEventListener('click', () => switchAuthTab('remote'));
    el('btn-close-auth-modal').addEventListener('click', closeAuthModal);

    // Validador PIN
    el('btn-submit-pin').addEventListener('click', () => {
        const pin = el('supervisor-pin').value.trim();
        const pinError = el('pin-error');
        pinError.style.display = 'none';

        if (pin.length !== 4) {
            pinError.textContent = 'Ingresa un PIN de 4 dígitos.';
            pinError.style.display = 'block';
            return;
        }

        el('btn-submit-pin').disabled = true;

        fetch(routes.validatePin, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ pin: pin }),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok || !data.valid) {
                    throw new Error(data.message || 'PIN incorrecto.');
                }
                supervisorPinCode = pin;
                closeAuthModal();
                executeSale();
            })
            .catch((err) => {
                pinError.textContent = err.message;
                pinError.style.display = 'block';
                el('supervisor-pin').value = '';
                el('supervisor-pin').focus();
            })
            .finally(() => {
                el('btn-submit-pin').disabled = false;
            });
    });

    el('supervisor-pin').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            el('btn-submit-pin').click();
        }
    });

    // Enviar Solicitud Remota
    el('btn-send-remote').addEventListener('click', () => {
        const subtotal = cart.reduce((sum, item) => sum + item.price, 0);
        const lineDiscounts = cart.reduce((sum, item) => sum + item.discount, 0);
        const globalDiscount = Math.max(0, Number(el('discount_total').value) || 0);
        const discountTotal = lineDiscounts + globalDiscount;
        const percent = subtotal > 0 ? (discountTotal / subtotal) * 100 : 0;
        const reason = el('discount_reason').value.trim();

        if (!reason) {
            alert('Debes ingresar el motivo del descuento antes de solicitar autorización.');
            closeAuthModal();
            el('discount_reason').focus();
            return;
        }

        const payments = paymentRows().map((row) => ({
            method: row.querySelector('[data-method]').value,
            amount: row.querySelector('[data-amount]').value,
            received: row.querySelector('[data-received]').value || null,
            reference: row.querySelector('[data-reference]').value || null,
        }));

        el('remote-initial').style.display = 'none';
        el('remote-waiting').style.display = 'block';

        fetch(routes.requestRemote, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                sale_data: {
                    customer_id: el('customer_id').value || null,
                    items: cart.map((item) => ({ product_id: item.id, discount: item.discount })),
                    discount_total: el('discount_total').value || '0',
                    discount_reason: reason,
                    payments: payments,
                },
                subtotal: subtotal.toFixed(2),
                discount_amount: discountTotal.toFixed(2),
                discount_percent: percent.toFixed(2),
                reason: reason,
            }),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo crear la solicitud.');
                }
                currentAuthRequestId = data.id;
                startPollingStatus(data.id);
            })
            .catch((err) => {
                alert(err.message);
                el('remote-initial').style.display = 'block';
                el('remote-waiting').style.display = 'none';
            });
    });

    el('btn-cancel-remote').addEventListener('click', () => {
        stopPollingAndCountdown();
        el('remote-waiting').style.display = 'none';
        el('remote-initial').style.display = 'block';
    });

    el('btn-retry-auth').addEventListener('click', () => {
        closeAuthModal();
        el('discount_total').focus();
    });

    el('btn-retry-expired').addEventListener('click', () => {
        closeAuthModal();
        el('discount_total').focus();
    });

    function startPollingStatus(authId) {
        stopPollingAndCountdown();

        let remainingSeconds = 180; // 3 minutos
        const timerDisplay = el('remote-timer-display');

        function updateTimerUI() {
            const m = Math.floor(remainingSeconds / 60);
            const s = remainingSeconds % 60;
            if (timerDisplay) {
                timerDisplay.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                if (remainingSeconds <= 30) {
                    timerDisplay.style.color = 'var(--color-error)';
                } else {
                    timerDisplay.style.color = 'var(--color-dorado)';
                }
            }
        }
        updateTimerUI();

        countdownInterval = setInterval(() => {
            remainingSeconds--;
            updateTimerUI();
            if (remainingSeconds <= 0) {
                stopPollingAndCountdown();
                el('remote-waiting').style.display = 'none';
                el('remote-expired').style.display = 'block';
            }
        }, 1000);

        activePollingInterval = setInterval(() => {
            fetch(routes.checkStatus + '/' + authId + '/estado', {
                headers: { 'Accept': 'application/json' },
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data.status === 'approved') {
                        stopPollingAndCountdown();
                        approvedAuthorizationId = authId;

                        el('remote-waiting').style.display = 'none';
                        el('remote-approved').style.display = 'block';
                        el('approved-by-label').textContent = '¡Aprobado por ' + (data.authorized_by_name || 'Gerencia') + '!';

                        setTimeout(() => {
                            closeAuthModal();
                            executeSale();
                        }, 1200);
                    } else if (data.status === 'rejected') {
                        stopPollingAndCountdown();

                        el('remote-waiting').style.display = 'none';
                        el('remote-rejected').style.display = 'block';
                        el('rejected-reason-text').textContent = data.rejection_reason ? 'Motivo: ' + data.rejection_reason : 'Sin motivo especificado.';
                    } else if (data.status === 'pending' && !data.is_pending) {
                        stopPollingAndCountdown();
                        el('remote-waiting').style.display = 'none';
                        el('remote-expired').style.display = 'block';
                    }
                })
                .catch(() => {});
        }, 2500);
    }

    // --- Ejecutar Venta ---
    el('confirm-sale').addEventListener('click', () => {
        const error = el('pos-error');
        error.style.display = 'none';

        if (cart.length === 0) {
            error.textContent = 'Agrega al menos una pieza.';
            error.style.display = 'flex';
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + item.price, 0);
        const lineDiscounts = cart.reduce((sum, item) => sum + item.discount, 0);
        const globalDiscount = Math.max(0, Number(el('discount_total').value) || 0);
        const discountTotal = lineDiscounts + globalDiscount;
        const total = Math.max(0, subtotal - discountTotal);
        const percent = subtotal > 0 ? (discountTotal / subtotal) * 100 : 0;

        if (discountTotal > 0 && !el('discount_reason').value.trim()) {
            error.textContent = 'El motivo del descuento es obligatorio.';
            error.style.display = 'flex';
            el('discount_reason').focus();
            return;
        }

        // Check if authorization is needed
        if (percent > maxDiscountPercent && !canOverrideDiscount && !approvedAuthorizationId && !supervisorPinCode) {
            openAuthModal(subtotal, discountTotal, total, percent);
            return;
        }

        executeSale();
    });

    function executeSale() {
        const error = el('pos-error');
        error.style.display = 'none';

        const payments = paymentRows().map((row) => ({
            method: row.querySelector('[data-method]').value,
            amount: row.querySelector('[data-amount]').value,
            received: row.querySelector('[data-received]').value || null,
            reference: row.querySelector('[data-reference]').value || null,
        }));

        if (payments.length === 0) {
            error.textContent = 'Registra al menos un pago.';
            error.style.display = 'flex';
            return;
        }

        const payload = {
            customer_id: el('customer_id').value || null,
            items: cart.map((item) => ({ product_id: item.id, discount: item.discount })),
            discount_total: el('discount_total').value || '0',
            discount_reason: el('discount_reason').value || null,
            payments: payments,
            supervisor_pin: supervisorPinCode,
            discount_authorization_id: approvedAuthorizationId,
        };

        el('confirm-sale').disabled = true;

        fetch(routes.store, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(payload),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo registrar la venta.');
                }
                window.open(data.ticket_url, '_blank');
                window.location.reload();
            })
            .catch((exception) => {
                error.textContent = exception.message;
                error.style.display = 'flex';
                el('confirm-sale').disabled = false;
                resetAuthorizations();
            });
    }

    addPaymentRow('cash', null);
    renderCart();
})();
</script>
@endpush
