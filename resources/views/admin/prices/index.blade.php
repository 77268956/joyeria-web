@extends('layouts.admin')

@section('title', 'Precio del Metal')

@section('body_class', 'full-viewport')

@php
    $currentPricesForJs = $metals->mapWithKeys(function ($metal) use ($current) {
        $price = $current[$metal->code] ?? null;

        return [$metal->id => $price ? [
            'price' => number_format((float) $price->price_per_gram, 2),
            'date' => $price->price_date?->format('d/m/Y'),
        ] : null];
    });
    $lastUpdated = $current->filter()->sortByDesc(fn ($price) => $price->created_at?->getTimestamp() ?? 0)->first();
    $canOverride = auth()->user()?->can('metal_price.override') ?? false;
@endphp

@section('content')
<style>
    .prices-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 1rem;
        min-height: 0;
        overflow: hidden;
    }

    .prices-page > .alert {
        flex-shrink: 0;
        margin-bottom: 0;
    }

    .prices-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-shrink: 0;
        padding: 0.9rem 1.2rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: linear-gradient(125deg, var(--color-blanco) 35%, rgba(184, 154, 94, 0.08) 100%);
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.03);
    }

    .prices-identity {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .prices-thumb {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        flex-shrink: 0;
        border: 1px solid rgba(184, 154, 94, 0.35);
        border-radius: 5px;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .prices-kicker {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
        color: var(--color-piedra);
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .prices-title {
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.7rem;
        font-weight: 500;
        line-height: 1.05;
    }

    .prices-subtitle {
        margin-top: 0.15rem;
        color: var(--color-piedra);
        font-size: 0.78rem;
        line-height: 1.4;
    }

    .prices-hero-side {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        flex-shrink: 0;
    }

    .prices-updated {
        padding-right: 1.1rem;
        border-right: 1px solid var(--color-linea);
        text-align: right;
    }

    .prices-updated span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.63rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .prices-updated strong {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-size: 0.84rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .prices-updated small {
        display: block;
        margin-top: 0.05rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
    }

    .prices-hero-actions {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .prices-hero-actions form {
        margin: 0;
    }

    .prices-hero-actions .btn {
        height: 36px;
        white-space: nowrap;
    }

    .prices-current-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        flex-shrink: 0;
    }

    .prices-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 0.8rem 1rem;
    }

    .prices-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .prices-price-block {
        flex-shrink: 0;
        text-align: right;
    }

    .prices-price-block > span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.6rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .prices-metal {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .prices-metal-symbol {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border: 1px solid rgba(184, 154, 94, 0.35);
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
        font-family: var(--font-serif);
        font-size: 0.95rem;
        font-weight: 600;
    }

    .prices-metal h2 {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.12rem;
        font-weight: 500;
        line-height: 1.1;
    }

    .prices-metal p {
        margin-top: 0.05rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
    }

    .prices-main-value {
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.75rem;
        font-weight: 600;
        line-height: 1;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .prices-main-value span {
        margin-left: 0.3rem;
        color: var(--color-piedra);
        font-family: var(--font-sans);
        font-size: 0.9rem;
        font-weight: 400;
    }

    .prices-meta {
        display: flex;
        align-items: center;
        gap: 0.2rem 0.7rem;
        flex-wrap: wrap;
        margin-top: 0.35rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .prices-meta .badge {
        font-size: 0.6rem;
        white-space: nowrap;
    }

    .prices-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .prices-card-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 0.4rem;
        padding-top: 0.4rem;
        border-top: 1px solid var(--color-linea);
    }

    .prices-trend {
        display: inline-flex;
        align-items: baseline;
        gap: 0.35rem;
        font-size: 0.76rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .prices-trend span {
        color: var(--color-piedra);
        font-size: 0.7rem;
        font-weight: 400;
    }

    .prices-setby {
        max-width: 55%;
        color: var(--color-piedra);
        font-size: 0.68rem;
        line-height: 1.35;
        text-align: right;
    }

    .prices-setby strong {
        color: var(--color-noche);
        font-weight: 500;
    }

    .prices-purities {
        margin-top: 0.45rem;
        padding-top: 0.45rem;
        border-top: 1px solid var(--color-linea);
    }

    .prices-purities-title {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--color-piedra);
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .prices-purity-list {
        display: flex;
        align-items: baseline;
        gap: 0.25rem 0.9rem;
        flex-wrap: wrap;
    }

    .prices-purity {
        display: inline-flex;
        align-items: baseline;
        gap: 0.3rem;
        font-size: 0.72rem;
    }

    .prices-purity span {
        color: var(--color-piedra);
    }

    .prices-purity strong {
        color: var(--color-noche);
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .prices-empty-price {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-top: 0.8rem;
        padding: 0.75rem;
        border: 1px dashed var(--color-linea);
        border-radius: 4px;
        color: var(--color-piedra);
        font-size: 0.78rem;
    }

    .prices-empty-state {
        grid-column: 1 / -1;
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--color-piedra);
        font-size: 0.85rem;
    }

    .prices-lower {
        display: grid;
        grid-template-columns: minmax(300px, 0.8fr) minmax(0, 1.5fr);
        gap: 1rem;
        flex: 1;
        min-height: 0;
    }

    .prices-lower--single {
        grid-template-columns: minmax(0, 1fr);
    }

    .prices-manual {
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: 1rem 1.15rem;
        overflow: auto;
    }

    .prices-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.7rem;
    }

    .prices-section-head h2 {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
        font-weight: 500;
        line-height: 1.15;
    }

    .prices-section-head p {
        margin-top: 0.15rem;
        color: var(--color-piedra);
        font-size: 0.76rem;
        line-height: 1.4;
    }

    .prices-manual-form {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .prices-field label {
        display: block;
        margin-bottom: 0.2rem;
        color: var(--color-piedra);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .prices-current-hint {
        margin-top: -0.3rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .prices-current-hint strong {
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .prices-input-affix {
        display: flex;
        align-items: center;
        overflow: hidden;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: var(--color-blanco);
    }

    .prices-input-affix > span {
        display: flex;
        align-items: center;
        align-self: stretch;
        padding: 0 0.6rem;
        border-right: 1px solid var(--color-linea);
        background: var(--color-crema);
        color: var(--color-piedra);
        font-size: 0.78rem;
        white-space: nowrap;
    }

    .prices-input-affix .form-control {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        font-variant-numeric: tabular-nums;
        text-align: right;
    }

    .prices-field small {
        display: block;
        margin-top: 0.15rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
    }

    .prices-submit {
        width: 100%;
        height: 38px;
        justify-content: center;
        margin-top: 0.15rem;
    }

    .prices-audit-note {
        display: flex;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 0.65rem;
        border-top: 1px solid var(--color-linea);
        color: var(--color-piedra);
        font-size: 0.69rem;
        line-height: 1.4;
    }

    .prices-history {
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: 0;
        overflow: hidden;
    }

    .prices-history-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        flex-shrink: 0;
        padding: 0.65rem 0.95rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .prices-history-head h2 {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.05rem;
        font-weight: 500;
        line-height: 1.15;
    }

    .prices-history-head p {
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .prices-history-filter {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .prices-history-filter .form-select {
        min-width: 140px;
        height: 30px;
        padding: 0.2rem 1.8rem 0.2rem 0.6rem;
        font-size: 0.75rem;
    }

    .prices-history-scroll {
        flex: 1;
        min-height: 0;
        overflow: auto;
    }

    .prices-history-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
        font-size: 0.74rem;
    }

    .prices-history-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 0.4rem 0.7rem;
        border-bottom: 1px solid var(--color-linea);
        background: var(--color-crema);
        color: var(--color-piedra);
        font-size: 0.63rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .prices-history-table td {
        padding: 0.38rem 0.7rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .prices-history-table tbody tr:hover {
        background: #FBF8F2;
    }

    .prices-history-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .prices-history-date {
        color: var(--color-piedra);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .prices-history-metal {
        font-weight: 600;
        white-space: nowrap;
    }

    .prices-history-price {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .prices-history-spot {
        color: var(--color-piedra);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .prices-source-cell {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }

    .prices-source-cell .badge {
        align-self: flex-start;
        font-size: 0.6rem;
        white-space: nowrap;
    }

    .prices-provider {
        color: var(--color-piedra);
        font-size: 0.63rem;
    }

    .prices-history-user {
        color: var(--color-piedra);
        white-space: nowrap;
    }

    .prices-history-reason {
        display: block;
        max-width: 220px;
        overflow: hidden;
        color: var(--color-piedra);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .prices-history-foot {
        flex-shrink: 0;
        padding: 0.45rem 0.8rem;
        border-top: 1px solid var(--color-linea);
    }

    .prices-history-empty {
        padding: 2.5rem 1rem;
        text-align: center;
    }

    .prices-history-empty strong {
        display: block;
        color: var(--color-noche);
        font-size: 0.9rem;
    }

    .prices-history-empty p {
        margin-top: 0.2rem;
        color: var(--color-piedra);
        font-size: 0.78rem;
    }

    .is-positive {
        color: var(--color-exito);
    }

    .is-negative {
        color: var(--color-error);
    }

    .is-neutral {
        color: var(--color-piedra);
    }

    @media (max-height: 820px) and (min-width: 1181px) {
        .prices-hero {
            padding: 0.7rem 1rem;
        }

        .prices-thumb {
            width: 46px;
            height: 46px;
        }

        .prices-title {
            font-size: 1.55rem;
        }

        .prices-subtitle,
        .prices-purities-title {
            display: none;
        }

        .prices-card {
            padding: 0.7rem 0.9rem;
        }

        .prices-purities {
            margin-top: 0.35rem;
            padding-top: 0.35rem;
        }

        .prices-card-foot {
            margin-top: 0.3rem;
            padding-top: 0.3rem;
        }
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

        .prices-page {
            overflow: visible;
        }

        .prices-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .prices-hero-side {
            width: 100%;
            justify-content: space-between;
            padding-top: 0.85rem;
            border-top: 1px solid var(--color-linea);
        }

        .prices-lower,
        .prices-lower--single {
            grid-template-columns: minmax(0, 1fr);
        }

        .prices-manual,
        .prices-history {
            overflow: visible;
        }

        .prices-history-scroll {
            max-height: 60vh;
            overflow: auto;
        }
    }

    @media (max-width: 900px) {
        .prices-current-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .prices-hero-side {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.75rem;
        }

        .prices-updated {
            padding-right: 0;
            border-right: 0;
            text-align: left;
        }

        .prices-history-filter {
            width: 100%;
        }

        .prices-history-filter .form-select {
            flex: 1;
            min-width: 0;
        }
    }

    @media (max-width: 700px) {
        .prices-hero {
            padding: 1rem;
        }

        .prices-identity {
            align-items: flex-start;
        }

        .prices-thumb {
            width: 52px;
            height: 52px;
        }

        .prices-title {
            font-size: 1.6rem;
        }

        .prices-hero-actions {
            width: 100%;
        }

        .prices-hero-actions .btn,
        .prices-hero-actions form {
            flex: 1;
        }

        .prices-hero-actions form .btn {
            width: 100%;
            justify-content: center;
        }

        .prices-card-foot {
            align-items: flex-start;
            flex-direction: column;
        }

        .prices-setby {
            max-width: none;
            text-align: left;
        }

        .prices-history-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .prices-history-table {
            min-width: 0;
        }

        .prices-history-table thead {
            display: none;
        }

        .prices-history-table,
        .prices-history-table tbody,
        .prices-history-table tr,
        .prices-history-table td {
            display: block;
            width: 100%;
        }

        .prices-history-table tbody tr {
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid var(--color-linea);
        }

        .prices-history-table tbody tr:last-child {
            border-bottom: 0;
        }

        .prices-history-table td {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.2rem 0;
            border: 0;
            text-align: right;
        }

        .prices-history-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: var(--color-piedra);
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .prices-history-table td[colspan] {
            display: block;
            text-align: center;
        }

        .prices-history-table td[colspan]::before {
            display: none;
        }

        .prices-source-cell {
            align-items: flex-end;
        }

        .prices-history-reason {
            max-width: 180px;
        }
    }
</style>

<div class="prices-page">
    <header class="prices-hero">
        <div class="prices-identity">
            <div class="prices-thumb">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                    <path d="M12 3v18"></path>
                    <path d="M17 7.5c0-1.9-2.2-3-5-3s-5 1.1-5 3 2.2 2.6 5 3 5 1.1 5 3-2.2 3-5 3-5-1.1-5-3"></path>
                </svg>
            </div>

            <div style="min-width: 0;">
                <div class="prices-kicker">
                    <span>Materia prima &middot; Cotización</span>
                    <span class="badge badge-success">Precio vigente</span>
                </div>
                <h1 class="prices-title">Precio del Metal</h1>
                <p class="prices-subtitle">Precio por gramo de metal puro, purezas derivadas y ajustes manuales auditados.</p>
            </div>
        </div>

        <div class="prices-hero-side">
            <div class="prices-updated">
                <span>Última actualización</span>
                <strong>{{ $lastUpdated?->created_at?->format('d/m/Y H:i') ?? 'Sin registros' }}</strong>
                <small>{{ $lastUpdated?->source?->label() ?? 'Aún no hay precios' }}</small>
            </div>

            <div class="prices-hero-actions">
                @can('metal_price.override')
                    <form action="{{ route('admin.prices.refresh') }}" method="POST" data-refresh-form>
                        @csrf
                        <button type="submit" class="btn btn-outline">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            <span>Actualizar ahora</span>
                        </button>
                    </form>
                @endcan

                @can('settings.manage')
                    <a href="{{ route('admin.prices.purchase_settings') }}" class="btn btn-dark">
                        <span>Descuentos de compra</span>
                    </a>
                @endcan
            </div>
        </div>
    </header>

    @include('admin.partials.alerts')

    <section class="prices-current-grid" aria-label="Precios vigentes por metal">
        @forelse ($metals as $metal)
            @php
                $price = $current[$metal->code] ?? null;
                $previousPrice = $previous[$metal->code] ?? null;
                $difference = $price && $previousPrice
                    ? bcsub((string) $price->price_per_gram, (string) $previousPrice->price_per_gram, 4)
                    : null;
                $differenceComparison = $difference !== null ? bccomp($difference, '0', 4) : 0;
                $differencePercent = $difference !== null && bccomp((string) $previousPrice->price_per_gram, '0', 4) > 0
                    ? bcmul(bcdiv($difference, (string) $previousPrice->price_per_gram, 8), '100', 2)
                    : null;
                $trendClass = $differenceComparison > 0 ? 'is-positive' : ($differenceComparison < 0 ? 'is-negative' : 'is-neutral');
                $trendLabel = $difference !== null
                    ? ($differenceComparison > 0 ? '+' : '').number_format((float) $difference, 2)
                    : null;
                $trendPercentLabel = $differencePercent !== null
                    ? ($differenceComparison > 0 ? '+' : '').number_format((float) $differencePercent, 2).'%'
                    : null;
                $metalSymbol = match ($metal->code) {
                    'GOLD' => 'Au',
                    'SILVER' => 'Ag',
                    default => strtoupper(substr($metal->code, 0, 2)),
                };
            @endphp

            <article class="card prices-card">
                <div class="prices-card-head">
                    <div class="prices-metal">
                        <span class="prices-metal-symbol" aria-hidden="true">{{ $metalSymbol }}</span>
                        <div>
                            <h2>{{ $metal->name }}</h2>
                            <p>Precio por gramo de metal puro</p>
                        </div>
                    </div>

                    @if ($price)
                        <div class="prices-price-block">
                            <span>Precio vigente / g</span>
                            <div class="prices-main-value">
                                {{ $store->currency }} ${{ number_format((float) $price->price_per_gram, 2) }}<span>/ g</span>
                            </div>
                        </div>
                    @else
                        <span class="badge badge-muted">Sin precio</span>
                    @endif
                </div>

                @if ($price)
                    <div class="prices-meta">
                        <span class="badge {{ $price->source === \App\Enums\MetalPriceSource::Api ? 'badge-info' : 'badge-gold' }}">
                            {{ $price->source?->label() }}
                        </span>
                        <span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                                <path d="M16 2v4M8 2v4M3 9h18"></path>
                            </svg>
                            Vigente desde {{ $price->price_date?->format('d/m/Y') }}
                        </span>
                        @if ($price->provider)
                            <span>{{ $price->provider }}</span>
                        @endif
                        @if ($price->spot_per_ounce)
                            <span>Spot {{ number_format((float) $price->spot_per_ounce, 2) }} USD/oz</span>
                        @endif
                    </div>

                    <div class="prices-card-foot">
                        <div class="prices-trend {{ $trendClass }}">
                            @if ($difference !== null)
                                {{ $trendLabel }} ({{ $trendPercentLabel }})
                                <span>vs. precio anterior</span>
                            @else
                                <span>Sin referencia anterior</span>
                            @endif
                        </div>

                        @if ($price->setBy)
                            <div class="prices-setby">
                                Fijado por <strong>{{ $price->setBy->name }}</strong>
                                @if ($price->reason)
                                    &middot; {{ $price->reason }}
                                @endif
                            </div>
                        @endif
                    </div>

                    @php($purities = $purityPrices[$metal->code] ?? collect())
                    @if ($metal->purities->isNotEmpty() && $purities->isNotEmpty())
                        <div class="prices-purities">
                            <span class="prices-purities-title">Precio por pureza</span>
                            <div class="prices-purity-list">
                                @foreach ($metal->purities as $purity)
                                    <div class="prices-purity">
                                        <span>{{ $purity->label ?: $purity->code }}</span>
                                        <strong>{{ $store->currency }} ${{ number_format((float) ($purities[$purity->code] ?? 0), 2) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="prices-empty-price">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 8v4M12 16h.01"></path>
                        </svg>
                        <span>Aún no hay un precio registrado para este metal.</span>
                    </div>
                @endif
            </article>
        @empty
            <div class="card prices-empty-state">No hay metales preciosos configurados.</div>
        @endforelse
    </section>

    <div class="prices-lower {{ $canOverride ? '' : 'prices-lower--single' }}">
        @if ($canOverride)
            <section class="card prices-manual">
                <div class="prices-section-head">
                    <div>
                        <h2>Fijar precio manual</h2>
                        <p>El precio anterior se conserva en el historial.</p>
                    </div>
                    <span class="badge badge-gold">Auditado</span>
                </div>

                <form action="{{ route('admin.prices.manual') }}" method="POST" class="prices-manual-form" data-manual-form>
                    @csrf

                    <div class="prices-field">
                        <label for="metal_id">Metal</label>
                        <select name="metal_id" id="metal_id" class="form-select" required data-manual-metal>
                            @foreach ($metals as $metal)
                                <option value="{{ $metal->id }}" {{ (string) old('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="prices-current-hint" data-current-price></div>

                    <div class="prices-field">
                        <label for="price_per_gram">Precio por gramo (metal puro)</label>
                        <div class="prices-input-affix">
                            <span>{{ $store->currency }} $</span>
                            <input type="number" step="0.0001" min="0.0001" name="price_per_gram" id="price_per_gram" class="form-control" value="{{ old('price_per_gram') }}" placeholder="0.0000" required>
                        </div>
                    </div>

                    <div class="prices-field">
                        <label for="reason">Motivo del ajuste</label>
                        <input type="text" name="reason" id="reason" class="form-control" value="{{ old('reason') }}" placeholder="Ej. Corrección por caída de mercado" minlength="5" maxlength="255" required>
                        <small>Obligatorio, mínimo 5 caracteres.</small>
                    </div>

                    <button type="submit" class="btn btn-gold prices-submit">Fijar precio manual</button>
                </form>

                <div class="prices-audit-note">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M12 3 4 6v6c0 4.5 3.4 7.7 8 9 4.6-1.3 8-4.5 8-9V6l-8-3Z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                    <span>El cambio queda registrado con usuario, fecha y motivo en la bitácora.</span>
                </div>
            </section>
        @endif

        <section class="card prices-history">
            <div class="prices-history-head">
                <div>
                    <h2>Historial de precios</h2>
                    <p>{{ number_format($history->total()) }} {{ $history->total() === 1 ? 'registro' : 'registros' }} &middot; Los precios no se editan</p>
                </div>

                <form action="{{ route('admin.prices.index') }}" method="GET" class="prices-history-filter">
                    <select name="metal_id" class="form-select" onchange="this.form.submit()" aria-label="Filtrar historial por metal">
                        <option value="">Todos los metales</option>
                        @foreach ($metals as $metal)
                            <option value="{{ $metal->id }}" {{ (string) request('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                        @endforeach
                    </select>
                    @if (request()->filled('metal_id'))
                        <a href="{{ route('admin.prices.index') }}" class="btn btn-outline">Limpiar</a>
                    @endif
                </form>
            </div>

            <div class="prices-history-scroll">
                <table class="prices-history-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Metal</th>
                            <th class="text-right">Precio / g</th>
                            <th class="text-right">Spot / oz</th>
                            <th>Origen</th>
                            <th>Registrado por</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $record)
                            <tr>
                                <td data-label="Fecha">
                                    <span class="prices-history-date">{{ $record->created_at?->format('d/m/Y H:i') }}</span>
                                </td>
                                <td data-label="Metal">
                                    <span class="prices-history-metal">{{ $record->metal?->name }}</span>
                                </td>
                                <td data-label="Precio / g" class="text-right">
                                    <span class="prices-history-price">{{ $store->currency }} ${{ number_format((float) $record->price_per_gram, 2) }}</span>
                                </td>
                                <td data-label="Spot / oz" class="text-right">
                                    <span class="prices-history-spot">{{ $record->spot_per_ounce ? number_format((float) $record->spot_per_ounce, 2) : '—' }}</span>
                                </td>
                                <td data-label="Origen">
                                    <div class="prices-source-cell">
                                        <span class="badge {{ $record->source === \App\Enums\MetalPriceSource::Api ? 'badge-info' : 'badge-gold' }}">
                                            {{ $record->source?->label() }}
                                        </span>
                                        @if ($record->provider)
                                            <span class="prices-provider">{{ $record->provider }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Registrado por">
                                    <span class="prices-history-user">{{ $record->setBy?->name ?? 'Sistema' }}</span>
                                </td>
                                <td data-label="Motivo">
                                    <span class="prices-history-reason" title="{{ $record->reason ?? 'Sin motivo' }}">{{ $record->reason ?? '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="prices-history-empty">
                                        <strong>Sin registros de precios</strong>
                                        <p>El historial aparecerá cuando se actualice o fije un precio.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($history->hasPages())
                <div class="prices-history-foot">
                    {{ $history->links('vendor.pagination.admin') }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const currentByMetalId = @json($currentPricesForJs);
    const currency = @json($store->currency);
    const metalSelect = document.querySelector('[data-manual-metal]');
    const currentPrice = document.querySelector('[data-current-price]');

    function updateCurrentPrice() {
        if (!metalSelect || !currentPrice) {
            return;
        }

        const price = currentByMetalId[metalSelect.value];

        if (!price) {
            currentPrice.textContent = 'Sin precio registrado para este metal.';
            return;
        }

        currentPrice.innerHTML = 'Precio vigente: <strong>' + currency + ' $' + price.price + '</strong> / g'
            + (price.date ? ' &middot; ' + price.date : '');
    }

    if (metalSelect && currentPrice) {
        updateCurrentPrice();
        metalSelect.addEventListener('change', updateCurrentPrice);
    }

    const manualForm = document.querySelector('[data-manual-form]');
    if (manualForm) {
        manualForm.addEventListener('submit', function (event) {
            const metalName = metalSelect?.selectedOptions[0]?.textContent.trim() || 'este metal';
            const price = manualForm.querySelector('input[name="price_per_gram"]')?.value || '';

            if (!window.confirm('¿Fijar ' + currency + ' $' + price + ' por gramo de ' + metalName + '? El precio anterior se conservará en el historial.')) {
                event.preventDefault();
                return;
            }

            const button = manualForm.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.textContent = 'Guardando…';
            }
        });
    }

    const refreshForm = document.querySelector('[data-refresh-form]');
    if (refreshForm) {
        refreshForm.addEventListener('submit', function (event) {
            if (!window.confirm('¿Consultar ahora el precio del metal al proveedor configurado?')) {
                event.preventDefault();
                return;
            }

            const button = refreshForm.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                const label = button.querySelector('span');
                if (label) {
                    label.textContent = 'Actualizando…';
                }
            }
        });
    }
})();
</script>
@endpush
