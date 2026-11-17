@extends('layouts.admin')

@section('title', 'Editar pieza')

@section('body_class', 'full-viewport')

@php
    $P = \App\Enums\ProductStatus::class;
    $photo = $product->photos->firstWhere('is_primary', true) ?? $product->photos->first();
    $isUsed = $product->origin === \App\Enums\ProductOrigin::UsedPurchase;
    $stockNoun = (int) $stock === 1 ? 'unidad' : 'unidades';

    $statusBadge = match ($product->status) {
        $P::Available => 'badge-success',
        $P::Reserved => 'badge-gold',
        $P::Sold => 'badge-info',
        $P::Melting => 'badge-danger',
        $P::Withdrawn => 'badge-muted',
        default => 'badge-muted',
    };

    $categoryLabel = $product->category?->name ?? 'Sin categoría';
    $locationLabel = $product->location?->name ?? 'Sin asignar';
    $purityLabel = $product->purity?->label ?? 'No aplica';
    $margin = bcsub((string) ($product->price ?? '0'), (string) ($product->cost ?? '0'), 2);
    $marginNegative = bccomp($margin, '0', 2) < 0;
@endphp

@section('content')
<style>
    .edit-piece-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .edit-piece-page .alert {
        margin-bottom: 0;
    }

    .edit-piece-hero {
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

    .edit-piece-identity {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .edit-piece-thumb {
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

    .edit-piece-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .edit-piece-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.4rem;
    }

    .edit-piece-kicker > span:first-child {
        color: var(--color-dorado);
        font-size: 0.69rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .edit-piece-title {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.55rem;
        line-height: 1.15;
    }

    .edit-piece-subtitle {
        margin: 0.3rem 0 0;
        color: var(--color-piedra);
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .edit-piece-subtitle strong {
        color: var(--color-noche);
        font-weight: 600;
    }

    .edit-piece-hero-side {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        flex-shrink: 0;
    }

    .edit-piece-metrics {
        display: flex;
        gap: 1.15rem;
        padding-right: 1.2rem;
        border-right: 1px solid var(--color-linea);
    }

    .edit-piece-metric span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .edit-piece-metric strong {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.28rem;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .edit-piece-metric small {
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .edit-piece-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .edit-piece-layout {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1.5fr) minmax(290px, 0.5fr);
        align-items: stretch;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .edit-piece-form-card {
        min-height: 0;
        padding: 1.5rem;
        margin-bottom: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .edit-piece-form-card::-webkit-scrollbar,
    .edit-piece-aside::-webkit-scrollbar {
        width: 8px;
    }

    .edit-piece-form-card::-webkit-scrollbar-thumb,
    .edit-piece-aside::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(18, 22, 31, 0.16);
    }

    .edit-piece-form-card::-webkit-scrollbar-track,
    .edit-piece-aside::-webkit-scrollbar-track {
        background: transparent;
    }

    .edit-piece-form-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .edit-piece-form-head h2 {
        margin: 0 0 0.2rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
    }

    .edit-piece-form-head p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .edit-piece-badge {
        display: inline-flex;
        align-items: center;
        flex-shrink: 0;
        padding: 0.3rem 0.6rem;
        border-radius: 999px;
        background: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .edit-piece-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 1.15rem;
        margin-top: 1.35rem;
        border-top: 1px solid var(--color-linea);
    }

    .edit-piece-form-actions p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.74rem;
        line-height: 1.4;
    }

    .edit-piece-form-buttons {
        display: flex;
        flex-shrink: 0;
        gap: 0.6rem;
    }

    .edit-piece-aside {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .edit-piece-aside .card {
        padding: 1.25rem;
        margin-bottom: 0;
    }

    .edit-piece-aside-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.7rem;
    }

    .edit-piece-aside-head h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.02rem;
    }

    .edit-piece-side-list {
        display: flex;
        flex-direction: column;
    }

    .edit-piece-side-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.55rem 0;
        border-bottom: 1px solid rgba(228, 223, 211, 0.65);
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .edit-piece-side-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .edit-piece-side-row strong {
        color: var(--color-noche);
        font-size: 0.83rem;
        text-align: right;
    }

    .edit-piece-side-row .num {
        font-variant-numeric: tabular-nums;
    }

    .edit-piece-saved-note {
        margin: 0.7rem 0 0;
        color: var(--color-piedra);
        font-size: 0.7rem;
        line-height: 1.45;
    }

    .edit-piece-checklist {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .edit-piece-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
    }

    .edit-piece-checklist-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        border-radius: 50%;
        background: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
    }

    .edit-piece-checklist strong {
        display: block;
        margin-bottom: 0.08rem;
        color: var(--color-noche);
        font-size: 0.79rem;
    }

    .edit-piece-checklist small {
        display: block;
        color: var(--color-piedra);
        font-size: 0.73rem;
        line-height: 1.45;
    }

    .edit-piece-stock-note {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-bottom: 0.85rem;
    }

    .edit-piece-stock-note > span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        flex-shrink: 0;
        border-radius: 50%;
        background: rgba(184, 154, 94, 0.13);
        color: var(--color-dorado);
    }

    .edit-piece-stock-note strong {
        display: block;
        margin-bottom: 0.1rem;
        color: var(--color-noche);
        font-size: 0.81rem;
    }

    .edit-piece-stock-note p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.74rem;
        line-height: 1.45;
    }

    .edit-piece-stock-link {
        width: 100%;
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

        .edit-piece-page {
            overflow: visible;
        }

        .edit-piece-layout {
            overflow: visible;
        }

        .edit-piece-form-card,
        .edit-piece-aside {
            overflow: visible;
        }

        .edit-piece-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .edit-piece-hero-side {
            width: 100%;
            justify-content: space-between;
            padding-top: 0.9rem;
            border-top: 1px solid var(--color-linea);
        }

        .edit-piece-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .edit-piece-aside {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .edit-piece-aside > .edit-piece-stock-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .edit-piece-identity {
            align-items: flex-start;
        }

        .edit-piece-hero-side,
        .edit-piece-form-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        .edit-piece-metrics {
            width: 100%;
            padding-right: 0;
            border-right: 0;
        }

        .edit-piece-hero-actions {
            width: 100%;
        }

        .edit-piece-hero-actions .btn {
            flex: 1;
        }

        .edit-piece-aside {
            grid-template-columns: minmax(0, 1fr);
        }

        .edit-piece-form-buttons {
            width: 100%;
        }

        .edit-piece-form-buttons .btn {
            flex: 1;
        }
    }
</style>

<div class="edit-piece-page">
    <header class="edit-piece-hero">
        <div class="edit-piece-identity">
            <div class="edit-piece-thumb">
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
                <div class="edit-piece-kicker">
                    <span>Inventario · Edición</span>
                    <span class="badge {{ $statusBadge }}">{{ $product->status?->label() ?? 'Sin estado' }}</span>
                    @if ($isUsed)
                        <span class="badge badge-gold">Ocasión</span>
                    @endif
                    @if ($product->is_featured)
                        <span class="badge badge-info">Destacada</span>
                    @endif
                </div>
                <h1 class="edit-piece-title">{{ $product->name }}</h1>
                <p class="edit-piece-subtitle">
                    <strong>SKU</strong> {{ $product->sku }} &middot;
                    <strong>Categoría</strong> {{ $categoryLabel }} &middot;
                    <strong>Ubicación</strong> {{ $locationLabel }}
                </p>
            </div>
        </div>

        <div class="edit-piece-hero-side">
            <div class="edit-piece-metrics">
                <div class="edit-piece-metric">
                    <span>Stock</span>
                    <strong>{{ (int) $stock }}</strong>
                    <small>{{ $stockNoun }}</small>
                </div>
                <div class="edit-piece-metric">
                    <span>Precio</span>
                    <strong>$ {{ number_format((float) $product->price, 2) }}</strong>
                </div>
            </div>

            <div class="edit-piece-hero-actions">
                <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline">Ver detalle</a>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Volver</a>
            </div>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="edit-piece-layout">
        <div class="card edit-piece-form-card">
            <div class="edit-piece-form-head">
                <div>
                    <h2>Ficha de la pieza</h2>
                    <p>Actualiza los datos comerciales y de control. Los campos con * son obligatorios.</p>
                </div>
                <span class="edit-piece-badge">Edición</span>
            </div>

            <form action="{{ route('admin.inventory.update', $product) }}" method="POST" enctype="multipart/form-data" id="editPieceForm">
                @csrf
                @method('PUT')
                @include('admin.inventory._form')

                <div class="edit-piece-form-actions">
                    <p>Los cambios se reflejan en el inventario, el POS y el catálogo público.</p>
                    <div class="edit-piece-form-buttons">
                        <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline">Cancelar</a>
                        <button type="submit" class="btn btn-gold" id="editPieceSubmit">Guardar cambios</button>
                    </div>
                </div>
            </form>
        </div>

        <aside class="edit-piece-aside">
            <section class="card">
                <div class="edit-piece-aside-head">
                    <h2>Valores guardados</h2>
                    <span class="badge badge-muted">Actual</span>
                </div>

                <div class="edit-piece-side-list">
                    <div class="edit-piece-side-row">
                        <span>Estado</span>
                        <span class="badge {{ $statusBadge }}">{{ $product->status?->label() ?? 'Sin estado' }}</span>
                    </div>
                    <div class="edit-piece-side-row">
                        <span>Origen</span>
                        <strong>{{ $product->origin?->label() ?? 'Sin origen' }}</strong>
                    </div>
                    <div class="edit-piece-side-row">
                        <span>Pureza</span>
                        <strong>{{ $purityLabel }}</strong>
                    </div>
                    <div class="edit-piece-side-row">
                        <span>Stock</span>
                        <strong class="num">{{ (int) $stock }} {{ $stockNoun }}</strong>
                    </div>
                    <div class="edit-piece-side-row">
                        <span>Costo interno</span>
                        <strong class="num">$ {{ number_format((float) $product->cost, 2) }}</strong>
                    </div>
                    <div class="edit-piece-side-row">
                        <span>Margen estimado</span>
                        <strong class="num" style="{{ $marginNegative ? 'color: var(--color-error);' : '' }}">$ {{ number_format((float) $margin, 2) }}</strong>
                    </div>
                </div>

                <p class="edit-piece-saved-note">Estos valores cambian cuando guardas el formulario.</p>
            </section>

            <section class="card">
                <div class="edit-piece-aside-head">
                    <h2>Antes de guardar</h2>
                </div>

                <ul class="edit-piece-checklist">
                    <li>
                        <span class="edit-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>Estado y disponibilidad</strong>
                            <small>Cambiar el estado afecta si la pieza puede venderse en el POS.</small>
                        </span>
                    </li>
                    <li>
                        <span class="edit-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>Existencias</strong>
                            <small>El stock se corrige con un ajuste auditado, no desde esta ficha.</small>
                        </span>
                    </li>
                    <li>
                        <span class="edit-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>Fotografías</strong>
                            <small>Las imágenes nuevas se agregan; las actuales se conservan.</small>
                        </span>
                    </li>
                </ul>
            </section>

            @can('inventory.adjust')
                <section class="card edit-piece-stock-card">
                    <div class="edit-piece-stock-note">
                        <span aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 7 9 18l-5-5" />
                            </svg>
                        </span>
                        <div>
                            <strong>¿Necesitas corregir el stock?</strong>
                            <p>Registra un alta o baja con motivo. El movimiento quedará en el historial de la pieza.</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.inventory.adjust', $product) }}" class="btn btn-outline btn-sm edit-piece-stock-link">Ajustar existencias</a>
                </section>
            @endcan
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('editPieceForm');
        if (!form) {
            return;
        }

        form.addEventListener('submit', () => {
            const submit = document.getElementById('editPieceSubmit');
            submit.disabled = true;
            submit.textContent = 'Guardando…';
        });
    })();
</script>
@endpush
