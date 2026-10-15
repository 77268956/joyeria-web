@extends('layouts.admin')

@section('title', 'Ajustar Existencias')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Ajustar Existencias</h1>
        <p class="page-subtitle">{{ $product->name }} &middot; {{ $product->sku }} &middot; Stock actual: <strong>{{ $stock }}</strong></p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="card" style="max-width: 640px;">
    <p class="muted" style="margin-bottom: 1.25rem;">
        El ajuste no edita el stock directamente: genera un movimiento inmutable con motivo obligatorio que quedará auditado.
    </p>

    <form action="{{ route('admin.inventory.adjust.store', $product) }}" method="POST">
        @csrf

        <div class="form-group">
            <label class="form-label">Sentido del ajuste *</label>
            <select name="quantity_delta" class="form-select" required>
                <option value="1">+ Alta — la pieza vuelve a estar disponible</option>
                <option value="-1">- Baja — la pieza sale del stock (retirada)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Motivo del ajuste *</label>
            <textarea name="reason" rows="3" class="form-control" required minlength="5" placeholder="Ej. Conteo físico: pieza encontrada en caja fuerte"></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--color-linea); padding-top: 1.25rem;">
            <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-gold">Registrar ajuste</button>
        </div>
    </form>
</div>
@endsection
