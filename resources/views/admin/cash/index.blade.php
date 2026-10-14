@extends('layouts.admin')

@section('title', 'Caja y Turnos')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Gestión de Caja</h1>
        <p class="page-subtitle">Apertura, movimientos de efectivo, arqueo y cierre de turnos</p>
    </div>
</div>

<div class="card">
    <div style="text-align: center; padding: 3rem 1rem;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="1.5" style="margin-bottom: 1rem;">
            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
            <line x1="2" y1="10" x2="22" y2="10"></line>
        </svg>
        <h2 class="font-serif" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Control de Turnos de Caja</h2>
        <p style="color: var(--color-piedra); font-size: 0.95rem; max-width: 480px; margin: 0 auto 1.5rem;">
            Este módulo permitirá abrir turno con fondo inicial, registrar ingresos/retiros y realizar el arqueo con justificación obligatoria de descalces.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-logout" style="display: inline-block;">
            &larr; Volver al Dashboard
        </a>
    </div>
</div>
@endsection
