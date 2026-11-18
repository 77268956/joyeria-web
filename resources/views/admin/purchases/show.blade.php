@extends('layouts.admin')

@section('title', 'Compra '.$purchase->number)

@section('body_class', 'full-viewport')

@section('content')
@php
    $itemsCount = $purchase->items->count();
    $grossWeight = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, (string) $item->gross_weight_g, 3), '0.000');
    $netWeight = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, (string) $item->net_weight_g, 3), '0.000');
    $appraisedTotal = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, $item->appraisedAmount(), 2), '0.00');
    $negotiationDifference = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, $item->negotiationDifference(), 2), '0.00');
    $negotiationComparison = bccomp($negotiationDifference, '0', 2);
    $negotiationTone = $negotiationComparison > 0 ? 'is-positive' : ($negotiationComparison < 0 ? 'is-negative' : 'is-neutral');
    $negotiationLabel = $negotiationComparison === 0
        ? '$ 0.00'
        : ($negotiationComparison > 0 ? '+ $ ' : '- $ ').number_format(abs((float) $negotiationDifference), 2);
    $isPending = $purchase->isPendingApproval();
    $isRejected = $purchase->status === \App\Enums\MetalPurchaseStatus::Rejected;
    $badge = match ($purchase->status) {
        \App\Enums\MetalPurchaseStatus::Paid, \App\Enums\MetalPurchaseStatus::Adjusted => 'badge-success',
        \App\Enums\MetalPurchaseStatus::PendingApproval => 'badge-gold',
        default => 'badge-danger',
    };
    $totalLabel = match ($purchase->status) {
        \App\Enums\MetalPurchaseStatus::PendingApproval => 'Total a pagar',
        \App\Enums\MetalPurchaseStatus::Rejected => 'Total ofrecido',
        default => 'Total pagado',
    };
    $statusNote = match ($purchase->status) {
        \App\Enums\MetalPurchaseStatus::PendingApproval => 'Pendiente de aprobación',
        \App\Enums\MetalPurchaseStatus::Rejected => 'Rechazada sin movimientos',
        \App\Enums\MetalPurchaseStatus::Adjusted => 'Ajustada y auditada',
        default => $purchase->payout_method === \App\Enums\PayoutMethod::Cash
            ? 'Liquidada · caja actualizada'
            : 'Liquidada · pago por transferencia',
    };
    $statusTone = match ($purchase->status) {
        \App\Enums\MetalPurchaseStatus::PendingApproval => 'pending',
        \App\Enums\MetalPurchaseStatus::Rejected => 'rejected',
        \App\Enums\MetalPurchaseStatus::Adjusted => 'adjusted',
        default => 'paid',
    };
    $customerName = $purchase->customer?->full_name ?? 'Cliente sin nombre';
    $customerDocument = trim(($purchase->customer?->document_type ?? '').' '.($purchase->customer?->document_number ?? ''));
    $nameParts = preg_split('/\s+/u', trim($customerName)) ?: [];
    $customerInitials = mb_strtoupper(mb_substr($nameParts[0] ?? '', 0, 1).mb_substr($nameParts[1] ?? '', 0, 1)) ?: '?';
    $hasAdjustments = $purchase->adjustments->isNotEmpty();
@endphp

