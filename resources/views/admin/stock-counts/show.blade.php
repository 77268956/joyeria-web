@extends('layouts.admin')

@section('title', 'Conteo #'.$count->id)

@section('body_class', 'full-viewport')

@php
    $badge = match ($count->status) {
        \App\Enums\StockCountStatus::Applied => 'badge-success',
        \App\Enums\StockCountStatus::Finished => 'badge-gold',
        default => 'badge-danger',
    };
    $statusNote = match ($count->status) {
        \App\Enums\StockCountStatus::Open => 'Marca cada pieza como presente o faltante y guarda el avance.',
        \App\Enums\StockCountStatus::Finished => 'Conteo finalizado. Revisa las diferencias y aplícalo para ajustar el inventario.',
        \App\Enums\StockCountStatus::Applied => 'Conteo aplicado. Las diferencias ya ajustaron el inventario.',
    };
    $progressPct = $stats['expected'] > 0
        ? min(100, round(($stats['counted'] / $stats['expected']) * 100))
        : 0;
@endphp

@section('content')
<style>
    .sc-show {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
        gap: 0.6rem;
    }

    .sc-show .alert {
        margin-bottom: 0;
        padding: 0.5rem 0.85rem;
        flex-shrink: 0;
    }

    .sc-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(240px, auto);
        gap: 0.85rem;
        align-items: center;
        padding: 0.75rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
        flex-shrink: 0;
    }

    .sc-hero-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.25rem;
    }

    .sc-hero-title {
        margin: 0;
        font-family: var(--font-serif);
        font-size: clamp(1.35rem, 1.8vw, 1.75rem);
        font-weight: 500;
        line-height: 1.15;
        color: var(--color-noche);
    }

    .sc-hero-lede {
        margin: 0.25rem 0 0;
        max-width: 62ch;
        font-size: 0.82rem;
        line-height: 1.4;
        color: var(--color-piedra);
    }

    .sc-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.85rem;
        margin-top: 0.45rem;
        font-size: 0.74rem;
        color: var(--color-piedra);
    }

    .sc-hero-meta span { display: inline-flex; align-items: center; gap: 0.3rem; }
    .sc-hero-meta strong { color: var(--color-noche); font-weight: 600; }

    .sc-hero-side {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        min-width: 0;
        padding-left: 1.1rem;
        border-left: 1px solid var(--color-linea);
    }

    .sc-hero-progress-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--color-piedra);
    }

    .sc-hero-progress-value {
        display: block;
        margin-top: 0.1rem;
        font-family: var(--font-serif);
        font-size: 1.5rem;
        font-weight: 500;
        line-height: 1.1;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .sc-hero-progress-bar {
        height: 5px;
        margin-top: 0.3rem;
        background: var(--color-linea);
        border-radius: 999px;
        overflow: hidden;
    }

    .sc-hero-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: var(--color-dorado);
        transition: width 0.3s ease;
    }

    .sc-hero-progress-fill.is-complete { background: var(--color-exito); }

    .sc-hero-progress-hint {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.72rem;
        color: var(--color-piedra);
    }

    .sc-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .sc-hero-actions .btn { height: 32px; padding: 0 0.8rem; font-size: 0.78rem; }

    .sc-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .sc-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
        min-width: 0;
        padding: 0.5rem 0.75rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 5px;
    }

    .sc-stat-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .sc-stat-label {
        display: block;
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--color-piedra);
    }

    .sc-stat-value {
        display: block;
        margin-top: 0.1rem;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .sc-callout {
        position: relative;
        display: flex;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        padding: 0.5rem 0.85rem;
        border: 1px solid rgba(184, 154, 94, 0.4);
        border-left: 4px solid var(--color-dorado);
        border-radius: 4px;
        background: rgba(184, 154, 94, 0.09);
        color: var(--color-noche);
        flex-shrink: 0;
    }

    .sc-callout-copy { display: flex; align-items: center; gap: 0.6rem; min-width: 0; flex: 1; }
    .sc-callout-copy > svg { flex-shrink: 0; color: var(--color-dorado); }
    .sc-callout-copy p { margin: 0; font-size: 0.8rem; line-height: 1.4; color: var(--color-piedra); }
    .sc-callout-copy strong { color: var(--color-noche); }

    .sc-callout.is-success {
        border-color: rgba(62, 124, 89, 0.3);
        border-left-color: var(--color-exito);
        background: rgba(62, 124, 89, 0.06);
    }

    .sc-callout.is-success svg { color: var(--color-exito); }

    .sc-form {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        margin: 0;
    }

    .sc-table-card {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .sc-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.6rem 0.95rem;
        border-bottom: 1px solid var(--color-linea);
        background: var(--color-blanco);
        flex-shrink: 0;
    }

    .sc-table-head h2 { margin: 0; font-size: 0.98rem; font-weight: 500; color: var(--color-noche); }
    .sc-table-head p { margin: 0.1rem 0 0; font-size: 0.72rem; color: var(--color-piedra); }

    .sc-table-head-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .sc-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.18rem 0.5rem;
        border: 1px solid #E4D8BC;
        border-radius: 999px;
        background: #FBF6EA;
        color: #8A6516;
        font-size: 0.66rem;
        font-weight: 600;
    }

    .sc-table-wrap {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .sc-table-wrap::-webkit-scrollbar { width: 7px; height: 7px; }
    .sc-table-wrap::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .sc-table-wrap::-webkit-scrollbar-track { background: transparent; }

    .sc-table {
        width: 100%;
        min-width: 750px;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .sc-table thead tr {
        background-color: var(--color-tabla-header);
        border-bottom: 1px solid var(--color-linea);
    }

    .sc-table th {
        position: sticky;
        top: 0;
        z-index: 5;
        padding: 0.55rem 0.75rem;
        background-color: var(--color-tabla-header);
        box-shadow: inset 0 -1px 0 var(--color-linea);
        color: var(--color-noche);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .sc-table th.text-right { text-align: right; }

    .sc-table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .sc-table tbody tr:last-child td { border-bottom: 0; }
    .sc-table tbody tr:hover { background-color: #FBF9F5; }
    .sc-table .text-right { text-align: right; }

    .sc-table-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.55rem 0.95rem;
        background: #FCFBF8;
        border-top: 1px solid var(--color-linea);
        flex-shrink: 0;
    }

    .sc-product {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 0;
    }

    .sc-product-main { min-width: 0; }

    .sc-product-name {
        font-weight: 600;
        color: var(--color-noche);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sc-product-meta {
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sc-item-dot {
        width: 8px;
        height: 8px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--color-linea);
    }

    .sc-item-dot.is-present { background: var(--color-exito); }
    .sc-item-dot.is-missing { background: var(--color-error); }
    .sc-item-dot.is-surplus { background: var(--color-info); }

    .sc-surplus-card {
        padding: 0.55rem 0.95rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
        flex-shrink: 0;
    }

    .sc-surplus-card h2 {
        margin: 0 0 0.4rem;
        font-family: var(--font-serif);
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .sc-surplus-form {
        display: flex;
        gap: 0.65rem;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .sc-surplus-form .form-group { flex: 1; min-width: 220px; margin: 0; }
    .sc-surplus-form .form-label { margin-bottom: 0.2rem; font-size: 0.68rem; }
    .sc-surplus-form .form-select,
    .sc-surplus-form .form-control { height: 32px; font-size: 0.8rem; }
    .sc-surplus-form .btn { height: 32px; padding: 0 0.85rem; font-size: 0.78rem; }

    @media (max-width: 1100px) {
        .sc-hero { grid-template-columns: minmax(0, 1fr); }
        .sc-hero-side {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 0 0;
            border-top: 1px solid var(--color-linea);
            border-left: 0;
        }
        .sc-hero-actions { justify-content: flex-end; }
    }

    @media (max-width: 900px) {
        body.full-viewport { overflow: auto; }
        .full-viewport .main-wrapper { height: auto; overflow: visible; }
        .full-viewport .content-area { display: block; overflow: visible; }
        .sc-show { overflow: visible; height: auto; }
        .sc-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .sc-table-wrap { max-height: 60vh; }

        .sc-table { min-width: 0; }
        .sc-table thead { display: none; }
        .sc-table, .sc-table tbody, .sc-table tr, .sc-table td { display: block; width: 100%; }
        .sc-table tbody tr {
            margin: 0 0 0.45rem;
            padding: 0.65rem 0.75rem;
            border: 1px solid var(--color-linea);
            border-radius: 6px;
            background: var(--color-blanco);
        }
        .sc-table tbody tr:last-child { margin-bottom: 0; }
        .sc-table td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.22rem 0;
            border: 0;
            text-align: right;
        }
        .sc-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: var(--color-piedra);
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .sc-table td.sc-cell-product { display: block; padding: 0; text-align: left; margin-bottom: 0.35rem; }
        .sc-table td.sc-cell-product::before { display: none; }
        .sc-table .text-right { text-align: right; }
    }

    @media (max-width: 600px) {
        .sc-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sc-hero { padding: 0.75rem; }
        .sc-hero-side { flex-direction: column; align-items: stretch; }
        .sc-hero-actions { justify-content: flex-start; }
        .sc-hero-actions .btn { flex: 1 1 auto; }
        .sc-table-foot { flex-direction: column; align-items: stretch; }
        .sc-table-foot .btn { width: 100%; justify-content: center; }
    }

    .sc-confirm-hero {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .sc-confirm-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .sc-confirm-hero h3 {
        margin: 0;
        color: var(--color-noche);
        font-size: 1rem;
        font-weight: 600;
    }

    .sc-confirm-hero p {
        margin: 0.25rem 0 0;
        color: var(--color-piedra);
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .sc-confirm-list {
        display: grid;
        gap: 0.55rem;
        margin: 1rem 0 0;
        padding: 0.85rem 1rem;
        list-style: none;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-crema);
    }

    .sc-confirm-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        color: var(--color-noche);
        font-size: 0.84rem;
        line-height: 1.4;
    }

    .sc-confirm-list svg {
        flex-shrink: 0;
        margin-top: 0.15rem;
        color: var(--color-dorado);
    }
</style>

<div class="sc-show">
    <header class="sc-hero">
        <div>
            <div class="sc-hero-kicker">
                <span class="badge {{ $badge }}">{{ $count->status->label() }}</span>
            </div>

            <h1 class="sc-hero-title">Conteo físico #{{ $count->id }}</h1>

            <p class="sc-hero-lede">{{ $statusNote }}</p>

            <div class="sc-hero-meta">
                <span><strong>Inicio</strong> {{ $count->started_at?->format('d/m/Y H:i') }}</span>
                <span><strong>Responsable</strong> {{ $count->startedBy?->name ?? '—' }}</span>
                @if ($count->finished_at)
                    <span><strong>Finalizado</strong> {{ $count->finished_at->format('d/m/Y H:i') }} por {{ $count->finishedBy?->name ?? '—' }}</span>
                @endif
                @if ($count->applied_at)
                    <span><strong>Aplicado</strong> {{ $count->applied_at->format('d/m/Y H:i') }} por {{ $count->appliedBy?->name ?? '—' }}</span>
                @endif
            </div>
        </div>

        <div class="sc-hero-side">
            <div>
                <span class="sc-hero-progress-label">Avance</span>
                <strong class="sc-hero-progress-value">{{ $stats['counted'] }} / {{ $stats['expected'] }}</strong>
                <div class="sc-hero-progress-bar">
                    <div class="sc-hero-progress-fill {{ $progressPct === 100 ? 'is-complete' : '' }}" style="width: {{ $progressPct }}%"></div>
                </div>
                <span class="sc-hero-progress-hint">{{ $stats['pending'] }} pendientes · {{ $progressPct }}%</span>
            </div>

            <div class="sc-hero-actions">
                @if ($count->isOpen())
                    @can('inventory.adjust')
                        <form action="{{ route('admin.stock-counts.finish', $count) }}" method="POST" id="form-finish-stock-count" data-supervisor-action="finalizar el conteo">
                            @csrf
                            <button
                                type="button"
                                class="btn btn-dark"
                                data-stock-count-action-open
                                data-form="form-finish-stock-count"
                                data-title="Finalizar conteo #{{ $count->id }}"
                                data-description="Se guardará el avance actual y las piezas quedarán bloqueadas para edición. Después podrás revisar las diferencias y aplicar el conteo."
                                data-confirm-label="Finalizar conteo"
                            >Finalizar conteo</button>
                        </form>
                    @endcan
                @elseif ($count->isFinished())
                    @can('inventory.adjust')
                        <form action="{{ route('admin.stock-counts.apply', $count) }}" method="POST" id="form-apply-stock-count" data-supervisor-action="aplicar el conteo">
                            @csrf
                            <button
                                type="button"
                                class="btn btn-dark"
                                data-stock-count-action-open
                                data-form="form-apply-stock-count"
                                data-title="Aplicar conteo #{{ $count->id }}"
                                data-description="Se ajustará el inventario según las diferencias detectadas. Esta acción no se puede deshacer."
                                data-confirm-label="Aplicar conteo"
                            >Aplicar conteo</button>
                        </form>
                    @endcan
                @endif
                <a href="{{ route('admin.stock-counts.index') }}" class="btn btn-outline">Volver</a>
            </div>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="sc-stats">
        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Esperadas</span>
                <strong class="sc-stat-value">{{ $stats['expected'] }}</strong>
            </div>
            <span class="sc-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                    <rect x="9" y="3" width="6" height="4" rx="1" />
                </svg>
            </span>
        </div>

        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Contadas</span>
                <strong class="sc-stat-value" style="color: var(--color-exito);">{{ $stats['counted'] }}</strong>
            </div>
            <span class="sc-stat-icon" aria-hidden="true" style="background: #EDF7EE; color: var(--color-exito);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 7 9 18l-5-5" />
                </svg>
            </span>
        </div>

        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Sin contar</span>
                <strong class="sc-stat-value">{{ $stats['pending'] }}</strong>
            </div>
            <span class="sc-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
            </span>
        </div>

        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Faltantes</span>
                <strong class="sc-stat-value" style="{{ $stats['missing'] > 0 ? 'color: var(--color-error);' : '' }}">{{ $stats['missing'] }}</strong>
            </div>
            <span class="sc-stat-icon" aria-hidden="true" style="background: #FDEDED; color: var(--color-error);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9" />
                    <path d="m15 9-6 6M9 9l6 6" />
                </svg>
            </span>
        </div>

        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Sobrantes</span>
                <strong class="sc-stat-value" style="{{ $stats['surplus'] > 0 ? 'color: var(--color-info);' : '' }}">{{ $stats['surplus'] }}</strong>
            </div>
            <span class="sc-stat-icon" aria-hidden="true" style="background: #E8F0F5; color: var(--color-info);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v.01M11 12h1v4h1" />
                </svg>
            </span>
        </div>
    </div>

    @if ($count->isApplied())
        <div class="sc-callout is-success">
            <div class="sc-callout-copy">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <path d="M22 4 12 14.01l-3-3" />
                </svg>
                <p><strong>Conteo aplicado</strong> el {{ $count->applied_at?->format('d/m/Y H:i') }} por {{ $count->appliedBy?->name }}. Las diferencias ya ajustaron el inventario.</p>
            </div>
        </div>
    @endif

    @if ($count->notes)
        <div class="sc-callout">
            <div class="sc-callout-copy">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
                    <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" />
                </svg>
                <p><strong>Notas:</strong> {{ $count->notes }}</p>
            </div>
        </div>
    @endif

    @if ($count->isOpen())
        <form action="{{ route('admin.stock-counts.record', $count) }}" method="POST" class="sc-form" data-skip-supervisor>
            @csrf

            <div class="sc-table-card">
                <div class="sc-table-head">
                    <div>
                        <h2 class="font-serif">Piezas del conteo</h2>
                        <p>{{ $count->items->count() }} piezas en total</p>
                    </div>
                    @if ($stats['missing'] > 0 || $stats['surplus'] > 0)
                        <div class="sc-table-head-right">
                            @if ($stats['missing'] > 0)
                                <span class="sc-filter-chip" style="border-color: rgba(166, 61, 64, 0.3); background: #FFF0F0; color: var(--color-error);">{{ $stats['missing'] }} faltante{{ $stats['missing'] > 1 ? 's' : '' }}</span>
                            @endif
                            @if ($stats['surplus'] > 0)
                                <span class="sc-filter-chip" style="border-color: rgba(63, 95, 122, 0.3); background: #E8F0F5; color: var(--color-info);">{{ $stats['surplus'] }} sobrante{{ $stats['surplus'] > 1 ? 's' : '' }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="sc-table-wrap">
                    <table class="sc-table">
                        <thead>
                            <tr>
                                <th>Pieza</th>
                                <th>Metal / Pureza</th>
                                <th class="text-right">Peso</th>
                                <th>Esperada</th>
                                <th style="width: 180px;">Resultado</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($count->items as $index => $item)
                                @php
                                    $dotClass = match (true) {
                                        $item->found === true && $item->expected => 'is-present',
                                        $item->found === false && $item->expected => 'is-missing',
                                        $item->found === true && !$item->expected => 'is-surplus',
                                        default => '',
                                    };
                                @endphp
                                <tr>
                                    <td class="sc-cell-product">
                                        <div class="sc-product">
                                            <span class="sc-item-dot {{ $dotClass }}"></span>
                                            <div class="sc-product-main">
                                                <div class="sc-product-name">{{ $item->product?->name ?? '—' }}</div>
                                                <div class="sc-product-meta">{{ $item->product?->sku ?? '' }}</div>
                                            </div>
                                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                        </div>
                                    </td>
                                    <td data-label="Metal / Pureza">{{ $item->product?->metal?->name }}@if ($item->product?->purity?->code) · {{ $item->product->purity->code }}@endif</td>
                                    <td data-label="Peso" class="text-right num">{{ $item->product?->weight_g }} g</td>
                                    <td data-label="Esperada">
                                        @if ($item->expected)
                                            <span class="badge badge-success" style="font-size: 0.68rem;">Sí</span>
                                        @else
                                            <span class="badge badge-info" style="font-size: 0.68rem;">Sobrante</span>
                                        @endif
                                    </td>
                                    <td data-label="Resultado">
                                        <select name="items[{{ $index }}][found]" class="form-select">
                                            <option value="" {{ $item->found === null ? 'selected' : '' }}>Sin contar</option>
                                            <option value="1" {{ $item->found === true ? 'selected' : '' }}>✓ Presente</option>
                                            <option value="0" {{ $item->found === false ? 'selected' : '' }}>✗ Faltante</option>
                                        </select>
                                    </td>
                                    <td data-label="Nota"><input type="text" name="items[{{ $index }}][note]" class="form-control" value="{{ $item->note }}" maxlength="255" placeholder="Opcional"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="sc-table-foot">
                    <span class="muted" style="font-size: 0.78rem;">Guarda el avance cuantas veces necesites antes de finalizar.</span>
                    <button type="submit" class="btn btn-gold" style="display: inline-flex; align-items: center; gap: 0.4rem; height: 34px; padding: 0 1rem; font-size: 0.82rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" />
                            <path d="M17 21v-8H7v8M7 3v5h8" />
                        </svg>
                        <span>Guardar conteo</span>
                    </button>
                </div>
            </div>
        </form>

        @if ($candidates->isNotEmpty())
            <div class="sc-surplus-card">
                <h2>Registrar sobrante (pieza no esperada)</h2>
                <form action="{{ route('admin.stock-counts.record', $count) }}" method="POST" class="sc-surplus-form" data-skip-supervisor>
                    @csrf
                    <input type="hidden" name="items[0][found]" value="1">
                    <div class="form-group">
                        <label class="form-label" for="surplus_product">Pieza encontrada</label>
                        <select name="items[0][product_id]" id="surplus_product" class="form-select" required>
                            <option value="">Selecciona una pieza</option>
                            @foreach ($candidates as $candidate)
                                <option value="{{ $candidate->id }}">{{ $candidate->name }} · {{ $candidate->sku }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="min-width: 180px;">
                        <label class="form-label" for="surplus_note">Nota</label>
                        <input type="text" name="items[0][note]" id="surplus_note" class="form-control" maxlength="255" placeholder="Opcional">
                    </div>
                    <button type="submit" class="btn btn-outline">Registrar sobrante</button>
                </form>
            </div>
        @endif
    @else
        {{-- Read-only table for finished/applied counts --}}
        <div class="sc-table-card">
            <div class="sc-table-head">
                <div>
                    <h2 class="font-serif">Piezas del conteo</h2>
                    <p>{{ $count->items->count() }} piezas registradas</p>
                </div>
                @if ($stats['missing'] > 0 || $stats['surplus'] > 0)
                    <div class="sc-table-head-right">
                        @if ($stats['missing'] > 0)
                            <span class="sc-filter-chip" style="border-color: rgba(166, 61, 64, 0.3); background: #FFF0F0; color: var(--color-error);">{{ $stats['missing'] }} faltante{{ $stats['missing'] > 1 ? 's' : '' }}</span>
                        @endif
                        @if ($stats['surplus'] > 0)
                            <span class="sc-filter-chip" style="border-color: rgba(63, 95, 122, 0.3); background: #E8F0F5; color: var(--color-info);">{{ $stats['surplus'] }} sobrante{{ $stats['surplus'] > 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="sc-table-wrap">
                <table class="sc-table">
                    <thead>
                        <tr>
                            <th>Pieza</th>
                            <th>Metal / Pureza</th>
                            <th class="text-right">Peso</th>
                            <th>Esperada</th>
                            <th>Encontrada</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($count->items as $item)
                            @php
                                $dotClass = match (true) {
                                    $item->found === true && $item->expected => 'is-present',
                                    $item->found === false && $item->expected => 'is-missing',
                                    $item->found === true && !$item->expected => 'is-surplus',
                                    default => '',
                                };
                            @endphp
                            <tr>
                                <td class="sc-cell-product">
                                    <div class="sc-product">
                                        <span class="sc-item-dot {{ $dotClass }}"></span>
                                        <div class="sc-product-main">
                                            <div class="sc-product-name">{{ $item->product?->name ?? '—' }}</div>
                                            <div class="sc-product-meta">{{ $item->product?->sku ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Metal / Pureza">{{ $item->product?->metal?->name }}@if ($item->product?->purity?->code) · {{ $item->product->purity->code }}@endif</td>
                                <td data-label="Peso" class="text-right num">{{ $item->product?->weight_g }} g</td>
                                <td data-label="Esperada">
                                    @if ($item->expected)
                                        <span class="badge badge-success" style="font-size: 0.68rem;">Sí</span>
                                    @else
                                        <span class="badge badge-info" style="font-size: 0.68rem;">Sobrante</span>
                                    @endif
                                </td>
                                <td data-label="Encontrada">
                                    @if ($item->found === null)
                                        <span class="badge badge-danger">Sin contar</span>
                                    @elseif (! $item->expected && $item->found)
                                        <span class="badge" style="background: rgba(63, 95, 122, 0.12); color: var(--color-info); font-weight: 600;">Sobrante</span>
                                    @elseif ($item->expected && ! $item->found)
                                        <span class="badge badge-danger">Faltante</span>
                                    @else
                                        <span class="badge badge-success">Presente</span>
                                    @endif
                                </td>
                                <td data-label="Nota" class="muted">{{ $item->note ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="muted" style="text-align: center; padding: 1.5rem;">El conteo no tiene piezas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@can('inventory.adjust')
<div class="modal-backdrop" id="stock-count-action-modal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="stock-count-action-title" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <h2 id="stock-count-action-title" style="font-size: 1.05rem; margin: 0;">Confirmar acción</h2>
                <p class="muted" style="font-size: 0.78rem; margin: 0.2rem 0 0;">Conteo físico #{{ $count->id }}</p>
            </div>
            <button type="button" class="modal-close" data-stock-count-action-cancel aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <div class="sc-confirm-hero">
                <span class="sc-confirm-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                        <rect x="9" y="3" width="6" height="4" rx="1" />
                        <path d="m9 14 2 2 4-4" />
                    </svg>
                </span>
                <div>
                    <h3>Revisa antes de continuar</h3>
                    <p id="stock-count-action-description">Confirma la acción para continuar.</p>
                </div>
            </div>

            <ul class="sc-confirm-list">
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span>La acción quedará registrada en la bitácora de auditoría.</span>
                </li>
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span>Si tienes dudas, cancela y revisa el conteo antes de continuar.</span>
                </li>
            </ul>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-stock-count-action-cancel>Cancelar</button>
            <button type="button" class="btn btn-dark" data-stock-count-action-confirm>Continuar</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('stock-count-action-modal');

    if (!modal) {
        return;
    }

    const title = document.getElementById('stock-count-action-title');
    const description = document.getElementById('stock-count-action-description');
    const openButtons = document.querySelectorAll('[data-stock-count-action-open]');
    const cancelButtons = modal.querySelectorAll('[data-stock-count-action-cancel]');
    const confirmButton = modal.querySelector('[data-stock-count-action-confirm]');

    let activeForm = null;
    let submitting = false;
    let closeTimer = null;

    function openModal(button) {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        activeForm = document.getElementById(button.dataset.form);

        if (!activeForm) {
            return;
        }

        title.textContent = button.dataset.title || 'Confirmar acción';
        description.textContent = button.dataset.description || '';
        confirmButton.textContent = button.dataset.confirmLabel || 'Continuar';

        submitting = false;
        modal.style.display = 'flex';
        modal.classList.remove('is-closing');
        modal.classList.add('is-active');

        window.setTimeout(() => confirmButton.focus(), 80);
    }

    function closeModal() {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
        }

        modal.classList.add('is-closing');
        modal.classList.remove('is-active');

        closeTimer = window.setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('is-closing');
            closeTimer = null;
        }, 280);
    }

    openButtons.forEach((button) => {
        button.addEventListener('click', () => openModal(button));
    });

    cancelButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-active')) {
            closeModal();
        }
    });

    confirmButton.addEventListener('click', () => {
        if (submitting || !activeForm) {
            return;
        }

        submitting = true;
        closeModal();

        window.setTimeout(() => {
            activeForm.requestSubmit();
        }, 200);
    });
});
</script>
@endcan
@endsection
