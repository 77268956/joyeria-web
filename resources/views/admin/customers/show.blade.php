@extends('layouts.admin')

@section('title', 'Cliente: '.$customer->full_name)

@section('body_class', 'full-viewport')

@php
    $initials = collect(explode(' ', $customer->full_name))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');
    if (empty($initials)) {
        $initials = 'CL';
    }

    $salesCount = $stats['total_sales'] ?? 0;
    $totalSpent = $stats['total_spent'] ?? '0.00';
    $averageTicket = $stats['average_ticket'] ?? '0.00';
    $activeLayaways = $stats['active_layaways'] ?? 0;
@endphp

@section('content')
<style>
    .cust-show-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 0;
        overflow: hidden;
    }

    .cust-show-page .alert {
        margin-bottom: 0;
        padding: 0.5rem 0.85rem;
        flex-shrink: 0;
    }

    .cust-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 1.15rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
        flex-shrink: 0;
    }

    .cust-hero-main {
        display: flex;
        align-items: center;
        gap: 0.95rem;
        min-width: 0;
    }

    .cust-hero-avatar {
        display: grid;
        place-items: center;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--color-noche);
        color: var(--color-hueso);
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
        letter-spacing: 0.03em;
        box-shadow: 0 2px 4px rgba(18, 22, 31, 0.1);
    }

    .cust-hero-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.25rem;
    }

    .cust-hero-title {
        margin: 0;
        font-family: var(--font-serif);
        font-size: clamp(1.4rem, 2vw, 1.85rem);
        font-weight: 500;
        line-height: 1.15;
        color: var(--color-noche);
    }

    .cust-hero-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem 1rem;
        margin-top: 0.35rem;
        font-size: 0.76rem;
        color: var(--color-piedra);
    }

    .cust-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .cust-hero-meta strong {
        color: var(--color-noche);
    }

    .cust-hero-actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .cust-hero-actions .btn {
        height: 34px;
        padding: 0 0.85rem;
        font-size: 0.8rem;
    }

    /* Stats Grid con iconos a la derecha */
    .cust-show-stats {
        display: grid;
        flex-shrink: 0;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .cust-show-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        min-width: 0;
        padding: 0.65rem 0.95rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 5px;
    }

    .cust-show-stat-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .cust-show-stat-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--color-piedra);
    }

    .cust-show-stat-value {
        display: block;
        margin-top: 0.1rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .cust-show-stat-hint {
        display: block;
        margin-top: 0.1rem;
        font-size: 0.72rem;
        color: var(--color-piedra);
    }

    /* Grid principal en 2 columnas */
    .cust-show-grid {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1.6fr) minmax(320px, 1fr);
        gap: 0.85rem;
        align-items: stretch;
        min-height: 0;
        overflow: hidden;
    }

    .cust-show-column {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        min-width: 0;
        min-height: 0;
        overflow-y: auto;
        padding-right: 0.2rem;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .cust-show-column::-webkit-scrollbar { width: 6px; }
    .cust-show-column::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .cust-show-column::-webkit-scrollbar-track { background: transparent; }

    .cust-card {
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
        flex-shrink: 0;
    }

    .cust-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.75rem 1.05rem;
        border-bottom: 1px solid var(--color-linea);
        background: #FCFBF8;
    }

    .cust-card-header h2 {
        margin: 0;
        font-family: var(--font-serif);
        font-size: 1.05rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .cust-card-header p {
        margin: 0.1rem 0 0;
        font-size: 0.72rem;
        color: var(--color-piedra);
    }

    .cust-card-body {
        padding: 1rem 1.05rem;
    }

    /* Info rows */
    .cust-data-list {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .cust-data-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.55rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .cust-data-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .cust-data-label {
        font-size: 0.78rem;
        color: var(--color-piedra);
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .cust-data-label svg {
        color: var(--color-dorado);
        flex-shrink: 0;
    }

    .cust-data-val {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--color-noche);
        text-align: right;
    }

    .cust-data-val a {
        color: var(--color-noche);
        text-decoration: none;
        transition: color 0.15s;
    }

    .cust-data-val a:hover {
        color: var(--color-dorado);
    }

    /* Tabla de ventas */
    .cust-table-wrap {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .cust-table-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
    .cust-table-wrap::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .cust-table-wrap::-webkit-scrollbar-track { background: transparent; }

    .cust-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .cust-table thead tr {
        background-color: var(--color-crema);
        border-bottom: 1px solid var(--color-linea);
    }

    .cust-table th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 0.6rem 0.85rem;
        background-color: var(--color-crema);
        box-shadow: inset 0 -1px 0 var(--color-linea);
        color: var(--color-noche);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .cust-table th.text-right { text-align: right; }

    .cust-table td {
        padding: 0.55rem 0.85rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .cust-table tbody tr:last-child td { border-bottom: 0; }
    .cust-table tbody tr:hover { background-color: #FBF9F5; }
    .cust-table .text-right { text-align: right; }

    /* Identificación foto */
    .cust-id-preview {
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid var(--color-linea);
        background: #FCFBF8;
        position: relative;
    }

    .cust-id-preview img {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        display: block;
    }

    .cust-id-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.55rem 0.75rem;
        background: #FCFBF8;
        border-top: 1px solid var(--color-linea);
    }

    .cust-badge-verified {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: #EDF7EE;
        color: var(--color-exito);
        font-size: 0.68rem;
        font-weight: 600;
    }

    .cust-empty-block {
        padding: 2.2rem 1rem;
        text-align: center;
    }

    .cust-empty-icon {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        margin: 0 auto 0.65rem;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .cust-empty-block strong {
        display: block;
        font-size: 0.92rem;
        color: var(--color-noche);
    }

    .cust-empty-block p {
        margin: 0.2rem 0 0;
        font-size: 0.78rem;
        color: var(--color-piedra);
    }

    @media (max-width: 1024px) {
        body.full-viewport { overflow: auto; }
        .full-viewport .main-wrapper { height: auto; overflow: visible; }
        .full-viewport .content-area { display: block; overflow: visible; }
        .cust-show-page { overflow: visible; height: auto; }
        .cust-show-grid { grid-template-columns: minmax(0, 1fr); overflow: visible; }
        .cust-show-column { overflow: visible; padding-right: 0; }
        .cust-show-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 650px) {
        .cust-hero { flex-direction: column; align-items: flex-start; }
        .cust-hero-actions { width: 100%; }
        .cust-hero-actions .btn { flex: 1; justify-content: center; }
        .cust-show-stats { grid-template-columns: minmax(0, 1fr); }
    }
</style>

<div class="cust-show-page">
    {{-- Hero Header --}}
    <header class="cust-hero">
        <div class="cust-hero-main">
            <span class="cust-hero-avatar" aria-hidden="true">{{ $initials }}</span>
            <div>
                <div class="cust-hero-kicker">
                    <span class="cust-doc-badge" style="padding: 0.18rem 0.5rem; background: #F1EFEA; border-radius: 4px; font-weight: 700; font-size: 0.68rem; text-transform: uppercase;">
                        {{ $customer->document_type }}
                    </span>
                    <span class="num" style="font-weight: 600; font-size: 0.85rem; color: var(--color-noche);">
                        {{ $customer->document_number }}
                    </span>
                    @if ($salesCount > 0)
                        <span class="badge badge-success" style="font-size: 0.68rem;">Cliente recurrente</span>
                    @else
                        <span class="badge badge-muted" style="font-size: 0.68rem;">Sin compras aún</span>
                    @endif
                </div>

                <h1 class="cust-hero-title">{{ $customer->full_name }}</h1>

                <div class="cust-hero-meta">
                    @if ($customer->phone)
                        <span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <strong>{{ $customer->phone }}</strong>
                        </span>
                    @endif
                    @if ($customer->email)
                        <span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                            <span>{{ $customer->email }}</span>
                        </span>
                    @endif
                    @if ($customer->created_at)
                        <span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Registrado el {{ $customer->created_at->format('d/m/Y') }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="cust-hero-actions">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-gold" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m14 4 6 6L8 22H2v-6L14 4Z"></path>
                    <path d="m12 6 6 6"></path>
                </svg>
                <span>Editar Cliente</span>
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver</span>
            </a>
        </div>
    </header>

    @include('admin.partials.alerts')

    {{-- Tarjetas Estadísticas con Iconos a la Derecha --}}
    <div class="cust-show-stats">
        <div class="cust-show-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="cust-show-stat-label">Total en compras</span>
                <strong class="cust-show-stat-value" style="color: var(--color-noche);">$ {{ number_format((float) $totalSpent, 2) }}</strong>
                <span class="cust-show-stat-hint">Inversión acumulada</span>
            </div>
            <span class="cust-show-stat-icon" aria-hidden="true" style="background: #EDF7EE; color: var(--color-exito);">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </span>
        </div>

        <div class="cust-show-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="cust-show-stat-label">Compras cerradas</span>
                <strong class="cust-show-stat-value">{{ $salesCount }} {{ $salesCount === 1 ? 'venta' : 'ventas' }}</strong>
                <span class="cust-show-stat-hint">Transacciones en mostrador</span>
            </div>
            <span class="cust-show-stat-icon" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
            </span>
        </div>

        <div class="cust-show-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="cust-show-stat-label">Ticket promedio</span>
                <strong class="cust-show-stat-value">$ {{ number_format((float) $averageTicket, 2) }}</strong>
                <span class="cust-show-stat-hint">Gasto medio por visita</span>
            </div>
            <span class="cust-show-stat-icon" aria-hidden="true" style="background: rgba(184, 154, 94, 0.15); color: var(--color-dorado);">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18"></path>
                    <path d="m19 9-5 5-4-4-3 3"></path>
                </svg>
            </span>
        </div>

        <div class="cust-show-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="cust-show-stat-label">Apartados activos</span>
                <strong class="cust-show-stat-value" style="{{ $activeLayaways > 0 ? 'color: var(--color-dorado);' : '' }}">{{ $activeLayaways }}</strong>
                <span class="cust-show-stat-hint">{{ $activeLayaways > 0 ? 'Con saldo pendiente' : 'Sin apartados en curso' }}</span>
            </div>
            <span class="cust-show-stat-icon" aria-hidden="true" style="background: #E8F0F5; color: var(--color-info);">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </span>
        </div>
    </div>

    {{-- Grid principal en 2 columnas --}}
    <div class="cust-show-grid">
        {{-- Columna izquierda: Historial de compras y apartados --}}
        <div class="cust-show-column">
            {{-- Tarjeta de compras --}}
            <section class="cust-card">
                <div class="cust-card-header">
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span class="cust-show-stat-icon" aria-hidden="true" style="width: 30px; height: 30px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                <path d="M3 6h18"></path>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                        </span>
                        <div>
                            <h2>Historial de Compras</h2>
                            <p>Transacciones cobradas en mostrador asociadas a este cliente</p>
                        </div>
                    </div>
                    <span class="inv-origin" style="padding: 0.18rem 0.55rem; font-size: 0.65rem;">
                        {{ $customer->sales->count() }} {{ $customer->sales->count() === 1 ? 'compra' : 'compras' }}
                    </span>
                </div>

                <div class="cust-table-wrap">
                    <table class="cust-table">
                        <thead>
                            <tr>
                                <th>Folio de Venta</th>
                                <th>Fecha y Hora</th>
                                <th class="text-right">Total</th>
                                <th>Estado</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customer->sales as $sale)
                                <tr>
                                    <td class="num" style="font-weight: 600;">
                                        <a href="{{ route('admin.sales.show', $sale) }}" style="color: var(--color-noche); text-decoration: none;">
                                            {{ $sale->number }}
                                        </a>
                                    </td>
                                    <td class="num muted" style="font-size: 0.78rem;">
                                        {{ $sale->created_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-right num" style="font-weight: 600; font-size: 0.86rem;">
                                        $ {{ number_format((float) $sale->total, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $sale->isVoided() ? 'badge-danger' : 'badge-success' }}">
                                            {{ $sale->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.sales.show', $sale) }}" class="cust-icon-btn" title="Ver detalle de la venta">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="cust-empty-block">
                                            <span class="cust-empty-icon" aria-hidden="true">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="8" cy="21" r="1"></circle>
                                                    <circle cx="19" cy="21" r="1"></circle>
                                                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                                                </svg>
                                            </span>
                                            <strong>Aún no registra compras</strong>
                                            <p>Las compras que se cobren a nombre de este cliente aparecerán automáticamente aquí.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Tarjeta de apartados si existen --}}
            @if ($customer->layaways->isNotEmpty())
                <section class="cust-card">
                    <div class="cust-card-header">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span class="cust-show-stat-icon" aria-hidden="true" style="width: 30px; height: 30px; background: #E8F0F5; color: var(--color-info);">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </span>
                            <div>
                                <h2>Apartados (Layaways)</h2>
                                <p>Planes de apartado vigentes o liquidados</p>
                            </div>
                        </div>
                        <span class="inv-origin" style="padding: 0.18rem 0.55rem; font-size: 0.65rem;">
                            {{ $customer->layaways->count() }} {{ $customer->layaways->count() === 1 ? 'apartado' : 'apartados' }}
                        </span>
                    </div>

                    <div class="cust-table-wrap">
                        <table class="cust-table">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Saldo</th>
                                    <th>Estado</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customer->layaways as $layaway)
                                    <tr>
                                        <td class="num" style="font-weight: 600;">#{{ $layaway->id }}</td>
                                        <td class="num muted" style="font-size: 0.78rem;">{{ $layaway->created_at?->format('d/m/Y') }}</td>
                                        <td class="text-right num">$ {{ number_format((float) $layaway->total, 2) }}</td>
                                        <td class="text-right num" style="font-weight: 600; color: {{ bccomp((string) $layaway->balance, '0', 2) > 0 ? 'var(--color-dorado)' : 'var(--color-exito)' }};">
                                            $ {{ number_format((float) $layaway->balance, 2) }}
                                        </td>
                                        <td>
                                            @php
                                                $lBadge = match ($layaway->status) {
                                                    \App\Enums\LayawayStatus::Active => 'badge-gold',
                                                    \App\Enums\LayawayStatus::Completed => 'badge-success',
                                                    default => 'badge-danger',
                                                };
                                            @endphp
                                            <span class="badge {{ $lBadge }}">{{ $layaway->status->label() }}</span>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.layaways.show', $layaway) }}" class="cust-icon-btn" title="Ver apartado">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        {{-- Columna derecha: Datos personales, identificación y notas --}}
        <div class="cust-show-column">
            {{-- Datos de Contacto y Perfil --}}
            <section class="cust-card">
                <div class="cust-card-header">
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span class="cust-show-stat-icon" aria-hidden="true" style="width: 30px; height: 30px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <div>
                            <h2>Ficha de Contacto</h2>
                            <p>Canales de comunicación y domicilio</p>
                        </div>
                    </div>
                </div>

                <div class="cust-card-body">
                    <div class="cust-data-list">
                        <div class="cust-data-item">
                            <span class="cust-data-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                Teléfono móvil
                            </span>
                            <span class="cust-data-val num">
                                @if ($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" title="Llamar">{{ $customer->phone }}</a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </span>
                        </div>

                        <div class="cust-data-item">
                            <span class="cust-data-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                                Correo electrónico
                            </span>
                            <span class="cust-data-val">
                                @if ($customer->email)
                                    <a href="mailto:{{ $customer->email }}" title="Enviar correo">{{ $customer->email }}</a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </span>
                        </div>

                        <div class="cust-data-item">
                            <span class="cust-data-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Dirección de entrega
                            </span>
                            <span class="cust-data-val" style="max-width: 220px; font-weight: 500; font-size: 0.78rem;">
                                {{ $customer->address ?? 'Sin dirección registrada' }}
                            </span>
                        </div>

                        <div class="cust-data-item">
                            <span class="cust-data-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Fecha de nacimiento
                            </span>
                            <span class="cust-data-val num">
                                @if ($customer->birth_date)
                                    {{ $customer->birth_date->format('d/m/Y') }}
                                    <small class="muted" style="font-size: 0.72rem; font-weight: normal;">({{ $customer->birth_date->age }} años)</small>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Tarjeta de Identificación Oficial --}}
            <section class="cust-card">
                <div class="cust-card-header">
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span class="cust-show-stat-icon" aria-hidden="true" style="width: 30px; height: 30px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                <circle cx="9" cy="9" r="2"></circle>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                            </svg>
                        </span>
                        <div>
                            <h2>Documento de Identificación</h2>
                            <p>Copia digital archivada de {{ $customer->document_type }}</p>
                        </div>
                    </div>
                </div>

                <div class="cust-card-body">
                    @if ($customer->id_photo_path)
                        <div class="cust-id-preview">
                            <img
                                src="{{ route('admin.customers.photo', $customer) }}"
                                alt="Identificación de {{ $customer->full_name }}"
                            >
                            <div class="cust-id-actions">
                                <span class="cust-badge-verified">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span>Documento verificado</span>
                                </span>
                                <a href="{{ route('admin.customers.photo', $customer) }}" target="_blank" class="btn btn-outline btn-sm" style="height: 26px; padding: 0 0.65rem; font-size: 0.72rem;">
                                    Ver original
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="cust-empty-block" style="padding: 1.5rem 0.5rem;">
                            <span class="cust-empty-icon" aria-hidden="true" style="width: 38px; height: 38px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                    <circle cx="9" cy="9" r="2"></circle>
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                                </svg>
                            </span>
                            <strong>Sin foto de documento</strong>
                            <p>Puedes adjuntar la fotografía del DUI o pasaporte desde la opción "Editar Cliente".</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Tarjeta de Notas y Preferencias --}}
            <section class="cust-card">
                <div class="cust-card-header">
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span class="cust-show-stat-icon" aria-hidden="true" style="width: 30px; height: 30px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                        </span>
                        <div>
                            <h2>Notas y Preferencias</h2>
                            <p>Anotaciones de mostrador y gustos de joyería</p>
                        </div>
                    </div>
                </div>

                <div class="cust-card-body">
                    @if ($customer->notes)
                        <p style="margin: 0; font-size: 0.82rem; line-height: 1.55; color: var(--color-noche); background: #FCFBF8; padding: 0.75rem; border: 1px solid var(--color-linea); border-radius: 4px;">
                            {{ $customer->notes }}
                        </p>
                    @else
                        <p class="muted" style="margin: 0; font-size: 0.78rem; font-style: italic;">
                            Sin notas ni preferencias registradas para este cliente.
                        </p>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
