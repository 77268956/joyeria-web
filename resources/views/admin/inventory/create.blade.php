@extends('layouts.admin')

@section('title', 'Nueva pieza')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .create-piece-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .create-piece-page .alert {
        margin-bottom: 0;
    }

    .create-piece-hero {
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

    .create-piece-identity {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .create-piece-thumb {
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

    .create-piece-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.4rem;
    }

    .create-piece-kicker > span:first-child {
        color: var(--color-dorado);
        font-size: 0.69rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .create-piece-title {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.55rem;
        line-height: 1.15;
    }

    .create-piece-subtitle {
        margin: 0.3rem 0 0;
        color: var(--color-piedra);
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .create-piece-hero-side {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        flex-shrink: 0;
    }

    .create-piece-metrics {
        display: flex;
        gap: 1.15rem;
        padding-right: 1.2rem;
        border-right: 1px solid var(--color-linea);
    }

    .create-piece-metric span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .create-piece-metric strong {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.28rem;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .create-piece-metric small {
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .create-piece-layout {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1.5fr) minmax(290px, 0.5fr);
        align-items: stretch;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .create-piece-form-card {
        min-height: 0;
        padding: 1.5rem;
        margin-bottom: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .create-piece-form-card::-webkit-scrollbar,
    .create-piece-aside::-webkit-scrollbar {
        width: 8px;
    }

    .create-piece-form-card::-webkit-scrollbar-thumb,
    .create-piece-aside::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(18, 22, 31, 0.16);
    }

    .create-piece-form-card::-webkit-scrollbar-track,
    .create-piece-aside::-webkit-scrollbar-track {
        background: transparent;
    }

    .create-piece-form-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .create-piece-form-head h2 {
        margin: 0 0 0.2rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
    }

    .create-piece-form-head p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .create-piece-badge {
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

    .create-piece-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 1.15rem;
        margin-top: 1.35rem;
        border-top: 1px solid var(--color-linea);
    }

    .create-piece-form-actions p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.74rem;
        line-height: 1.4;
    }

    .create-piece-form-buttons {
        display: flex;
        flex-shrink: 0;
        gap: 0.6rem;
    }

    .create-piece-aside {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .create-piece-aside .card {
        padding: 1.25rem;
        margin-bottom: 0;
    }

    .create-piece-aside-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.7rem;
    }

    .create-piece-aside-head h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.02rem;
    }

    .create-piece-checklist {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .create-piece-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
    }

    .create-piece-checklist-icon {
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

    .create-piece-checklist strong {
        display: block;
        margin-bottom: 0.08rem;
        color: var(--color-noche);
        font-size: 0.79rem;
    }

    .create-piece-checklist small {
        display: block;
        color: var(--color-piedra);
        font-size: 0.73rem;
        line-height: 1.45;
    }

    .create-piece-note {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-bottom: 0.85rem;
    }

    .create-piece-note > span {
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

    .create-piece-note strong {
        display: block;
        margin-bottom: 0.1rem;
        color: var(--color-noche);
        font-size: 0.81rem;
    }

    .create-piece-note p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.74rem;
        line-height: 1.45;
    }

    .create-piece-link {
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

        .create-piece-page {
            overflow: visible;
        }

        .create-piece-layout {
            overflow: visible;
        }

        .create-piece-form-card,
        .create-piece-aside {
            overflow: visible;
        }

        .create-piece-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .create-piece-hero-side {
            width: 100%;
            justify-content: space-between;
            padding-top: 0.9rem;
            border-top: 1px solid var(--color-linea);
        }

        .create-piece-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .create-piece-aside {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .create-piece-aside > .create-piece-purchase-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .create-piece-identity {
            align-items: flex-start;
        }

        .create-piece-hero-side,
        .create-piece-form-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        .create-piece-metrics {
            width: 100%;
            padding-right: 0;
            border-right: 0;
        }

        .create-piece-aside {
            grid-template-columns: minmax(0, 1fr);
        }

        .create-piece-form-buttons {
            width: 100%;
        }

        .create-piece-form-buttons .btn {
            flex: 1;
        }
    }
</style>

<div class="create-piece-page">
    <header class="create-piece-hero">
        <div class="create-piece-identity">
            <div class="create-piece-thumb">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                    <path d="M6 3h12l3 6-9 12L3 9l3-6Z" />
                    <path d="M3 9h18M9 3l3 18M15 3l-3 18" />
                </svg>
            </div>

            <div style="min-width: 0;">
                <div class="create-piece-kicker">
                    <span>Inventario · Alta</span>
                    <span class="badge badge-success">Nueva pieza</span>
                    <span class="badge badge-gold">Entrada automática</span>
                </div>
                <h1 class="create-piece-title">Nueva pieza</h1>
                <p class="create-piece-subtitle">Registra una pieza nueva en el catálogo interno. Al guardarla se crea su entrada de inventario y queda lista para el POS.</p>
            </div>
        </div>

        <div class="create-piece-hero-side">
            <div class="create-piece-metrics">
                <div class="create-piece-metric">
                    <span>Movimiento</span>
                    <strong>+1</strong>
                    <small>entrada de inventario</small>
                </div>
                <div class="create-piece-metric">
                    <span>Estado inicial</span>
                    <strong>Disponible</strong>
                    <small>lista para vender</small>
                </div>
            </div>

            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Volver al inventario</a>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="create-piece-layout">
        <div class="card create-piece-form-card">
            <div class="create-piece-form-head">
                <div>
                    <h2>Datos de la pieza</h2>
                    <p>Completa la ficha comercial y de control. Los campos con * son obligatorios.</p>
                </div>
                <span class="create-piece-badge">Alta</span>
            </div>

            <form action="{{ route('admin.inventory.store') }}" method="POST" enctype="multipart/form-data" id="createPieceForm">
                @csrf
                @include('admin.inventory._form')

                <div class="create-piece-form-actions">
                    <p>La pieza se registrará con una entrada inmutable en el historial de inventario.</p>
                    <div class="create-piece-form-buttons">
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Cancelar</a>
                        <button type="submit" class="btn btn-gold" id="createPieceSubmit">Registrar pieza</button>
                    </div>
                </div>
            </form>
        </div>

        <aside class="create-piece-aside">
            <section class="card">
                <div class="create-piece-aside-head">
                    <h2>Al registrar la pieza</h2>
                    <span class="badge badge-success">Automático</span>
                </div>

                <ul class="create-piece-checklist">
                    <li>
                        <span class="create-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>Entrada de inventario</strong>
                            <small>Se registra un movimiento de alta con cantidad +1.</small>
                        </span>
                    </li>
                    <li>
                        <span class="create-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>SKU y código de barras</strong>
                            <small>Si los dejas vacíos, el sistema los genera automáticamente.</small>
                        </span>
                    </li>
                    <li>
                        <span class="create-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>Disponibilidad</strong>
                            <small>La pieza queda disponible para el POS y, si es pública, para el catálogo web.</small>
                        </span>
                    </li>
                </ul>
            </section>

            <section class="card">
                <div class="create-piece-aside-head">
                    <h2>Antes de guardar</h2>
                </div>

                <ul class="create-piece-checklist">
                    <li>
                        <span class="create-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>Identificación clara</strong>
                            <small>Usa un nombre descriptivo y verifica categoría, metal y pureza.</small>
                        </span>
                    </li>
                    <li>
                        <span class="create-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>Peso y precio correctos</strong>
                            <small>El costo es interno; el precio de venta se usa en el POS y el catálogo.</small>
                        </span>
                    </li>
                    <li>
                        <span class="create-piece-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </span>
                        <span>
                            <strong>Fotografías</strong>
                            <small>Puedes adjuntar hasta 6 imágenes JPG, PNG o WebP de máximo 4 MB.</small>
                        </span>
                    </li>
                </ul>
            </section>

            <section class="card create-piece-purchase-card">
                <div class="create-piece-note">
                    <span aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 8h.01M11 12h1v4h1" />
                        </svg>
                    </span>
                    <div>
                        <strong>¿La pieza viene de un cliente?</strong>
                        <p>Si fue comprada como pieza de ocasión, regístrala desde Compra de metales para conservar la trazabilidad, la caja y el origen usado.</p>
                    </div>
                </div>

                <a href="{{ route('admin.purchases.create') }}" class="btn btn-outline btn-sm create-piece-link">Ir a compra de metales</a>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('createPieceForm');
        if (!form) {
            return;
        }

        form.addEventListener('submit', () => {
            const submit = document.getElementById('createPieceSubmit');
            submit.disabled = true;
            submit.textContent = 'Registrando…';
        });
    })();
</script>
@endpush