<style>
    .purchase-show-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.8rem;
        min-height: 0;
    }

    .purchase-show-page > .alert {
        flex-shrink: 0;
        max-height: 25vh;
        margin-bottom: 0;
        overflow-y: auto;
    }

    .purchase-hero {
        display: grid;
        flex-shrink: 0;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.5rem 1rem;
        align-items: center;
        padding: 0.7rem 0.9rem;
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        background: linear-gradient(135deg, #FFFFFF 0%, #FBF8F1 100%);
    }

    .purchase-hero-main {
        min-width: 0;
    }

    .purchase-hero-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .purchase-hero-title h1 {
        margin: 0;
        font-family: var(--font-serif);
        font-size: 1.3rem;
        line-height: 1.15;
    }

    .purchase-hero .page-subtitle {
        margin: 0.15rem 0 0;
        font-size: 0.78rem;
    }

    .purchase-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
        margin-top: 0.4rem;
    }

    .purchase-chip {
        padding: 0.15rem 0.45rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background-color: var(--color-crema);
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 500;
        line-height: 1.4;
    }

    .purchase-chip--pending {
        border-color: #E8D9B5;
        background-color: #FBF6EA;
        color: #7A5A16;
    }

    .purchase-chip--rejected {
        border-color: #E8C4C4;
        background-color: #FBF1F1;
        color: #7A2A2C;
    }

    .purchase-chip--adjusted {
        border-color: #C9DECF;
        background-color: #F2F7F3;
        color: #255330;
    }

    .purchase-hero-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.4rem;
    }

    .purchase-hero-total {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        min-width: 160px;
        text-align: right;
    }

    .purchase-hero-total-label {
        color: var(--color-piedra);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .purchase-hero-total strong {
        margin-top: 0.05rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.55rem;
        line-height: 1.05;
    }

    .purchase-hero-breakdown {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin-top: 0.15rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
    }

    .purchase-hero-breakdown b {
        color: var(--color-noche);
        font-weight: 600;
    }

    .is-positive {
        color: #2F6B3D !important;
    }

    .is-negative {
        color: var(--color-error) !important;
    }

    .is-neutral {
        color: var(--color-piedra) !important;
    }

    .purchase-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        justify-content: flex-end;
    }

    .purchase-hero-actions .btn {
        padding: 0.35rem 0.6rem;
        font-size: 0.74rem;
    }

    .purchase-stats {
        display: grid;
        flex-shrink: 0;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .purchase-stat {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        min-width: 0;
        padding: 0.55rem 0.7rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background-color: var(--color-blanco);
    }

    .purchase-stat-icon {
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
    }

    .purchase-stat-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        order: 1;
    }

    .purchase-stat-label {
        color: var(--color-piedra);
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .purchase-stat-value {
        overflow: hidden;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1.2;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .purchase-stat-hint {
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.68rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .purchase-layout {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1fr) 330px;
        gap: 0.8rem;
        align-items: stretch;
        min-height: 0;
    }

    .purchase-main,
    .purchase-side {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        min-width: 0;
        min-height: 0;
        padding-right: 0.2rem;
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .purchase-table-card,
    .purchase-adjustments {
        padding: 0;
        margin: 0;
        overflow: hidden;
    }

    .purchase-adjustments .table-wrap {
        max-height: 230px;
        overflow: auto;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .purchase-adjustments .table-wrap::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .purchase-adjustments .table-wrap::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #D9D2C3;
    }

    .purchase-adjustments .table-wrap::-webkit-scrollbar-track {
        background: transparent;
    }

    .purchase-adjustments .purchase-responsive-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: var(--color-tabla-header);
        box-shadow: inset 0 -1px 0 var(--color-linea);
    }

    .purchase-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.55rem 0.9rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .purchase-card-head h2 {
        margin: 0;
        font-size: 0.96rem;
    }

    .purchase-card-meta {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
        line-height: 1.3;
    }

    .purchase-responsive-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.83rem;
    }

    .purchase-responsive-table thead tr {
        background-color: var(--color-tabla-header);
        border-bottom: 1px solid var(--color-linea);
    }

    .purchase-responsive-table th {
        padding: 0.5rem 0.9rem;
        color: var(--color-noche);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-align: left;
        text-transform: uppercase;
    }

    .purchase-responsive-table th.text-right {
        text-align: right;
    }

    .purchase-responsive-table td {
        padding: 0.55rem 0.9rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: top;
    }

    .purchase-responsive-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .purchase-responsive-table tbody tr:hover {
        background-color: #FBF9F5;
    }

    .purchase-responsive-table .text-right {
        text-align: right;
    }

    .purchase-cell {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        min-width: 0;
        text-align: right;
    }

    .purchase-col-item .purchase-cell,
    .purchase-cell--left {
        align-items: flex-start;
        text-align: left;
    }

    .purchase-item-name {
        display: block;
        font-weight: 600;
    }

    .cell-strong {
        display: block;
        font-weight: 600;
    }

    .cell-sub {
        display: block;
        margin-top: 0.08rem;
        color: var(--color-piedra);
        font-size: 0.69rem;
        font-weight: 400;
        line-height: 1.3;
    }

    .purchase-diff {
        display: inline-block;
        margin-top: 0.1rem;
        font-size: 0.69rem;
        font-weight: 600;
    }

    .purchase-diff.is-neutral {
        font-weight: 400;
    }

    .purchase-destination {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.5rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background-color: var(--color-crema);
        color: var(--color-noche);
        font-size: 0.72rem;
        font-weight: 600;
        text-decoration: none;
    }

    .purchase-destination:hover {
        border-color: var(--color-dorado);
    }

    .purchase-items-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.75rem 1rem;
        border-top: 1px solid var(--color-linea);
        background-color: #FAF8F3;
        font-size: 0.85rem;
    }

    .purchase-footer-total {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
        white-space: nowrap;
    }

    .purchase-side-card {
        padding: 0.95rem 1rem;
        margin: 0;
    }

    .purchase-side-head {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.7rem;
    }

    .purchase-side-icon {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: var(--color-crema);
        color: var(--color-dorado);
    }

    .purchase-side-head h2 {
        margin: 0;
        font-size: 1rem;
    }

    .purchase-side-head p {
        margin: 0.1rem 0 0;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .purchase-customer {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.6rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background-color: #FAF8F3;
    }

    .purchase-avatar {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: var(--color-noche);
        color: var(--color-hueso);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .purchase-customer strong {
        display: block;
        overflow: hidden;
        font-size: 0.88rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .purchase-customer span {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .purchase-info-list {
        display: grid;
        gap: 0.35rem;
        margin: 0.7rem 0 0;
    }

    .purchase-info-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.35rem;
        border-bottom: 1px dashed var(--color-linea);
    }

    .purchase-info-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .purchase-info-row dt {
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .purchase-info-row dd {
        margin: 0;
        color: var(--color-noche);
        font-size: 0.84rem;
        font-weight: 500;
        text-align: right;
    }

    .purchase-info-row--full {
        display: block;
    }

    .purchase-info-row--full dd {
        margin-top: 0.2rem;
        font-weight: 400;
        text-align: left;
    }

    .purchase-pending-note {
        display: flex;
        flex-shrink: 0;
        align-items: flex-start;
        gap: 0.6rem;
        padding: 0.8rem 1rem;
        border: 1px solid #E8D9B5;
        border-radius: 4px;
        background-color: #FBF6EA;
        color: #7A5A16;
        font-size: 0.82rem;
    }

    .purchase-approval {
        flex-shrink: 0;
        max-height: 40vh;
        margin: 0;
        padding: 1rem 1.15rem;
        overflow-y: auto;
    }

    .purchase-approval-head {
        margin-bottom: 0.8rem;
    }

    .purchase-approval-head h2 {
        margin: 0 0 0.2rem;
        font-size: 1.1rem;
    }

    .purchase-approval-head p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .purchase-approval-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.8rem;
    }

    .purchase-approval-box {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        padding: 0.85rem 0.95rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: #FAF8F3;
    }

    .purchase-approval-box h3 {
        margin: 0;
        font-family: var(--font-serif);
        font-size: 1rem;
    }

    .purchase-approval-box p {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .purchase-approval-box form {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        margin-top: auto;
    }

    .purchase-approval-box .btn {
        align-self: flex-start;
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

        .purchase-show-page {
            min-height: auto;
        }

        .purchase-layout {
            flex: none;
            grid-template-columns: 1fr;
            min-height: auto;
        }

        .purchase-main,
        .purchase-side {
            min-height: auto;
            padding-right: 0;
            overflow: visible;
        }

        .purchase-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 820px) {
        .purchase-hero {
            grid-template-columns: 1fr;
        }

        .purchase-hero-side {
            width: 100%;
            align-items: flex-start;
        }

        .purchase-hero-total {
            align-items: flex-start;
            text-align: left;
        }

        .purchase-hero-breakdown {
            justify-content: flex-start;
        }

        .purchase-hero-actions {
            justify-content: flex-start;
        }

        .purchase-approval-grid {
            grid-template-columns: 1fr;
        }

        .purchase-responsive-table thead {
            display: none;
        }

        .purchase-responsive-table,
        .purchase-responsive-table tbody,
        .purchase-responsive-table tr,
        .purchase-responsive-table td {
            display: block;
            width: 100%;
        }

        .purchase-responsive-table tbody tr {
            margin: 0 0 0.55rem;
            padding: 0.7rem 0.85rem;
            border: 1px solid var(--color-linea);
            border-radius: 6px;
            background-color: var(--color-blanco);
        }

        .purchase-responsive-table tbody tr:last-child {
            margin-bottom: 0;
        }

        .purchase-responsive-table td {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.25rem 0;
            border: 0;
            text-align: right;
        }

        .purchase-responsive-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: var(--color-piedra);
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .purchase-responsive-table td.purchase-col-item {
            display: block;
            padding-bottom: 0.4rem;
            text-align: left;
        }

        .purchase-responsive-table td.purchase-col-item::before {
            display: block;
            margin-bottom: 0.2rem;
        }

        .purchase-responsive-table .purchase-cell,
        .purchase-responsive-table td.text-right {
            align-items: flex-end;
            text-align: right;
        }

        .purchase-items-footer {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 560px) {
        .purchase-stats {
            grid-template-columns: 1fr;
        }

        .purchase-hero-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .purchase-approval {
            max-height: none;
        }
    }
</style>

<div class="purchase-show-page">
    <header class="purchase-hero">
        <div class="purchase-hero-main">
            <div class="purchase-hero-title">
                <h1 class="font-serif">Compra {{ $purchase->number }}</h1>
                <span class="badge {{ $badge }}">{{ $purchase->status->label() }}</span>
            </div>
            <p class="page-subtitle">{{ $purchase->created_at?->format('d/m/Y H:i') }} · Registrado por {{ $purchase->user?->name ?? '—' }}</p>

            <div class="purchase-chips">
                <span class="purchase-chip">{{ $purchase->payout_method->label() }}</span>
                <span class="purchase-chip">{{ number_format($itemsCount) }} {{ $itemsCount === 1 ? 'pieza' : 'piezas' }}</span>
                <span class="purchase-chip">{{ number_format((float) $netWeight, 3) }} g netos</span>
                <span class="purchase-chip purchase-chip--{{ $statusTone }}">{{ $statusNote }}</span>
            </div>
        </div>

        <div class="purchase-hero-side">
            <div class="purchase-hero-total">
                <span class="purchase-hero-total-label">{{ $totalLabel }}</span>
                <strong>$ {{ number_format((float) $purchase->total_amount, 2) }}</strong>
                <div class="purchase-hero-breakdown">
                    <span>Sistema <b>$ {{ number_format((float) $appraisedTotal, 2) }}</b></span>
                    <span>Negociación <b class="{{ $negotiationTone }}">{{ $negotiationLabel }}</b></span>
                </div>
            </div>

            <div class="purchase-hero-actions">
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline">Volver</a>
                <a href="{{ route('admin.purchases.receipt', $purchase) }}" class="btn btn-dark" target="_blank">Comprobante</a>
                @if ($canApprove && in_array($purchase->status, [\App\Enums\MetalPurchaseStatus::Paid, \App\Enums\MetalPurchaseStatus::Adjusted], true))
                    <a href="{{ route('admin.purchases.adjust', $purchase) }}" class="btn btn-outline">Ajustar</a>
                @endif
            </div>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="purchase-stats">
        <div class="purchase-stat">
            <span class="purchase-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3h12l4 6-10 12L2 9z"></path>
                    <path d="M2 9h20"></path>
                </svg>
            </span>
            <div class="purchase-stat-body">
                <span class="purchase-stat-label">Piezas</span>
                <strong class="purchase-stat-value">{{ number_format($itemsCount) }}</strong>
                <span class="purchase-stat-hint">{{ number_format((float) $netWeight, 3) }} g netos · {{ number_format((float) $grossWeight, 3) }} g bruto</span>
            </div>
        </div>
        <div class="purchase-stat">
            <span class="purchase-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v18M8 21h8"></path>
                    <path d="M5 7h14"></path>
                    <path d="M5 7l-2 6a3 3 0 006 0z"></path>
                    <path d="M19 7l-2 6a3 3 0 006 0z"></path>
                </svg>
            </span>
            <div class="purchase-stat-body">
                <span class="purchase-stat-label">Valuación</span>
                <strong class="purchase-stat-value">$ {{ number_format((float) $appraisedTotal, 2) }}</strong>
                <span class="purchase-stat-hint">Precio base del sistema</span>
            </div>
        </div>
        <div class="purchase-stat">
            <span class="purchase-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="6" rx="7" ry="3"></ellipse>
                    <path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6"></path>
                    <path d="M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"></path>
                </svg>
            </span>
            <div class="purchase-stat-body">
                <span class="purchase-stat-label">Negociación</span>
                <strong class="purchase-stat-value {{ $negotiationTone }}">{{ $negotiationLabel }}</strong>
                <span class="purchase-stat-hint">Acordado $ {{ number_format((float) $purchase->total_amount, 2) }}</span>
            </div>
        </div>
        <div class="purchase-stat">
            <span class="purchase-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7h16M6 7V5h12v2M7 7l1 12h8l1-12"></path>
                </svg>
            </span>
            <div class="purchase-stat-body">
                <span class="purchase-stat-label">Aprobación</span>
                <strong class="purchase-stat-value" style="font-size: 0.92rem;">{{ $purchase->approvedBy?->name ?? 'Sin decisión' }}</strong>
                <span class="purchase-stat-hint">{{ $purchase->approved_at?->format('d/m/Y H:i') ?? 'Registro directo' }}</span>
            </div>
        </div>
    </div>

    <div class="purchase-layout">
        <div class="purchase-main">
            <div class="card purchase-table-card">
                <div class="purchase-card-head">
                    <div>
                        <h2 class="font-serif">Piezas recibidas</h2>
                        <span class="purchase-card-meta">{{ number_format($itemsCount) }} {{ $itemsCount === 1 ? 'pieza' : 'piezas' }} · {{ number_format((float) $netWeight, 3) }} g netos</span>
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="purchase-responsive-table">
                        <thead>
                            <tr>
                                <th>Pieza</th>
                                <th class="text-right">Peso</th>
                                <th class="text-right">Valuación</th>
                                <th class="text-right">Acordado</th>
                                <th>Destino</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchase->items as $item)
                                @php($itemDifference = (float) $item->negotiationDifference())
                                <tr>
                                    <td data-label="Pieza" class="purchase-col-item">
                                        <div class="purchase-cell">
                                            <span class="purchase-item-name">{{ $item->description }}</span>
                                            <span class="cell-sub">{{ $item->metal?->name }}{{ $item->purity?->code ? ' · '.$item->purity->code : '' }} · Ley {{ $item->fineness_snapshot }}</span>
                                            <span class="cell-sub">{{ $item->weight_source->label() }} · Descuento {{ number_format((float) $item->discount_percent_snapshot, 2) }}%</span>
                                        </div>
                                    </td>
                                    <td data-label="Peso" class="text-right num">
                                        <div class="purchase-cell">
                                            <span class="cell-strong">{{ number_format((float) $item->net_weight_g, 3) }} g</span>
                                            <span class="cell-sub">Bruto {{ number_format((float) $item->gross_weight_g, 3) }} g</span>
                                        </div>
                                    </td>
                                    <td data-label="Valuación" class="text-right num">
                                        <div class="purchase-cell">
                                            <span class="cell-strong">$ {{ number_format((float) $item->appraisedAmount(), 2) }}</span>
                                            <span class="cell-sub">$ {{ number_format((float) $item->price_per_gram_snapshot, 2) }} por gramo</span>
                                        </div>
                                    </td>
                                    <td data-label="Acordado" class="text-right num">
                                        <div class="purchase-cell">
                                            <span class="cell-strong">$ {{ number_format((float) $item->amount, 2) }}</span>
                                            @if ($itemDifference == 0.0)
                                                <span class="purchase-diff is-neutral">Igual al sistema</span>
                                            @else
                                                <span class="purchase-diff {{ $itemDifference > 0 ? 'is-positive' : 'is-negative' }}">
                                                    {{ $itemDifference > 0 ? '+' : '-' }} $ {{ number_format(abs($itemDifference), 2) }} vs sistema
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-label="Destino">
                                        @if ($item->product_id)
                                            <a href="{{ route('admin.inventory.show', $item->product_id) }}" class="purchase-destination">Ocasión</a>
                                        @elseif ($item->material_lot_id)
                                            <a href="{{ route('admin.inventory.lots.show', $item->material_lot_id) }}" class="purchase-destination">Fundición</a>
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="muted" style="text-align: center; padding: 2rem;">Esta compra no tiene piezas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="purchase-items-footer">
                    <div>
                        <span class="muted">Total de la compra</span>
                        @if ($negotiationComparison !== 0)
                            <span class="cell-sub">Sistema $ {{ number_format((float) $appraisedTotal, 2) }} · Negociación {{ $negotiationLabel }}</span>
                        @endif
                    </div>
                    <strong class="purchase-footer-total">$ {{ number_format((float) $purchase->total_amount, 2) }}</strong>
                </div>
            </div>

            @if ($hasAdjustments)
                <div class="card purchase-adjustments">
                    <div class="purchase-card-head">
                        <div>
                            <h2 class="font-serif">Ajustes registrados</h2>
                            <span class="purchase-card-meta">Correcciones auditadas sobre esta compra</span>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table class="purchase-responsive-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th class="text-right">Anterior</th>
                                    <th class="text-right">Nuevo</th>
                                    <th class="text-right">Diferencia</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->adjustments as $adjustment)
                                    @php($difference = (float) $adjustment->amount_difference)
                                    <tr>
                                        <td data-label="Fecha" class="num">{{ $adjustment->created_at?->format('d/m/Y H:i') }}</td>
                                        <td data-label="Usuario">{{ $adjustment->user?->name }}</td>
                                        <td data-label="Anterior" class="text-right num">$ {{ number_format((float) ($adjustment->old_values['total_amount'] ?? 0), 2) }}</td>
                                        <td data-label="Nuevo" class="text-right num">$ {{ number_format((float) ($adjustment->new_values['total_amount'] ?? 0), 2) }}</td>
                                        <td data-label="Diferencia" class="text-right num {{ $difference >= 0 ? 'is-positive' : 'is-negative' }}">
                                            {{ $difference >= 0 ? '+' : '' }}$ {{ number_format($difference, 2) }}
                                        </td>
                                        <td data-label="Motivo">{{ $adjustment->reason }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <aside class="purchase-side">
            <section class="card purchase-side-card">
                <div class="purchase-side-head">
                    <span class="purchase-side-icon" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 21a8 8 0 0116 0"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 class="font-serif">Vendedor</h2>
                        <p>Datos de contacto</p>
                    </div>
                </div>
                <div class="purchase-customer">
                    <span class="purchase-avatar">{{ $customerInitials }}</span>
                    <div style="min-width: 0;">
                        <strong>{{ $customerName }}</strong>
                        <span>{{ $customerDocument !== '' ? $customerDocument : 'Sin documento registrado' }}</span>
                    </div>
                </div>
                @if ($purchase->customer?->phone || $purchase->customer?->email || $purchase->customer?->address)
                    <dl class="purchase-info-list">
                        @if ($purchase->customer?->phone)
                            <div class="purchase-info-row">
                                <dt>Teléfono</dt>
                                <dd>{{ $purchase->customer->phone }}</dd>
                            </div>
                        @endif
                        @if ($purchase->customer?->email)
                            <div class="purchase-info-row">
                                <dt>Correo</dt>
                                <dd>{{ $purchase->customer->email }}</dd>
                            </div>
                        @endif
                        @if ($purchase->customer?->address)
                            <div class="purchase-info-row purchase-info-row--full">
                                <dt>Dirección</dt>
                                <dd>{{ $purchase->customer->address }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif
            </section>

            <section class="card purchase-side-card">
                <div class="purchase-side-head">
                    <span class="purchase-side-icon" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="4" width="14" height="17" rx="2"></rect>
                            <path d="M9 4V2h6v2M9 9h6M9 13h6M9 17h4"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 class="font-serif">Registro y aprobación</h2>
                        <p>Trazabilidad de la compra</p>
                    </div>
                </div>
                <dl class="purchase-info-list">
                    <div class="purchase-info-row">
                        <dt>Registró</dt>
                        <dd>{{ $purchase->user?->name ?? '—' }}</dd>
                    </div>
                    <div class="purchase-info-row">
                        <dt>Fecha</dt>
                        <dd>{{ $purchase->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="purchase-info-row">
                        <dt>Pago</dt>
                        <dd>{{ $purchase->payout_method->label() }}</dd>
                    </div>
                    @if ($purchase->approved_at)
                        <div class="purchase-info-row">
                            <dt>{{ $isRejected ? 'Rechazó' : 'Aprobó' }}</dt>
                            <dd>{{ $purchase->approvedBy?->name ?? '—' }}</dd>
                        </div>
                        <div class="purchase-info-row">
                            <dt>Fecha decisión</dt>
                            <dd>{{ $purchase->approved_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($purchase->approval_note)
                        <div class="purchase-info-row purchase-info-row--full">
                            <dt>Nota de decisión</dt>
                            <dd>{{ $purchase->approval_note }}</dd>
                        </div>
                    @endif
                    @if ($purchase->notes)
                        <div class="purchase-info-row purchase-info-row--full">
                            <dt>Notas</dt>
                            <dd>{{ $purchase->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            @if ($isPending && ! $canApprove)
                <div class="purchase-pending-note">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 0.1rem;">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 8v5M12 16h.01"></path>
                    </svg>
                    <span>Esta compra espera la aprobación de gerencia. Las piezas y el efectivo se moverán al aprobarse.</span>
                </div>
            @endif
        </aside>
    </div>

    @if ($canApprove && $isPending)
        <div class="card purchase-approval">
            <div class="purchase-approval-head">
                <h2 class="font-serif">Decisión de gerencia</h2>
                <p>Al aprobar, las piezas entran al inventario y el efectivo sale de la caja. Al rechazar, no hay movimientos.</p>
            </div>
            <div class="purchase-approval-grid">
                <div class="purchase-approval-box">
                    <h3>Aprobar compra</h3>
                    <p>Liquida la compra con el método de pago registrado.</p>
                    <form action="{{ route('admin.purchases.approve', $purchase) }}" method="POST">
                        @csrf
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="approval_note">Nota (opcional)</label>
                            <input type="text" name="approval_note" id="approval_note" class="form-control" maxlength="2000">
                        </div>
                        <button type="submit" class="btn btn-dark">Aprobar y liquidar</button>
                    </form>
                </div>
                <div class="purchase-approval-box">
                    <h3>Rechazar compra</h3>
                    <p>La compra queda registrada como rechazada.</p>
                    <form action="{{ route('admin.purchases.reject', $purchase) }}" method="POST" onsubmit="return confirm('¿Confirmas rechazar la compra {{ $purchase->number }}?');">
                        @csrf
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="reason">Motivo del rechazo <span style="color: var(--color-error);">*</span></label>
                            <input type="text" name="reason" id="reason" class="form-control" maxlength="255" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Rechazar compra</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
