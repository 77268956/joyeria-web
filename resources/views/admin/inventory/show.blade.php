@extends('layouts.admin')

@section('title', 'Detalle de Pieza')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">{{ $product->name }}</h1>
        <p class="page-subtitle">{{ $product->sku }} &middot; Stock actual: <strong>{{ $stock }}</strong></p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.label', $product) }}" target="_blank" class="btn btn-outline">Etiqueta</a>
        @can('inventory.adjust')
            <a href="{{ route('admin.inventory.adjust', $product) }}" class="btn btn-outline">Ajustar existencias</a>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('meltModal').style.display='flex'">Enviar a fundición</button>
            <a href="{{ route('admin.inventory.edit', $product) }}" class="btn btn-gold">Editar</a>
        @endcan
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <div class="card">
        <h2 class="font-serif" style="font-size: 1.25rem; margin-bottom: 1rem;">Ficha técnica</h2>
        <table class="data-table">
            <tbody>
                <tr><td class="muted" style="width: 40%;">Categoría</td><td>{{ $product->category?->name ?? '—' }}</td></tr>
                <tr><td class="muted">Metal</td><td>{{ $product->metal?->name ?? '—' }}</td></tr>
                <tr><td class="muted">Pureza</td><td>{{ $product->purity?->label ?? 'No aplica' }}</td></tr>
                <tr><td class="muted">Peso</td><td class="num">{{ $product->weight_g }} g</td></tr>
                <tr><td class="muted">Costo (interno)</td><td class="num">$ {{ number_format((float) $product->cost, 2) }}</td></tr>
                <tr><td class="muted">Precio de venta</td><td class="num">$ {{ number_format((float) $product->price, 2) }}</td></tr>
                <tr><td class="muted">Ubicación</td><td>{{ $product->location?->name ?? '—' }}</td></tr>
                <tr><td class="muted">Estado</td><td><span class="badge badge-gold">{{ $product->status?->label() }}</span></td></tr>
                <tr><td class="muted">Origen</td><td>{{ $product->origin?->label() }}</td></tr>
                <tr><td class="muted">Código de barras</td><td class="num">{{ $product->barcode }}</td></tr>
                <tr><td class="muted">Sitio público</td><td>{{ $product->is_public ? 'Visible' : 'Oculta' }}{{ $product->is_featured ? ' · Destacada' : '' }}</td></tr>
            </tbody>
        </table>

        @if ($product->description)
            <p class="muted" style="margin-top: 1rem; line-height: 1.6;">{{ $product->description }}</p>
        @endif
    </div>

    <div class="card">
        <h2 class="font-serif" style="font-size: 1.25rem; margin-bottom: 1rem;">Fotografías</h2>
        @if ($product->photos->isEmpty())
            <p class="muted">Sin fotografías registradas.</p>
        @else
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem;">
                @foreach ($product->photos as $photo)
                    <img src="{{ asset('storage/'.$photo->path) }}" alt="{{ $product->name }}" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-linea);">
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-linea);">
        <h2 class="font-serif" style="font-size: 1.25rem;">Historial de movimientos</h2>
        <p class="muted" style="font-size: 0.85rem;">Registro inmutable que explica el estado de la pieza.</p>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Movimiento</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Peso</th>
                    <th>Motivo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($product->movements as $movement)
                    <tr>
                        <td class="muted">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $movement->type->label() }}</td>
                        <td class="text-right num">{{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }}</td>
                        <td class="text-right num">{{ $movement->weight_delta_g }} g</td>
                        <td class="muted">{{ $movement->reason ?? '—' }}</td>
                        <td class="muted">{{ $movement->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; padding: 2.5rem;" class="muted">Sin movimientos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('inventory.adjust')
    <div id="meltModal" class="modal-backdrop">
        <div class="modal">
            <div class="modal-header">
                <h3 class="font-serif" style="font-size: 1.2rem;">Enviar a fundición</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('meltModal').style.display='none'">&#10005;</button>
            </div>
            <form action="{{ route('admin.inventory.melt', $product) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="muted" style="margin-bottom: 1rem;">La pieza saldrá del stock disponible y quedará registrada como enviada a fundición.</p>
                    <label class="form-label">Motivo *</label>
                    <textarea name="reason" rows="3" class="form-control" required minlength="5" placeholder="Ej. Pieza dañada sin reparación posible"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('meltModal').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn btn-gold">Confirmar envío</button>
                </div>
            </form>
        </div>
    </div>
@endcan
@endsection
