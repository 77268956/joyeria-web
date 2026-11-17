@extends('layouts.admin')

@section('title', 'Detalle de Pieza')

@section('body_class', 'full-viewport')

@php
    $isUsed = $product->origin === \App\Enums\ProductOrigin::UsedPurchase;
    $photos = $product->photos;
    $heroPhoto = $photos->firstWhere('is_primary', true) ?? $photos->first();
    $lastMovement = $product->movements->first();
    $P = \App\Enums\ProductStatus::class;

    $statusBadge = match ($product->status) {
        $P::Available => 'badge-success',
        $P::Reserved => 'badge-gold',
        $P::Sold => 'badge-info',
        $P::Melting => 'badge-danger',
        $P::Withdrawn => 'badge-muted',
        default => 'badge-muted',
    };

    $statusNote = match ($product->status) {
        $P::Available => 'Disponible para vender en el POS y, si está publicada, en el catálogo web.',
        $P::Reserved => 'Apartada por una venta o un layaway; no está libre para una venta nueva.',
        $P::Sold => 'Vendida; ya no forma parte del stock disponible.',
        $P::Melting => 'Enviada a fundición; ya no forma parte del inventario vendible.',
        $P::Withdrawn => 'Retirada del inventario; no está disponible para la venta.',
        default => 'Estado no definido.',
    };

    $originNote = $isUsed
        ? 'Comprada a un cliente. Se revende como pieza de ocasión y conserva ese origen en todo el sistema.'
        : 'Pieza nueva. Ingresó al inventario como mercancía regular de la tienda.';

    $purityLabel = $product->purity?->label ?? 'No aplica';
    $weightLabel = $product->weight_g !== null ? number_format((float) $product->weight_g, 3).' g' : '—';
    $metalLine = trim(($product->metal?->name ?? '').' '.$purityLabel);
    $margin = bcsub((string) ($product->price ?? '0'), (string) ($product->cost ?? '0'), 2);
    $marginNegative = bccomp($margin, '0', 2) < 0;
    $marginPercent = ($product->price && bccomp((string) $product->price, '0', 2) > 0)
        ? bcmul(bcdiv($margin, (string) $product->price, 4), '100', 1)
        : null;
@endphp

