@extends('layouts.admin')

@section('title', 'Gestión de Clientes')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Clientes y Proveedores de Metal</h1>
        <p class="page-subtitle">Fichas de identificación, historial comercial y compras asociadas</p>
    </div>
</div>

<div class="card">
    <div style="text-align: center; padding: 3rem 1rem;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="1.5" style="margin-bottom: 1rem;">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
        </svg>
        <h2 class="font-serif" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Fichas de Clientes</h2>
        <p style="color: var(--color-piedra); font-size: 0.95rem; max-width: 480px; margin: 0 auto 1.5rem;">
            Registro seguro de documentos de identidad con almacenamiento protegido en disco privado, historial de compras, apartados y ventas.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-logout" style="display: inline-block;">
            &larr; Volver al Dashboard
        </a>
    </div>
</div>
@endsection
