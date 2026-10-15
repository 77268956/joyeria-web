@extends('layouts.admin')

@section('title', 'Materia Prima')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Materia Prima</h1>
        <p class="page-subtitle">Lotes de metal controlados por peso para fundición</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Inventario</a>
        @can('inventory.adjust')
            <a href="{{ route('admin.inventory.lots.create') }}" class="btn btn-gold">Nuevo Lote</a>
        @endcan
    </div>
</div>

@include('admin.partials.alerts')

<div class="card" style="padding: 1.25rem 1.75rem;">
    <form action="{{ route('admin.inventory.lots.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label">Buscar por código</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ej. LOT-001-00001">
        </div>
        <div style="min-width: 150px;">
            <label class="form-label">Metal</label>
            <select name="metal_id" class="form-select">
                <option value="">Todos</option>
                @foreach ($metals as $metal)
                    <option value="{{ $metal->id }}" {{ (string) request('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                @endforeach
            </select>
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
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-dark">Filtrar</button>
            @if (request()->hasAny(['search', 'metal_id', 'status']))
                <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Limpiar</a>
            @endif
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Metal / Pureza</th>
                    <th class="text-right">Peso inicial</th>
                    <th class="text-right">Peso actual</th>
                    <th>Ubicación</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lots as $lot)
                    <tr>
                        <td style="font-weight: 600;">{{ $lot->code }}</td>
                        <td>{{ $lot->metal?->name }} <span class="muted">({{ $lot->purity?->code }})</span></td>
                        <td class="text-right num">{{ $lot->initial_weight_g }} g</td>
                        <td class="text-right num" style="font-weight: 600;">{{ $lot->current_weight_g }} g</td>
                        <td class="muted">{{ $lot->location?->name ?? '—' }}</td>
                        <td>
                            @php
                                $badge = match ($lot->status) {
                                    \App\Enums\MaterialLotStatus::Open => 'badge-success',
                                    \App\Enums\MaterialLotStatus::SentToMelting => 'badge-gold',
                                    default => 'badge-muted',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $lot->status?->label() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.inventory.lots.show', $lot) }}" class="btn btn-sm btn-outline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align: center; padding: 3rem;" class="muted">Todavía no hay lotes de materia prima registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($lots->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--color-linea);">{{ $lots->links() }}</div>
    @endif
</div>
@endsection
