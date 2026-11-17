@extends('layouts.admin')

@section('title', 'Venta '.$sale->number)

@section('body_class', 'full-viewport')

@section('content')
@php
    $itemsCount = $sale->items->count();
    $totalWeight = $sale->items->reduce(fn ($carry, $item) => bcadd($carry, (string) $item->weight_g, 3), '0.000');
    $paidTotal = $sale->payments->reduce(fn ($carry, $payment) => bcadd($carry, (string) $payment->amount, 2), '0.00');
    $isVoided = $sale->isVoided();
@endphp

<style>
    .sale-title-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .sale-stats {
        flex-shrink: 0;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 0;
    }

    .sale-stat {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.45rem 0.7rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
    }

    .sale-stat-icon {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        margin-left: auto;
        border-radius: 50%;
        background-color: var(--color-crema);
        color: var(--color-dorado);
        order: 2;
        animation: sale-icon-bob 2.8s ease-in-out infinite;
    }

    .sale-stat:nth-child(2) .sale-stat-icon {
        animation-delay: 0.2s;
    }

    .sale-stat:nth-child(3) .sale-stat-icon {
        animation-delay: 0.4s;
    }

    .sale-stat:nth-child(4) .sale-stat-icon {
        animation-delay: 0.6s;
    }

    @keyframes sale-icon-bob {
        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-2px) scale(1.08);
        }
    }

    .sale-stat-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        order: 1;
    }

    .sale-stat-label {
        color: var(--color-piedra);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .sale-stat-value {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.2rem;
        font-weight: 600;
        line-height: 1.15;
    }

    .sale-stat-hint {
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.68rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sale-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 0;
    }

    .sale-page .page-header {
        flex-shrink: 0;
        margin-bottom: 0;
    }

    .sale-page .alert {
        flex-shrink: 0;
        margin-bottom: 0;
    }

    .sale-layout {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 1rem;
        align-items: stretch;
        min-height: 0;
    }

    .sale-main {
        display: flex;
        min-height: 0;
    }

    .sale-side {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 0;
        overflow-y: auto;
        padding-right: 0.15rem;
    }

    .sale-card {
        padding: 0.9rem 1rem;
        margin-bottom: 0;
    }

    .sale-card-title {
        margin: 0 0 0.65rem;
        font-size: 1.08rem;
    }

    .sale-table-card {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-height: 0;
        padding: 0;
        overflow: hidden;
    }

    .sale-table-card .table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
    }

    .sale-table-card .data-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: var(--color-crema);
    }

    .sale-table-head {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .sale-table-head h2 {
        margin: 0;
        font-size: 1.15rem;
    }

    .sale-table-meta {
        color: var(--color-piedra);
        font-size: 0.78rem;
    }

    .sale-item-name {
        display: block;
        font-weight: 600;
    }

    .sale-item-sku {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .sale-line-total {
        font-weight: 600;
    }

    .sale-items-footer {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.7rem 1rem;
        border-top: 1px solid var(--color-linea);
        background-color: #FAF8F3;
        font-size: 0.85rem;
    }

    .sale-info-list {
        display: grid;
        gap: 0.55rem;
    }

    .sale-info-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.45rem;
        border-bottom: 1px dashed var(--color-linea);
    }

    .sale-info-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .sale-info-row dt {
        color: var(--color-piedra);
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .sale-info-row dd {
        margin: 0;
        color: var(--color-noche);
        font-size: 0.86rem;
        font-weight: 500;
        text-align: right;
    }

    .sale-info-row--full {
        display: block;
    }

    .sale-info-row--full dd {
        margin-top: 0.2rem;
        font-weight: 400;
        text-align: left;
    }

    .sale-payments {
        display: grid;
        gap: 0.5rem;
    }

    .sale-payment {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.55rem 0.65rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: #FAF8F3;
    }

    .sale-payment-method {
        display: block;
        color: var(--color-noche);
        font-size: 0.85rem;
        font-weight: 600;
    }

    .sale-payment-meta {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .sale-payment-amount {
        text-align: right;
    }

    .sale-payment-amount strong {
        display: block;
        font-size: 0.92rem;
    }

    .sale-payment-amount span {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .sale-paid-total {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
        font-weight: 600;
    }

    .sale-payments-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.8rem;
    }

    .sale-payments-head .sale-card-title {
        margin: 0;
    }

    .sale-void-card {
        border-color: #E0B4B4;
        background-color: #FFFBFB;
    }

    .sale-void-card summary {
        color: var(--color-error);
        cursor: pointer;
        font-family: var(--font-serif);
        font-size: 1.05rem;
        font-weight: 600;
        list-style: none;
    }

    .sale-void-card summary::-webkit-details-marker {
        display: none;
    }

    .sale-void-card summary::after {
        content: '+';
        float: right;
        font-family: var(--font-sans);
        font-size: 1rem;
    }

    .sale-void-card details[open] summary::after {
        content: '-';
    }

    .sale-void-card p {
        margin: 0.35rem 0 0.7rem;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    @media (prefers-reduced-motion: reduce) {
        .sale-stat-icon {
            animation: none;
        }
    }

    @media (max-width: 1100px) {
        .sale-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .sale-layout {
            flex: none;
            grid-template-columns: 1fr;
        }

        .sale-main {
            display: block;
        }

        .sale-side {
            overflow: visible;
            padding-right: 0;
        }

        .sale-table-card {
            min-height: 320px;
        }
    }

    @media (max-width: 640px) {
        .sale-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .sale-header .page-actions {
            width: 100%;
        }

        .sale-header .page-actions .btn {
            flex: 1;
        }

        .sale-stats {
            grid-template-columns: 1fr;
        }

        .sale-card {
            padding: 0.9rem;
        }

        .sale-table-head {
            flex-direction: column;
            align-items: flex-start;
            padding: 0.85rem;
        }

        .sale-items-footer {
            padding: 0.75rem 0.85rem;
        }
    }
</style>

<div class="sale-page">
<div class="page-header sale-header">
    <div>
        <div class="sale-title-row">
            <h1 class="page-title font-serif">Venta {{ $sale->number }}</h1>
            <span class="badge {{ $isVoided ? 'badge-danger' : 'badge-success' }}">{{ $sale->status->label() }}</span>
        </div>
        <p class="page-subtitle">{{ $sale->created_at?->format('d/m/Y H:i') }} · {{ $sale->user?->name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline">Volver</a>
        <a href="{{ route('admin.sales.ticket', $sale) }}" target="_blank" class="btn btn-dark">Ticket</a>
        <a href="{{ route('admin.sales.ticket', ['sale' => $sale, 'format' => 'invoice']) }}" target="_blank" class="btn btn-dark">Factura</a>
    </div>
</div>

@include('admin.partials.alerts')

@if ($isVoided)
    <div class="alert alert-error" style="display: block;">
        <strong>Venta anulada</strong> el {{ $sale->voided_at?->format('d/m/Y H:i') }} por {{ $sale->voidedBy?->name }}.
        <br>Motivo: {{ $sale->void_reason }}
    </div>
@endif

<div class="sale-stats">
    <div class="sale-stat">
        <span class="sale-stat-icon" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                <circle cx="12" cy="12" r="2.5"></circle>
                <path d="M6.5 12h.01M17.5 12h.01"></path>
            </svg>
        </span>
        <div class="sale-stat-body">
            <span class="sale-stat-label">Total</span>
            <strong class="sale-stat-value">$ {{ number_format((float) $sale->total, 2) }}</strong>
            <span class="sale-stat-hint">{{ $isVoided ? 'Venta sin efecto' : 'Importe cobrado' }}</span>
        </div>
    </div>
    <div class="sale-stat">
        <span class="sale-stat-icon" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"></path>
                <path d="M9 8h6M9 12h6"></path>
            </svg>
        </span>
        <div class="sale-stat-body">
            <span class="sale-stat-label">Subtotal</span>
            <strong class="sale-stat-value">$ {{ number_format((float) $sale->subtotal, 2) }}</strong>
            <span class="sale-stat-hint">Antes de descuentos</span>
        </div>
    </div>
    <div class="sale-stat">
        <span class="sale-stat-icon" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12l9-9h7v7l-9 9z"></path>
                <circle cx="16" cy="8" r="1.2"></circle>
            </svg>
        </span>
        <div class="sale-stat-body">
            <span class="sale-stat-label">Descuento</span>
            <strong class="sale-stat-value">$ {{ number_format((float) $sale->discount_total, 2) }}</strong>
            <span class="sale-stat-hint">{{ (float) $sale->discount_total > 0 ? 'Aplicado a la venta' : 'Sin descuentos' }}</span>
        </div>
    </div>
    <div class="sale-stat">
        <span class="sale-stat-icon" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 3h12l4 6-10 12L2 9z"></path>
                <path d="M2 9h20"></path>
            </svg>
        </span>
        <div class="sale-stat-body">
            <span class="sale-stat-label">Piezas</span>
            <strong class="sale-stat-value">{{ number_format($itemsCount) }}</strong>
            <span class="sale-stat-hint">{{ number_format((float) $totalWeight, 2) }} g en total</span>
        </div>
    </div>
</div>

<div class="sale-layout">
    <div class="sale-main">
        <div class="card sale-card sale-table-card">
            <div class="sale-table-head">
                <div>
                    <h2 class="font-serif">Piezas vendidas</h2>
                    <span class="sale-table-meta">{{ number_format($itemsCount) }} {{ $itemsCount === 1 ? 'pieza' : 'piezas' }} · {{ number_format((float) $totalWeight, 2) }} g</span>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pieza</th>
                            <th>Metal / Pureza</th>
                            <th class="text-right">Peso</th>
                            <th class="text-right">Precio</th>
                            <th class="text-right">Descuento</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale->items as $item)
                            <tr>
                                <td>
                                    <span class="sale-item-name">{{ $item->name_snapshot }}</span>
                                    @if ($item->product?->sku)
                                        <span class="sale-item-sku">{{ $item->product->sku }}</span>
                                    @endif
                                </td>
                                <td>{{ $item->metal_snapshot }}@if ($item->purity_snapshot) · {{ $item->purity_snapshot }}@endif</td>
                                <td class="text-right num">{{ number_format((float) $item->weight_g, 2) }} g</td>
                                <td class="text-right num">$ {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="text-right num">$ {{ number_format((float) $item->discount, 2) }}</td>
                                <td class="text-right num sale-line-total">$ {{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="muted" style="text-align: center; padding: 2rem;">Esta venta no tiene piezas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="sale-items-footer">
                <span class="muted">Total de la venta</span>
                <strong class="font-serif" style="font-size: 1.15rem;">$ {{ number_format((float) $sale->total, 2) }}</strong>
            </div>
        </div>
    </div>

    <aside class="sale-side">
        <div class="card sale-card">
            <h2 class="font-serif sale-card-title">Datos de la venta</h2>
            <dl class="sale-info-list">
                <div class="sale-info-row">
                    <dt>Cliente</dt>
                    <dd>{{ $sale->customer?->full_name ?? 'Consumidor final' }}</dd>
                </div>
                <div class="sale-info-row">
                    <dt>Vendedor</dt>
                    <dd>{{ $sale->user?->name ?? '—' }}</dd>
                </div>
                <div class="sale-info-row">
                    <dt>Fecha</dt>
                    <dd>{{ $sale->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="sale-info-row">
                    <dt>Estado</dt>
                    <dd><span class="badge {{ $isVoided ? 'badge-danger' : 'badge-success' }}">{{ $sale->status->label() }}</span></dd>
                </div>
                @if ($sale->notes)
                    <div class="sale-info-row sale-info-row--full">
                        <dt>Notas</dt>
                        <dd>{{ $sale->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="card sale-card">
            <div class="sale-payments-head">
                <h2 class="font-serif sale-card-title">Pagos</h2>
                <span class="sale-paid-total">$ {{ number_format((float) $paidTotal, 2) }}</span>
            </div>
            <div class="sale-payments">
                @forelse ($sale->payments as $payment)
                    @php($change = $payment->received !== null ? bcsub((string) $payment->received, (string) $payment->amount, 2) : null)
                    <div class="sale-payment">
                        <div>
                            <span class="sale-payment-method">{{ $payment->method->label() }}</span>
                            @if ($payment->reference)
                                <span class="sale-payment-meta">Ref. {{ $payment->reference }}</span>
                            @endif
                        </div>
                        <div class="sale-payment-amount">
                            <strong>$ {{ number_format((float) $payment->amount, 2) }}</strong>
                            @if ($payment->received !== null)
                                <span>Recibido $ {{ number_format((float) $payment->received, 2) }}</span>
                                @if (bccomp((string) $change, '0', 2) === 1)
                                    <span>Cambio $ {{ number_format((float) $change, 2) }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="muted" style="font-size: 0.82rem;">Esta venta no tiene pagos registrados.</p>
                @endforelse
            </div>
        </div>

        @can('sales.void')
            @unless ($isVoided)
                <div class="card sale-card sale-void-card">
                    <details>
                        <summary>Anular venta</summary>
                        <p>Las piezas volverán al inventario disponible. Esta acción es irreversible.</p>
                        <form action="{{ route('admin.sales.void', $sale) }}" method="POST" onsubmit="return confirm('¿Confirmas anular la venta {{ $sale->number }}?');">
                            @csrf
                            <div class="form-group">
                                <label class="form-label" for="reason">Motivo de la anulación</label>
                                <input type="text" name="reason" id="reason" class="form-control" maxlength="255" required>
                            </div>
                            <button type="submit" class="btn btn-danger">Anular venta</button>
                        </form>
                    </details>
                </div>
            @endunless
        @endcan
    </aside>
</div>
</div>
@endsection
