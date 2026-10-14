@extends('layouts.admin')

@section('title', 'Punto de Venta (POS)')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Punto de Venta (POS)</h1>
        <p class="page-subtitle">Escaneo y venta directa de piezas de joyería</p>
    </div>
</div>

<div class="card">
    <div style="text-align: center; padding: 3rem 1rem;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="1.5" style="margin-bottom: 1rem;">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <h2 class="font-serif" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Módulo de Punto de Venta</h2>
        <p style="color: var(--color-piedra); font-size: 0.95rem; max-width: 480px; margin: 0 auto 1.5rem;">
            El módulo interactivo de cobro, lectura de código de barras y pago mixto se integrará con componentes reactivos Livewire en la Fase 4.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-logout" style="display: inline-block;">
            &larr; Volver al Dashboard
        </a>
    </div>
</div>
@endsection
