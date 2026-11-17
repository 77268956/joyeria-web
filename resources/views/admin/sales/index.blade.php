@extends('layouts.admin')

@section('title', 'Ventas')

@section('body_class', 'full-viewport')

@section('content')
@php
    $filters = request()->only(['search', 'status', 'date']);
    $scopeUrl = function (string $value) use ($filters) {
        $params = $value === '' ? $filters : array_merge($filters, ['scope' => $value]);

        return route('admin.sales.index', array_filter($params, fn ($item) => $item !== null && $item !== ''));
    };
    $clearUrl = route('admin.sales.index', $scope !== '' ? ['scope' => $scope] : []);
    $hasFilters = request()->filled('search') || request()->filled('status') || request()->filled('date');
    $averageTicket = $summary['completed_count'] > 0
        ? bcdiv($summary['total'], (string) $summary['completed_count'], 2)
        : '0.00';
    $scopeLabel = match (true) {
        $scope === 'shift' && $session !== null => 'Ventas del turno',
        $scope === 'today' => 'Ventas de hoy',
        default => 'Ventas registradas',
    };
    $scopeHint = match (true) {
        $scope === 'shift' && $session !== null => 'Turno abierto desde '.$session->opened_at?->format('d/m/Y H:i'),
        $scope === 'today' => now()->format('d/m/Y'),
        default => 'Historial completo',
    };
@endphp

