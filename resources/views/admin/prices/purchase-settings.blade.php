@extends('layouts.admin')

@section('title', 'Descuentos de compra')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .purchase-page {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .purchase-body {
        display: flex;
        gap: 0.75rem;
        flex: 1;
        min-height: 0;
    }

    .purchase-form {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.75rem;
        min-width: 0;
        min-height: 0;
    }

    .purchase-metals {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        flex: 1;
        min-height: 0;
        align-content: start;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .purchase-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.75rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
        flex-shrink: 0;
    }

    .purchase-identity {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
    }

    .purchase-thumb {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .purchase-kicker {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.45rem;
        color: var(--color-piedra);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .purchase-title {
        margin: 0.15rem 0 0;
        color: var(--color-noche);
        font-size: 1.25rem;
        font-weight: 500;
        line-height: 1.15;
    }

    .purchase-subtitle {
        margin: 0.2rem 0 0;
        max-width: 72ch;
        color: var(--color-piedra);
        font-size: 0.78rem;
        line-height: 1.4;
    }

    .purchase-hero-actions {
        display: flex;
        flex-shrink: 0;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .purchase-guide {
        display: flex;
        flex: 0 0 200px;
        flex-direction: column;
        gap: 0.55rem;
        min-height: 0;
    }

    .purchase-guide-step {
        display: flex;
        flex: 0 0 auto;
        flex-direction: column;
        gap: 0.25rem;
        min-height: 0;
        padding: 0.6rem 0.7rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 5px;
    }

    .purchase-guide-head {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .purchase-guide-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .purchase-guide-step strong {
        color: var(--color-noche);
        font-size: 0.76rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .purchase-guide-step > span {
        display: block;
        margin-left: calc(28px + 0.6rem);
        color: var(--color-piedra);
        font-size: 0.7rem;
        line-height: 1.35;
    }

    .purchase-metal-card {
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: 0;
        overflow: hidden;
    }

    .purchase-metal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 0.6rem 1rem;
        background: linear-gradient(180deg, #FCFBF8 0%, var(--color-blanco) 100%);
        border-bottom: 1px solid var(--color-linea);
    }

    .purchase-metal-identity {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        min-width: 0;
    }

    .purchase-metal-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .purchase-metal-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .purchase-metal-title-row h2 {
        margin: 0;
        color: var(--color-noche);
        font-size: 1rem;
        font-weight: 500;
    }

    .purchase-metal-note {
        margin: 0.15rem 0 0;
        color: var(--color-piedra);
        font-size: 0.72rem;
        line-height: 1.35;
    }

    .purchase-metal-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.3rem;
        margin-top: 0.25rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
    }

    .purchase-metal-meta strong {
        color: var(--color-noche);
        font-weight: 600;
    }

    .purchase-metal-price {
        flex-shrink: 0;
        text-align: right;
    }

    .purchase-metal-price > span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.58rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .purchase-metal-price strong {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-size: 0.95rem;
        font-weight: 600;
    }

    .purchase-metal-price strong small {
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 500;
    }

    .purchase-metal-price > small {
        display: block;
        margin-top: 0.05rem;
        color: var(--color-piedra);
        font-size: 0.64rem;
    }

    .purchase-table {
        width: 100%;
        border-collapse: collapse;
    }

    .purchase-table th {
        padding: 0.45rem 1rem;
        background: #FCFBF8;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-piedra);
        font-size: 0.6rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-align: left;
        text-transform: uppercase;
    }

    .purchase-table th:last-child {
        text-align: right;
    }

    .purchase-table td {
        padding: 0.45rem 1rem;
        border-bottom: 1px solid var(--color-linea);
        vertical-align: middle;
    }

    .purchase-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .purchase-default-row {
        background: #FCFBF8;
    }

    .purchase-default-label {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.45rem;
        color: var(--color-noche);
        font-weight: 600;
    }

    .purchase-default-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--color-dorado);
    }

    .purchase-purity {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .purchase-purity-dot {
        flex-shrink: 0;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--color-linea);
    }

    .purchase-purity-name {
        color: var(--color-noche);
        font-size: 0.84rem;
        font-weight: 600;
    }

    .purchase-purity-code {
        margin-top: 0.05rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .purchase-discount {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.45rem;
    }

    .purchase-discount .form-control {
        width: 110px;
        height: 34px;
        padding: 0 0.5rem;
        font-size: 0.82rem;
        text-align: right;
    }

    .purchase-discount-suffix {
        color: var(--color-piedra);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .purchase-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.6rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        flex-shrink: 0;
    }

    .purchase-footer-note {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        max-width: 62ch;
        color: var(--color-piedra);
        font-size: 0.72rem;
        line-height: 1.4;
    }

    .purchase-footer-note svg {
        flex-shrink: 0;
        margin-top: 0.1rem;
        color: var(--color-dorado);
    }

    .purchase-footer-actions {
        display: flex;
        flex-shrink: 0;
        gap: 0.75rem;
    }

    .purchase-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        padding: 2rem 1.5rem;
        text-align: center;
    }

    .purchase-empty strong {
        color: var(--color-noche);
        font-size: 0.95rem;
    }

    .purchase-empty p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.82rem;
    }

    @media (max-width: 1300px) {
        .purchase-body {
            flex-direction: column;
        }

        .purchase-guide {
            display: grid;
            flex: 0 0 auto;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .purchase-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .purchase-guide {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
        }

        .purchase-metals {
            grid-template-columns: minmax(0, 1fr);
        }

        .purchase-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .purchase-footer-actions .btn {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 700px) {
        .purchase-metal-head {
            flex-direction: column;
        }

        .purchase-metal-price {
            text-align: left;
        }

        .purchase-table thead {
            display: none;
        }

        .purchase-table,
        .purchase-table tbody,
        .purchase-table tr,
        .purchase-table td {
            display: block;
            width: 100%;
        }

        .purchase-table tr {
            padding: 0.7rem 1rem;
            border-bottom: 1px solid var(--color-linea);
        }

        .purchase-table tbody tr:last-child {
            border-bottom: 0;
        }

        .purchase-table td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.25rem 0;
            border: 0;
            text-align: right;
        }

        .purchase-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: var(--color-piedra);
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .purchase-table td:first-child::before {
            display: none;
        }

        .purchase-table td:first-child {
            display: block;
            text-align: left;
        }

        .purchase-discount {
            justify-content: flex-end;
        }
    }
</style>

<div class="purchase-page">
    <header class="purchase-hero">
        <div class="purchase-identity">
            <div class="purchase-thumb" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3h12l4 6-10 12L2 9l4-6Z" />
                    <path d="M2 9h20M12 3 8 9l4 12 4-12-4-6Z" />
                </svg>
            </div>

            <div style="min-width: 0;">
                <div class="purchase-kicker">
                    <span>Materia prima &middot; Compras</span>
                    <span class="badge badge-gold">Solo dueño</span>
                </div>
                <h1 class="purchase-title font-serif">Descuentos de compra</h1>
                <p class="purchase-subtitle">
                    Define cuánto retiene la tienda al comprar metal a un cliente. Cada metal y pureza puede tener su propio porcentaje; el valor por defecto cubre las piezas sin pureza registrada.
                </p>
            </div>
        </div>

        <div class="purchase-hero-actions">
            <a href="{{ route('admin.prices.index') }}" class="btn btn-outline">
                <span>&larr; Volver a precios</span>
            </a>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="purchase-body">
        <aside class="purchase-guide" aria-label="Cómo funcionan los descuentos de compra">
        <div class="purchase-guide-step">
            <div class="purchase-guide-head">
                <span class="purchase-guide-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v10M9.5 9.5c0-1 1.1-1.8 2.5-1.8s2.5.8 2.5 1.8-1.1 1.6-2.5 1.8-2.5.8-2.5 1.8 1.1 1.8 2.5 1.8 2.5-.8 2.5-1.8" />
                    </svg>
                </span>
                <strong>Precio vigente</strong>
            </div>
            <span>Se toma la cotización del metal por gramo.</span>
        </div>

        <div class="purchase-guide-step">
            <div class="purchase-guide-head">
                <span class="purchase-guide-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 5 5 19" />
                        <circle cx="7.5" cy="7.5" r="2.5" />
                        <circle cx="16.5" cy="16.5" r="2.5" />
                    </svg>
                </span>
                <strong>Descuento</strong>
            </div>
            <span>Porcentaje que retiene la tienda en la compra.</span>
        </div>

        <div class="purchase-guide-step">
            <div class="purchase-guide-head">
                <span class="purchase-guide-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 10h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8Z" />
                        <path d="M16 10V7a4 4 0 0 0-8 0v3" />
                        <path d="M12 14v2" />
                    </svg>
                </span>
                <strong>Oferta al cliente</strong>
            </div>
            <span>El resto se paga según el peso y la pureza.</span>
        </div>
        </aside>

        <form action="{{ route('admin.prices.purchase_settings.update') }}" method="POST" class="purchase-form">
            @csrf
            @method('PUT')

            <div class="purchase-metals">
                @forelse ($metals as $metal)
            @php
                $metalCurrent = $current[$metal->code] ?? null;
                $defaultSetting = $settings->get($metal->id.'-default');
                $defaultValue = old('discounts.'.$metal->id.'-default', $defaultSetting?->discount_percent);
            @endphp

            <article class="card purchase-metal-card">
                <header class="purchase-metal-head">
                    <div class="purchase-metal-identity">
                        <span class="purchase-metal-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 3h12l4 6-10 12L2 9l4-6Z" />
                                <path d="M2 9h20M12 3 8 9l4 12 4-12-4-6Z" />
                            </svg>
                        </span>
                        <div>
                            <div class="purchase-metal-title-row">
                                <h2 class="font-serif">{{ $metal->name }}</h2>
                                <span class="badge badge-gold">{{ $metal->code }}</span>
                            </div>
                            <p class="purchase-metal-note">El valor por defecto aplica cuando la pieza no tiene una pureza registrada.</p>
                            <div class="purchase-metal-meta">
                                <span>{{ $metal->purities->count() }} {{ $metal->purities->count() === 1 ? 'pureza' : 'purezas' }}</span>
                                <span aria-hidden="true">&middot;</span>
                                <span>Descuento por defecto: <strong data-purchase-summary="{{ $metal->id }}">{{ filled($defaultValue) ? $defaultValue.'%' : '—' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="purchase-metal-price">
                        <span>Precio vigente</span>
                        @if ($metalCurrent)
                            <strong>{{ $store->currency }} ${{ bcadd((string) $metalCurrent->price_per_gram, '0', 2) }}<small> / g</small></strong>
                            <small>{{ $metalCurrent->price_date?->format('d/m/Y') ?? $metalCurrent->created_at?->format('d/m/Y') }}</small>
                        @else
                            <strong class="muted">Sin precio</strong>
                            <small>Actualiza la cotización</small>
                        @endif
                    </div>
                </header>

                <table class="purchase-table">
                    <thead>
                        <tr>
                            <th>Pureza</th>
                            <th>Detalle</th>
                            <th>Descuento (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="purchase-default-row">
                            <td data-label="Pureza">
                                <div class="purchase-default-label">
                                    <span class="purchase-default-dot" aria-hidden="true"></span>
                                    <span>Valor por defecto</span>
                                    <span class="badge badge-gold">Predeterminado</span>
                                </div>
                            </td>
                            <td data-label="Detalle" class="muted">Se usa cuando la pieza no tiene pureza registrada.</td>
                            <td data-label="Descuento (%)">
                                <div class="purchase-discount">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        inputmode="decimal"
                                        name="discounts[{{ $metal->id }}-default]"
                                        value="{{ $defaultValue }}"
                                        class="form-control"
                                        placeholder="0.00"
                                        aria-label="Descuento por defecto para {{ $metal->name }}"
                                        data-purchase-default
                                        data-metal-id="{{ $metal->id }}"
                                    >
                                    <span class="purchase-discount-suffix">%</span>
                                </div>
                            </td>
                        </tr>

                        @foreach ($metal->purities as $purity)
                            @php
                                $setting = $settings->get($metal->id.'-'.$purity->id);
                                $purityValue = old('discounts.'.$metal->id.'-'.$purity->id, $setting?->discount_percent);
                                $finenessPct = $purity->fineness !== null
                                    ? rtrim(rtrim(bcmul((string) $purity->fineness, '100', 2), '0'), '.')
                                    : null;
                            @endphp
                            <tr>
                                <td data-label="Pureza">
                                    <div class="purchase-purity">
                                        <span class="purchase-purity-dot" aria-hidden="true"></span>
                                        <div>
                                            <div class="purchase-purity-name">{{ $purity->label ?: $purity->code }}</div>
                                            <div class="purchase-purity-code">{{ $purity->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Detalle">
                                    @if ($finenessPct)
                                        <span class="badge badge-info">Pureza {{ $finenessPct }}%</span>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td data-label="Descuento (%)">
                                    <div class="purchase-discount">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            inputmode="decimal"
                                            name="discounts[{{ $metal->id }}-{{ $purity->id }}]"
                                            value="{{ $purityValue }}"
                                            class="form-control"
                                            placeholder="0.00"
                                            aria-label="Descuento para {{ $metal->name }} {{ $purity->label ?: $purity->code }}"
                                        >
                                        <span class="purchase-discount-suffix">%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </article>
        @empty
            <div class="card purchase-empty">
                <strong>No hay metales preciosos configurados.</strong>
                <p>Agrega metales al catálogo para definir sus descuentos de compra.</p>
            </div>
                @endforelse
            </div>

            <div class="purchase-footer">
            <div class="purchase-footer-note">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3 4 6v6c0 4.5 3.4 7.7 8 9 4.6-1.3 8-4.5 8-9V6l-8-3Z" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <span>Los cambios se aplican a nuevas compras y quedan registrados con usuario y fecha en la bitácora.</span>
            </div>

            <div class="purchase-footer-actions">
                <a href="{{ route('admin.prices.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-gold">Guardar descuentos</button>
            </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-purchase-default]').forEach(function (input) {
        const summary = document.querySelector('[data-purchase-summary="' + input.dataset.metalId + '"]');

        if (!summary) {
            return;
        }

        const updateSummary = function () {
            const value = input.value.trim();
            summary.textContent = value !== '' ? value + '%' : '—';
        };

        input.addEventListener('input', updateSummary);
        updateSummary();
    });
});
</script>
@endpush
