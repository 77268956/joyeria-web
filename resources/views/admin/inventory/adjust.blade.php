@extends('layouts.admin')

@section('title', 'Ajustar existencias')

@php
    $P = \App\Enums\ProductStatus::class;
    $photo = $product->photos->firstWhere('is_primary', true) ?? $product->photos->first();
    $selectedDelta = (int) old('quantity_delta', 1) === -1 ? -1 : 1;
    $initialNewStock = $stock + $selectedDelta;
    $stockNoun = $stock === 1 ? 'unidad' : 'unidades';

    $statusBadge = match ($product->status) {
        $P::Available => 'badge-success',
        $P::Reserved => 'badge-gold',
        $P::Sold => 'badge-info',
        $P::Melting => 'badge-danger',
        $P::Withdrawn => 'badge-muted',
        default => 'badge-muted',
    };

    $currentStatus = $product->status?->label() ?? 'Sin estado';
    $initialStatus = $selectedDelta > 0 ? 'Disponible' : 'Retirada';
    $initialStatusBadge = $selectedDelta > 0 ? 'badge-success' : 'badge-muted';

    $purityLabel = $product->purity?->label ?? 'No aplica';
    $locationLabel = $product->location?->name ?? 'Sin asignar';
    $categoryLabel = $product->category?->name ?? 'Sin categoría';
@endphp

