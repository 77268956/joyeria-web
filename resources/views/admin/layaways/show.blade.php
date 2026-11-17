@extends('layouts.admin')

@section('title', 'Apartado '.$layaway->number)

@section('body_class', 'full-viewport')

@section('content')
@php
    $payments = $layaway->payments
        ->sortByDesc(fn ($payment) => $payment->created_at?->getTimestamp() ?? 0)
        ->values();
    $paymentCount = $payments->count();
    $lastPayment = $payments->first();
    $isActive = $layaway->isActive();
    $isCancelled = $layaway->isCancelled();
    $isCompleted = $layaway->isCompleted();
    $isFullyPaid = $layaway->isFullyPaid();
    $badge = match ($layaway->status) {
        \App\Enums\LayawayStatus::Completed => 'badge-success',
        \App\Enums\LayawayStatus::Active => 'badge-gold',
        default => 'badge-danger',
    };
    $paidPercent = bccomp((string) $layaway->total_price, '0', 2) === 1
        ? bcmul(bcdiv((string) $layaway->paid_total, (string) $layaway->total_price, 6), '100', 2)
        : '0.00';
    $progressWidth = min(100, max(0, (float) $paidPercent));
    $paidPercentLabel = number_format((float) $paidPercent, 1);
    $daysRemaining = $isActive && $layaway->due_date
        ? (int) now()->startOfDay()->diffInDays($layaway->due_date->copy()->startOfDay(), false)
        : null;
    $overdueDays = $daysRemaining !== null && $daysRemaining < 0 ? abs($daysRemaining) : 0;
    $isOverdue = $isActive && $daysRemaining !== null && $daysRemaining < 0;
    $dueHint = match (true) {
        ! $isActive => $layaway->status->label(),
        $daysRemaining === null => 'Sin fecha de vencimiento',
        $daysRemaining < 0 => 'Vencido hace '.$overdueDays.' '.($overdueDays === 1 ? 'día' : 'días'),
        $daysRemaining === 0 => 'Vence hoy',
        $daysRemaining === 1 => 'Vence mañana',
        default => 'Vence en '.$daysRemaining.' días',
    };
    $customer = $layaway->customer;
    $product = $layaway->product;
    $photo = $product?->primaryPhoto;
    $customerInitial = $customer ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customer->full_name, 0, 1)) : '—';
@endphp

