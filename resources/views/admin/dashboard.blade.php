@extends('layouts.admin')

@section('title', 'Dashboard Principal')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .dashboard-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        flex: 1;
        min-height: 0;
    }

    .dashboard-grid .page-header {
        margin-bottom: 0;
    }

    .dashboard-grid .page-title {
        font-size: 1.5rem;
    }

    .dashboard-grid .page-subtitle {
        font-size: 0.82rem;
        margin-top: 0.1rem;
    }

    .dashboard-grid .card {
        padding: 0.85rem 1rem;
        margin-bottom: 0;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .metric-card {
        background-color: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        padding: 0.8rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 0;
        min-width: 0;
    }

    .metric-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.4rem;
    }

    .metric-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-piedra);
        font-weight: 600;
    }

    .metric-icon {
        color: var(--color-dorado);
    }

    .metric-value {
        font-size: 1.45rem;
        font-weight: 600;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
        line-height: 1.1;
    }

    .metric-footer {
        font-size: 0.72rem;
        color: var(--color-piedra);
        margin-top: 0.35rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dashboard-main {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 1rem;
        flex: 1;
        min-height: 0;
    }

    .charts-col,
    .side-col {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        min-width: 0;
    }

    .charts-col .card,
    .side-col .card {
        flex: 1;
        min-height: 0;
    }

    .side-col .card.stock-banner {
        flex: 0 0 auto;
    }

    .chart-wrap {
        position: relative;
        flex: 1;
        min-height: 150px;
    }

    .chart-carousel-card {
        overflow: hidden;
        gap: 0.5rem;
    }

    .chart-carousel-label {
        font-family: var(--font-serif);
        font-size: 0.95rem;
        color: var(--color-noche);
        letter-spacing: 0.01em;
    }

    .carousel-controls {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-shrink: 0;
    }

    .carousel-counter {
        font-size: 0.7rem;
        color: var(--color-piedra);
        font-variant-numeric: tabular-nums;
        margin-right: 0.15rem;
    }

    .carousel-btn {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 1px solid var(--color-linea);
        background: var(--color-blanco);
        color: var(--color-noche);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.2s, background-color 0.2s, color 0.2s;
    }

    .carousel-btn:hover {
        border-color: var(--color-dorado);
        background: var(--color-dorado-claro);
        color: var(--color-noche);
    }

    .carousel-btn:focus-visible {
        outline: 2px solid var(--color-dorado);
        outline-offset: 2px;
    }

    .chart-carousel-viewport {
        flex: 1;
        min-height: 0;
        width: 100%;
        overflow: hidden;
    }

    .chart-carousel-track {
        display: flex;
        height: 100%;
        transition: transform 0.45s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
    }

    .chart-slide {
        flex: 0 0 100%;
        min-width: 0;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    .chart-slide-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
        flex-shrink: 0;
    }

    .chart-slide-title {
        font-size: 1rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .chart-slide-hint {
        font-size: 0.7rem;
        color: var(--color-piedra);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .carousel-dots {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.35rem;
        padding-top: 0.4rem;
        flex-shrink: 0;
    }

    .carousel-dot {
        width: 6px;
        height: 6px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: var(--color-linea);
        cursor: pointer;
        transition: width 0.2s, background-color 0.2s;
    }

    .carousel-dot.active {
        width: 18px;
        border-radius: 3px;
        background: var(--color-dorado);
    }

    .info-card-title {
        font-size: 1.05rem;
        color: var(--color-noche);
        font-weight: 500;
        margin-bottom: 0.6rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--color-linea);
        flex-shrink: 0;
    }

    .card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        padding-bottom: 0.45rem;
        border-bottom: 1px solid var(--color-linea);
        flex-shrink: 0;
    }

    .card-body {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
    }

    .quick-actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .activity-panel .card-body {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        overflow: hidden;
    }

    .quick-actions-row {
        flex-wrap: nowrap;
        gap: 0.45rem;
        overflow-x: auto;
        padding-bottom: 0.15rem;
        flex-shrink: 0;
    }

    .quick-actions-row .btn-action {
        flex: 0 0 auto;
        padding: 0.45rem 0.6rem;
        font-size: 0.72rem;
        white-space: nowrap;
    }

    .activity-list {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-action-primary {
        background-color: var(--color-noche);
        color: var(--color-hueso);
        border: 1px solid var(--color-noche);
    }

    .btn-action-primary:hover {
        background-color: #1e2533;
    }

    .btn-action-secondary {
        background-color: var(--color-blanco);
        color: var(--color-noche);
        border: 1px solid var(--color-linea);
    }

    .btn-action-secondary:hover {
        border-color: var(--color-dorado);
        background-color: var(--color-dorado-claro);
    }

    .dashboard-grid .card.stock-banner {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0.4rem;
        height: 40px;
        padding: 0 0.75rem;
        border-left: 3px solid var(--color-aviso);
        white-space: nowrap;
        overflow: hidden;
        flex: 0 0 40px;
    }

    .stock-banner strong {
        color: var(--color-noche);
        flex-shrink: 0;
        font-size: 0.7rem;
    }

    .stock-banner-list {
        color: var(--color-piedra);
        font-size: 0.68rem;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            display: block;
        }

        .dashboard-grid > * {
            margin-bottom: 1rem;
        }

        .metrics-grid {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        }

        .dashboard-main {
            grid-template-columns: minmax(0, 1fr);
        }

        .charts-col,
        .side-col {
            display: block;
        }

        .charts-col .card,
        .side-col .card {
            margin-bottom: 1rem;
        }

        .chart-wrap {
            height: 240px;
            flex: none;
        }

        .chart-carousel-track {
            height: auto;
        }

        .chart-slide-hint {
            display: none;
        }

        .dashboard-grid .card {
            display: block;
        }

        .card-body {
            overflow: visible;
        }

        .activity-panel .card-body {
            overflow: visible;
        }

        .activity-list {
            overflow: visible;
        }
    }
</style>

<div class="dashboard-grid">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Bienvenido, {{ Auth::user()->name }}</h1>
            <p class="page-subtitle">{{ $store->name ?? 'Joyería Web' }} — Resumen General del Día</p>
        </div>
    </div>

    <!-- Fila única de 6 tarjetas de métricas -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Ventas del Día</span>
                <div class="metric-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
            </div>
            <div class="metric-value">${{ number_format((float) $salesTodayTotal, 2) }}</div>
            <div class="metric-footer">{{ $salesTodayCount }} transacciones hoy</div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Compras Metal</span>
                <div class="metric-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
            </div>
            <div class="metric-value">${{ number_format((float) $purchasesTodayTotal, 2) }}</div>
            <div class="metric-footer">{{ number_format((float) $purchasesTodayGrams, 3) }} g adquiridos hoy</div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Inventario Activo</span>
                <div class="metric-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                </div>
            </div>
            <div class="metric-value">{{ $productsCount }}</div>
            <div class="metric-footer">Piezas registradas en catálogo</div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Colaboradores</span>
                <div class="metric-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>
            <div class="metric-value">{{ $usersCount }}</div>
            <div class="metric-footer">{{ $customersCount }} clientes registrados</div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Flujo Neto de Caja</span>
                <div class="metric-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                </div>
            </div>
            <div class="metric-value" style="color: {{ bccomp($netCashFlow, '0', 2) < 0 ? 'var(--color-error)' : 'var(--color-exito)' }};">${{ number_format((float) $netCashFlow, 2) }}</div>
            <div class="metric-footer">Efectivo cobrado menos egresos de hoy</div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-label">Cotización del Oro</span>
                <div class="metric-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </div>
            </div>
            <div class="metric-value">${{ $goldQuote ? number_format((float) $goldQuote['price_per_gram'], 4) : '—' }}</div>
            <div class="metric-footer">{{ $goldQuote ? 'Por gramo ('.$goldQuote['metal'].') · '.$goldQuote['date'] : 'Sin cotización registrada' }}</div>
        </div>
    </div>

    <!-- Zona principal: gráficas a la izquierda, alerta + paneles a la derecha -->
    <div class="dashboard-main">
        <!-- Columna izquierda: carrusel de gráficas -->
        <div class="charts-col">
            <div class="card chart-carousel-card" data-chart-carousel tabindex="0" aria-roledescription="carrusel" aria-label="Gráficas del negocio">
                <div class="card-head chart-carousel-head">
                    <span class="chart-carousel-label">Gráficas del negocio</span>
                    <div class="carousel-controls">
                        <span class="carousel-counter" data-carousel-counter>1 / 6</span>
                        <button type="button" class="carousel-btn" data-carousel-prev aria-label="Gráfica anterior">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <button type="button" class="carousel-btn" data-carousel-next aria-label="Gráfica siguiente">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="chart-carousel-viewport">
                    <div class="chart-carousel-track" data-carousel-track>
                        <div class="chart-slide" data-chart-slide aria-label="Ventas de los últimos 7 días">
                            <div class="chart-slide-head">
                                <h2 class="chart-slide-title font-serif">Ventas de los últimos 7 días</h2>
                                <span class="chart-slide-hint">Cobrado por día</span>
                            </div>
                            <div class="chart-wrap">
                                <canvas id="sales-chart"></canvas>
                            </div>
                        </div>

                        <div class="chart-slide" data-chart-slide aria-label="Ventas por método de pago">
                            <div class="chart-slide-head">
                                <h2 class="chart-slide-title font-serif">Ventas por método de pago</h2>
                                <span class="chart-slide-hint">Cobros de los últimos 7 días</span>
                            </div>
                            <div class="chart-wrap">
                                <canvas id="payment-methods-chart"></canvas>
                            </div>
                        </div>

                        <div class="chart-slide" data-chart-slide aria-label="Inventario por metal">
                            <div class="chart-slide-head">
                                <h2 class="chart-slide-title font-serif">Inventario por metal (gramos)</h2>
                                <span class="chart-slide-hint">Peso disponible</span>
                            </div>
                            <div class="chart-wrap">
                                <canvas id="metals-chart"></canvas>
                            </div>
                        </div>

                        <div class="chart-slide" data-chart-slide aria-label="Inventario por estado">
                            <div class="chart-slide-head">
                                <h2 class="chart-slide-title font-serif">Inventario por estado</h2>
                                <span class="chart-slide-hint">Piezas registradas</span>
                            </div>
                            <div class="chart-wrap">
                                <canvas id="inventory-status-chart"></canvas>
                            </div>
                        </div>

                        <div class="chart-slide" data-chart-slide aria-label="Compras de metal de los últimos 7 días">
                            <div class="chart-slide-head">
                                <h2 class="chart-slide-title font-serif">Compras de metal (7 días)</h2>
                                <span class="chart-slide-hint">Inversión diaria</span>
                            </div>
                            <div class="chart-wrap">
                                <canvas id="purchases-chart"></canvas>
                            </div>
                        </div>

                        <div class="chart-slide" data-chart-slide aria-label="Flujo de caja de los últimos 7 días">
                            <div class="chart-slide-head">
                                <h2 class="chart-slide-title font-serif">Flujo de caja (7 días)</h2>
                                <span class="chart-slide-hint">Ingresos y retiros manuales</span>
                            </div>
                            <div class="chart-wrap">
                                <canvas id="cash-flow-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-dots" data-carousel-dots aria-label="Seleccionar gráfica"></div>
            </div>
        </div>

        <!-- Columna derecha: alerta + paneles operativos -->
        <div class="side-col">
            @if (! empty($lowStock))
                <div class="card stock-banner">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-aviso)" stroke-width="2" style="flex-shrink: 0;">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <strong>Alerta de stock bajo</strong>
                    <span class="stock-banner-list">
                        @foreach ($lowStock as $alert)
                            {{ $alert['name'] }}: <strong style="color: var(--color-noche);">{{ $alert['available'] }}</strong> (umbral {{ $alert['threshold'] }}){{ $loop->last ? '' : ' · ' }}
                        @endforeach
                    </span>
                </div>
            @endif

            <!-- Acciones rápidas + mini bitácora -->
            <div class="card activity-panel">
                <div class="card-body">
                    <div class="quick-actions quick-actions-row">
                        @can('sales.create')
                            <a href="{{ route('admin.pos.index') }}" class="btn-action btn-action-primary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                Nueva Venta (POS)
                            </a>
                        @endcan
                        @can('cash.open')
                            <a href="{{ route('admin.cash.index') }}" class="btn-action btn-action-secondary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                </svg>
                                Turno de Caja
                            </a>
                        @endcan
                        @can('inventory.view')
                            <a href="{{ route('admin.inventory.index') }}" class="btn-action btn-action-secondary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                                Inventario
                            </a>
                        @endcan
                    </div>

                    <div class="activity-list">
                        @forelse ($recentActivity as $activity)
                            <div style="display: flex; gap: 0.6rem; padding: 0.45rem 0; border-bottom: 1px solid var(--color-linea);">
                                <div style="width: 26px; height: 26px; border-radius: 50%; background: rgba(184,154,94,0.12); border: 1px solid var(--color-dorado-claro); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="font-size: 0.65rem; font-weight: bold; color: var(--color-dorado);">
                                        {{ strtoupper(substr($activity->causer?->name ?? 'S', 0, 1)) }}
                                    </span>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.78rem; color: var(--color-noche); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $activity->description }}
                                    </div>
                                    <div style="font-size: 0.68rem; color: var(--color-piedra); margin-top: 0.1rem;">
                                        {{ $activity->causer?->name ?? 'Sistema' }} &bull; {{ $activity->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p style="color: var(--color-piedra); font-size: 0.82rem; font-style: italic;">Sin actividad registrada todavía.</p>
                        @endforelse

                        @hasanyrole('Dueño|Gerente')
                            <a href="{{ route('admin.audit.index') }}" style="color: var(--color-dorado); font-size: 0.78rem; text-decoration: none; font-weight: 500; display: block; margin-top: 0.5rem;">
                                Ver bitácora completa &rarr;
                            </a>
                        @endhasanyrole
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.dashboardData = {
            sales: @json($salesChart),
            paymentMethods: @json($paymentMethodsChart),
            metals: @json($inventoryChart),
            inventoryStatus: @json($inventoryStatusChart),
            purchases: @json($purchasesChart),
            cashFlow: @json($cashFlowChart),
        };
    </script>
    @vite('resources/js/dashboard.js')
@endpush
@endsection
