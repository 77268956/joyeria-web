@extends('layouts.admin')

@section('title', 'Detalle de Lote')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Lote {{ $lot->code }}</h1>
        <p class="page-subtitle">{{ $lot->metal?->name }} &middot; {{ $lot->purity?->label }} &middot; Peso actual: <strong>{{ $lot->current_weight_g }} g</strong></p>
    </div>
    <div class="page-actions">
        @can('inventory.adjust')
            @if ($lot->isOpen())
                <button type="button" class="btn btn-outline" onclick="document.getElementById('adjustModal').style.display='flex'">Ajustar peso</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('meltModal').style.display='flex'">Enviar a fundición</button>
            @endif
        @endcan
        <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="card">
    <table class="data-table">
        <tbody>
            <tr><td class="muted" style="width: 30%;">Código</td><td>{{ $lot->code }}</td></tr>
            <tr><td class="muted">Metal</td><td>{{ $lot->metal?->name }}</td></tr>
            <tr><td class="muted">Pureza</td><td>{{ $lot->purity?->label }}</td></tr>
            <tr><td class="muted">Peso inicial</td><td class="num">{{ $lot->initial_weight_g }} g</td></tr>
            <tr><td class="muted">Peso actual</td><td class="num" style="font-weight: 600;">{{ $lot->current_weight_g }} g</td></tr>
            <tr><td class="muted">Ubicación</td><td>{{ $lot->location?->name ?? '—' }}</td></tr>
            <tr><td class="muted">Estado</td><td><span class="badge badge-gold">{{ $lot->status?->label() }}</span></td></tr>
            @if ($lot->notes)
                <tr><td class="muted">Notas</td><td>{{ $lot->notes }}</td></tr>
            @endif
        </tbody>
    </table>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-linea);">
        <h2 class="font-serif" style="font-size: 1.25rem;">Historial de movimientos de peso</h2>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Movimiento</th>
                    <th class="text-right">Peso</th>
                    <th>Motivo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lot->movements as $movement)
                    <tr>
                        <td class="muted">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $movement->type->label() }}</td>
                        <td class="text-right num">{{ $movement->weight_delta_g }} g</td>
                        <td class="muted">{{ $movement->reason ?? '—' }}</td>
                        <td class="muted">{{ $movement->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; padding: 2.5rem;" class="muted">Sin movimientos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('inventory.adjust')
    @if ($lot->isOpen())
        <div id="adjustModal" class="modal-backdrop">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="font-serif" style="font-size: 1.2rem;">Ajustar peso del lote</h3>
                    <button type="button" class="modal-close" onclick="document.getElementById('adjustModal').style.display='none'">&#10005;</button>
                </div>
                <form action="{{ route('admin.inventory.lots.adjust', $lot) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Variación de peso (g) *</label>
                            <input type="number" step="0.001" name="weight_delta_g" class="form-control" required placeholder="Positivo suma, negativo resta. Ej. -12.500">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Motivo *</label>
                            <textarea name="reason" rows="3" class="form-control" required minlength="5" placeholder="Ej. Consumo parcial en taller"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="document.getElementById('adjustModal').style.display='none'">Cancelar</button>
                        <button type="submit" class="btn btn-gold">Registrar ajuste</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="meltModal" class="modal-backdrop">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="font-serif" style="font-size: 1.2rem;">Enviar lote a fundición</h3>
                    <button type="button" class="modal-close" onclick="document.getElementById('meltModal').style.display='none'">&#10005;</button>
                </div>
                <form action="{{ route('admin.inventory.lots.melt', $lot) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="muted" style="margin-bottom: 1rem;">El peso restante ({{ $lot->current_weight_g }} g) saldrá del lote y quedará registrado como envío a fundición.</p>
                        <label class="form-label">Motivo *</label>
                        <textarea name="reason" rows="3" class="form-control" required minlength="5" placeholder="Ej. Traslado a fundición externa"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="document.getElementById('meltModal').style.display='none'">Cancelar</button>
                        <button type="submit" class="btn btn-gold">Confirmar envío</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan
@endsection
