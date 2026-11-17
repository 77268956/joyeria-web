@extends('layouts.admin')

@section('title', 'Nuevo Lote')

@section('body_class', 'full-viewport')

@php
    $puritiesByMetal = $metals->mapWithKeys(function ($metal) {
        return [$metal->id => $metal->purities->map(fn ($purity) => [
            'id' => $purity->id,
            'code' => $purity->code,
            'label' => $purity->label,
        ])->values()];
    });
@endphp

@section('content')
<style>
    .lot-create-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .lot-create-page .alert {
        margin-bottom: 0;
    }

    .lot-create-hero {
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

    .lot-create-identity {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .lot-create-thumb {
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

    .lot-create-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.4rem;
    }

    .lot-create-kicker > span:first-child {
        color: var(--color-dorado);
        font-size: 0.69rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .lot-create-title {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.55rem;
        line-height: 1.15;
    }

    .lot-create-subtitle {
        margin: 0.3rem 0 0;
        color: var(--color-piedra);
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .lot-create-hero-side {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        flex-shrink: 0;
    }

    .lot-create-metrics {
        display: flex;
        gap: 1.15rem;
        padding-right: 1.2rem;
        border-right: 1px solid var(--color-linea);
    }

    .lot-create-metric span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .lot-create-metric strong {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.2rem;
        line-height: 1.1;
        white-space: nowrap;
    }

    .lot-create-metric small {
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .lot-create-layout {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1.45fr) minmax(290px, 0.55fr);
        align-items: stretch;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .lot-create-form-card {
        min-height: 0;
        padding: 1.5rem;
        margin-bottom: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .lot-create-form-card::-webkit-scrollbar,
    .lot-create-aside::-webkit-scrollbar {
        width: 8px;
    }

    .lot-create-form-card::-webkit-scrollbar-thumb,
    .lot-create-aside::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(18, 22, 31, 0.16);
    }

    .lot-create-form-card::-webkit-scrollbar-track,
    .lot-create-aside::-webkit-scrollbar-track {
        background: transparent;
    }

    .lot-create-form-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .lot-create-form-head h2 {
        margin: 0 0 0.2rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
    }

    .lot-create-form-head p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .lot-create-badge {
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

    .lot-form-section {
        padding-bottom: 1.35rem;
        margin-bottom: 1.35rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .lot-form-section:last-of-type {
        padding-bottom: 0;
        margin-bottom: 0;
        border-bottom: 0;
    }

    .lot-form-section-head {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-bottom: 0.9rem;
    }

    .lot-form-section-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
        font-size: 0.68rem;
        font-weight: 700;
    }

    .lot-form-section-head h3 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.03rem;
        line-height: 1.3;
    }

    .lot-form-section-head p {
        margin: 0.1rem 0 0;
        color: var(--color-piedra);
        font-size: 0.75rem;
        line-height: 1.45;
    }

    .lot-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .lot-form-grid .form-group {
        margin-bottom: 0;
    }

    .lot-form-grid .form-label {
        font-size: 0.7rem;
    }

    .lot-form-grid .form-control,
    .lot-form-grid .form-select {
        height: 40px;
        font-size: 0.86rem;
    }

    .lot-form-grid textarea.form-control {
        height: auto;
        min-height: 82px;
        resize: vertical;
    }

    .lot-form-grid .form-hint {
        display: block;
        margin-top: 0.28rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
        line-height: 1.4;
    }

    .lot-form-grid .form-group--full {
        grid-column: 1 / -1;
    }

    .lot-create-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 1.15rem;
        margin-top: 1.35rem;
        border-top: 1px solid var(--color-linea);
    }

    .lot-create-form-actions p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.74rem;
        line-height: 1.4;
    }

    .lot-create-form-buttons {
        display: flex;
        flex-shrink: 0;
        gap: 0.6rem;
    }

    .lot-create-aside {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .lot-create-aside .card {
        padding: 1.25rem;
        margin-bottom: 0;
    }

    .lot-create-aside-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .lot-create-aside-head h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.02rem;
    }

    .lot-preview-list {
        display: flex;
        flex-direction: column;
        gap: 0;
        margin: 0;
    }

    .lot-preview-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.48rem 0;
        border-bottom: 1px dashed var(--color-linea);
    }

    .lot-preview-row:last-child {
        border-bottom: 0;
    }

    .lot-preview-row dt {
        flex-shrink: 0;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .lot-preview-row dd {
        margin: 0;
        overflow: hidden;
        color: var(--color-noche);
        font-size: 0.78rem;
        font-weight: 600;
        text-align: right;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lot-preview-weight {
        color: var(--color-dorado) !important;
        font-family: var(--font-serif);
        font-size: 1.1rem !important;
        font-variant-numeric: tabular-nums;
    }

    .lot-checklist {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .lot-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
    }

    .lot-checklist-icon {
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

    .lot-checklist strong {
        display: block;
        margin-bottom: 0.08rem;
        color: var(--color-noche);
        font-size: 0.79rem;
    }

    .lot-checklist small {
        display: block;
        color: var(--color-piedra);
        font-size: 0.73rem;
        line-height: 1.45;
    }

    .lot-create-tip {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
    }

    .lot-create-tip > span {
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

    .lot-create-tip strong {
        display: block;
        margin-bottom: 0.1rem;
        color: var(--color-noche);
        font-size: 0.81rem;
    }

    .lot-create-tip p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.74rem;
        line-height: 1.45;
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

        .lot-create-page,
        .lot-create-layout {
            overflow: visible;
        }

        .lot-create-form-card,
        .lot-create-aside {
            overflow: visible;
        }

        .lot-create-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .lot-create-hero-side {
            width: 100%;
            justify-content: space-between;
            padding-top: 0.9rem;
            border-top: 1px solid var(--color-linea);
        }

        .lot-create-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .lot-create-aside {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lot-create-aside > .lot-create-tip-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .lot-create-identity {
            align-items: flex-start;
        }

        .lot-create-hero-side,
        .lot-create-form-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        .lot-create-metrics {
            width: 100%;
            padding-right: 0;
            border-right: 0;
        }

        .lot-form-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .lot-create-aside {
            grid-template-columns: minmax(0, 1fr);
        }

        .lot-create-form-buttons {
            width: 100%;
        }

        .lot-create-form-buttons .btn {
            flex: 1;
        }
    }
</style>

<div class="lot-create-page">
    <header class="lot-create-hero">
        <div class="lot-create-identity">
            <div class="lot-create-thumb">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                    <path d="m12 3 9 5-9 5-9-5 9-5Z"></path>
                    <path d="m3 12 9 5 9-5"></path>
                    <path d="m3 16 9 5 9-5"></path>
                </svg>
            </div>

            <div style="min-width: 0;">
                <div class="lot-create-kicker">
                    <span>Materia prima · Alta</span>
                    <span class="badge badge-success">Lote abierto</span>
                    <span class="badge badge-gold">Entrada automática</span>
                </div>
                <h1 class="lot-create-title">Nuevo Lote</h1>
                <p class="lot-create-subtitle">Registra un lote de metal por peso. El peso inicial se asienta automáticamente como movimiento de entrada.</p>
            </div>
        </div>

        <div class="lot-create-hero-side">
            <div class="lot-create-metrics">
                <div class="lot-create-metric">
                    <span>Estado inicial</span>
                    <strong>Abierto</strong>
                    <small>listo para ajustes</small>
                </div>
                <div class="lot-create-metric">
                    <span>Movimiento</span>
                    <strong>Entrada</strong>
                    <small>peso inicial</small>
                </div>
            </div>

            <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Volver a lotes</a>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="lot-create-layout">
        <div class="card lot-create-form-card">
            <div class="lot-create-form-head">
                <div>
                    <h2>Datos del lote</h2>
                    <p>Identifica el metal, registra el peso real de entrada y deja la ubicación física del material.</p>
                </div>
                <span class="lot-create-badge">Alta</span>
            </div>

            <form action="{{ route('admin.inventory.lots.store') }}" method="POST" id="lotCreateForm">
                @csrf

                <section class="lot-form-section">
                    <div class="lot-form-section-head">
                        <span class="lot-form-section-number">01</span>
                        <div>
                            <h3>Metal y pureza</h3>
                            <p>La pureza disponible se filtra según el metal seleccionado.</p>
                        </div>
                    </div>

                    <div class="lot-form-grid">
                        <div class="form-group">
                            <label class="form-label" for="metal_id">Metal *</label>
                            <select name="metal_id" id="metal_id" class="form-select" required>
                                <option value="">Selecciona…</option>
                                @foreach ($metals as $metal)
                                    <option value="{{ $metal->id }}" {{ (string) old('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="purity_id">Pureza *</label>
                            <select name="purity_id" id="purity_id" class="form-select" required>
                                <option value="">Selecciona…</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="lot-form-section">
                    <div class="lot-form-section-head">
                        <span class="lot-form-section-number">02</span>
                        <div>
                            <h3>Peso y ubicación</h3>
                            <p>Usa el peso neto real del material que entra al inventario.</p>
                        </div>
                    </div>

                    <div class="lot-form-grid">
                        <div class="form-group">
                            <label class="form-label" for="initial_weight_g">Peso inicial (g) *</label>
                            <input type="number" step="0.001" min="0.001" name="initial_weight_g" id="initial_weight_g" value="{{ old('initial_weight_g') }}" class="form-control" placeholder="Ej. 40.500" required>
                            <span class="form-hint">Se registrará como el primer movimiento del lote.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="location_id">Ubicación</label>
                            <select name="location_id" id="location_id" class="form-select">
                                <option value="">Sin ubicación</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" {{ (string) old('location_id') === (string) $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section class="lot-form-section">
                    <div class="lot-form-section-head">
                        <span class="lot-form-section-number">03</span>
                        <div>
                            <h3>Identificación y notas</h3>
                            <p>El código es opcional; si lo dejas vacío se genera automáticamente.</p>
                        </div>
                    </div>

                    <div class="lot-form-grid">
                        <div class="form-group form-group--full">
                            <label class="form-label" for="code">Código del lote</label>
                            <input type="text" name="code" id="code" value="{{ old('code') }}" class="form-control" maxlength="40" placeholder="Se genera automáticamente si se deja vacío">
                        </div>

                        <div class="form-group form-group--full">
                            <label class="form-label" for="notes">Notas</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Proveedor, procedencia o cualquier detalle útil para el lote">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </section>

                <div class="lot-create-form-actions">
                    <p>El lote quedará abierto y podrás ajustar su peso con motivos auditados.</p>
                    <div class="lot-create-form-buttons">
                        <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Cancelar</a>
                        <button type="submit" class="btn btn-gold" id="lotCreateSubmit">Registrar Lote</button>
                    </div>
                </div>
            </form>
        </div>

        <aside class="lot-create-aside">
            <section class="card">
                <div class="lot-create-aside-head">
                    <h2>Resumen del lote</h2>
                    <span class="badge badge-success">Abierto</span>
                </div>

                <dl class="lot-preview-list">
                    <div class="lot-preview-row">
                        <dt>Metal</dt>
                        <dd data-lot-preview="metal">—</dd>
                    </div>
                    <div class="lot-preview-row">
                        <dt>Pureza</dt>
                        <dd data-lot-preview="purity">—</dd>
                    </div>
                    <div class="lot-preview-row">
                        <dt>Peso inicial</dt>
                        <dd class="lot-preview-weight" data-lot-preview="weight">— g</dd>
                    </div>
                    <div class="lot-preview-row">
                        <dt>Ubicación</dt>
                        <dd data-lot-preview="location">Sin ubicación</dd>
                    </div>
                    <div class="lot-preview-row">
                        <dt>Código</dt>
                        <dd data-lot-preview="code">Automático</dd>
                    </div>
                </dl>
            </section>

            <section class="card">
                <div class="lot-create-aside-head">
                    <h2>Al registrar el lote</h2>
                    <span class="badge badge-gold">Automático</span>
                </div>

                <ul class="lot-checklist">
                    <li>
                        <span class="lot-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6"></path>
                            </svg>
                        </span>
                        <span>
                            <strong>Movimiento de entrada</strong>
                            <small>El peso inicial queda registrado en el historial inmutable.</small>
                        </span>
                    </li>
                    <li>
                        <span class="lot-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6"></path>
                            </svg>
                        </span>
                        <span>
                            <strong>Estado abierto</strong>
                            <small>Podrás sumar o restar peso con un motivo obligatorio.</small>
                        </span>
                    </li>
                    <li>
                        <span class="lot-checklist-icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6"></path>
                            </svg>
                        </span>
                        <span>
                            <strong>Código único</strong>
                            <small>Si no escribes uno, el sistema genera el siguiente código disponible.</small>
                        </span>
                    </li>
                </ul>
            </section>

            <section class="card lot-create-tip-card">
                <div class="lot-create-tip">
                    <span aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 8h.01M11 12h1v4h1"></path>
                        </svg>
                    </span>
                    <div>
                        <strong>Antes de guardar</strong>
                        <p>Verifica que el peso corresponda al metal neto y que la pureza sea la correcta. Un error aquí afecta todo el historial del lote.</p>
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
        const puritiesByMetal = @json($puritiesByMetal);
        const initialPurity = @json((string) old('purity_id', ''));

        const metalSelect = document.getElementById('metal_id');
        const puritySelect = document.getElementById('purity_id');
        const weightInput = document.getElementById('initial_weight_g');
        const locationSelect = document.getElementById('location_id');
        const codeInput = document.getElementById('code');
        const form = document.getElementById('lotCreateForm');

        const preview = {
            metal: document.querySelector('[data-lot-preview="metal"]'),
            purity: document.querySelector('[data-lot-preview="purity"]'),
            weight: document.querySelector('[data-lot-preview="weight"]'),
            location: document.querySelector('[data-lot-preview="location"]'),
            code: document.querySelector('[data-lot-preview="code"]'),
        };

        const selectedText = (select, fallback) => {
            if (!select || select.selectedIndex < 0) {
                return fallback;
            }

            return select.options[select.selectedIndex].textContent.trim() || fallback;
        };

        const updatePreview = () => {
            preview.metal.textContent = selectedText(metalSelect, '—');
            preview.purity.textContent = selectedText(puritySelect, '—');
            preview.location.textContent = selectedText(locationSelect, 'Sin ubicación');
            preview.weight.textContent = weightInput.value.trim() === '' ? '— g' : `${weightInput.value.trim()} g`;
            preview.code.textContent = codeInput.value.trim() === '' ? 'Automático' : codeInput.value.trim();
        };

        const syncPurities = (keepSelection) => {
            const metalId = metalSelect.value;
            const purities = puritiesByMetal[metalId] || [];
            const current = keepSelection ? initialPurity : '';

            puritySelect.innerHTML = '<option value="">Selecciona…</option>';

            purities.forEach((purity) => {
                const option = document.createElement('option');
                option.value = purity.id;
                option.textContent = purity.label || purity.code;

                if (String(purity.id) === String(current)) {
                    option.selected = true;
                }

                puritySelect.appendChild(option);
            });

            updatePreview();
        };

        metalSelect.addEventListener('change', () => syncPurities(false));
        puritySelect.addEventListener('change', updatePreview);
        weightInput.addEventListener('input', updatePreview);
        locationSelect.addEventListener('change', updatePreview);
        codeInput.addEventListener('input', updatePreview);

        form.addEventListener('submit', () => {
            const submit = document.getElementById('lotCreateSubmit');
            submit.disabled = true;
            submit.textContent = 'Registrando…';
        });

        syncPurities(true);
        updatePreview();
    })();
</script>
@endpush