@section('content')
<style>
    .adjust-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        padding: 1.15rem 1.35rem;
        margin-bottom: 1rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: linear-gradient(125deg, var(--color-blanco) 35%, rgba(184, 154, 94, 0.08) 100%);
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.03);
    }

    .adjust-identity {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .adjust-thumb {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 68px;
        height: 68px;
        flex-shrink: 0;
        overflow: hidden;
        border: 1px solid rgba(184, 154, 94, 0.35);
        border-radius: 5px;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .adjust-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .adjust-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.4rem;
    }

    .adjust-kicker .adjust-eyebrow {
        color: var(--color-dorado);
        font-size: 0.69rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .adjust-title {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.55rem;
        line-height: 1.15;
    }

    .adjust-subtitle {
        margin: 0.3rem 0 0;
        color: var(--color-piedra);
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .adjust-subtitle strong {
        color: var(--color-noche);
        font-weight: 600;
    }

    .adjust-stock {
        flex-shrink: 0;
        padding-left: 1.35rem;
        border-left: 1px solid var(--color-linea);
        text-align: right;
    }

    .adjust-stock span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .adjust-stock strong {
        display: block;
        margin: 0.1rem 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 2.15rem;
        line-height: 1;
    }

    .adjust-stock small {
        color: var(--color-piedra);
        font-size: 0.74rem;
    }

    .adjust-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(290px, 0.6fr);
        align-items: start;
        gap: 1rem;
    }

    .adjust-form-card {
        margin-bottom: 0;
        padding: 1.5rem;
    }

    .adjust-form-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .adjust-form-head h2 {
        margin: 0 0 0.2rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
    }

    .adjust-form-head p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .adjust-audit-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        flex-shrink: 0;
        padding: 0.3rem 0.6rem;
        border-radius: 999px;
        background: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .adjust-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .adjust-option {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        padding: 0.85rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-blanco);
        cursor: pointer;
        transition: border-color 0.2s, background-color 0.2s, box-shadow 0.2s;
    }

    .adjust-option:hover {
        border-color: rgba(184, 154, 94, 0.55);
        background: #fdfbf7;
    }

    .adjust-option.is-selected {
        border-color: var(--color-dorado);
        background: rgba(184, 154, 94, 0.08);
        box-shadow: 0 0 0 1px rgba(184, 154, 94, 0.14);
    }

    .adjust-option.is-out.is-selected {
        border-color: #c58b8b;
        background: #fdf4f4;
        box-shadow: 0 0 0 1px rgba(166, 61, 64, 0.1);
    }

    .adjust-option:focus-within {
        outline: 2px solid rgba(184, 154, 94, 0.45);
        outline-offset: 2px;
    }

    .adjust-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .adjust-option-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .adjust-option-icon.in {
        background: #edf7ee;
        color: var(--color-exito);
    }

    .adjust-option-icon.out {
        background: #fdeded;
        color: var(--color-error);
    }

    .adjust-option-copy {
        min-width: 0;
    }

    .adjust-option-copy strong {
        display: block;
        margin-bottom: 0.12rem;
        color: var(--color-noche);
        font-size: 0.87rem;
    }

    .adjust-option-copy small {
        display: block;
        color: var(--color-piedra);
        font-size: 0.75rem;
        line-height: 1.4;
    }

    .adjust-option-check {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        margin-left: auto;
        border: 1px solid var(--color-linea);
        border-radius: 50%;
        background: var(--color-blanco);
        color: transparent;
        transition: color 0.2s, background-color 0.2s, border-color 0.2s;
    }

    .adjust-option.is-selected .adjust-option-check {
        border-color: var(--color-dorado);
        background: var(--color-dorado);
        color: var(--color-noche);
    }

    .adjust-option.is-out.is-selected .adjust-option-check {
        border-color: var(--color-error);
        background: var(--color-error);
        color: #fff;
    }

    .adjust-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.55rem;
    }

    .adjust-chip {
        padding: 0.28rem 0.65rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background: var(--color-crema);
        color: var(--color-piedra);
        font-family: var(--font-sans);
        font-size: 0.72rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .adjust-chip:hover {
        border-color: var(--color-dorado);
        background: #fdfbf7;
        color: var(--color-noche);
    }

    .adjust-reason-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 0.35rem;
    }

    .adjust-reason-meta .form-hint {
        margin: 0;
    }

    .adjust-counter {
        flex-shrink: 0;
        color: var(--color-piedra);
        font-size: 0.72rem;
        font-variant-numeric: tabular-nums;
    }

    .adjust-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        padding: 1rem 1.1rem;
        margin: 1.25rem 0;
        border: 1px dashed rgba(184, 154, 94, 0.55);
        border-radius: 5px;
        background: linear-gradient(110deg, rgba(184, 154, 94, 0.07), rgba(247, 244, 238, 0.55));
    }

    .adjust-preview-label {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .adjust-flow {
        display: flex;
        align-items: baseline;
        gap: 0.6rem;
        color: var(--color-piedra);
        font-family: var(--font-serif);
    }

    .adjust-flow strong {
        color: var(--color-noche);
        font-size: 1.75rem;
        line-height: 1;
    }

    .adjust-flow strong.is-negative {
        color: var(--color-error);
    }

    .adjust-flow small {
        font-family: var(--font-sans);
        font-size: 0.74rem;
    }

    .adjust-preview-text {
        margin: 0.35rem 0 0;
        color: var(--color-piedra);
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .adjust-preview-status {
        flex-shrink: 0;
        text-align: right;
    }

    .adjust-preview-status span:first-child {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .adjust-warning {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.6rem 0.75rem;
        margin-top: 0.65rem;
        border-left: 3px solid var(--color-error);
        border-radius: 3px;
        background: #fdeded;
        color: #5f2120;
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .adjust-warning[hidden] {
        display: none;
    }

    .adjust-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 1.15rem;
        margin-top: 1.15rem;
        border-top: 1px solid var(--color-linea);
    }

    .adjust-actions p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.74rem;
        line-height: 1.4;
    }

    .adjust-actions-buttons {
        display: flex;
        flex-shrink: 0;
        gap: 0.6rem;
    }

    .adjust-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .adjust-side .card {
        padding: 1.25rem;
        margin-bottom: 0;
    }

    .adjust-side-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.7rem;
    }

    .adjust-side-head h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.02rem;
    }

    .adjust-side-list {
        display: flex;
        flex-direction: column;
    }

    .adjust-side-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.55rem 0;
        border-bottom: 1px solid rgba(228, 223, 211, 0.65);
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .adjust-side-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .adjust-side-row strong {
        color: var(--color-noche);
        font-size: 0.84rem;
        text-align: right;
    }

    .adjust-side-row .num {
        font-variant-numeric: tabular-nums;
    }

    .adjust-meta {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .adjust-meta-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.8rem;
    }

    .adjust-meta-row span {
        color: var(--color-piedra);
    }

    .adjust-meta-row strong {
        color: var(--color-noche);
        font-size: 0.81rem;
        text-align: right;
    }

    .adjust-note {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
    }

    .adjust-note-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 50%;
        background: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
    }

    .adjust-note strong {
        display: block;
        margin-bottom: 0.15rem;
        color: var(--color-noche);
        font-size: 0.84rem;
    }

    .adjust-note p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.76rem;
        line-height: 1.5;
    }

    @media (max-width: 1024px) {
        .adjust-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .adjust-side {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .adjust-side > .adjust-note-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .adjust-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .adjust-stock {
            width: 100%;
            padding-top: 0.9rem;
            padding-left: 0;
            border-top: 1px solid var(--color-linea);
            border-left: 0;
            text-align: left;
        }

        .adjust-options,
        .adjust-side {
            grid-template-columns: minmax(0, 1fr);
        }

        .adjust-preview,
        .adjust-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        .adjust-preview-status {
            text-align: left;
        }

        .adjust-actions-buttons {
            width: 100%;
        }

        .adjust-actions-buttons .btn {
            flex: 1;
        }
    }
</style>

<div class="adjust-page">
    <header class="adjust-hero">
        <div class="adjust-identity">
            <div class="adjust-thumb">
                @if ($photo)
                    <img src="{{ asset('storage/'.$photo->path) }}" alt="Fotografía de {{ $product->name }}">
                @else
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                        <path d="M6 3h12l3 6-9 12L3 9l3-6Z" />
                        <path d="M3 9h18M9 3l3 18M15 3l-3 18" />
                    </svg>
                @endif
            </div>

            <div style="min-width: 0;">
                <div class="adjust-kicker">
                    <span class="adjust-eyebrow">Inventario · Ajuste</span>
                    <span class="badge {{ $statusBadge }}">{{ $currentStatus }}</span>
                    <span class="badge badge-gold">Auditado</span>
                </div>
                <h1 class="adjust-title">{{ $product->name }}</h1>
                <p class="adjust-subtitle">
                    <strong>SKU</strong> {{ $product->sku }} &middot;
                    <strong>Categoría</strong> {{ $categoryLabel }} &middot;
                    <strong>Ubicación</strong> {{ $locationLabel }}
                </p>
            </div>
        </div>

        <div class="adjust-stock">
            <span>Stock actual</span>
            <strong>{{ $stock }}</strong>
            <small>{{ $stockNoun }} en existencia</small>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="adjust-layout">
        <form
            action="{{ route('admin.inventory.adjust.store', $product) }}"
            method="POST"
            class="card adjust-form-card"
            id="adjustForm"
            data-stock="{{ (int) $stock }}"
            data-product="{{ $product->name }}"
        >
            @csrf

            <div class="adjust-form-head">
                <div>
                    <h2>Registrar movimiento</h2>
                    <p>Indica qué pasó con la pieza. El sistema calculará el nuevo stock y dejará el movimiento en la bitácora.</p>
                </div>
                <span class="adjust-audit-badge">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                    </svg>
                    Inmutable
                </span>
            </div>

            <fieldset class="form-group" style="border: 0; padding: 0; margin: 0 0 1.15rem;">
                <legend class="form-label">Sentido del ajuste *</legend>

                <div class="adjust-options">
                    <label class="adjust-option in {{ $selectedDelta === 1 ? 'is-selected' : '' }}">
                        <input type="radio" name="quantity_delta" value="1" @checked($selectedDelta === 1)>
                        <span class="adjust-option-icon in" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                        </span>
                        <span class="adjust-option-copy">
                            <strong>Alta de pieza</strong>
                            <small>La pieza vuelve a contar como existencia disponible.</small>
                        </span>
                        <span class="adjust-option-check" aria-hidden="true">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                    </label>

                    <label class="adjust-option out {{ $selectedDelta === -1 ? 'is-selected' : '' }}">
                        <input type="radio" name="quantity_delta" value="-1" @checked($selectedDelta === -1)>
                        <span class="adjust-option-icon out" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M5 12h14" />
                            </svg>
                        </span>
                        <span class="adjust-option-copy">
                            <strong>Baja o retiro</strong>
                            <small>La pieza sale del stock vendible y queda retirada.</small>
                        </span>
                        <span class="adjust-option-check" aria-hidden="true">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                    </label>
                </div>
            </fieldset>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="adjustReason" class="form-label">Motivo del ajuste *</label>
                <textarea
                    name="reason"
                    id="adjustReason"
                    rows="3"
                    class="form-control"
                    required
                    minlength="5"
                    maxlength="255"
                    aria-describedby="adjustReasonHint"
                    placeholder="Ej. Conteo físico: pieza encontrada en caja fuerte"
                >{{ old('reason') }}</textarea>

                <div class="adjust-reason-meta">
                    <p class="form-hint" id="adjustReasonHint">Mínimo 5 caracteres. Sé específico para facilitar la auditoría.</p>
                    <span class="adjust-counter" id="adjustReasonCounter">0 / 255</span>
                </div>

                <div class="adjust-suggestions" aria-label="Motivos frecuentes">
                    <button type="button" class="adjust-chip" data-reason="Conteo físico: pieza localizada en resguardo">Conteo: pieza localizada</button>
                    <button type="button" class="adjust-chip" data-reason="Conteo físico: pieza no localizada">Conteo: pieza no localizada</button>
                    <button type="button" class="adjust-chip" data-reason="Devolución de apartado cancelado">Devolución de apartado</button>
                    <button type="button" class="adjust-chip" data-reason="Corrección de captura de inventario">Corrección de captura</button>
                </div>
            </div>

            <div class="adjust-preview" aria-live="polite">
                <div>
                    <span class="adjust-preview-label">Resultado del ajuste</span>
                    <div class="adjust-flow">
                        <strong>{{ $stock }}</strong>
                        <span aria-hidden="true">&rarr;</span>
                        <strong id="adjustResult" class="{{ $initialNewStock < 0 ? 'is-negative' : '' }}">{{ $initialNewStock }}</strong>
                        <small>{{ $stockNoun }}</small>
                    </div>
                    <p class="adjust-preview-text" id="adjustPreviewText">
                        {{ $selectedDelta > 0 ? 'La pieza volverá a contar como existencia disponible.' : 'La pieza quedará fuera del stock vendible.' }}
                    </p>
                </div>
                <div class="adjust-preview-status">
                    <span>Estado resultante</span>
                    <span class="badge {{ $initialStatusBadge }}" id="adjustResultStatus">{{ $initialStatus }}</span>
                </div>
            </div>

            <div class="adjust-warning" id="adjustWarning" @if ($initialNewStock >= 0) hidden @endif>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink: 0; margin-top: 1px;">
                    <path d="M10.3 3.7 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z" />
                    <path d="M12 9v4M12 17h.01" />
                </svg>
                <span>El stock quedaría en negativo. Revisa si el sentido del ajuste es correcto antes de continuar.</span>
            </div>

            <div class="adjust-actions">
                <p>Se guardará tu usuario, la fecha y el motivo en el historial de la pieza.</p>
                <div class="adjust-actions-buttons">
                    <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-gold" id="adjustSubmit">Registrar ajuste</button>
                </div>
            </div>
        </form>

        <aside class="adjust-side">
            <section class="card">
                <div class="adjust-side-head">
                    <h2>Resumen del ajuste</h2>
                    <span class="badge badge-gold">Se registrará</span>
                </div>

                <div class="adjust-side-list">
                    <div class="adjust-side-row">
                        <span>Stock actual</span>
                        <strong class="num">{{ $stock }}</strong>
                    </div>
                    <div class="adjust-side-row">
                        <span>Movimiento</span>
                        <strong class="num" id="adjustSideDelta">{{ $selectedDelta > 0 ? '+1' : '-1' }}</strong>
                    </div>
                    <div class="adjust-side-row">
                        <span>Stock final</span>
                        <strong class="num" id="adjustSideResult">{{ $initialNewStock }}</strong>
                    </div>
                    <div class="adjust-side-row">
                        <span>Estado final</span>
                        <span class="badge {{ $initialStatusBadge }}" id="adjustSideStatus">{{ $initialStatus }}</span>
                    </div>
                    <div class="adjust-side-row">
                        <span>Estado actual</span>
                        <span class="badge {{ $statusBadge }}">{{ $currentStatus }}</span>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="adjust-side-head">
                    <h2>La pieza</h2>
                </div>

                <div class="adjust-meta">
                    <div class="adjust-meta-row">
                        <span>Metal</span>
                        <strong>{{ $product->metal?->name ?? 'Sin metal' }}</strong>
                    </div>
                    <div class="adjust-meta-row">
                        <span>Pureza</span>
                        <strong>{{ $purityLabel }}</strong>
                    </div>
                    <div class="adjust-meta-row">
                        <span>Categoría</span>
                        <strong>{{ $categoryLabel }}</strong>
                    </div>
                    <div class="adjust-meta-row">
                        <span>Ubicación</span>
                        <strong>{{ $locationLabel }}</strong>
                    </div>
                </div>
            </section>

            <section class="card adjust-note-card">
                <div class="adjust-note">
                    <span class="adjust-note-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 8h.01M11 12h1v4h1" />
                        </svg>
                    </span>
                    <div>
                        <strong>El stock no se edita directamente</strong>
                        <p>Este formulario genera un movimiento inmutable. Si necesitas corregir un error, registra un nuevo ajuste con su motivo; no se borra el anterior.</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('adjustForm');
        if (!form) {
            return;
        }

        const stock = Number(form.dataset.stock) || 0;
        const productName = form.dataset.product || 'la pieza';
        const radios = form.querySelectorAll('input[name="quantity_delta"]');
        const options = form.querySelectorAll('.adjust-option');
        const result = document.getElementById('adjustResult');
        const previewText = document.getElementById('adjustPreviewText');
        const resultStatus = document.getElementById('adjustResultStatus');
        const sideDelta = document.getElementById('adjustSideDelta');
        const sideResult = document.getElementById('adjustSideResult');
        const sideStatus = document.getElementById('adjustSideStatus');
        const warning = document.getElementById('adjustWarning');
        const reason = document.getElementById('adjustReason');
        const reasonCounter = document.getElementById('adjustReasonCounter');
        const submit = document.getElementById('adjustSubmit');

        const selectedDelta = () => Number(form.querySelector('input[name="quantity_delta"]:checked')?.value || 1);

        const syncReasonCounter = () => {
            reasonCounter.textContent = `${reason.value.length} / 255`;
        };

        const syncPreview = () => {
            const delta = selectedDelta();
            const nextStock = stock + delta;
            const isIncoming = delta > 0;
            const resultLabel = isIncoming ? 'Disponible' : 'Retirada';
            const resultBadge = isIncoming ? 'badge badge-success' : 'badge badge-muted';

            options.forEach((option) => {
                option.classList.toggle('is-selected', option.querySelector('input').checked);
            });

            result.textContent = nextStock;
            result.classList.toggle('is-negative', nextStock < 0);
            previewText.textContent = isIncoming
                ? 'La pieza volverá a contar como existencia disponible.'
                : 'La pieza quedará fuera del stock vendible.';

            resultStatus.textContent = resultLabel;
            resultStatus.className = resultBadge;
            sideDelta.textContent = isIncoming ? '+1' : '-1';
            sideResult.textContent = nextStock;
            sideStatus.textContent = resultLabel;
            sideStatus.className = resultBadge;
            warning.hidden = nextStock >= 0;
        };

        radios.forEach((radio) => {
            radio.addEventListener('change', syncPreview);
        });

        form.querySelectorAll('.adjust-chip').forEach((chip) => {
            chip.addEventListener('click', () => {
                reason.value = chip.dataset.reason || '';
                reason.focus();
                syncReasonCounter();
            });
        });

        reason.addEventListener('input', syncReasonCounter);

        form.addEventListener('submit', (event) => {
            const delta = selectedDelta();
            const nextStock = stock + delta;
            const action = delta > 0 ? 'alta' : 'baja';

            if (!window.confirm(`¿Confirmas el ${action} de "${productName}"? El stock pasará de ${stock} a ${nextStock}.`)) {
                event.preventDefault();
                return;
            }

            submit.disabled = true;
            submit.textContent = 'Registrando…';
        });

        syncReasonCounter();
        syncPreview();
    })();
</script>
@endpush
