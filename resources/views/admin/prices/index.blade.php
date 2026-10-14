@extends('layouts.admin')

@section('title', 'Precios del Metal')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Precio del Metal</h1>
        <p class="page-subtitle">Cotización internacional diaria, márgenes de compra y ajuste manual auditado</p>
    </div>
</div>

<div class="card">
    <div style="text-align: center; padding: 3rem 1rem;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="1.5" style="margin-bottom: 1rem;">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
        <h2 class="font-serif" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Cotización y Fijación de Precios</h2>
        <p style="color: var(--color-piedra); font-size: 0.95rem; max-width: 480px; margin: 0 auto 1.5rem;">
            Consulta automática diaria mediante API externa (GoldAPI/Metals.dev) con fallback al último precio y sobreescritura manual auditada.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-logout" style="display: inline-block;">
            &larr; Volver al Dashboard
        </a>
    </div>
</div>
@endsection
