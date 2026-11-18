@extends('layouts.admin')

@section('title', 'Configuración del Sistema')

@section('body_class', 'full-viewport')

@php
    $brandName = old('system_name', $settings->system_name);
    $brandInitial = $brandName !== '' ? mb_strtoupper(mb_substr($brandName, 0, 1)) : 'J';
    $lastUpdated = $settings->updated_at?->format('d/m/Y H:i') ?? 'Sin cambios registrados';
    $currency = $store?->currency ?? 'USD';
@endphp

@section('content')
<style>
    .settings-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 0;
        overflow: hidden;
    }

    .settings-page .alert {
        flex-shrink: 0;
        margin-bottom: 0;
        padding: 0.5rem 0.85rem;
    }

    .settings-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.9rem;
        flex-shrink: 0;
        padding: 0.7rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .settings-hero-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .settings-hero-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .settings-hero h1 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.4rem;
        font-weight: 500;
        line-height: 1.15;
    }

    .settings-hero p {
        margin: 0.1rem 0 0;
        color: var(--color-piedra);
        font-size: 0.76rem;
    }

    .settings-hero-actions .btn {
        height: 32px;
        padding: 0 0.8rem;
        font-size: 0.78rem;
    }

    .settings-scroll {
        flex: 1;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        scrollbar-color: #D9D2C3 transparent;
        scrollbar-width: thin;
    }

    .settings-scroll::-webkit-scrollbar { width: 6px; }
    .settings-scroll::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .settings-scroll::-webkit-scrollbar-track { background: transparent; }

    .settings-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
        gap: 0.8rem;
        align-items: start;
    }

    .settings-stack {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        min-width: 0;
    }

    .settings-card {
        overflow: hidden;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .settings-card-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.65rem 0.9rem;
        border-bottom: 1px solid var(--color-linea);
        background: #FCFBF8;
    }

    .settings-card-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .settings-card-header h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1rem;
        font-weight: 500;
    }

    .settings-card-header p {
        margin: 0.05rem 0 0;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .settings-card-body {
        padding: 0.9rem;
    }

    .settings-section-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.7rem;
    }

    .settings-field {
        margin-bottom: 0.75rem;
    }

    .settings-field:last-child {
        margin-bottom: 0;
    }

    .settings-field-full {
        grid-column: 1 / -1;
    }

    .settings-field label {
        display: block;
        margin-bottom: 0.22rem;
        color: var(--color-piedra);
        font-size: 0.67rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .settings-field label .req {
        margin-left: 0.15rem;
        color: var(--color-error);
    }

    .settings-field .form-control {
        width: 100%;
        height: 34px;
        padding: 0 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: #FCFBF8;
        color: var(--color-noche);
        font-family: var(--font-sans);
        font-size: 0.82rem;
        transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
    }

    .settings-field textarea.form-control {
        height: auto;
        min-height: 78px;
        padding: 0.55rem 0.75rem;
        line-height: 1.45;
        resize: vertical;
    }

    .settings-field .form-control:focus {
        border-color: var(--color-dorado);
        background: var(--color-blanco);
        box-shadow: 0 0 0 2px rgba(184, 154, 94, 0.12);
        outline: none;
    }

    .settings-field .form-control.is-invalid {
        border-color: var(--color-error);
        background-color: #FFF5F5;
    }

    .settings-hint {
        margin: 0.22rem 0 0;
        color: var(--color-piedra);
        font-size: 0.7rem;
        line-height: 1.4;
    }

    .settings-error {
        display: block;
        margin-top: 0.2rem;
        color: var(--color-error);
        font-size: 0.7rem;
        font-weight: 500;
    }

    .settings-preview {
        padding: 0.9rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-noche);
        color: var(--color-hueso);
    }

    .settings-preview-brand {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .settings-preview-logo {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 1px solid rgba(184, 154, 94, 0.55);
        background: rgba(184, 154, 94, 0.16);
        color: var(--color-dorado);
        font-family: var(--font-serif);
        font-size: 1.1rem;
        font-weight: 600;
    }

    .settings-preview-brand strong {
        display: block;
        overflow: hidden;
        color: #FDFCF9;
        font-size: 0.95rem;
        font-weight: 500;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .settings-preview-subtitle {
        display: block;
        margin-top: 0.1rem;
        overflow: hidden;
        color: var(--color-dorado);
        font-size: 0.58rem;
        letter-spacing: 0.08em;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .settings-preview p {
        margin: 0.75rem 0 0;
        color: #B3B0A6;
        font-size: 0.7rem;
        line-height: 1.45;
    }

    .settings-summary {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        margin: 0;
    }

    .settings-summary div {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.55rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .settings-summary div:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .settings-summary dt {
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .settings-summary dd {
        margin: 0;
        color: var(--color-noche);
        font-size: 0.78rem;
        font-weight: 500;
        text-align: right;
    }

    .settings-note {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        margin-top: 0.8rem;
        padding: 0.55rem 0.6rem;
        border: 1px solid rgba(184, 154, 94, 0.3);
        border-radius: 4px;
        background: rgba(184, 154, 94, 0.09);
        color: #6E5728;
        font-size: 0.7rem;
        line-height: 1.4;
    }

    .settings-note svg {
        flex-shrink: 0;
        margin-top: 0.05rem;
        color: var(--color-dorado);
    }

    .settings-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.8rem;
        padding: 0.7rem 0.9rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .settings-actions p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .settings-actions-buttons {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    @media (max-width: 1024px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        body.full-viewport { overflow: auto; }
        .full-viewport .main-wrapper { height: auto; overflow: visible; }
        .full-viewport .content-area { display: block; overflow: visible; }
        .settings-page { height: auto; overflow: visible; }
        .settings-hero { flex-direction: column; align-items: flex-start; }
        .settings-hero-actions { width: 100%; }
        .settings-hero-actions .btn { width: 100%; }
        .settings-section-grid { grid-template-columns: 1fr; }
        .settings-actions { flex-direction: column; align-items: stretch; }
        .settings-actions-buttons .btn { flex: 1; justify-content: center; }
    }
</style>

<div class="settings-page">
    <header class="settings-hero">
        <div class="settings-hero-main">
            <span class="settings-hero-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </span>
            <div>
                <h1>Configuración del Sistema</h1>
                <p>Identidad de la marca, parámetros operativos y textos legales de la tienda</p>
            </div>
        </div>

        <div class="settings-hero-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">
                <span>&larr; Volver al Dashboard</span>
            </a>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="settings-scroll">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="settings-grid">
                <div class="settings-stack">
                    <section class="settings-card">
                        <div class="settings-card-header">
                            <span class="settings-card-icon" aria-hidden="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2 2 7l10 5 10-5-10-5Z"></path>
                                    <path d="m2 17 10 5 10-5"></path>
                                    <path d="m2 12 10 5 10-5"></path>
                                </svg>
                            </span>
                            <div>
                                <h2>Identidad de la marca</h2>
                                <p>Nombre visible en el panel, el acceso y el sitio público.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="settings-field">
                                <label for="system_name">Nombre del sistema <span class="req">*</span></label>
                                <input
                                    type="text"
                                    id="system_name"
                                    name="system_name"
                                    value="{{ old('system_name', $settings->system_name) }}"
                                    maxlength="100"
                                    required
                                    autocomplete="organization"
                                    data-system-name-input
                                    class="form-control {{ $errors->has('system_name') ? 'is-invalid' : '' }}"
                                >
                                <p class="settings-hint">Ej. Joyería Aurora. Se muestra en el menú lateral, la pantalla de ingreso y el sitio web.</p>
                                @error('system_name')
                                    <span class="settings-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="settings-card">
                        <div class="settings-card-header">
                            <span class="settings-card-icon" aria-hidden="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 3v18h18"></path>
                                    <path d="m7 15 4-4 3 3 5-6"></path>
                                </svg>
                            </span>
                            <div>
                                <h2>Parámetros operativos</h2>
                                <p>Límites que aplican a ventas, compras, apartados y alertas del panel.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="settings-section-grid">
                                <div class="settings-field">
                                    <label for="purchase_approval_limit">Límite de aprobación de compras ({{ $currency }}) <span class="req">*</span></label>
                                    <input
                                        type="number"
                                        id="purchase_approval_limit"
                                        name="purchase_approval_limit"
                                        value="{{ old('purchase_approval_limit', $settings->purchase_approval_limit) }}"
                                        min="0"
                                        max="9999999999.99"
                                        step="0.01"
                                        required
                                        class="form-control {{ $errors->has('purchase_approval_limit') ? 'is-invalid' : '' }}"
                                    >
                                    <p class="settings-hint">Las compras de metal por encima de este monto requieren aprobación de un gerente.</p>
                                    @error('purchase_approval_limit')
                                        <span class="settings-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="settings-field">
                                    <label for="max_discount_percent">Descuento máximo sin autorización (%) <span class="req">*</span></label>
                                    <input
                                        type="number"
                                        id="max_discount_percent"
                                        name="max_discount_percent"
                                        value="{{ old('max_discount_percent', $settings->max_discount_percent) }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        required
                                        class="form-control {{ $errors->has('max_discount_percent') ? 'is-invalid' : '' }}"
                                    >
                                    <p class="settings-hint">Porcentaje que un vendedor puede aplicar sin PIN de supervisor.</p>
                                    @error('max_discount_percent')
                                        <span class="settings-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="settings-field">
                                    <label for="layaway_max_days">Días máximos de apartado <span class="req">*</span></label>
                                    <input
                                        type="number"
                                        id="layaway_max_days"
                                        name="layaway_max_days"
                                        value="{{ old('layaway_max_days', $settings->layaway_max_days) }}"
                                        min="1"
                                        max="3650"
                                        step="1"
                                        required
                                        class="form-control {{ $errors->has('layaway_max_days') ? 'is-invalid' : '' }}"
                                    >
                                    <p class="settings-hint">Vigencia máxima permitida al crear un apartado.</p>
                                    @error('layaway_max_days')
                                        <span class="settings-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="settings-field">
                                    <label for="low_stock_alert_threshold">Umbral de stock bajo (piezas) <span class="req">*</span></label>
                                    <input
                                        type="number"
                                        id="low_stock_alert_threshold"
                                        name="low_stock_alert_threshold"
                                        value="{{ old('low_stock_alert_threshold', $settings->low_stock_alert_threshold) }}"
                                        min="0"
                                        max="100000"
                                        step="1"
                                        required
                                        class="form-control {{ $errors->has('low_stock_alert_threshold') ? 'is-invalid' : '' }}"
                                    >
                                    <p class="settings-hint">El dashboard alerta cuando las piezas disponibles caen por debajo de este valor.</p>
                                    @error('low_stock_alert_threshold')
                                        <span class="settings-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="settings-field">
                                    <label for="cash_difference_alert_limit">Límite de diferencia de caja ({{ $currency }}) <span class="req">*</span></label>
                                    <input
                                        type="number"
                                        id="cash_difference_alert_limit"
                                        name="cash_difference_alert_limit"
                                        value="{{ old('cash_difference_alert_limit', $settings->cash_difference_alert_limit) }}"
                                        min="0"
                                        max="9999999999.99"
                                        step="0.01"
                                        required
                                        class="form-control {{ $errors->has('cash_difference_alert_limit') ? 'is-invalid' : '' }}"
                                    >
                                    <p class="settings-hint">Diferencia de arqueo a partir de la cual se genera una alerta.</p>
                                    @error('cash_difference_alert_limit')
                                        <span class="settings-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="settings-field">
                                    <label for="whatsapp_number">WhatsApp público</label>
                                    <input
                                        type="text"
                                        id="whatsapp_number"
                                        name="whatsapp_number"
                                        value="{{ old('whatsapp_number', $settings->whatsapp_number) }}"
                                        maxlength="20"
                                        placeholder="+1 555 019 2834"
                                        class="form-control {{ $errors->has('whatsapp_number') ? 'is-invalid' : '' }}"
                                    >
                                    <p class="settings-hint">Número que se muestra en el sitio público para contacto directo.</p>
                                    @error('whatsapp_number')
                                        <span class="settings-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="settings-card">
                        <div class="settings-card-header">
                            <span class="settings-card-icon" aria-hidden="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="8" y1="13" x2="16" y2="13"></line>
                                    <line x1="8" y1="17" x2="13" y2="17"></line>
                                </svg>
                            </span>
                            <div>
                                <h2>Textos legales y de impresión</h2>
                                <p>Mensajes que aparecen en tickets, contratos de apartado y comprobantes de compra.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="settings-field">
                                <label for="ticket_footer_text">Pie de ticket</label>
                                <textarea
                                    id="ticket_footer_text"
                                    name="ticket_footer_text"
                                    rows="3"
                                    maxlength="2000"
                                    class="form-control {{ $errors->has('ticket_footer_text') ? 'is-invalid' : '' }}"
                                >{{ old('ticket_footer_text', $settings->ticket_footer_text) }}</textarea>
                                <p class="settings-hint">Mensaje impreso al final de los tickets de venta.</p>
                                @error('ticket_footer_text')
                                    <span class="settings-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="settings-field">
                                <label for="default_layaway_terms">Términos de apartado</label>
                                <textarea
                                    id="default_layaway_terms"
                                    name="default_layaway_terms"
                                    rows="3"
                                    maxlength="2000"
                                    class="form-control {{ $errors->has('default_layaway_terms') ? 'is-invalid' : '' }}"
                                >{{ old('default_layaway_terms', $settings->default_layaway_terms) }}</textarea>
                                <p class="settings-hint">Condiciones que se imprimen en los contratos de apartado.</p>
                                @error('default_layaway_terms')
                                    <span class="settings-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="settings-field">
                                <label for="default_purchase_disclaimer">Aviso legal de compra de metales</label>
                                <textarea
                                    id="default_purchase_disclaimer"
                                    name="default_purchase_disclaimer"
                                    rows="3"
                                    maxlength="2000"
                                    class="form-control {{ $errors->has('default_purchase_disclaimer') ? 'is-invalid' : '' }}"
                                >{{ old('default_purchase_disclaimer', $settings->default_purchase_disclaimer) }}</textarea>
                                <p class="settings-hint">Texto legal que acompaña los comprobantes de compra de metal.</p>
                                @error('default_purchase_disclaimer')
                                    <span class="settings-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="settings-stack">
                    <section class="settings-card">
                        <div class="settings-card-header">
                            <span class="settings-card-icon" aria-hidden="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                            <div>
                                <h2>Vista previa</h2>
                                <p>Cómo se verá el nombre en el panel.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="settings-preview">
                                <div class="settings-preview-brand">
                                    <span class="settings-preview-logo" data-system-initial-preview>{{ $brandInitial }}</span>
                                    <div style="min-width: 0;">
                                        <strong data-system-name-preview>{{ $brandName }}</strong>
                                        <span class="settings-preview-subtitle">Alta Gama &amp; Control</span>
                                    </div>
                                </div>
                                <p>El nombre también aparece en la pantalla de ingreso y en la cabecera del sitio público.</p>
                            </div>
                        </div>
                    </section>

                    <section class="settings-card">
                        <div class="settings-card-header">
                            <span class="settings-card-icon" aria-hidden="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                            </span>
                            <div>
                                <h2>Resumen</h2>
                                <p>Datos de la configuración actual.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <dl class="settings-summary">
                                <div>
                                    <dt>Sucursal</dt>
                                    <dd>{{ $store?->name ?? 'Principal' }}</dd>
                                </div>
                                <div>
                                    <dt>Moneda</dt>
                                    <dd>{{ $currency }}</dd>
                                </div>
                                <div>
                                    <dt>Última actualización</dt>
                                    <dd>{{ $lastUpdated }}</dd>
                                </div>
                            </dl>

                            <div class="settings-note">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                <span>Solo el rol Dueño puede modificar la configuración. Cada cambio queda registrado en la auditoría.</span>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>

            <div class="settings-actions">
                <p>Los cambios se aplican al guardar. El nombre se actualiza en todo el sistema.</p>
                <div class="settings-actions-buttons">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-gold">Guardar configuración</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector('[data-system-name-input]');
        var namePreview = document.querySelector('[data-system-name-preview]');
        var initialPreview = document.querySelector('[data-system-initial-preview]');

        if (!input || !namePreview || !initialPreview) {
            return;
        }

        var syncPreview = function () {
            var value = input.value.trim();
            namePreview.textContent = value || 'Nombre del sistema';
            initialPreview.textContent = (value.charAt(0) || 'J').toUpperCase();
        };

        input.addEventListener('input', syncPreview);
        syncPreview();
    });
</script>
@endpush
