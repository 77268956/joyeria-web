@extends('layouts.admin')

@section('title', 'Compra y Recompra de Metales')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Compra de Metales</h1>
        <p class="page-subtitle">Valuación en 3 pasos con identificación obligatoria y registro fotográfico</p>
    </div>
</div>

<div class="card">
    <div style="text-align: center; padding: 3rem 1rem;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="1.5" style="margin-bottom: 1rem;">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
        </svg>
        <h2 class="font-serif" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Módulo de Recompra de Oro y Plata</h2>
        <p style="color: var(--color-piedra); font-size: 0.95rem; max-width: 480px; margin: 0 auto 1.5rem;">
            Cálculo matemático en tiempo real a través de <code>PricingCalculator</code>, captura directa de balanza vía Web Serial API y comprobante formal de compraventa en PDF.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-logout" style="display: inline-block;">
            &larr; Volver al Dashboard
        </a>
    </div>
</div>
@endsection
