@extends('layouts.admin')

@section('title', 'Dashboard Principal')

@section('content')
<style>
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .metric-card {
        background-color: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .metric-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .metric-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--color-piedra);
        font-weight: 600;
    }

    .metric-icon {
        color: var(--color-dorado);
    }

    .metric-value {
        font-size: 2.25rem;
        font-weight: 600;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
        line-height: 1.1;
    }

    .metric-footer {
        font-size: 0.78rem;
        color: var(--color-piedra);
        margin-top: 0.5rem;
    }

    .dashboard-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 1024px) {
        .dashboard-row {
            grid-template-columns: 1fr;
        }
    }

    .info-card-title {
        font-size: 1.25rem;
        color: var(--color-noche);
        font-weight: 500;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .quick-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.25rem;
        border-radius: 4px;
        font-size: 0.85rem;
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
</style>

<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Bienvenido, {{ Auth::user()->name }}</h1>
        <p class="page-subtitle">{{ $store->name ?? 'Joyería Web' }} — Resumen General del Día</p>
    </div>

    <div class="quick-actions">
        <a href="{{ route('admin.pos.index') }}" class="btn-action btn-action-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span>Nueva Venta (POS)</span>
        </a>

        <a href="{{ route('admin.cash.index') }}" class="btn-action btn-action-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
            <span>Turno de Caja</span>
        </a>
    </div>
</div>

<!-- Fila superior de 4 tarjetas de métricas -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-header">
            <span class="metric-label">Ventas del Día</span>
            <div class="metric-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
</div>

@if (! empty($lowStock))
    <div class="card" style="border-left: 3px solid var(--color-aviso); margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.75rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-aviso)" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <strong style="color: var(--color-noche);">Alerta de stock bajo</strong>
        </div>
        <ul style="list-style: none; color: var(--color-piedra); font-size: 0.9rem; line-height: 1.7;">
            @foreach ($lowStock as $alert)
                <li>{{ $alert['name'] }}: <strong style="color: var(--color-noche);">{{ $alert['available'] }}</strong> piezas disponibles (umbral {{ $alert['threshold'] }}).</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Métricas financieras del día -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-header">
            <span class="metric-label">Flujo Neto de Caja</span>
            <div class="metric-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

<!-- Gráficos -->
<div class="dashboard-row">
    <div class="card">
        <h2 class="info-card-title font-serif">Ventas de los últimos 7 días</h2>
        <div style="position: relative; height: 280px;">
            <canvas id="sales-chart"></canvas>
        </div>
    </div>

    <div class="card">
        <h2 class="info-card-title font-serif">Inventario por metal (gramos)</h2>
        <div style="position: relative; height: 280px;">
            <canvas id="metals-chart"></canvas>
        </div>
    </div>
</div>

<!-- Filas de Información Operativa -->
<div class="dashboard-row">
    <!-- Panel Operativo / Acciones rápidas -->
    <div class="card">
        <h2 class="info-card-title font-serif">Estado del Sistema</h2>

        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
            <!-- Store info -->
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--color-crema); border-radius: 4px; border: 1px solid var(--color-linea);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <div>
                    <div style="font-weight: 600; font-size: 0.88rem;">{{ $store->name }}</div>
                    <div style="font-size: 0.78rem; color: var(--color-piedra);">{{ $store->address ?? 'Sin dirección registrada' }} &bull; Moneda: {{ $store->currency }}</div>
                </div>
            </div>

            <!-- Role info -->
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--color-crema); border-radius: 4px; border: 1px solid var(--color-linea);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <div>
                    <div style="font-weight: 600; font-size: 0.88rem;">{{ $user->name }}</div>
                    <div style="font-size: 0.78rem; color: var(--color-piedra);">Rol: {{ $user->roles->first()?->name ?? 'Sin rol asignado' }} &bull; Último acceso: {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Primer ingreso' }}</div>
                </div>
            </div>
        </div>

        <p style="color: var(--color-piedra); font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.25rem;">
            Para realizar cobros o registrar compras de metales, debes contar con un <strong>turno de caja abierto</strong>.
        </p>

        <div class="quick-actions">
            @can('sales.create')
                <a href="{{ route('admin.pos.index') }}" class="btn-action btn-action-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    Nueva Venta (POS)
                </a>
            @endcan
            @can('cash.open')
                <a href="{{ route('admin.cash.index') }}" class="btn-action btn-action-secondary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                    Turno de Caja
                </a>
            @endcan
            @can('inventory.view')
                <a href="{{ route('admin.inventory.index') }}" class="btn-action btn-action-secondary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    Inventario
                </a>
            @endcan
        </div>
    </div>

    <!-- Actividad Reciente del Sistema -->
    <div class="card">
        <h2 class="info-card-title font-serif">Actividad Reciente</h2>

        @forelse ($recentActivity as $activity)
            <div style="display: flex; gap: 0.75rem; padding: 0.65rem 0; border-bottom: 1px solid var(--color-linea);">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: rgba(184,154,94,0.12); border: 1px solid var(--color-dorado-claro); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span style="font-size: 0.7rem; font-weight: bold; color: var(--color-dorado);">
                        {{ strtoupper(substr($activity->causer?->name ?? 'S', 0, 1)) }}
                    </span>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 0.82rem; color: var(--color-noche); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $activity->description }}
                    </div>
                    <div style="font-size: 0.72rem; color: var(--color-piedra); margin-top: 0.15rem;">
                        {{ $activity->causer?->name ?? 'Sistema' }} &bull; {{ $activity->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @empty
            <p style="color: var(--color-piedra); font-size: 0.88rem; font-style: italic;">Sin actividad registrada todavía.</p>
        @endforelse

        @hasanyrole('Dueño|Gerente')
            <a href="{{ route('admin.audit.index') }}" style="color: var(--color-dorado); font-size: 0.82rem; text-decoration: none; font-weight: 500; display: block; margin-top: 0.75rem;">
                Ver bitácora completa &rarr;
            </a>
        @endhasanyrole
    </div>
</div>

@push('scripts')
    <script>
        window.dashboardData = {
            sales: @json($salesChart),
            metals: @json($inventoryChart),
        };
    </script>
    @vite('resources/js/dashboard.js')
@endpush
@endsection
