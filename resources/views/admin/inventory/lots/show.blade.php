@extends('layouts.admin')

@section('title', 'Detalle de lote')

@section('body_class', 'full-viewport')

@php
    $initialWeight = (string) $lot->initial_weight_g;
    $currentWeight = (string) $lot->current_weight_g;
    $differenceWeight = bcsub($currentWeight, $initialWeight, 3);
    $remainingPercent = bccomp($initialWeight, '0', 3) > 0
        ? (float) bcmul(bcdiv($currentWeight, $initialWeight, 6), '100', 2)
        : 0.0;
    $remainingPercent = max(0, min(100, $remainingPercent));
    $differencePositive = bccomp($differenceWeight, '0', 3) > 0;
    $differenceNegative = bccomp($differenceWeight, '0', 3) < 0;
    $badge = match ($lot->status) {
        \App\Enums\MaterialLotStatus::Open => 'badge-success',
        \App\Enums\MaterialLotStatus::SentToMelting => 'badge-gold',
        default => 'badge-muted',
    };
@endphp

@section('content')
<style>
    .lot-show-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .lot-show-page .alert {
        margin-bottom: 0;
    }

    .lot-show-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        flex-shrink: 0;
        padding: 1.15rem 1.35rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: linear-gradient(125deg, var(--color-blanco) 35%, rgba(184, 154, 94, 0.08) 100%);
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.03);
    }

    .lot-show-identity {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .lot-show-thumb {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 68px;
        height: 68px;
        flex-shrink: 0;
        border: 1px solid rgba(184, 154, 94, 0.35);
        border-radius: 5px;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .lot-show-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.4rem;
    }

    .lot-show-kicker > span:first-child {
        color: var(--color-dorado);
        font-size: 0.69rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .lot-show-title {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.55rem;
        line-height: 1.15;
        letter-spacing: 0.01em;
    }

    .lot-show-subtitle {
        margin: 0.3rem 0 0;
        color: var(--color-piedra);
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .lot-show-hero-side {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        flex-shrink: 0;
    }

    .lot-show-hero-weight {
        padding-right: 1.2rem;
        border-right: 1px solid var(--color-linea);
        text-align: right;
    }

    .lot-show-hero-weight span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .lot-show-hero-weight strong {
        display: block;
        margin-top: 0.05rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.55rem;
        font-weight: 600;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .lot-show-hero-weight small {
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .lot-show-hero-actions {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .lot-show-hero-actions .btn {
        height: 34px;
    }

    .lot-show-layout {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.4fr);
        align-items: stretch;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .lot-show-main {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .lot-show-main > .card {
        margin-bottom: 0;
    }

    .lot-show-summary {
        flex-shrink: 0;
        padding: 1.25rem;
    }

    .lot-summary-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .lot-weight-overview {
        flex: 1;
        min-width: 240px;
    }

    .lot-weight-label {
        display: block;
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .lot-weight-value {
        display: flex;
        align-items: baseline;
        gap: 0.4rem;
        margin-top: 0.12rem;
    }

    .lot-weight-value strong {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 2rem;
        font-weight: 600;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .lot-weight-value span {
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .lot-weight-bar {
        height: 6px;
        margin-top: 0.7rem;
        overflow: hidden;
        border-radius: 999px;
        background: var(--color-crema);
    }

    .lot-weight-bar span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--color-dorado), #D6BE85);
        transition: width 0.4s ease;
    }

    .lot-weight-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 0.4rem;
        color: var(--color-piedra);
        font-size: 0.71rem;
    }

    .lot-weight-meta strong {
        color: var(--color-noche);
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .lot-weight-meta .is-positive {
        color: var(--color-exito);
    }

    .lot-weight-meta .is-negative {
        color: var(--color-error);
    }

    .lot-summary-facts {
        display: grid;
        grid-template-columns: repeat(2, minmax(120px, 1fr));
        gap: 0.6rem;
        min-width: 260px;
    }

    .lot-summary-fact {
        padding: 0.55rem 0.7rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: #FCFBF8;
    }

    .lot-summary-fact span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .lot-summary-fact strong {
        display: block;
        margin-top: 0.1rem;
        overflow: hidden;
        color: var(--color-noche);
        font-size: 0.82rem;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lot-notes {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        padding: 0.65rem 0.75rem;
        margin-top: 0.9rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: var(--color-crema);
    }

    .lot-notes svg {
        flex-shrink: 0;
        margin-top: 0.1rem;
        color: var(--color-dorado);
    }

    .lot-notes span {
        color: var(--color-noche);
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .lot-movements-card {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-height: 0;
        padding: 0 !important;
        overflow: hidden;
    }

    .lot-movements-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-shrink: 0;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .lot-movements-head h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.05rem;
    }

    .lot-movements-head p {
        margin: 0.1rem 0 0;
        color: var(--color-piedra);
        font-size: 0.71rem;
    }

    .lot-movements-scroll {
        flex: 1;
        min-height: 0;
        overflow: auto;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .lot-movements-scroll::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .lot-movements-scroll::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #D9D2C3;
    }

    .lot-movements-table {
        width: 100%;
        min-width: 660px;
        border-collapse: collapse;
        font-size: 0.8rem;
    }

    .lot-movements-table thead tr {
        background: var(--color-tabla-header);
    }

    .lot-movements-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 0.55rem 0.8rem;
        background: var(--color-tabla-header);
        box-shadow: inset 0 -1px 0 var(--color-linea);
        color: var(--color-noche);
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .lot-movements-table td {
        padding: 0.6rem 0.8rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .lot-movements-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .lot-movements-table tbody tr:hover {
        background: #FBF9F5;
    }

    .lot-movements-date {
        color: var(--color-piedra);
        font-size: 0.72rem;
        white-space: nowrap;
    }

    .lot-movement-delta {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .lot-movement-delta.is-positive {
        color: var(--color-exito);
    }

    .lot-movement-delta.is-negative {
        color: var(--color-error);
    }

    .lot-movement-delta.is-zero {
        color: var(--color-piedra);
    }

    .lot-movements-reason {
        max-width: 260px;
        overflow: hidden;
        color: var(--color-piedra);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lot-movements-empty {
        padding: 2.25rem 1rem;
        text-align: center;
    }

    .lot-movements-empty-icon {
        display: grid;
        place-items: center;
        width: 48px;
        height: 48px;
        margin: 0 auto 0.65rem;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .lot-movements-empty strong {
        display: block;
        color: var(--color-noche);
        font-size: 0.9rem;
    }

    .lot-movements-empty p {
        margin: 0.2rem 0 0;
        color: var(--color-piedra);
        font-size: 0.78rem;
    }

    .lot-show-aside {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .lot-show-aside::-webkit-scrollbar {
        width: 8px;
    }

    .lot-show-aside::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(18, 22, 31, 0.16);
    }

    .lot-show-aside::-webkit-scrollbar-track {
        background: transparent;
    }

    .lot-show-aside .card {
        padding: 1.2rem;
        margin-bottom: 0;
    }

    .lot-aside-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .lot-aside-head h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1rem;
    }

    .lot-action-stack {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .lot-action-stack .btn {
        width: 100%;
        height: 36px;
        justify-content: center;
    }

    .lot-aside-note {
        margin: 0.75rem 0 0;
        color: var(--color-piedra);
        font-size: 0.72rem;
        line-height: 1.5;
    }

    .lot-trace-list {
        display: flex;
        flex-direction: column;
        gap: 0;
        margin: 0;
    }

    .lot-trace-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.48rem 0;
        border-bottom: 1px dashed var(--color-linea);
    }

    .lot-trace-row:last-child {
        border-bottom: 0;
    }

    .lot-trace-row dt {
        flex-shrink: 0;
        color: var(--color-piedra);
        font-size: 0.71rem;
    }

    .lot-trace-row dd {
        margin: 0;
        color: var(--color-noche);
        font-size: 0.77rem;
        font-weight: 600;
        text-align: right;
    }

    .lot-trace-note {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.65rem 0.7rem;
        margin-top: 0.75rem;
        border-radius: 4px;
        background: var(--color-crema);
        color: var(--color-piedra);
        font-size: 0.71rem;
        line-height: 1.45;
    }

    .lot-trace-note svg {
        flex-shrink: 0;
        margin-top: 0.08rem;
        color: var(--color-dorado);
    }

    .lot-show-aside .is-positive {
        color: var(--color-exito);
    }

    .lot-show-aside .is-negative {
        color: var(--color-error);
    }

    .lot-reason-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin: -0.25rem 0 0.9rem;
    }

    .lot-reason-chip {
        padding: 0.25rem 0.55rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background: var(--color-blanco);
        color: var(--color-piedra);
        font-family: var(--font-sans);
        font-size: 0.7rem;
        cursor: pointer;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }

    .lot-reason-chip:hover {
        border-color: var(--color-dorado);
        background: #FBF6EA;
        color: var(--color-dorado);
    }

    .lot-modal-current {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.65rem 0.75rem;
        margin-bottom: 1rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: var(--color-crema);
    }

    .lot-modal-current span {
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .lot-modal-current strong {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.2rem;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .lot-modal-warning {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.7rem 0.75rem;
        margin-bottom: 1rem;
        border-left: 3px solid var(--color-dorado);
        border-radius: 3px;
        background: #FBF6EA;
        color: var(--color-noche);
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .lot-modal-warning svg {
        flex-shrink: 0;
        margin-top: 0.1rem;
        color: var(--color-dorado);
    }

    @media (max-width: 1180px) {
        body.full-viewport {
            overflow: auto;
        }

        .full-viewport .main-wrapper {
            height: auto;
            overflow: visible;
        }

        .full-viewport .content-area {
            display: block;
            overflow: visible;
        }

        .lot-show-page,
        .lot-show-layout,
        .lot-show-main {
            overflow: visible;
        }

        .lot-show-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .lot-show-hero-side {
            width: 100%;
            justify-content: space-between;
            padding-top: 0.9rem;
            border-top: 1px solid var(--color-linea);
        }

        .lot-show-hero-weight {
            text-align: left;
        }

        .lot-show-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .lot-show-aside {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            overflow: visible;
        }

        .lot-movements-card {
            min-height: 320px;
        }
    }

    @media (max-width: 740px) {
        .lot-show-identity {
            align-items: flex-start;
        }

        .lot-summary-top {
            flex-direction: column;
            gap: 1rem;
        }

        .lot-summary-facts {
            width: 100%;
            min-width: 0;
        }

        .lot-show-hero-side,
        .lot-show-hero-actions {
            width: 100%;
        }

        .lot-show-hero-side {
            align-items: flex-start;
            flex-direction: column;
        }

        .lot-show-hero-weight {
            padding-right: 0;
            padding-bottom: 0.8rem;
            border-right: 0;
            border-bottom: 1px solid var(--color-linea);
        }

        .lot-show-hero-actions .btn {
            flex: 1;
        }

        .lot-weight-meta {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.25rem;
        }

        .lot-show-aside {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<div class="lot-show-page">
    <header class="lot-show-hero">
        <div class="lot-show-identity">
            <div class="lot-show-thumb">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                    <path d="m12 3 9 5-9 5-9-5 9-5Z"></path>
                    <path d="m3 12 9 5 9-5"></path>
                    <path d="m3 16 9 5 9-5"></path>
                </svg>
            </div>

            <div style="min-width: 0;">
                <div class="lot-show-kicker">
                    <span>Materia prima · Lote</span>
                    <span class="badge {{ $badge }}">{{ $lot->status?->label() ?? 'Sin estado' }}</span>
                    <span class="badge badge-gold">{{ $lot->metal?->name ?? 'Sin metal' }}</span>
                </div>
                <h1 class="lot-show-title">{{ $lot->code }}</h1>
                <p class="lot-show-subtitle">
                    {{ $lot->purity?->label ?? 'Sin pureza' }} · Registrado el {{ $lot->created_at?->format('d/m/Y \a \l\a\s H:i') }}
                </p>
            </div>
        </div>

        <div class="lot-show-hero-side">
            <div class="lot-show-hero-weight">
                <span>Peso actual</span>
                <strong>{{ number_format((float) $lot->current_weight_g, 3) }} g</strong>
                <small>{{ number_format($remainingPercent, 1) }}% del peso inicial</small>
            </div>

            <div class="lot-show-hero-actions">
                @can('inventory.adjust')
                    @if ($lot->isOpen())
                        <button type="button" class="btn btn-outline" data-modal-open="adjustModal">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 6h10M18 6h2M4 12h2M10 12h10M4 18h10M18 18h2"></path>
                                <circle cx="16" cy="6" r="2"></circle>
                                <circle cx="8" cy="12" r="2"></circle>
                                <circle cx="16" cy="18" r="2"></circle>
                            </svg>
                            Ajustar peso
                        </button>
                        <button type="button" class="btn btn-outline" data-modal-open="meltModal">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3c2.5 3.5 1 5.5 0 7-1-1.5-2.5-3.5 0-7Z"></path>
                                <path d="M7 14a5 5 0 0 0 10 0c0-2-1.5-3.4-2.5-4.5C13.5 11 12 12 12 12s-1.5-1-2.5-2.5C8.5 10.6 7 12 7 14Z"></path>
                                <path d="M5 21h14"></path>
                            </svg>
                            Fundir
                        </button>
                    @endif
                @endcan
                <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Volver a lotes</a>
            </div>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="lot-show-layout">
        <div class="lot-show-main">
            <section class="card lot-show-summary">
                <div class="lot-summary-top">
                    <div class="lot-weight-overview">
                        <span class="lot-weight-label">Peso disponible</span>
                        <div class="lot-weight-value">
                            <strong>{{ number_format((float) $lot->current_weight_g, 3) }}</strong>
                            <span>gramos</span>
                        </div>
                        <div class="lot-weight-bar" role="img" aria-label="{{ number_format($remainingPercent, 1) }}% del peso inicial disponible">
                            <span style="width: {{ number_format($remainingPercent, 2, '.', '') }}%;"></span>
                        </div>
                        <div class="lot-weight-meta">
                            <span>Inicial: <strong>{{ number_format((float) $lot->initial_weight_g, 3) }} g</strong></span>
                            <span>
                                Ajuste neto:
                                <strong class="{{ $differencePositive ? 'is-positive' : ($differenceNegative ? 'is-negative' : '') }}">
                                    {{ $differencePositive ? '+' : '' }}{{ number_format((float) $differenceWeight, 3) }} g
                                </strong>
                            </span>
                        </div>
                    </div>

                    <div class="lot-summary-facts">
                        <div class="lot-summary-fact">
                            <span>Metal</span>
                            <strong>{{ $lot->metal?->name ?? 'Sin metal' }}</strong>
                        </div>
                        <div class="lot-summary-fact">
                            <span>Pureza</span>
                            <strong>{{ $lot->purity?->code ?? 'Sin pureza' }}</strong>
                        </div>
                        <div class="lot-summary-fact">
                            <span>Ubicación</span>
                            <strong>{{ $lot->location?->name ?? 'Sin ubicación' }}</strong>
                        </div>
                        <div class="lot-summary-fact">
                            <span>Estado</span>
                            <strong>{{ $lot->status?->label() ?? 'Sin estado' }}</strong>
                        </div>
                    </div>
                </div>

                @if ($lot->notes)
                    <div class="lot-notes">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 3h9l4 4v14H6z"></path>
                            <path d="M9 12h6M9 16h6"></path>
                        </svg>
                        <span>{{ $lot->notes }}</span>
                    </div>
                @endif
            </section>

            <section class="card lot-movements-card">
                <div class="lot-movements-head">
                    <div>
                        <h2>Historial de movimientos de peso</h2>
                        <p>{{ number_format($lot->movements->count()) }} {{ $lot->movements->count() === 1 ? 'movimiento registrado' : 'movimientos registrados' }} · No se pueden editar ni borrar</p>
                    </div>
                    <span class="badge badge-gold">Auditado</span>
                </div>

                <div class="lot-movements-scroll">
                    <table class="lot-movements-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Movimiento</th>
                                <th class="text-right">Peso</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lot->movements as $movement)
                                @php
                                    $delta = (string) $movement->weight_delta_g;
                                    $deltaComparison = bccomp($delta, '0', 3);
                                    $deltaClass = $deltaComparison > 0 ? 'is-positive' : ($deltaComparison < 0 ? 'is-negative' : 'is-zero');
                                    $deltaLabel = $deltaComparison > 0
                                        ? '+'.number_format((float) $delta, 3)
                                        : number_format((float) $delta, 3);
                                    $movementBadge = match ($movement->type) {
                                        \App\Enums\InventoryMovementType::Entry,
                                        \App\Enums\InventoryMovementType::PurchaseEntry => 'badge-success',
                                        \App\Enums\InventoryMovementType::Adjustment => 'badge-gold',
                                        \App\Enums\InventoryMovementType::ToMelting => 'badge-danger',
                                        default => 'badge-muted',
                                    };
                                    $movementLabel = match ($movement->type) {
                                        \App\Enums\InventoryMovementType::Entry => 'Entrada',
                                        \App\Enums\InventoryMovementType::PurchaseEntry => 'Entrada por compra',
                                        \App\Enums\InventoryMovementType::Adjustment => 'Ajuste',
                                        \App\Enums\InventoryMovementType::ToMelting => 'Envío a fundición',
                                        default => $movement->type->label(),
                                    };
                                @endphp
                                <tr>
                                    <td class="lot-movements-date">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge {{ $movementBadge }}">{{ $movementLabel }}</span></td>
                                    <td class="text-right lot-movement-delta {{ $deltaClass }}">{{ $deltaLabel }} g</td>
                                    <td class="lot-movements-reason" title="{{ $movement->reason ?? 'Sin motivo' }}">{{ $movement->reason ?? '—' }}</td>
                                    <td class="muted">{{ $movement->user?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="lot-movements-empty">
                                            <span class="lot-movements-empty-icon" aria-hidden="true">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 4h16v16H4z"></path>
                                                    <path d="M8 9h8M8 13h8M8 17h5"></path>
                                                </svg>
                                            </span>
                                            <strong>Sin movimientos registrados</strong>
                                            <p>El historial aparecerá cuando se registre peso en este lote.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="lot-show-aside">
            @can('inventory.adjust')
                <section class="card">
                    <div class="lot-aside-head">
                        <h2>Acciones del lote</h2>
                    </div>

                    @if ($lot->isOpen())
                        <div class="lot-action-stack">
                            <button type="button" class="btn btn-gold" data-modal-open="adjustModal">Ajustar peso</button>
                            <button type="button" class="btn btn-outline" data-modal-open="meltModal">Enviar a fundición</button>
                        </div>
                        <p class="lot-aside-note">Ambas acciones requieren un motivo y quedan registradas en el historial.</p>
                    @else
                        <p class="lot-aside-note" style="margin-top: 0;">Este lote ya no admite cambios de peso porque fue enviado a fundición.</p>
                    @endif
                </section>
            @endcan

            <section class="card">
                <div class="lot-aside-head">
                    <h2>Trazabilidad</h2>
                    <span class="badge badge-success">Activa</span>
                </div>

                <dl class="lot-trace-list">
                    <div class="lot-trace-row">
                        <dt>Movimientos</dt>
                        <dd>{{ number_format($lot->movements->count()) }}</dd>
                    </div>
                    <div class="lot-trace-row">
                        <dt>Último cambio</dt>
                        <dd>{{ $lot->movements->first()?->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                    <div class="lot-trace-row">
                        <dt>Responsable</dt>
                        <dd>{{ $lot->movements->first()?->user?->name ?? '—' }}</dd>
                    </div>
                    <div class="lot-trace-row">
                        <dt>Código</dt>
                        <dd>{{ $lot->code }}</dd>
                    </div>
                </dl>

                <div class="lot-trace-note">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3 4 6v6c0 4.5 3.4 7.7 8 9 4.6-1.3 8-4.5 8-9V6l-8-3Z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                    <span>Cada gramo que entra o sale queda auditado con usuario, fecha y motivo.</span>
                </div>
            </section>

            <section class="card">
                <div class="lot-aside-head">
                    <h2>Resumen</h2>
                </div>

                <dl class="lot-trace-list">
                    <div class="lot-trace-row">
                        <dt>Peso inicial</dt>
                        <dd>{{ number_format((float) $lot->initial_weight_g, 3) }} g</dd>
                    </div>
                    <div class="lot-trace-row">
                        <dt>Peso actual</dt>
                        <dd>{{ number_format((float) $lot->current_weight_g, 3) }} g</dd>
                    </div>
                    <div class="lot-trace-row">
                        <dt>Diferencia</dt>
                        <dd class="{{ $differencePositive ? 'is-positive' : ($differenceNegative ? 'is-negative' : '') }}">
                            {{ $differencePositive ? '+' : '' }}{{ number_format((float) $differenceWeight, 3) }} g
                        </dd>
                    </div>
                    <div class="lot-trace-row">
                        <dt>Disponible</dt>
                        <dd>{{ number_format($remainingPercent, 1) }}%</dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
</div>

@can('inventory.adjust')
    @if ($lot->isOpen())
        <div id="adjustModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="adjustModalTitle">
            <div class="modal">
                <div class="modal-header">
                    <h3 id="adjustModalTitle" class="font-serif" style="font-size: 1.2rem;">Ajustar peso del lote</h3>
                    <button type="button" class="modal-close" data-modal-close aria-label="Cerrar">&#10005;</button>
                </div>
                <form action="{{ route('admin.inventory.lots.adjust', $lot) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="lot-modal-current">
                            <span>Peso actual del lote</span>
                            <strong>{{ number_format((float) $lot->current_weight_g, 3) }} g</strong>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="weight_delta_g">Variación de peso (g) *</label>
                            <input type="number" step="0.001" name="weight_delta_g" id="weight_delta_g" class="form-control" required placeholder="Positivo suma, negativo resta. Ej. -12.500">
                        </div>

                        <div class="form-group" style="margin-bottom: 0.35rem;">
                            <label class="form-label" for="adjustReason">Motivo *</label>
                            <textarea name="reason" id="adjustReason" rows="3" class="form-control" required minlength="5" placeholder="Ej. Consumo parcial en taller"></textarea>
                        </div>

                        <div class="lot-reason-chips">
                            <button type="button" class="lot-reason-chip" data-reason-target="adjustReason" data-reason="Consumo parcial en taller">Consumo en taller</button>
                            <button type="button" class="lot-reason-chip" data-reason-target="adjustReason" data-reason="Merma por manipulación">Merma</button>
                            <button type="button" class="lot-reason-chip" data-reason-target="adjustReason" data-reason="Corrección de pesaje">Corrección de pesaje</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                        <button type="submit" class="btn btn-gold">Registrar ajuste</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="meltModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="meltModalTitle">
            <div class="modal">
                <div class="modal-header">
                    <h3 id="meltModalTitle" class="font-serif" style="font-size: 1.2rem;">Enviar lote a fundición</h3>
                    <button type="button" class="modal-close" data-modal-close aria-label="Cerrar">&#10005;</button>
                </div>
                <form action="{{ route('admin.inventory.lots.melt', $lot) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="lot-modal-warning">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                                <path d="M10.3 3.7 2.5 17.3A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.7L13.7 3.7a2 2 0 0 0-3.4 0Z"></path>
                            </svg>
                            <span>El peso restante de <strong>{{ number_format((float) $lot->current_weight_g, 3) }} g</strong> saldrá del lote y quedará registrado como envío a fundición. Esta acción no se puede deshacer.</span>
                        </div>

                        <div class="form-group" style="margin-bottom: 0.35rem;">
                            <label class="form-label" for="meltReason">Motivo *</label>
                            <textarea name="reason" id="meltReason" rows="3" class="form-control" required minlength="5" placeholder="Ej. Traslado a fundición externa"></textarea>
                        </div>

                        <div class="lot-reason-chips">
                            <button type="button" class="lot-reason-chip" data-reason-target="meltReason" data-reason="Traslado a fundición externa">Fundición externa</button>
                            <button type="button" class="lot-reason-chip" data-reason-target="meltReason" data-reason="Material sin uso en taller">Material sin uso</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-modal-close>Cancelar</button>
                        <button type="submit" class="btn btn-gold">Confirmar envío</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan
@endsection

@push('scripts')
<script>
    (() => {
        const openModal = (id) => {
            const modal = document.getElementById(id);
            if (!modal) {
                return;
            }

            modal.style.display = 'flex';
            requestAnimationFrame(() => modal.classList.add('is-active'));
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('is-closing');
            window.setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('is-active', 'is-closing');
            }, 280);
        };

        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => openModal(button.dataset.modalOpen));
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => closeModal(button.closest('.modal-backdrop')));
        });

        document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
            backdrop.addEventListener('click', (event) => {
                if (event.target === backdrop) {
                    closeModal(backdrop);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('.modal-backdrop').forEach((modal) => {
                if (getComputedStyle(modal).display !== 'none') {
                    closeModal(modal);
                }
            });
        });

        document.querySelectorAll('[data-reason-target]').forEach((chip) => {
            chip.addEventListener('click', () => {
                const target = document.getElementById(chip.dataset.reasonTarget);
                if (!target) {
                    return;
                }

                target.value = chip.dataset.reason;
                target.focus();
            });
        });

        document.querySelectorAll('.modal form').forEach((form) => {
            form.addEventListener('submit', () => {
                const submit = form.querySelector('button[type="submit"]');
                if (submit) {
                    submit.disabled = true;
                    submit.textContent = 'Registrando…';
                }
            });
        });
    })();
</script>
@endpush
