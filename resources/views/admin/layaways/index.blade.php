@extends('layouts.admin')

@section('title', 'Apartados')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Apartados</h1>
        <p class="page-subtitle">Piezas reservadas con abonos parciales hasta su liquidación y entrega.</p>
    </div>
    <div class="page-actions">
        @can('layaways.manage')
            <a href="{{ route('admin.layaways.create') }}" class="btn btn-dark">Nuevo apartado</a>
        @endcan
    </div>
</div>

@include('admin.partials.alerts')

<div class="card">
    <form action="{{ route('admin.layaways.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 220px; margin: 0;">
            <label class="form-label" for="search">Buscar</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Folio del apartado">
        </div>
        <div class="form-group" style="min-width: 200px; margin: 0;">
            <label class="form-label" for="status">Estado</label>
            <select name="status" id="status" class="form-select">
                <option value="">Todos</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-outline">Filtrar</button>
            @if (request()->filled('search') || request()->filled('status'))
                <a href="{{ route('admin.layaways.index') }}" class="btn btn-outline">Limpiar</a>
            @endif
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Apertura</th>
                    <th>Cliente</th>
                    <th>Pieza</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Abonado</th>
                    <th class="text-right">Saldo</th>
                    <th>Vence</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($layaways as $layaway)
                    <tr>
                        <td class="num"><a href="{{ route('admin.layaways.show', $layaway) }}" style="color: var(--color-noche);">{{ $layaway->number }}</a></td>
                        <td class="num">{{ $layaway->created_at?->format('d/m/Y') }}</td>
                        <td>{{ $layaway->customer?->full_name ?? '—' }}</td>
                        <td>{{ $layaway->product?->name ?? '—' }}</td>
                        <td class="text-right num">$ {{ number_format((float) $layaway->total_price, 2) }}</td>
                        <td class="text-right num">$ {{ number_format((float) $layaway->paid_total, 2) }}</td>
                        <td class="text-right num">$ {{ number_format((float) $layaway->balance, 2) }}</td>
                        <td class="num">{{ $layaway->due_date?->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $badge = match ($layaway->status) {
                                    \App\Enums\LayawayStatus::Completed => 'badge-success',
                                    \App\Enums\LayawayStatus::Active => 'badge-gold',
                                    default => 'badge-danger',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $layaway->status->label() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.layaways.show', $layaway) }}" class="btn btn-outline btn-sm">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="muted" style="text-align: center; padding: 1.5rem;">No hay apartados registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1rem;">
    {{ $layaways->links() }}
</div>
@endsection