@section('content')
<style>
    .piece-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.8rem;
        min-height: 0;
        overflow: hidden;
    }

    .piece-page .card,
    .piece-page .alert {
        margin-bottom: 0;
    }

    .piece-hero {
        display: grid;
        flex-shrink: 0;
        grid-template-columns: 112px minmax(0, 1fr) minmax(250px, auto);
        gap: 1.1rem;
        align-items: center;
        padding: 1rem 1.15rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .piece-hero-media {
        width: 112px;
        aspect-ratio: 1;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .piece-hero-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .piece-hero-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.45rem;
    }

    .piece-hero-title {
        margin: 0;
        font-family: var(--font-serif);
        font-size: clamp(1.55rem, 2.2vw, 2.15rem);
        font-weight: 500;
        line-height: 1.15;
        color: var(--color-noche);
    }

    .piece-hero-lede {
        margin: 0.5rem 0 0;
        max-width: 62ch;
        font-size: 0.9rem;
        line-height: 1.55;
        color: var(--color-piedra);
    }

    .piece-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem 1rem;
        margin-top: 0.85rem;
        font-size: 0.78rem;
        color: var(--color-piedra);
    }

    .piece-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .piece-hero-meta strong {
        color: var(--color-noche);
        font-weight: 600;
    }

    .piece-hero-side {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        min-width: 0;
        padding-left: 1.35rem;
        border-left: 1px solid var(--color-linea);
    }

    .piece-price-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--color-piedra);
    }

    .piece-price strong {
        display: block;
        margin-top: 0.15rem;
        font-family: var(--font-serif);
        font-size: 1.9rem;
        font-weight: 500;
        line-height: 1.1;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .piece-price small {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.76rem;
        color: var(--color-piedra);
    }

    .piece-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .piece-hero-actions .btn {
        height: 36px;
        padding: 0 0.85rem;
        font-size: 0.8rem;
    }

    .piece-callout {
        position: relative;
        display: flex;
        flex-shrink: 0;
        gap: 0.85rem;
        padding: 0.8rem 2.4rem 0.8rem 1rem;
        border: 1px solid rgba(184, 154, 94, 0.4);
        border-left: 4px solid var(--color-dorado);
        border-radius: 4px;
        background: rgba(184, 154, 94, 0.09);
        color: var(--color-noche);
    }

    .piece-callout > svg {
        flex-shrink: 0;
        margin-top: 0.1rem;
        color: var(--color-dorado);
    }

    .piece-callout-copy {
        min-width: 0;
        flex: 1;
    }

    .piece-callout-close {
        position: absolute;
        top: 0.4rem;
        right: 0.4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: var(--color-piedra);
        cursor: pointer;
        transition: background-color 0.2s, color 0.2s;
    }

    .piece-callout-close:hover {
        background: rgba(18, 22, 31, 0.06);
        color: var(--color-noche);
    }

    .piece-callout strong {
        display: block;
        margin-bottom: 0.15rem;
        font-size: 0.88rem;
    }

    .piece-callout p {
        margin: 0;
        font-size: 0.84rem;
        line-height: 1.5;
        color: var(--color-piedra);
    }

    .piece-stats {
        display: grid;
        flex-shrink: 0;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .piece-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        min-width: 0;
        padding: 0.75rem 0.95rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 5px;
    }

    .piece-stat-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .piece-stat-label {
        display: block;
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--color-piedra);
    }

    .piece-stat-value {
        display: block;
        margin-top: 0.15rem;
        overflow: hidden;
        font-size: 1.02rem;
        font-weight: 600;
        color: var(--color-noche);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .piece-stat-hint {
        display: block;
        margin-top: 0.15rem;
        overflow: hidden;
        font-size: 0.74rem;
        color: var(--color-piedra);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .piece-grid {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1.65fr) minmax(280px, 1fr);
        gap: 1rem;
        align-items: stretch;
        min-height: 0;
        overflow: hidden;
    }

    .piece-column {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        min-width: 0;
        min-height: 0;
        padding-right: 0.35rem;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .piece-column::-webkit-scrollbar {
        width: 7px;
    }

    .piece-column::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #D9D2C3;
    }

    .piece-column::-webkit-scrollbar-track {
        background: transparent;
    }

    .piece-column > .card {
        flex-shrink: 0;
    }

    .piece-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .piece-card-head h2 {
        margin: 0;
        font-family: var(--font-serif);
        font-size: 1.2rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .piece-card-head p {
        margin: 0.2rem 0 0;
        font-size: 0.8rem;
        line-height: 1.45;
        color: var(--color-piedra);
    }

    .piece-section-label {
        margin: 1.2rem 0 0.55rem;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--color-dorado);
    }

    .piece-section-label:first-of-type {
        margin-top: 0;
    }

    .piece-sheet {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem;
    }

    .piece-sheet-row {
        min-width: 0;
        padding: 0.65rem 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: #FCFBF8;
    }

    .piece-sheet-row span {
        display: block;
        font-size: 0.68rem;
        color: var(--color-piedra);
    }

    .piece-sheet-row strong {
        display: block;
        margin-top: 0.12rem;
        overflow-wrap: anywhere;
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--color-noche);
    }

    .piece-sheet-row strong.num {
        font-variant-numeric: tabular-nums;
    }

    .piece-description {
        margin: 1.1rem 0 0;
        padding-top: 1rem;
        border-top: 1px solid var(--color-linea);
        font-size: 0.87rem;
        line-height: 1.6;
        color: var(--color-piedra);
    }

    .piece-mini-list {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .piece-mini-list > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.55rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .piece-mini-list > div:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .piece-mini-list span {
        font-size: 0.8rem;
        color: var(--color-piedra);
    }

    .piece-mini-list strong {
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--color-noche);
        text-align: right;
    }

    .piece-card-note {
        margin: 0.85rem 0 0;
        padding-top: 0.75rem;
        border-top: 1px solid var(--color-linea);
        font-size: 0.8rem;
        line-height: 1.5;
        color: var(--color-piedra);
    }

    .piece-origin {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.65rem;
    }

    .piece-origin-icon {
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .piece-origin strong {
        display: block;
        font-size: 0.9rem;
        color: var(--color-noche);
    }

    .piece-origin small {
        display: block;
        margin-top: 0.1rem;
        font-size: 0.72rem;
        color: var(--color-piedra);
    }

    .piece-photo-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .piece-photo-grid img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: var(--color-crema);
    }

    .piece-empty {
        padding: 1.5rem 1rem;
        border: 1px dashed var(--color-linea);
        border-radius: 4px;
        font-size: 0.82rem;
        line-height: 1.5;
        color: var(--color-piedra);
        text-align: center;
    }

    .piece-table-wrap {
        overflow-x: auto;
    }

    .piece-table-wrap table.data-table {
        margin-bottom: 0;
    }

    .movement-type {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-weight: 600;
        color: var(--color-noche);
    }

    .movement-dot {
        width: 7px;
        height: 7px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--color-dorado);
    }

    .movement-dot.is-out {
        background: var(--color-error);
    }

    .movement-qty {
        display: inline-block;
        min-width: 2.4rem;
        padding: 0.16rem 0.45rem;
        border-radius: 999px;
        background: #EDF7EE;
        color: var(--color-exito);
        font-size: 0.76rem;
        font-weight: 600;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }

    .movement-qty.is-out {
        background: #FDEDED;
        color: var(--color-error);
    }

    @media (max-width: 1100px) {
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

        .piece-page {
            overflow: visible;
        }

        .piece-hero {
            grid-template-columns: 112px minmax(0, 1fr);
        }

        .piece-hero-side {
            grid-column: 1 / -1;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0 0;
            border-top: 1px solid var(--color-linea);
            border-left: 0;
        }

        .piece-hero-actions {
            justify-content: flex-end;
        }

        .piece-grid {
            grid-template-columns: minmax(0, 1fr);
            overflow: visible;
        }

        .piece-column {
            min-height: auto;
            padding-right: 0;
            overflow: visible;
        }
    }

    @media (max-width: 900px) {
        .piece-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .piece-hero {
            grid-template-columns: minmax(0, 1fr);
        }

        .piece-hero-media {
            width: 100%;
            max-width: 220px;
            aspect-ratio: 16 / 10;
        }

        .piece-hero-side {
            flex-direction: column;
            align-items: stretch;
        }

        .piece-hero-actions {
            justify-content: flex-start;
        }

        .piece-hero-actions .btn {
            flex: 1 1 auto;
        }
    }

    @media (max-width: 600px) {
        .piece-stats,
        .piece-sheet {
            grid-template-columns: minmax(0, 1fr);
        }

        .piece-hero {
            padding: 1rem;
        }

        .piece-photo-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<div class="piece-page">
    <header class="piece-hero">
        <div class="piece-hero-media">
            @if ($heroPhoto)
                <img src="{{ asset('storage/'.$heroPhoto->path) }}" alt="Fotografía de {{ $product->name }}">
            @else
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                    <path d="M6 3h12l3 6-9 12L3 9l3-6Z" />
                    <path d="M3 9h18M9 3l3 18M15 3l-3 18" />
                </svg>
            @endif
        </div>

        <div>
            <div class="piece-hero-kicker">
                <span class="badge {{ $statusBadge }}">{{ $product->status?->label() ?? 'Sin estado' }}</span>
                @if ($isUsed)
                    <span class="badge badge-gold">Ocasión</span>
                @endif
                @if ($product->is_featured)
                    <span class="badge badge-info">Destacada</span>
                @endif
            </div>

            <h1 class="piece-hero-title">{{ $product->name }}</h1>

            <p class="piece-hero-lede">
                Ficha interna de la pieza: aquí se consultan su estado, precio, ubicación e historial.
                Las ventas se cobran desde el POS.
            </p>

            <div class="piece-hero-meta">
                <span><strong>SKU</strong> {{ $product->sku }}</span>
                <span><strong>Stock</strong> {{ $stock }}</span>
                <span><strong>Ubicación</strong> {{ $product->location?->name ?? 'Sin asignar' }}</span>
            </div>
        </div>

        <div class="piece-hero-side">
            <div>
                <span class="piece-price-label">Precio de venta</span>
                <strong>$ {{ number_format((float) $product->price, 2) }}</strong>
                <small>Costo interno: $ {{ number_format((float) $product->cost, 2) }}</small>
            </div>

            <div class="piece-hero-actions">
                @can('inventory.adjust')
                    <a href="{{ route('admin.inventory.edit', $product) }}" class="btn btn-gold" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m14 4 6 6L8 22H2v-6L14 4Z"></path>
                            <path d="m12 6 6 6"></path>
                        </svg>
                        <span>Editar</span>
                    </a>
                    <a href="{{ route('admin.inventory.adjust', $product) }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 6h10M18 6h2M4 12h2M10 12h10M4 18h10M18 18h2"></path>
                            <circle cx="16" cy="6" r="2"></circle>
                            <circle cx="8" cy="12" r="2"></circle>
                            <circle cx="16" cy="18" r="2"></circle>
                        </svg>
                        <span>Ajustar stock</span>
                    </a>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('meltModal').style.display='flex'" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path>
                        </svg>
                        <span>Fundición</span>
                    </button>
                @endcan
                <a href="{{ route('admin.inventory.label', $product) }}" target="_blank" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.6 13.4 12 22l-9-9 8.6-8.6a2 2 0 0 1 1.4-.6H20a2 2 0 0 1 2 2v6.2a2 2 0 0 1-.6 1.4Z"></path>
                        <circle cx="16" cy="8" r="1"></circle>
                    </svg>
                    <span>Etiqueta</span>
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </header>

    @include('admin.partials.alerts')

    @if ($isUsed)
        <div class="piece-callout" id="usedPieceCallout" data-product-id="{{ $product->id }}">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path d="M12 8v.01M11 12h1v4h1" />
            </svg>
            <div class="piece-callout-copy">
                <strong>Esta es una pieza de ocasión</strong>
                <p>Fue comprada a un cliente y quedó registrada como pieza usada. Puede venderse como cualquier otro producto, pero conserva el origen “ocasión”.</p>
            </div>
            <button
                type="button"
                class="piece-callout-close"
                data-dismiss-used-callout
                aria-label="Ocultar aviso de pieza de ocasión"
                title="Ocultar aviso"
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <script>
            (() => {
                const callout = document.getElementById('usedPieceCallout');
                if (!callout) {
                    return;
                }

                const storageKey = `joyeria.used-piece-callout.dismissed.${callout.dataset.productId}`;
                const dismissButton = callout.querySelector('[data-dismiss-used-callout]');
                const removeCallout = () => callout.remove();

                try {
                    if (window.localStorage.getItem(storageKey) === '1') {
                        removeCallout();
                        return;
                    }
                } catch (error) {
                }

                dismissButton?.addEventListener('click', () => {
                    try {
                        window.localStorage.setItem(storageKey, '1');
                    } catch (error) {
                    }

                    removeCallout();
                });
            })();
        </script>
    @endif

    <div class="piece-stats">
        <div class="piece-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="piece-stat-label">Stock actual</span>
                <strong class="piece-stat-value">{{ $stock }} {{ $stock === 1 ? 'unidad' : 'unidades' }}</strong>
                <span class="piece-stat-hint">{{ $product->status?->isInStock() ? 'Cuenta como existencia' : 'Fuera del stock vendible' }}</span>
            </div>
            <span class="piece-stat-icon" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 7 9 18l-5-5" />
                </svg>
            </span>
        </div>

        <div class="piece-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="piece-stat-label">Metal y pureza</span>
                <strong class="piece-stat-value">{{ $product->metal?->name ?? 'Sin metal' }}</strong>
                <span class="piece-stat-hint">{{ $purityLabel }} · {{ $weightLabel }}</span>
            </div>
            <span class="piece-stat-icon" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="8" />
                    <path d="M12 7v5l3 2" />
                </svg>
            </span>
        </div>

        <div class="piece-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="piece-stat-label">Ubicación física</span>
                <strong class="piece-stat-value">{{ $product->location?->name ?? 'Sin asignar' }}</strong>
                <span class="piece-stat-hint">Lugar donde se resguarda</span>
            </div>
            <span class="piece-stat-icon" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                    <circle cx="12" cy="10" r="2.5" />
                </svg>
            </span>
        </div>

        <div class="piece-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="piece-stat-label">Movimientos</span>
                <strong class="piece-stat-value">{{ $product->movements->count() }} registros</strong>
                <span class="piece-stat-hint">{{ $lastMovement?->created_at?->format('d/m/Y H:i') ?? 'Sin movimientos' }}</span>
            </div>
            <span class="piece-stat-icon" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8v5l3 2" />
                    <circle cx="12" cy="12" r="9" />
                </svg>
            </span>
        </div>
    </div>

    <div class="piece-grid">
        <div class="piece-column">
            <section class="card">
                <div class="piece-card-head">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span class="piece-stat-icon" aria-hidden="true" style="width: 32px; height: 32px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 3h12l3 6-9 12L3 9l3-6Z"></path>
                                <path d="M3 9h18M9 3l3 18M15 3l-3 18"></path>
                            </svg>
                        </span>
                        <div>
                            <h2>Ficha Técnica</h2>
                            <p>Especificaciones físicas, pureza de metales y valuación comercial</p>
                        </div>
                    </div>
                    @if ($product->metal)
                        <span class="inv-origin" style="padding: 0.2rem 0.6rem; font-size: 0.68rem;">{{ $metalLine }}</span>
                    @endif
                </div>

                <div class="piece-section-label">Especificaciones de Joyería</div>
                <div class="piece-sheet">
                    <div class="piece-sheet-row">
                        <span>Categoría</span>
                        <strong>{{ $product->category?->name ?? 'Sin categoría' }}</strong>
                    </div>
                    <div class="piece-sheet-row">
                        <span>Metal precioso</span>
                        <strong>{{ $product->metal?->name ?? 'Sin metal' }}</strong>
                    </div>
                    <div class="piece-sheet-row">
                        <span>Pureza / Ley</span>
                        <strong>{{ $purityLabel }}</strong>
                    </div>
                    <div class="piece-sheet-row">
                        <span>Peso exacto</span>
                        <strong class="num">{{ $weightLabel }}</strong>
                    </div>
                    <div class="piece-sheet-row">
                        <span>Código SKU</span>
                        <strong class="num">{{ $product->sku }}</strong>
                    </div>
                    <div class="piece-sheet-row">
                        <span>Ubicación en tienda</span>
                        <strong>{{ $product->location?->name ?? 'Sin asignar' }}</strong>
                    </div>
                </div>

                <div class="piece-section-label">Valuación y Control Comercial</div>
                <div class="piece-sheet">
                    <div class="piece-sheet-row">
                        <span>Precio de venta al público</span>
                        <strong class="num" style="color: var(--color-noche); font-size: 1.05rem;">$ {{ number_format((float) $product->price, 2) }}</strong>
                    </div>
                    <div class="piece-sheet-row">
                        <span>Costo interno de adquisición</span>
                        <strong class="num">$ {{ number_format((float) $product->cost, 2) }}</strong>
                    </div>
                    <div class="piece-sheet-row">
                        <span>Utilidad bruta estimada</span>
                        <div style="display: flex; align-items: baseline; gap: 0.4rem; margin-top: 0.12rem;">
                            <strong class="num" style="{{ $marginNegative ? 'color: var(--color-error);' : 'color: var(--color-exito);' }}">
                                $ {{ number_format((float) $margin, 2) }}
                            </strong>
                            @if ($marginPercent !== null)
                                <small style="font-size: 0.72rem; font-weight: 600; color: {{ $marginNegative ? 'var(--color-error)' : 'var(--color-exito)' }};">
                                    ({{ $marginNegative ? '' : '+' }}{{ $marginPercent }}%)
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="piece-sheet-row">
                        <span>Código de barras</span>
                        <strong class="num" style="display: flex; align-items: center; gap: 0.35rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" style="color: var(--color-piedra);">
                                <path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"></path>
                            </svg>
                            <span>{{ $product->barcode ?: 'Sin código asignado' }}</span>
                        </strong>
                    </div>
                </div>

                <div class="piece-section-label">Descripción de la Pieza</div>
                @if ($product->description)
                    <p class="piece-description" style="margin-top: 0; background: #FCFBF8; padding: 0.85rem; border: 1px solid var(--color-linea); border-radius: 4px;">{{ $product->description }}</p>
                @else
                    <p class="piece-description" style="margin-top: 0; font-style: italic;">Sin descripción registrada para esta pieza de joyería.</p>
                @endif
            </section>

            <section class="card" style="padding: 0; overflow: hidden;">
                <div class="piece-card-head" style="padding: 1rem 1.25rem 0.85rem; margin-bottom: 0; border-bottom: 1px solid var(--color-linea);">
                    <div>
                        <h2>Historial de Movimientos</h2>
                        <p>Registro inmutable de entradas, ventas, apartados, traslados o fundición</p>
                    </div>
                    <span class="inv-origin" style="padding: 0.2rem 0.55rem; font-size: 0.65rem;">{{ $product->movements->count() }} {{ $product->movements->count() === 1 ? 'registro' : 'registros' }}</span>
                </div>

                <div class="piece-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Movimiento</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Peso</th>
                                <th>Motivo</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($product->movements as $movement)
                                @php($isEntry = $movement->quantity_delta > 0)
                                <tr>
                                    <td class="muted num" style="font-size: 0.78rem;">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="movement-type">
                                            <span class="movement-dot {{ $isEntry ? '' : 'is-out' }}"></span>
                                            {{ $movement->type->label() }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <span class="movement-qty {{ $isEntry ? '' : 'is-out' }}">{{ $isEntry ? '+' : '' }}{{ $movement->quantity_delta }}</span>
                                    </td>
                                    <td class="text-right num">{{ $movement->weight_delta_g !== null ? number_format((float) $movement->weight_delta_g, 3).' g' : '—' }}</td>
                                    <td class="muted" style="max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $movement->reason ?? '—' }}</td>
                                    <td class="muted">{{ $movement->user?->name ?? 'Sistema' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="piece-empty">Aún no hay movimientos registrados. El primero aparecerá al realizar una operación.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="piece-column">
            <section class="card">
                <div class="piece-card-head">
                    <div>
                        <h2>Estado y disponibilidad</h2>
                        <p>Determina si la pieza puede venderse o no.</p>
                    </div>
                </div>

                <div class="piece-mini-list">
                    <div>
                        <span>Estado</span>
                        <strong><span class="badge {{ $statusBadge }}">{{ $product->status?->label() ?? 'Sin estado' }}</span></strong>
                    </div>
                    <div>
                        <span>Stock actual</span>
                        <strong>{{ $stock }}</strong>
                    </div>
                    <div>
                        <span>Catálogo web</span>
                        <strong>{{ $product->is_public ? 'Visible' : 'Oculta' }}</strong>
                    </div>
                    <div>
                        <span>Destacada</span>
                        <strong>{{ $product->is_featured ? 'Sí' : 'No' }}</strong>
                    </div>
                </div>

                <p class="piece-card-note">{{ $statusNote }}</p>
            </section>

            <section class="card">
                <div class="piece-card-head">
                    <div>
                        <h2>Origen</h2>
                        <p>De dónde llegó esta pieza al inventario.</p>
                    </div>
                </div>

                <div class="piece-origin">
                    <span class="piece-origin-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12 12 3l9 9" />
                            <path d="M5 10v10h14V10" />
                        </svg>
                    </span>
                    <div>
                        <strong>{{ $product->origin?->label() ?? 'Sin origen' }}</strong>
                        <small>{{ $isUsed ? 'Pieza usada comprada a un cliente' : 'Mercancía nueva de la tienda' }}</small>
                    </div>
                </div>

                <p class="piece-card-note" style="border-top: 0; padding-top: 0;">{{ $originNote }}</p>
            </section>

            <section class="card">
                <div class="piece-card-head">
                    <div>
                        <h2>Fotografías</h2>
                        <p>{{ $photos->count() }} {{ $photos->count() === 1 ? 'imagen registrada' : 'imágenes registradas' }}.</p>
                    </div>
                </div>

                @if ($photos->isEmpty())
                    <div class="piece-empty">Sin fotografías. Puedes agregarlas desde “Editar”.</div>
                @else
                    <div class="piece-photo-grid">
                        @foreach ($photos as $photo)
                            <img src="{{ asset('storage/'.$photo->path) }}" alt="Fotografía de {{ $product->name }}">
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>
    </div>
</div>

@can('inventory.adjust')
    <div id="meltModal" class="modal-backdrop">
        <div class="modal">
            <div class="modal-header">
                <h3 class="font-serif" style="font-size: 1.2rem;">Enviar a fundición</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('meltModal').style.display='none'">&#10005;</button>
            </div>
            <form action="{{ route('admin.inventory.melt', $product) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="muted" style="margin-bottom: 1rem;">La pieza saldrá del stock disponible y quedará registrada como enviada a fundición.</p>
                    <label class="form-label">Motivo *</label>
                    <textarea name="reason" rows="3" class="form-control" required minlength="5" placeholder="Ej. Pieza dañada sin reparación posible"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('meltModal').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn btn-gold">Confirmar envío</button>
                </div>
            </form>
        </div>
    </div>
@endcan
@endsection
