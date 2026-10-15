@extends('layouts.admin')

@section('title', 'Ventas')

@section('content')
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

<div class="card" style="padding: 1.25rem 1.75rem;">
    <form action="{{ route('admin.sales.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label">Número</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ej. VTA-000001">
        </div>
        <div style="min-width: 160px;">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select">
                <option value="">Todos</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width: 170px;">
            <label class="form-label">Fecha</label>
            <input type="date" name="date" value="{{ request('date') }}" class="form-control">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-dark">Filtrar</button>
            @if (request()->hasAny(['search', 'status', 'date']))
                <a href="{{ route('admin.sales.index') }}" class="btn btn-outline">Limpiar</a>
            @endif
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrap">
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
                        <td class="num"><a href="{{ route('admin.sales.show', $sale) }}" style="color: var(--color-noche);">{{ $sale->number }}</a></td>
                        <td class="num">{{ $sale->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer?->full_name ?? 'Consumidor final' }}</td>
                        <td class="text-right num">{{ $sale->items_count }}</td>
                        <td class="text-right num">$ {{ number_format((float) $sale->total, 2) }}</td>
                        <td>
                            <span class="badge {{ $sale->isVoided() ? 'badge-danger' : 'badge-success' }}">{{ $sale->status->label() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline btn-sm">Ver</a>
                            <a href="{{ route('admin.sales.ticket', $sale) }}" target="_blank" class="btn btn-outline btn-sm">Ticket</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted" style="text-align: center; padding: 1.5rem;">No hay ventas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1rem;">
    {{ $sales->links() }}
</div>
@endsection
