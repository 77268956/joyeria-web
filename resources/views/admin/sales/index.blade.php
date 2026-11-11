@extends('layouts.admin')

@section('title', 'Ventas')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .sales-page {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        gap: 0.85rem;
    }

    .sales-page .page-header {
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .sales-page .sales-filter-card {
        padding: 0.85rem 1.25rem;
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .sales-page .sales-table-card {
        padding: 0;
        margin-bottom: 0;
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .sales-page .sales-table-wrap {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: auto;
    }

    .sales-page .data-table {
        margin-bottom: 0;
    }

    .sales-page .data-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: var(--color-crema);
        box-shadow: inset 0 -1px 0 var(--color-linea);
    }

    .sales-page .sales-pagination {
        flex-shrink: 0;
        margin-top: 0;
    }

    @media (max-width: 1024px) {
        .sales-page {
            display: block;
        }

        .sales-page .sales-table-card {
            max-height: 60vh;
            margin-bottom: 1rem;
        }
    }
</style>

<div class="sales-page">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Ventas</h1>
            <p class="page-subtitle">Historial de ventas del punto de cobro</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.pos.index') }}" class="btn btn-gold">Ir al POS</a>
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="card sales-filter-card">
        <form action="{{ route('admin.sales.index') }}" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 180px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Número</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ej. VTA-000001" style="height: 36px; font-size: 0.85rem;">
            </div>
            <div style="min-width: 150px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Estado</label>
                <select name="status" class="form-select" style="height: 36px; font-size: 0.85rem;">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 150px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Fecha</label>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control" style="height: 36px; font-size: 0.85rem;">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-dark" style="height: 36px;">Filtrar</button>
                @if (request()->hasAny(['search', 'status', 'date']))
                    <a href="{{ route('admin.sales.index') }}" class="btn btn-outline" style="height: 36px; display: inline-flex; align-items: center;">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card sales-table-card">
        <div class="sales-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th class="text-right">Piezas</th>
                        <th class="text-right">Total</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="num"><a href="{{ route('admin.sales.show', $sale) }}" style="color: var(--color-noche); font-weight: 600;">{{ $sale->number }}</a></td>
                            <td class="num">{{ $sale->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->customer?->full_name ?? 'Consumidor final' }}</td>
                            <td class="text-right num">{{ $sale->items_count }}</td>
                            <td class="text-right num" style="font-weight: 600;">$ {{ number_format((float) $sale->total, 2) }}</td>
                            <td>
                                <span class="badge {{ $sale->isVoided() ? 'badge-danger' : 'badge-success' }}">{{ $sale->status->label() }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline btn-sm">Ver</a>
                                <a href="{{ route('admin.sales.ticket', $sale) }}" target="_blank" class="btn btn-outline btn-sm">Ticket</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted" style="text-align: center; padding: 2rem;">No hay ventas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="sales-pagination">
        {{ $sales->links() }}
    </div>
</div>
@endsection
