{{--
 * Plantilla reutilizable para páginas "en construcción" del panel.
 * Variables requeridas:
 *   $moduleTitle   - Nombre del módulo, ej. "Punto de Venta"
 *   $moduleSubtitle - Descripción breve del módulo
 *   $moduleIcon    - SVG path del ícono (solo el interior del <svg>)
 *   $phase         - "Fase X" - indica cuándo estará disponible
 *   $phaseItems    - array de strings describiendo qué incluirá la fase
--}}
@extends('layouts.admin')

@section('title', $moduleTitle)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">{{ $moduleTitle }}</h1>
        <p class="page-subtitle">{{ $moduleSubtitle }}</p>
    </div>
</div>

<div class="card" style="text-align: center; padding: 3.5rem 2rem;">
    <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: rgba(184, 154, 94, 0.1); border: 1px solid var(--color-dorado-claro); margin-bottom: 1.5rem;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="1.5">
            {!! $moduleIcon !!}
        </svg>
    </div>

    <h2 class="font-serif" style="font-size: 1.75rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--color-noche);">
        {{ $moduleTitle }}
    </h2>
    <p style="color: var(--color-piedra); font-size: 0.95rem; max-width: 500px; margin: 0 auto 2rem;">
        {{ $moduleSubtitle }}
    </p>

    <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(183, 121, 31, 0.08); border: 1px solid rgba(183, 121, 31, 0.2); color: var(--color-aviso); border-radius: 4px; padding: 0.5rem 1rem; font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        En desarrollo &mdash; {{ $phase }}
    </div>

    @if (!empty($phaseItems))
        <div style="display: flex; flex-direction: column; gap: 0.5rem; max-width: 420px; margin: 0 auto 2rem; text-align: left;">
            @foreach ($phaseItems as $item)
                <div style="display: flex; align-items: flex-start; gap: 0.65rem; font-size: 0.88rem; color: var(--color-piedra);">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="2" style="flex-shrink: 0; margin-top: 1px;">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span>{{ $item }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <a href="{{ route('admin.dashboard') }}"
       style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-piedra); font-size: 0.85rem; text-decoration: none; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0.5rem 1rem; transition: border-color 0.2s;">
        &larr; Volver al Dashboard
    </a>
</div>
@endsection
