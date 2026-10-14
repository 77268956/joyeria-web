@extends('layouts.admin')

@section('title', 'Usuarios y Permisos')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Usuarios y Permisos</h1>
        <p class="page-subtitle">Administración de colaboradores, asignación de roles y estados de cuenta</p>
    </div>
</div>

<div class="card">
    <div style="text-align: center; padding: 3rem 1rem;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="1.5" style="margin-bottom: 1rem;">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="8.5" cy="7" r="4"></circle>
            <line x1="20" y1="8" x2="20" y2="14"></line>
            <line x1="23" y1="11" x2="17" y2="11"></line>
        </svg>
        <h2 class="font-serif" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Gestión de Personal</h2>
        <p style="color: var(--color-piedra); font-size: 0.95rem; max-width: 480px; margin: 0 auto 1.5rem;">
            Control de usuarios con roles Dueño, Gerente y Vendedor, desactivación segura sin borrado físico y matriz de permisos Spatie.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-logout" style="display: inline-block;">
            &larr; Volver al Dashboard
        </a>
    </div>
</div>
@endsection
