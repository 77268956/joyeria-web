@extends('layouts.admin')

@section('title', 'Conteos de Inventario')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Conteos de Inventario</h1>
        <p class="page-subtitle">Auditoría física que concilia las existencias del sistema con las piezas presentes.</p>
    </div>
    <div class="page-actions">
        @can('inventory.adjust')
            <form action="{{ route('admin.stock-counts.store') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-dark" onclick="return confirm('¿Iniciar un nuevo conteo físico con el inventario actual?');">Nuevo conteo</button>
            </form>
        @endcan
    </div>
</div>

@include('admin.partials.alerts')

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Inicio</th>
                    <th>Iniciado por</th>
                    <th class="text-right">Piezas</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($counts as $count)
                    <tr>
                        <td class="num"><a href="{{ route('admin.stock-counts.show', $count) }}" style="color: var(--color-noche);">#{{ $count->id }}</a></td>
                        <td class="num">{{ $count->started_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $count->startedBy?->name ?? '—' }}</td>
                        <td class="text-right num">{{ $count->items_count }}</td>
                        <td>
                            @php
                                $badge = match ($count->status) {
                                    \App\Enums\StockCountStatus::Applied => 'badge-success',
                                    \App\Enums\StockCountStatus::Finished => 'badge-gold',
                                    default => 'badge-danger',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $count->status->label() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.stock-counts.show', $count) }}" class="btn btn-outline btn-sm">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted" style="text-align: center; padding: 1.5rem;">No hay conteos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1rem;">
    {{ $counts->links() }}
</div>
@endsection
