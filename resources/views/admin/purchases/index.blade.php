@extends('layouts.admin')

@section('title', 'Compra de Metales')

@section('body_class', 'full-viewport')

@section('content')
@php
    $activeStatus = (string) request('status', '');
    $statusUrl = function (string $value) {
        $params = array_filter([
            'status' => $value,
            'search' => request('search'),
        ], fn ($item) => $item !== null && $item !== '');

        return route('admin.purchases.index', $params);
    };
    $clearUrl = route('admin.purchases.index', $activeStatus !== '' ? ['status' => $activeStatus] : []);
    $hasFilters = request()->filled('search');
@endphp

<style>
    .purchases-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        flex-shrink: 0;
    }

    .purchases-tabs {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background-color: var(--color-blanco);
    }

    .purchases-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 30px;
        padding: 0 0.8rem;
        border-radius: 999px;
        color: var(--color-piedra);
        font-size: 0.78rem;
        font-weight: 500;
        text-decoration: none;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .purchases-tab:hover {
        color: var(--color-noche);
        background-color: var(--color-crema);
    }

    .purchases-tab.is-active {
        background-color: var(--color-noche);
        color: var(--color-hueso);
    }

    .purchases-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .purchases-toolbar-actions .btn {
        height: 30px;
        padding-top: 0;
        padding-bottom: 0;
    }

    .purchases-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .purchases-stat {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
    }

    .purchases-stat-icon {
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
        animation: purchases-icon-bob 2.8s ease-in-out infinite;
    }

    .purchases-stat:nth-child(2) .purchases-stat-icon {
        animation-delay: 0.2s;
    }

    .purchases-stat:nth-child(3) .purchases-stat-icon {
        animation-delay: 0.4s;
    }

    .purchases-stat:nth-child(4) .purchases-stat-icon {
        animation-delay: 0.6s;
    }

    .purchases-stat--pending .purchases-stat-icon {
        background-color: #FBF3E2;
        color: #9A6B1F;
    }

    @keyframes purchases-icon-bob {
        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-2px) scale(1.08);
        }
    }

    .purchases-stat-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        order: 1;
    }

    .purchases-stat-label {
        color: var(--color-piedra);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .purchases-stat-value {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.15;
    }

    .purchases-stat-hint {
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.68rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .purchases-filter-form {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) auto;
        gap: 0.65rem;
        align-items: end;
    }

    .purchases-filter-field label {
        margin-bottom: 0.25rem;
        font-size: 0.68rem;
    }

    .purchases-filter-field .form-control {
        height: 38px;
        font-size: 0.85rem;
    }

    .purchases-filter-actions {
        display: flex;
        gap: 0.5rem;
    }

    .purchases-filter-actions .btn {
        height: 38px;
    }

    .purchases-table td {
        vertical-align: middle;
    }

    .purchases-number {
        color: var(--color-noche);
        font-weight: 600;
        text-decoration: none;
    }

    .purchases-number:hover {
        color: var(--color-dorado);
    }

    .purchases-date {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .purchases-customer {
        font-weight: 500;
    }

    .purchases-total {
        font-weight: 700;
    }

    .purchases-total-hint {
        display: block;
        color: var(--color-piedra);
        font-size: 0.72rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .purchases-row--rejected td {
        color: var(--color-piedra);
    }

    .purchases-row--rejected .purchases-total {
        text-decoration: line-through;
    }

    .purchases-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.35rem;
    }

    .purchases-empty {
        padding: 3rem 1rem !important;
        text-align: center;
    }

    .purchases-empty-icon {
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

    .purchases-empty-title {
        color: var(--color-noche);
        font-weight: 600;
    }

    .purchases-empty-hint {
        display: block;
        margin-top: 0.2rem;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    @media (prefers-reduced-motion: reduce) {
        .purchases-stat-icon {
            animation: none;
        }
    }

    @media (max-width: 1100px) {
        .purchases-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .purchases-toolbar-actions {
            width: 100%;
            justify-content: space-between;
        }

        .purchases-filter-form {
            grid-template-columns: 1fr;
        }

        .purchases-filter-actions .btn {
            flex: 1;
        }
    }

    @media (max-width: 640px) {
        .purchases-stats {
            grid-template-columns: 1fr;
        }

        .purchases-tabs {
            max-width: 100%;
            overflow-x: auto;
        }
    }
</style>

<div class="paginated-page">
    @include('admin.partials.alerts')

    <div class="purchases-toolbar">
        <nav class="purchases-tabs" aria-label="Estado de las compras">
            <a href="{{ $statusUrl('') }}" class="purchases-tab {{ $activeStatus === '' ? 'is-active' : '' }}">Todas</a>
            <a href="{{ $statusUrl('pending_approval') }}" class="purchases-tab {{ $activeStatus === 'pending_approval' ? 'is-active' : '' }}">Pendientes</a>
            <a href="{{ $statusUrl('paid') }}" class="purchases-tab {{ $activeStatus === 'paid' ? 'is-active' : '' }}">Pagadas</a>
            <a href="{{ $statusUrl('adjusted') }}" class="purchases-tab {{ $activeStatus === 'adjusted' ? 'is-active' : '' }}">Ajustadas</a>
            <a href="{{ $statusUrl('rejected') }}" class="purchases-tab {{ $activeStatus === 'rejected' ? 'is-active' : '' }}">Rechazadas</a>
        </nav>
        <div class="purchases-toolbar-actions">
            @can('purchases.create')
                <a href="{{ route('admin.purchases.create') }}" class="btn btn-dark">Nueva compra</a>
            @endcan
        </div>
    </div>

    <div class="purchases-stats">
        <div class="purchases-stat">
            <span class="purchases-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"></path>
                    <path d="M9 8h6M9 12h6"></path>
                </svg>
            </span>
            <div class="purchases-stat-body">
                <span class="purchases-stat-label">Compras</span>
                <strong class="purchases-stat-value">{{ number_format($summary['count']) }}</strong>
                <span class="purchases-stat-hint">En el filtro actual</span>
            </div>
        </div>
        <div class="purchases-stat">
            <span class="purchases-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                    <circle cx="12" cy="12" r="2.5"></circle>
                    <path d="M6.5 12h.01M17.5 12h.01"></path>
                </svg>
            </span>
            <div class="purchases-stat-body">
                <span class="purchases-stat-label">Total pagado</span>
                <strong class="purchases-stat-value">$ {{ number_format((float) $summary['paid_total'], 2) }}</strong>
                <span class="purchases-stat-hint">Pagadas y ajustadas</span>
            </div>
        </div>
        <div class="purchases-stat purchases-stat--pending">
            <span class="purchases-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v5l3 2"></path>
                </svg>
            </span>
            <div class="purchases-stat-body">
                <span class="purchases-stat-label">Pendientes</span>
                <strong class="purchases-stat-value">{{ number_format($summary['pending_count']) }}</strong>
                <span class="purchases-stat-hint">{{ $summary['pending_count'] > 0 ? 'Esperan aprobación' : 'Sin pendientes' }}</span>
            </div>
        </div>
        <div class="purchases-stat">
            <span class="purchases-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3h12l4 6-10 12L2 9z"></path>
                    <path d="M2 9h20"></path>
                </svg>
            </span>
            <div class="purchases-stat-body">
                <span class="purchases-stat-label">Piezas</span>
                <strong class="purchases-stat-value">{{ number_format($summary['pieces_count']) }}</strong>
                <span class="purchases-stat-hint">Metal recibido</span>
            </div>
        </div>
    </div>

    <div class="card paginated-filter-card">
        <form action="{{ route('admin.purchases.index') }}" method="GET" class="purchases-filter-form">
            @if ($activeStatus !== '')
                <input type="hidden" name="status" value="{{ $activeStatus }}">
            @endif
            <div class="purchases-filter-field">
                <label class="form-label" for="purchases-search">Buscar compra</label>
                <input type="text" id="purchases-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Folio o nombre del cliente">
            </div>
            <div class="purchases-filter-actions">
                <button type="submit" class="btn btn-dark">Buscar</button>
                @if ($hasFilters)
                    <a href="{{ $clearUrl }}" class="btn btn-outline">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card paginated-table-card">
        <div class="paginated-table-wrap">
            <table class="data-table purchases-table">
                <thead>
                    <tr>
                        <th>Compra</th>
                        <th>Cliente</th>
                        <th class="text-right">Piezas</th>
                        <th class="text-right">Total</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        @php
                            $badge = match ($purchase->status) {
                                \App\Enums\MetalPurchaseStatus::Paid, \App\Enums\MetalPurchaseStatus::Adjusted => 'badge-success',
                                \App\Enums\MetalPurchaseStatus::PendingApproval => 'badge-gold',
                                default => 'badge-danger',
                            };
                        @endphp
                        <tr class="{{ $purchase->status === \App\Enums\MetalPurchaseStatus::Rejected ? 'purchases-row--rejected' : '' }}">
                            <td>
                                <a href="{{ route('admin.purchases.show', $purchase) }}" class="purchases-number">{{ $purchase->number }}</a>
                                <span class="purchases-date">{{ $purchase->created_at?->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <span class="purchases-customer">{{ $purchase->customer?->full_name ?? '—' }}</span>
                            </td>
                            <td class="text-right num">{{ $purchase->items_count }}</td>
                            <td class="text-right num purchases-total">
                                $ {{ number_format((float) $purchase->total_amount, 2) }}
                                @if ($purchase->appraised_total !== null && bccomp((string) $purchase->appraised_total, (string) $purchase->total_amount, 2) !== 0)
                                    <span class="purchases-total-hint">Sistema $ {{ number_format((float) $purchase->appraised_total, 2) }}</span>
                                @endif
                            </td>
                            <td>{{ $purchase->payout_method->label() }}</td>
                            <td>
                                <span class="badge {{ $badge }}">{{ $purchase->status->label() }}</span>
                            </td>
                            <td class="text-right">
                                <div class="purchases-row-actions">
                                    <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-outline btn-sm">Ver</a>
                                    <a href="{{ route('admin.purchases.receipt', $purchase) }}" target="_blank" class="btn btn-outline btn-sm">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="purchases-empty">
                                <span class="purchases-empty-icon" aria-hidden="true">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"></path>
                                        <path d="M9 8h6M9 12h6"></path>
                                    </svg>
                                </span>
                                <span class="purchases-empty-title">No hay compras registradas</span>
                                <span class="purchases-empty-hint">Prueba con otro estado o limpia los filtros.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($purchases->hasPages())
        <div class="paginated-pagination">
            {{ $purchases->links('vendor.pagination.admin') }}
        </div>
    @endif
</div>
@endsection
