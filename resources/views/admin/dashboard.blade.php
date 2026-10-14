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

    .status-pill {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-available {
        background-color: #EDF7EE;
        color: var(--color-exito);
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
        <div class="metric-value">$0.00</div>
        <div class="metric-footer">{{ $salesCount }} transacciones registradas</div>
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
        <div class="metric-value">$0.00</div>
        <div class="metric-footer">0.000 g adquiridos hoy</div>
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
            <span class="metric-label">Clientes</span>
            <div class="metric-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>
        <div class="metric-value">{{ $customersCount }}</div>
        <div class="metric-footer">Clientes registrados en ficha</div>
    </div>
</div>

<!-- Filas de Información Operativa -->
<div class="dashboard-row">
    <!-- Panel Operativo -->
    <div class="card">
        <h2 class="info-card-title font-serif">Estado de Turno y Caja Actual</h2>
        <p style="color: var(--color-piedra); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
            Para realizar cobros en el punto de venta o compras de metales a clientes, debes contar con un turno de caja abierto con su respectivo fondo inicial.
        </p>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <a href="{{ route('admin.cash.index') }}" class="btn-action btn-action-primary">
                Gestionar Turno de Caja
            </a>
        </div>
    </div>

    <!-- Panel de Cotización del Día -->
    <div class="card">
        <h2 class="info-card-title font-serif">Cotización de Metales</h2>
        <div style="margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--color-linea);">
                <span style="color: var(--color-piedra); font-size: 0.85rem;">Oro Puro 24k</span>
                <strong style="color: var(--color-noche);">$74.50 / g</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--color-linea);">
                <span style="color: var(--color-piedra); font-size: 0.85rem;">Oro 18k (0.7500)</span>
                <strong style="color: var(--color-noche);">$55.88 / g</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--color-linea);">
                <span style="color: var(--color-piedra); font-size: 0.85rem;">Plata Ley 925</span>
                <strong style="color: var(--color-noche);">$0.85 / g</strong>
            </div>
        </div>
        <a href="{{ route('admin.prices.index') }}" style="color: var(--color-dorado); font-size: 0.82rem; text-decoration: none; font-weight: 500;">
            Ver historial y detalles &rarr;
        </a>
    </div>
</div>
@endsection
