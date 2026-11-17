@extends('layouts.admin')

@section('title', 'Clientes')

@section('body_class', 'full-viewport')

@php
    $searchTerm = request('search');
    $hasSearch = ! empty($searchTerm);
@endphp

@section('content')
<style>
    .cust-stats {
        display: grid;
        flex-shrink: 0;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .cust-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        min-width: 0;
        padding: 0.75rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 5px;
    }

    .cust-stat-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .cust-stat-label {
        display: block;
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--color-piedra);
    }

    .cust-stat-value {
        display: block;
        margin-top: 0.15rem;
        font-size: 1.15rem;
        font-weight: 600;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .cust-stat-hint {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.74rem;
        color: var(--color-piedra);
    }

    .cust-filter-card {
        padding: 0.85rem 1.15rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        flex-shrink: 0;
    }

    .cust-filter-form {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .cust-search-wrap {
        position: relative;
        flex: 1;
        min-width: 260px;
    }

    .cust-search-wrap svg {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--color-piedra);
        pointer-events: none;
    }

    .cust-search-wrap .form-control {
        width: 100%;
        height: 38px;
        padding-left: 2.35rem;
        font-size: 0.85rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: #FCFBF8;
        transition: border-color 0.15s, background-color 0.15s;
    }

    .cust-search-wrap .form-control:focus {
        background: var(--color-blanco);
        border-color: var(--color-dorado);
        outline: none;
    }

    .cust-filter-actions {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .cust-filter-actions .btn {
        height: 38px;
        padding: 0 1rem;
        font-size: 0.82rem;
    }

    .cust-table-card {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .cust-table-head {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.75rem 1.15rem;
        border-bottom: 1px solid var(--color-linea);
        background: var(--color-blanco);
    }

    .cust-table-head h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .cust-table-head p {
        margin: 0.12rem 0 0;
        font-size: 0.74rem;
        color: var(--color-piedra);
    }

    .cust-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.22rem 0.55rem;
        border: 1px solid #E4D8BC;
        border-radius: 999px;
        background: #FBF6EA;
        color: #8A6516;
        font-size: 0.68rem;
        font-weight: 600;
    }

    .cust-table-wrap {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .cust-table-wrap::-webkit-scrollbar { width: 7px; height: 7px; }
    .cust-table-wrap::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .cust-table-wrap::-webkit-scrollbar-track { background: transparent; }

    .cust-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
        font-size: 0.84rem;
    }

    .cust-table thead tr {
        background-color: var(--color-crema);
        border-bottom: 1px solid var(--color-linea);
    }

    .cust-table th {
        position: sticky;
        top: 0;
        z-index: 5;
        padding: 0.65rem 0.95rem;
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
        padding: 0.65rem 0.95rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .cust-table tbody tr:last-child td { border-bottom: 0; }
    .cust-table tbody tr:hover { background-color: #FBF9F5; }
    .cust-table .text-right { text-align: right; }

    .cust-profile {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .cust-avatar {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--color-noche);
        color: var(--color-hueso);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
        letter-spacing: 0.02em;
    }

    .cust-name {
        font-weight: 600;
        color: var(--color-noche);
        text-decoration: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }

    .cust-name:hover {
        color: var(--color-dorado);
    }

    .cust-address {
        margin-top: 0.1rem;
        font-size: 0.72rem;
        color: var(--color-piedra);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cust-doc-badge {
        display: inline-block;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        background: #F1EFEA;
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-right: 0.35rem;
    }

    .cust-contact-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--color-noche);
        text-decoration: none;
        font-size: 0.8rem;
    }

    .cust-contact-link:hover {
        color: var(--color-dorado);
    }

    .cust-contact-link svg {
        color: var(--color-piedra);
        flex-shrink: 0;
    }

    .cust-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.3rem;
    }

    .cust-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: transparent;
        color: var(--color-piedra);
        text-decoration: none;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }

    .cust-icon-btn:hover {
        border-color: var(--color-dorado);
        background: #FBF6EA;
        color: var(--color-dorado);
    }

    .cust-icon-btn--dark {
        border-color: var(--color-noche);
        background: var(--color-noche);
        color: var(--color-hueso);
    }

    .cust-icon-btn--dark:hover {
        border-color: var(--color-noche);
        background: #232A38;
        color: var(--color-hueso);
    }

    .cust-empty {
        padding: 3rem 1.5rem;
        text-align: center;
    }

    .cust-empty-icon {
        display: grid;
        place-items: center;
        width: 56px;
        height: 56px;
        margin: 0 auto 0.85rem;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .cust-empty strong {
        display: block;
        font-size: 1rem;
        color: var(--color-noche);
    }

    .cust-empty p {
        margin: 0.25rem 0 1rem;
        font-size: 0.82rem;
        color: var(--color-piedra);
    }

    @media (max-width: 1024px) {
        .cust-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 900px) {
        .cust-table { min-width: 0; }
        .cust-table thead { display: none; }
        .cust-table, .cust-table tbody, .cust-table tr, .cust-table td { display: block; width: 100%; }
        .cust-table tbody tr {
            margin: 0 0 0.55rem;
            padding: 0.75rem 0.85rem;
            border: 1px solid var(--color-linea);
            border-radius: 6px;
            background: var(--color-blanco);
        }
        .cust-table tbody tr:last-child { margin-bottom: 0; }
        .cust-table td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.24rem 0;
            border: 0;
            text-align: right;
        }
        .cust-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: var(--color-piedra);
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .cust-table td.cust-cell-profile,
        .cust-table td.cust-cell-actions {
            display: block;
            padding: 0;
            text-align: left;
        }
        .cust-table td.cust-cell-profile { margin-bottom: 0.45rem; }
        .cust-table td.cust-cell-profile::before,
        .cust-table td.cust-cell-actions::before { display: none; }
        .cust-table td.cust-cell-actions {
            margin-top: 0.55rem;
            padding-top: 0.55rem;
            border-top: 1px solid var(--color-linea);
        }
        .cust-actions { justify-content: flex-start; }
        .cust-table .text-right { text-align: right; }
    }

    @media (max-width: 600px) {
        .cust-stats { grid-template-columns: minmax(0, 1fr); }
        .cust-filter-form { flex-direction: column; align-items: stretch; }
        .cust-filter-actions { justify-content: stretch; }
        .cust-filter-actions .btn { flex: 1; }
    }
</style>

<div class="paginated-page">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Clientes</h1>
            <p class="page-subtitle">Directorio de clientes, datos de contacto e historial de compras en mostrador.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-gold" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Nuevo Cliente</span>
            </a>
        </div>
    </div>

    @include('admin.partials.alerts')

    {{-- Tarjetas estadísticas con iconos al lado derecho --}}
    <div class="cust-stats">
        <div class="cust-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="cust-stat-label">Total de clientes</span>
                <strong class="cust-stat-value">{{ number_format($stats['total'] ?? $customers->total()) }}</strong>
                <span class="cust-stat-hint">Fichas registradas</span>
            </div>
            <span class="cust-stat-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </span>
        </div>

        <div class="cust-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="cust-stat-label">Con compras</span>
                <strong class="cust-stat-value" style="color: var(--color-exito);">{{ number_format($stats['with_sales'] ?? 0) }}</strong>
                <span class="cust-stat-hint">Clientes recurrentes</span>
            </div>
            <span class="cust-stat-icon" aria-hidden="true" style="background: #EDF7EE; color: var(--color-exito);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
            </span>
        </div>

        <div class="cust-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="cust-stat-label">Nuevos este mes</span>
                <strong class="cust-stat-value">{{ number_format($stats['new_this_month'] ?? 0) }}</strong>
                <span class="cust-stat-hint">Registrados en {{ now()->translatedFormat('F') }}</span>
            </div>
            <span class="cust-stat-icon" aria-hidden="true" style="background: rgba(184, 154, 94, 0.15); color: var(--color-dorado);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" y1="8" x2="19" y2="14"></line>
                    <line x1="22" y1="11" x2="16" y2="11"></line>
                </svg>
            </span>
        </div>

        <div class="cust-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="cust-stat-label">Con contacto</span>
                <strong class="cust-stat-value">{{ number_format($stats['with_contact'] ?? 0) }}</strong>
                <span class="cust-stat-hint">Teléfono o correo guardado</span>
            </div>
            <span class="cust-stat-icon" aria-hidden="true" style="background: #E8F0F5; color: var(--color-info);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
            </span>
        </div>
    </div>

    {{-- Barra de búsqueda y filtro --}}
    <div class="cust-filter-card">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="cust-filter-form">
            <div class="cust-search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $searchTerm }}"
                    class="form-control"
                    placeholder="Buscar cliente por nombre, documento o teléfono..."
                    autocomplete="off"
                >
            </div>
            <div class="cust-filter-actions">
                <button type="submit" class="btn btn-dark">Buscar</button>
                @if ($hasSearch)
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">Limpiar filtro</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabla de clientes --}}
    <div class="cust-table-card">
        <div class="cust-table-head">
            <div>
                <h2 class="font-serif">Directorio de Clientes</h2>
                <p>{{ number_format($customers->total()) }} {{ $customers->total() === 1 ? 'cliente encontrado' : 'clientes encontrados' }} · Ordenados por nombre</p>
            </div>
            @if ($hasSearch)
                <span class="cust-filter-chip">Filtro: "{{ $searchTerm }}"</span>
            @endif
        </div>

        <div class="cust-table-wrap">
            <table class="cust-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Correo electrónico</th>
                        <th class="text-right">Compras</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $initials = collect(explode(' ', $customer->full_name))
                                ->filter()
                                ->map(fn ($part) => mb_substr($part, 0, 1))
                                ->take(2)
                                ->implode('');
                            if (empty($initials)) {
                                $initials = 'CL';
                            }
                        @endphp
                        <tr>
                            <td class="cust-cell-profile">
                                <div class="cust-profile">
                                    <span class="cust-avatar" aria-hidden="true">{{ $initials }}</span>
                                    <div style="min-width: 0;">
                                        <a href="{{ route('admin.customers.show', $customer) }}" class="cust-name">
                                            {{ $customer->full_name }}
                                        </a>
                                        @if ($customer->address)
                                            <div class="cust-address" title="{{ $customer->address }}">{{ $customer->address }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td data-label="Documento">
                                @if ($customer->document_type)
                                    <span class="cust-doc-badge">{{ $customer->document_type }}</span>
                                @endif
                                <span class="num" style="font-weight: 500;">{{ $customer->document_number }}</span>
                            </td>
                            <td data-label="Teléfono">
                                @if ($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" class="cust-contact-link num">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                        <span>{{ $customer->phone }}</span>
                                    </a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td data-label="Correo">
                                @if ($customer->email)
                                    <a href="mailto:{{ $customer->email }}" class="cust-contact-link">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                        </svg>
                                        <span>{{ $customer->email }}</span>
                                    </a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td data-label="Compras" class="text-right num">
                                @if ($customer->sales_count > 0)
                                    <span class="badge badge-success">{{ $customer->sales_count }} {{ $customer->sales_count === 1 ? 'venta' : 'ventas' }}</span>
                                @else
                                    <span class="muted" style="font-size: 0.78rem;">Sin ventas</span>
                                @endif
                            </td>
                            <td class="text-right cust-cell-actions">
                                <div class="cust-actions">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="cust-icon-btn" title="Ver ficha de cliente" aria-label="Ver ficha de {{ $customer->full_name }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.customers.edit', $customer) }}" class="cust-icon-btn cust-icon-btn--dark" title="Editar cliente" aria-label="Editar {{ $customer->full_name }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m14 4 6 6L8 22H2v-6L14 4Z"></path>
                                            <path d="m12 6 6 6"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="cust-empty">
                                    <span class="cust-empty-icon" aria-hidden="true">
                                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <line x1="17" y1="8" x2="23" y2="14"></line>
                                            <line x1="23" y1="8" x2="17" y2="14"></line>
                                        </svg>
                                    </span>
                                    <strong>{{ $hasSearch ? 'No se encontraron clientes para tu búsqueda' : 'No hay clientes registrados en esta sucursal' }}</strong>
                                    <p>{{ $hasSearch ? 'Prueba con otros términos de búsqueda o verifica el número de documento.' : 'Registra tu primer cliente para asociar ventas, compras de oro o apartados.' }}</p>
                                    @if ($hasSearch)
                                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">Limpiar búsqueda</a>
                                    @else
                                        <a href="{{ route('admin.customers.create') }}" class="btn btn-gold">Registrar cliente</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($customers->hasPages())
        <div class="paginated-pagination" style="padding: 0.65rem 1.1rem; border: 1px solid var(--color-linea); border-radius: 5px; background: var(--color-blanco);">
            {{ $customers->links('vendor.pagination.admin') }}
        </div>
    @endif
</div>
@endsection