<style>
    .sales-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        flex-shrink: 0;
    }

    .sales-tabs {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background-color: var(--color-blanco);
    }

    .sales-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 30px;
        padding: 0 0.85rem;
        border-radius: 999px;
        color: var(--color-piedra);
        font-size: 0.78rem;
        font-weight: 500;
        text-decoration: none;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .sales-tab:hover {
        color: var(--color-noche);
        background-color: var(--color-crema);
    }

    .sales-tab.is-active {
        background-color: var(--color-noche);
        color: var(--color-hueso);
    }

    .sales-toolbar-note {
        color: var(--color-piedra);
        font-size: 0.78rem;
    }

    .sales-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .sales-toolbar-actions .btn {
        height: 30px;
        padding-top: 0;
        padding-bottom: 0;
    }

    .sales-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .sales-stat {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
    }

    .sales-stat-icon {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        margin-left: auto;
        border-radius: 50%;
        background-color: var(--color-crema);
        color: var(--color-dorado);
        order: 2;
        animation: sales-icon-bob 2.8s ease-in-out infinite;
    }

    .sales-stat:nth-child(2) .sales-stat-icon {
        animation-delay: 0.2s;
    }

    .sales-stat:nth-child(3) .sales-stat-icon {
        animation-delay: 0.4s;
    }

    .sales-stat:nth-child(4) .sales-stat-icon {
        animation-delay: 0.6s;
    }

    @keyframes sales-icon-bob {
        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-2px) scale(1.08);
        }
    }

    .sales-stat-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0;
        min-width: 0;
        order: 1;
    }

    .sales-stat-label {
        color: var(--color-piedra);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .sales-stat-value {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.15;
    }

    .sales-stat-hint {
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.68rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sales-stat--danger .sales-stat-value {
        color: var(--color-error);
    }

    .sales-stat--danger .sales-stat-icon {
        background-color: #FDECEC;
        color: var(--color-error);
    }

    .sales-filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.4fr) minmax(150px, 0.6fr) minmax(150px, 0.6fr) auto;
        gap: 0.65rem;
        align-items: end;
    }

    .sales-filter-field label {
        margin-bottom: 0.25rem;
        font-size: 0.68rem;
    }

    .sales-filter-field .form-control,
    .sales-filter-field .form-select {
        height: 38px;
        font-size: 0.85rem;
    }

    .sales-filter-actions {
        display: flex;
        gap: 0.5rem;
    }

    .sales-filter-actions .btn {
        height: 38px;
    }

    .sales-table td {
        vertical-align: middle;
    }

    .sales-number {
        color: var(--color-noche);
        font-weight: 600;
        text-decoration: none;
    }

    .sales-number:hover {
        color: var(--color-dorado);
    }

    .sales-date {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .sales-customer {
        font-weight: 500;
    }

    .sales-seller {
        color: var(--color-piedra);
        font-size: 0.82rem;
    }

    .sales-total {
        font-weight: 700;
    }

    .sales-row--voided td {
        color: var(--color-piedra);
    }

    .sales-row--voided .sales-total {
        text-decoration: line-through;
    }

    .sales-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.35rem;
    }

    .sales-empty {
        padding: 3rem 1rem !important;
        text-align: center;
    }

    .sales-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        margin: 0 auto 0.6rem;
        border-radius: 50%;
        background-color: var(--color-crema);
        color: var(--color-dorado);
    }

    .sales-empty-title {
        color: var(--color-noche);
        font-weight: 600;
    }

    .sales-empty-hint {
        display: block;
        margin-top: 0.2rem;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    @media (prefers-reduced-motion: reduce) {
        .sales-stat-icon {
            animation: none;
        }
    }

    @media (max-width: 1100px) {
        .sales-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .sales-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sales-filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 640px) {
        .sales-stats,
        .sales-filter-form {
            grid-template-columns: 1fr;
        }

        .sales-toolbar-actions {
            width: 100%;
            justify-content: space-between;
        }

        .sales-filter-actions .btn {
            flex: 1;
        }
    }
</style>

<div class="paginated-page">
    @include('admin.partials.alerts')

    <div class="sales-toolbar">
        <nav class="sales-tabs" aria-label="Rango de ventas">
            @if ($session)
                <a href="{{ $scopeUrl('shift') }}" class="sales-tab {{ $scope === 'shift' ? 'is-active' : '' }}">Turno actual</a>
            @endif
            <a href="{{ $scopeUrl('today') }}" class="sales-tab {{ $scope === 'today' ? 'is-active' : '' }}">Hoy</a>
            <a href="{{ $scopeUrl('') }}" class="sales-tab {{ $scope === '' ? 'is-active' : '' }}">Todas</a>
        </nav>
        <div class="sales-toolbar-actions">
            <span class="sales-toolbar-note">{{ $scopeHint }}</span>
            <a href="{{ route('admin.pos.index') }}" class="btn btn-gold btn-sm">Ir al POS</a>
        </div>
    </div>

    <div class="sales-stats">
        <div class="sales-stat">
            <span class="sales-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"></path>
                    <path d="M9 8h6M9 12h6"></path>
                </svg>
            </span>
            <div class="sales-stat-body">
                <span class="sales-stat-label">{{ $scopeLabel }}</span>
                <strong class="sales-stat-value">{{ number_format($summary['count']) }}</strong>
                <span class="sales-stat-hint">Incluye ventas anuladas</span>
            </div>
        </div>
        <div class="sales-stat">
            <span class="sales-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                    <circle cx="12" cy="12" r="2.5"></circle>
                    <path d="M6.5 12h.01M17.5 12h.01"></path>
                </svg>
            </span>
            <div class="sales-stat-body">
                <span class="sales-stat-label">Total cobrado</span>
                <strong class="sales-stat-value">$ {{ number_format((float) $summary['total'], 2) }}</strong>
                <span class="sales-stat-hint">Solo ventas completadas</span>
            </div>
        </div>
        <div class="sales-stat">
            <span class="sales-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 17l6-6 4 4 7-7"></path>
                    <path d="M14 8h6v6"></path>
                </svg>
            </span>
            <div class="sales-stat-body">
                <span class="sales-stat-label">Ticket promedio</span>
                <strong class="sales-stat-value">$ {{ number_format((float) $averageTicket, 2) }}</strong>
                <span class="sales-stat-hint">Por venta completada</span>
            </div>
        </div>
        <div class="sales-stat {{ $summary['voided_count'] > 0 ? 'sales-stat--danger' : '' }}">
            <span class="sales-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M5.6 18.4L18.4 5.6"></path>
                </svg>
            </span>
            <div class="sales-stat-body">
                <span class="sales-stat-label">Anuladas</span>
                <strong class="sales-stat-value">{{ number_format($summary['voided_count']) }}</strong>
                <span class="sales-stat-hint">No afectan la caja</span>
            </div>
        </div>
    </div>

    <div class="card paginated-filter-card">
        <form action="{{ route('admin.sales.index') }}" method="GET" class="sales-filter-form">
            @if ($scope !== '')
                <input type="hidden" name="scope" value="{{ $scope }}">
            @endif
            <div class="sales-filter-field">
                <label class="form-label" for="sales-search">Número de venta</label>
                <input type="text" id="sales-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ej. VTA-000001">
            </div>
            <div class="sales-filter-field">
                <label class="form-label" for="sales-status">Estado</label>
                <select name="status" id="sales-status" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sales-filter-field">
                <label class="form-label" for="sales-date">Fecha</label>
                <input type="date" id="sales-date" name="date" value="{{ request('date') }}" class="form-control">
            </div>
            <div class="sales-filter-actions">
                <button type="submit" class="btn btn-dark">Filtrar</button>
                @if ($hasFilters)
                    <a href="{{ $clearUrl }}" class="btn btn-outline">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card paginated-table-card">
        <div class="paginated-table-wrap">
            <table class="data-table sales-table">
                <thead>
                    <tr>
                        <th>Venta</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th class="text-right">Piezas</th>
                        <th class="text-right">Total</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr class="{{ $sale->isVoided() ? 'sales-row--voided' : '' }}">
                            <td>
                                <a href="{{ route('admin.sales.show', $sale) }}" class="sales-number">{{ $sale->number }}</a>
                                <span class="sales-date">{{ $sale->created_at?->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <span class="sales-customer">{{ $sale->customer?->full_name ?? 'Consumidor final' }}</span>
                            </td>
                            <td class="sales-seller">{{ $sale->user?->name ?? '—' }}</td>
                            <td class="text-right num">{{ $sale->items_count }}</td>
                            <td class="text-right num sales-total">$ {{ number_format((float) $sale->total, 2) }}</td>
                            <td>
                                <span class="badge {{ $sale->isVoided() ? 'badge-danger' : 'badge-success' }}">{{ $sale->status->label() }}</span>
                            </td>
                            <td class="text-right">
                                <div class="sales-row-actions">
                                    <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline btn-sm">Ver</a>
                                    <a href="{{ route('admin.sales.ticket', $sale) }}" target="_blank" class="btn btn-outline btn-sm">Ticket</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="sales-empty">
                                <span class="sales-empty-icon" aria-hidden="true">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 3h9l4 4v14H6z"></path>
                                        <path d="M15 3v4h4M9 12h6M9 16h6"></path>
                                    </svg>
                                </span>
                                <span class="sales-empty-title">No hay ventas registradas</span>
                                <span class="sales-empty-hint">Prueba con otro rango de fechas o limpia los filtros.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($sales->hasPages())
        <div class="paginated-pagination">
            {{ $sales->links('vendor.pagination.admin') }}
        </div>
    @endif
</div>
@endsection
