@extends('layouts.admin')

@section('title', 'Nueva Pieza')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Nueva Pieza</h1>
        <p class="page-subtitle">Registra una pieza en el catálogo interno; su entrada de inventario se genera automáticamente</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="card">
    <form action="{{ route('admin.inventory.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.inventory._form')
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--color-linea); padding-top: 1.25rem;">
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-gold">Registrar Pieza</button>
        </div>
    </form>
</div>
@endsection
