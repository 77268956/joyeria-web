@extends('layouts.admin')

@section('title', 'Inventario y Catálogo Interno')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Inventario y Catálogo</h1>
        <p class="page-subtitle">Control inmutable por movimientos, piezas y lotes de fundición</p>
    </div>
</div>

<div class="card">
    <div style="text-align: center; padding: 3rem 1rem;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="1.5" style="margin-bottom: 1rem;">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        </svg>
        <h2 class="font-serif" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Catálogo e Inventario</h2>
        <p style="color: var(--color-piedra); font-size: 0.95rem; max-width: 480px; margin: 0 auto 1.5rem;">
            Administración de piezas únicas con SKU y código de barras, fotos con zoom, lotes de materia prima y trazabilidad mediante <code>InventoryService</code>.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-logout" style="display: inline-block;">
            &larr; Volver al Dashboard
        </a>
    </div>
</div>
@endsection
