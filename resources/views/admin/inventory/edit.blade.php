@extends('layouts.admin')

@section('title', 'Editar Pieza')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Editar Pieza</h1>
        <p class="page-subtitle">{{ $product->name }} &middot; {{ $product->sku }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline">Ver detalle</a>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="card">
    <form action="{{ route('admin.inventory.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.inventory._form')
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--color-linea); padding-top: 1.25rem;">
            <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-gold">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