<style>
    .layaway-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 0;
    }

    .layaway-page .page-header {
        flex-shrink: 0;
        margin-bottom: 0;
    }

    .layaway-page .alert {
        flex-shrink: 0;
        margin-bottom: 0;
    }

    .layaway-title-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .layaway-notice {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        flex-shrink: 0;
        padding: 0.65rem 0.85rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .layaway-notice svg {
        flex-shrink: 0;
        margin-top: 0.1rem;
    }

    .layaway-notice strong {
        display: block;
        margin-bottom: 0.1rem;
    }

    .layaway-notice--warning {
        border-color: #E8D5A6;
        background-color: #FDF9EE;
        color: #6D571D;
    }

    .layaway-notice--success {
        border-color: #C7E4CB;
        background-color: #F3FAF4;
        color: #235B2B;
    }

    .layaway-notice--error {
        border-color: #E0B4B4;
        background-color: #FFFBFB;
        color: #5F2120;
    }

    .layaway-stats {
        flex-shrink: 0;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .layaway-stat {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.5rem 0.7rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
    }

    .layaway-stat-icon {
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

    .layaway-stat-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        order: 1;
    }

    .layaway-stat-label {
        color: var(--color-piedra);
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .layaway-stat-value {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.15rem;
        font-weight: 600;
        line-height: 1.15;
    }

    .layaway-stat-hint {
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.67rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .layaway-layout {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 1rem;
        min-height: 0;
    }

    .layaway-main {
        display: flex;
        min-height: 0;
    }

    .layaway-side {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 0;
        overflow-y: auto;
        padding-right: 0.15rem;
    }

    .layaway-panel-progress {
        order: 1;
    }

    .layaway-panel-payment {
        order: 2;
    }

    .layaway-panel-delivery {
        order: 3;
    }

    .layaway-panel-cancel {
        order: 4;
    }

    .layaway-panel-customer {
        order: 5;
    }

    .layaway-panel-piece {
        order: 6;
    }

    .layaway-card {
        padding: 0.9rem 1rem;
        margin-bottom: 0;
    }

    .layaway-card-title {
        margin: 0 0 0.65rem;
        font-size: 1.05rem;
    }

    .layaway-table-card {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-height: 0;
        padding: 0;
        overflow: hidden;
    }

    .layaway-table-card .table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
    }

    .layaway-table-card .data-table {
        min-width: 620px;
    }

    .layaway-table-card .data-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: var(--color-crema);
    }

    .layaway-table-head {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .layaway-table-head h2 {
        margin: 0;
        font-size: 1.15rem;
    }

    .layaway-table-meta {
        color: var(--color-piedra);
        font-size: 0.78rem;
    }

    .layaway-table-total {
        flex-shrink: 0;
        text-align: right;
    }

    .layaway-table-total span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.6rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .layaway-table-total strong {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.2rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .layaway-table-footer {
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

    .layaway-payment-date {
        display: block;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .layaway-payment-time {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
    }

    .layaway-reference {
        color: var(--color-piedra);
        font-size: 0.76rem;
    }

    .layaway-payment-amount {
        color: var(--color-noche);
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .layaway-empty {
        padding: 2.2rem 1rem;
        color: var(--color-piedra);
        font-size: 0.82rem;
        text-align: center;
    }

    .layaway-progress-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .layaway-progress-head h2 {
        margin: 0;
        font-size: 1.05rem;
    }

    .layaway-progress-head strong {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.35rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .layaway-progress {
        height: 8px;
        margin: 0.65rem 0 0.45rem;
        overflow: hidden;
        border-radius: 999px;
        background-color: var(--color-crema);
    }

    .layaway-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--color-dorado), #D8C08A);
        transition: width 0.4s ease;
    }

    .layaway-progress-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .layaway-info-list {
        display: grid;
        gap: 0.5rem;
        margin-top: 0.8rem;
    }

    .layaway-info-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.4rem;
        border-bottom: 1px dashed var(--color-linea);
    }

    .layaway-info-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .layaway-info-row dt {
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .layaway-info-row dd {
        margin: 0;
        color: var(--color-noche);
        font-size: 0.86rem;
        font-weight: 500;
        text-align: right;
    }

    .layaway-info-row--strong dd {
        font-family: var(--font-serif);
        font-size: 1.15rem;
        font-weight: 600;
    }

    .layaway-person {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .layaway-avatar {
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 50%;
        background-color: var(--color-crema);
        color: var(--color-dorado);
        font-family: var(--font-serif);
        font-size: 1rem;
        font-weight: 600;
    }

    .layaway-person-name {
        display: block;
        color: var(--color-noche);
        font-size: 0.9rem;
        font-weight: 600;
    }

    .layaway-person-meta {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .layaway-contact-list {
        display: grid;
        gap: 0.35rem;
        margin-top: 0.75rem;
        padding-top: 0.65rem;
        border-top: 1px dashed var(--color-linea);
        color: var(--color-piedra);
        font-size: 0.76rem;
    }

    .layaway-contact-list span {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .layaway-piece {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .layaway-piece-media {
        display: grid;
        place-items: center;
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        overflow: hidden;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-crema);
        color: var(--color-dorado);
    }

    .layaway-piece-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .layaway-piece-name {
        display: block;
        color: var(--color-noche);
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .layaway-piece-meta {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .layaway-piece-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem 0.75rem;
        margin-top: 0.65rem;
        padding-top: 0.55rem;
        border-top: 1px dashed var(--color-linea);
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .layaway-card-link {
        width: 100%;
        justify-content: center;
        margin-top: 0.7rem;
    }

    .layaway-payment-card .form-group {
        margin-bottom: 0.6rem;
    }

    .layaway-form-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 0.55rem;
    }

    .layaway-form-row .form-group {
        margin: 0;
    }

    .layaway-payment-card .btn,
    .layaway-cancel-body .btn {
        width: 100%;
        justify-content: center;
    }

    .layaway-action-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .layaway-action-head h2 {
        margin: 0;
        font-size: 1.05rem;
    }

    .layaway-action-copy {
        margin: 0.5rem 0 0.75rem;
        color: var(--color-piedra);
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .layaway-cancel-card {
        padding: 0;
        overflow: hidden;
    }

    .layaway-cancel-card summary {
        padding: 0.85rem 1rem;
        color: var(--color-error);
        cursor: pointer;
        font-family: var(--font-serif);
        font-size: 1.05rem;
        font-weight: 600;
        list-style: none;
    }

    .layaway-cancel-card summary::-webkit-details-marker {
        display: none;
    }

    .layaway-cancel-card summary::after {
        content: '+';
        float: right;
        font-family: var(--font-sans);
        font-size: 1rem;
    }

    .layaway-cancel-card[open] summary::after {
        content: '-';
    }

    .layaway-cancel-body {
        padding: 0 1rem 1rem;
        border-top: 1px solid var(--color-linea);
    }

    .layaway-cancel-body p {
        margin: 0.7rem 0;
        color: var(--color-piedra);
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .layaway-cancel-body .form-group {
        margin-bottom: 0.6rem;
    }

    @media (prefers-reduced-motion: reduce) {
        .layaway-progress span {
            transition: none;
        }
    }

    @media (max-width: 1100px) {
        .layaway-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .layaway-layout {
            flex: none;
            grid-template-columns: minmax(0, 1fr);
        }

        .layaway-main {
            display: block;
        }

        .layaway-side {
            overflow: visible;
            padding-right: 0;
        }

        .layaway-table-card {
            min-height: 320px;
        }
    }

    @media (max-width: 640px) {
        .layaway-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .layaway-header .page-actions {
            width: 100%;
        }

        .layaway-header .page-actions .btn {
            flex: 1;
        }

        .layaway-stats {
            grid-template-columns: minmax(0, 1fr);
        }

        .layaway-card {
            padding: 0.85rem;
        }

        .layaway-table-head {
            align-items: flex-start;
            flex-direction: column;
            padding: 0.85rem;
        }

        .layaway-table-footer {
            padding: 0.7rem 0.85rem;
        }

        .layaway-form-row {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<div class="layaway-page">
    <div class="page-header layaway-header">
        <div>
            <div class="layaway-title-row">
                <h1 class="page-title font-serif">Apartado {{ $layaway->number }}</h1>
                <span class="badge {{ $badge }}">{{ $layaway->status->label() }}</span>
            </div>
            <p class="page-subtitle">
                Abierto el {{ $layaway->created_at?->format('d/m/Y H:i') }}
                &middot; {{ $layaway->user?->name ?? 'Usuario no disponible' }}
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.layaways.index') }}" class="btn btn-outline">Volver</a>
            @if ($layaway->completedSale)
                <a href="{{ route('admin.sales.show', $layaway->completedSale) }}" class="btn btn-dark">Ver venta</a>
            @endif
        </div>
    </div>

    @include('admin.partials.alerts')

    @if ($isCancelled)
        <div class="layaway-notice layaway-notice--error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M15 9l-6 6M9 9l6 6"></path>
            </svg>
            <div>
                <strong>Apartado cancelado</strong>
                {{ $layaway->cancel_reason ?: 'La pieza volvió al inventario disponible.' }}
            </div>
        </div>
    @elseif ($isCompleted)
        <div class="layaway-notice layaway-notice--success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div>
                <strong>Apartado entregado</strong>
                @if ($layaway->completedSale)
                    Se generó la venta {{ $layaway->completedSale->number }} el {{ $layaway->completedSale->created_at?->format('d/m/Y H:i') }}.
                @else
                    La pieza fue entregada al cliente.
                @endif
            </div>
        </div>
    @elseif ($isOverdue)
        <div class="layaway-notice layaway-notice--warning">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <path d="M12 9v4M12 17h.01"></path>
            </svg>
            <div>
                <strong>Apartado vencido</strong>
                La fecha límite era el {{ $layaway->due_date?->format('d/m/Y') }}. Contacta al cliente para regularizar el saldo.
            </div>
        </div>
    @elseif ($isActive && $isFullyPaid)
        <div class="layaway-notice layaway-notice--success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div>
                <strong>Saldo liquidado</strong>
                El apartado está listo para entregarse al cliente.
            </div>
        </div>
    @endif

    <div class="layaway-stats">
        <div class="layaway-stat">
            <span class="layaway-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                    <circle cx="12" cy="12" r="2.5"></circle>
                    <path d="M6.5 12h.01M17.5 12h.01"></path>
                </svg>
            </span>
            <div class="layaway-stat-body">
                <span class="layaway-stat-label">Total</span>
                <strong class="layaway-stat-value">$ {{ number_format((float) $layaway->total_price, 2) }}</strong>
                <span class="layaway-stat-hint">Precio del apartado</span>
            </div>
        </div>
        <div class="layaway-stat">
            <span class="layaway-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v18M5 10l7-7 7 7"></path>
                </svg>
            </span>
            <div class="layaway-stat-body">
                <span class="layaway-stat-label">Abonado</span>
                <strong class="layaway-stat-value">$ {{ number_format((float) $layaway->paid_total, 2) }}</strong>
                <span class="layaway-stat-hint">{{ $paymentCount }} {{ $paymentCount === 1 ? 'abono registrado' : 'abonos registrados' }}</span>
            </div>
        </div>
        <div class="layaway-stat">
            <span class="layaway-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v5l3 2"></path>
                </svg>
            </span>
            <div class="layaway-stat-body">
                <span class="layaway-stat-label">Saldo</span>
                <strong class="layaway-stat-value">$ {{ number_format((float) $layaway->balance, 2) }}</strong>
                <span class="layaway-stat-hint">{{ $isFullyPaid ? 'Saldo liquidado' : ($isCancelled ? 'Apartado cancelado' : 'Pendiente de pago') }}</span>
            </div>
        </div>
        <div class="layaway-stat">
            <span class="layaway-stat-icon" aria-hidden="true">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                    <path d="M16 2v4M8 2v4M3 9h18"></path>
                </svg>
            </span>
            <div class="layaway-stat-body">
                <span class="layaway-stat-label">Vence</span>
                <strong class="layaway-stat-value">{{ $layaway->due_date?->format('d/m/Y') ?? '—' }}</strong>
                <span class="layaway-stat-hint">{{ $dueHint }}</span>
            </div>
        </div>
    </div>

    <div class="layaway-layout">
        <div class="layaway-main">
            <div class="card layaway-card layaway-table-card">
                <div class="layaway-table-head">
                    <div>
                        <h2 class="font-serif">Abonos registrados</h2>
                        <span class="layaway-table-meta">
                            {{ $paymentCount }} {{ $paymentCount === 1 ? 'abono' : 'abonos' }}
                            @if ($lastPayment)
                                &middot; Último: {{ $lastPayment->created_at?->format('d/m/Y H:i') }}
                            @endif
                        </span>
                    </div>
                    <div class="layaway-table-total">
                        <span>Total abonado</span>
                        <strong>$ {{ number_format((float) $layaway->paid_total, 2) }}</strong>
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Usuario</th>
                                <th class="text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                @php
                                    $methodBadge = match ($payment->method) {
                                        \App\Enums\PaymentMethod::Cash => 'badge-success',
                                        \App\Enums\PaymentMethod::Card => 'badge-info',
                                        \App\Enums\PaymentMethod::Transfer => 'badge-gold',
                                        default => 'badge-muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <span class="layaway-payment-date">{{ $payment->created_at?->format('d/m/Y') }}</span>
                                        <span class="layaway-payment-time">{{ $payment->created_at?->format('H:i') }}</span>
                                    </td>
                                    <td><span class="badge {{ $methodBadge }}">{{ $payment->method->label() }}</span></td>
                                    <td><span class="layaway-reference">{{ $payment->reference ?: '—' }}</span></td>
                                    <td>{{ $payment->user?->name ?? '—' }}</td>
                                    <td class="text-right num layaway-payment-amount">$ {{ number_format((float) $payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="layaway-empty">
                                            Aún no se han registrado abonos para este apartado.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="layaway-table-footer">
                    <span class="muted">Acumulado de abonos</span>
                    <strong class="font-serif" style="font-size: 1.1rem;">$ {{ number_format((float) $layaway->paid_total, 2) }}</strong>
                </div>
            </div>
        </div>

        <aside class="layaway-side">
            <div class="card layaway-card layaway-panel-progress">
                <div class="layaway-progress-head">
                    <h2 class="font-serif">Progreso del pago</h2>
                    <strong>{{ $paidPercentLabel }}%</strong>
                </div>
                <div class="layaway-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressWidth }}">
                    <span style="width: {{ $progressWidth }}%;"></span>
                </div>
                <div class="layaway-progress-meta">
                    <span>Abonado $ {{ number_format((float) $layaway->paid_total, 2) }}</span>
                    <span>Falta $ {{ number_format((float) $layaway->balance, 2) }}</span>
                </div>
                <dl class="layaway-info-list">
                    <div class="layaway-info-row">
                        <dt>Precio total</dt>
                        <dd>$ {{ number_format((float) $layaway->total_price, 2) }}</dd>
                    </div>
                    <div class="layaway-info-row">
                        <dt>Abonado</dt>
                        <dd>$ {{ number_format((float) $layaway->paid_total, 2) }}</dd>
                    </div>
                    <div class="layaway-info-row layaway-info-row--strong">
                        <dt>Saldo pendiente</dt>
                        <dd>$ {{ number_format((float) $layaway->balance, 2) }}</dd>
                    </div>
                    <div class="layaway-info-row">
                        <dt>Apertura</dt>
                        <dd>{{ $layaway->created_at?->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card layaway-card layaway-panel-customer">
                <h2 class="font-serif layaway-card-title">Cliente</h2>
                <div class="layaway-person">
                    <span class="layaway-avatar" aria-hidden="true">{{ $customerInitial }}</span>
                    <div>
                        <span class="layaway-person-name">{{ $customer?->full_name ?? 'Cliente no disponible' }}</span>
                        <span class="layaway-person-meta">
                            @if ($customer)
                                {{ $customer->document_type }} {{ $customer->document_number }}
                            @else
                                Sin documento
                            @endif
                        </span>
                    </div>
                </div>
                @if ($customer && ($customer->phone || $customer->email))
                    <div class="layaway-contact-list">
                        @if ($customer->phone)
                            <span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                {{ $customer->phone }}
                            </span>
                        @endif
                        @if ($customer->email)
                            <span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="M3 7l9 6 9-6"></path>
                                </svg>
                                {{ $customer->email }}
                            </span>
                        @endif
                    </div>
                @endif
                @if ($customer)
                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline btn-sm layaway-card-link">Ver cliente</a>
                @endif
            </div>

            <div class="card layaway-card layaway-panel-piece">
                <h2 class="font-serif layaway-card-title">Pieza apartada</h2>
                <div class="layaway-piece">
                    <div class="layaway-piece-media">
                        @if ($photo)
                            <img src="{{ $photo->url }}" alt="Fotografía de {{ $product?->name }}">
                        @else
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M6 3h12l4 6-10 12L2 9z"></path>
                                <path d="M2 9h20"></path>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <span class="layaway-piece-name">{{ $product?->name ?? 'Pieza no disponible' }}</span>
                        <span class="layaway-piece-meta">{{ $product?->sku ?? 'Sin SKU' }}</span>
                    </div>
                </div>
                <div class="layaway-piece-specs">
                    @if ($product?->metal)
                        <span>{{ $product->metal->name }}</span>
                    @endif
                    @if ($product?->purity)
                        <span>{{ $product->purity->label }}</span>
                    @endif
                    @if ($product)
                        <span>{{ number_format((float) $product->weight_g, 2) }} g</span>
                    @endif
                </div>
                @if ($product)
                    <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline btn-sm layaway-card-link">Ver pieza</a>
                @endif
            </div>

            @if ($isActive)
                @unless ($isFullyPaid)
                    <div class="card layaway-card layaway-payment-card layaway-panel-payment">
                        <div class="layaway-action-head">
                            <h2 class="font-serif">Registrar abono</h2>
                            <span class="badge badge-gold">Saldo $ {{ number_format((float) $layaway->balance, 2) }}</span>
                        </div>
                        <form action="{{ route('admin.layaways.payment', $layaway) }}" method="POST">
                            @csrf
                            <div class="layaway-form-row">
                                <div class="form-group">
                                    <label class="form-label" for="method">Método</label>
                                    <select name="method" id="method" class="form-select">
                                        @foreach ($paymentMethods as $method)
                                            <option value="{{ $method->value }}" {{ old('method') === $method->value ? 'selected' : '' }}>{{ $method->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="amount">Monto</label>
                                    <input type="number" step="0.01" min="0.01" max="{{ $layaway->balance }}" name="amount" id="amount" class="form-control num" value="{{ old('amount', $layaway->balance) }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="reference">Referencia (opcional)</label>
                                <input type="text" name="reference" id="reference" class="form-control" maxlength="80" value="{{ old('reference') }}">
                            </div>
                            <button type="submit" class="btn btn-gold">Registrar abono</button>
                        </form>
                    </div>
                @endunless

                <div class="card layaway-card layaway-panel-delivery">
                    <div class="layaway-action-head">
                        <h2 class="font-serif">Entrega</h2>
                        <span class="badge {{ $isFullyPaid ? 'badge-success' : 'badge-muted' }}">{{ $isFullyPaid ? 'Lista' : 'Pendiente' }}</span>
                    </div>
                    @if ($canComplete)
                        <p class="layaway-action-copy">El saldo está liquidado. Al entregar, la pieza pasa a vendida y se genera la venta final.</p>
                        <form action="{{ route('admin.layaways.complete', $layaway) }}" method="POST" onsubmit="return confirm('¿Confirmas entregar el apartado {{ $layaway->number }}?');">
                            @csrf
                            <button type="submit" class="btn btn-dark" style="width: 100%; justify-content: center;">Entregar pieza</button>
                        </form>
                    @else
                        <p class="layaway-action-copy">La pieza podrá entregarse cuando el saldo pendiente llegue a cero.</p>
                    @endif
                </div>

                <details class="card layaway-card layaway-cancel-card layaway-panel-cancel">
                    <summary>Cancelar apartado</summary>
                    <div class="layaway-cancel-body">
                        <p>La pieza vuelve al inventario disponible. Los abonos registrados no se reintegran automáticamente.</p>
                        <form action="{{ route('admin.layaways.cancel', $layaway) }}" method="POST" onsubmit="return confirm('¿Confirmas cancelar el apartado {{ $layaway->number }}?');">
                            @csrf
                            <div class="form-group">
                                <label class="form-label" for="reason">Motivo de la cancelación <span style="color: var(--color-error);">*</span></label>
                                <input type="text" name="reason" id="reason" class="form-control" maxlength="255" value="{{ old('reason') }}" required>
                            </div>
                            <button type="submit" class="btn btn-danger">Cancelar apartado</button>
                        </form>
                    </div>
                </details>
            @endif
        </aside>
    </div>
</div>
@endsection
