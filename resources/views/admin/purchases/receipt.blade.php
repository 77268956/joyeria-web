<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $purchase->number }}</title>
    <style>
        @page { margin: 30px 34px 40px; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: #2B2B2B;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }

        table { width: 100%; border-collapse: collapse; }
        .muted { color: #6B6B70; }
        .text-right { text-align: right; }
        .num { font-variant-numeric: tabular-nums; }
        .nowrap { white-space: nowrap; }

        .header {
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 3px solid #B89A5E;
        }

        .header > tbody > tr > td { vertical-align: top; }

        .brand {
            color: #12161F;
            font-family: Georgia, "DejaVu Serif", serif;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 0.03em;
        }

        .tagline {
            margin-top: 2px;
            color: #B89A5E;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }

        .brand-meta {
            margin-top: 7px;
            color: #6B6B70;
            font-size: 8.5px;
            line-height: 1.5;
        }

        .doc-cell { width: 235px; text-align: right; }

        .doc-box {
            display: inline-block;
            min-width: 210px;
            padding: 8px 11px;
            border: 1px solid #D8CFBA;
            background: #FCFAF5;
            text-align: right;
        }

        .doc-label {
            color: #8A6516;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .doc-number {
            margin: 1px 0 3px;
            color: #12161F;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.02em;
        }

        .doc-date { color: #6B6B70; font-size: 8.5px; }

        .status {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 9px;
            border-radius: 9px;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .status--paid { background: #E4F1E6; color: #2F6B3D; }
        .status--pending { background: #FBF3DD; color: #8A6516; }
        .status--rejected { background: #F8E3E3; color: #A63D40; }
        .status--adjusted { background: #E9EDF5; color: #33517F; }

        .notice {
            margin-bottom: 13px;
            padding: 7px 10px;
            border-left: 3px solid #B89A5E;
            background: #FBF6EA;
            color: #4B4B4B;
            font-size: 8.5px;
            line-height: 1.45;
        }

        .notice--pending { border-left-color: #B88916; background: #FCF6E4; }
        .notice--rejected { border-left-color: #A63D40; background: #FBEEEE; }
        .notice--adjusted { border-left-color: #33517F; background: #EEF2F8; }

        .parties { margin-bottom: 14px; page-break-inside: avoid; }
        .parties > tbody > tr > td { vertical-align: top; }
        .party { width: 54%; padding-right: 14px; }
        .party--right { width: 46%; padding-right: 0; }

        .panel {
            padding: 9px 11px;
            border: 1px solid #E4DFD3;
            background: #FCFBF8;
        }

        .eyebrow {
            margin-bottom: 5px;
            padding-bottom: 4px;
            border-bottom: 1px solid #E4DFD3;
            color: #B89A5E;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .party-name { color: #12161F; font-size: 12.5px; font-weight: bold; }
        .party-line { margin-top: 2px; color: #4B4B4B; font-size: 9px; }

        .meta-table td { padding: 1.5px 0; font-size: 8.8px; vertical-align: top; }
        .meta-table td.label { width: 96px; color: #6B6B70; }
        .meta-table td.value { color: #12161F; font-weight: bold; text-align: right; }

        .section-title {
            margin: 0 0 5px;
            color: #12161F;
            font-family: Georgia, "DejaVu Serif", serif;
            font-size: 11px;
            font-weight: bold;
        }

        .items { page-break-inside: auto; }
        .items thead { display: table-header-group; }
        .items tbody tr { page-break-inside: avoid; }

        .items th {
            padding: 6px 5px;
            border: 1px solid #12161F;
            background: #12161F;
            color: #F7F4EE;
            font-size: 7.8px;
            font-weight: bold;
            letter-spacing: 0.04em;
            text-align: left;
            text-transform: uppercase;
        }

        .items td {
            padding: 5px 5px;
            border: 1px solid #E4DFD3;
            font-size: 8.8px;
            vertical-align: top;
        }

        .items tbody tr:nth-child(even) td { background: #FAF8F3; }
        .items .item-name { color: #12161F; font-weight: bold; }
        .items .item-sub { display: block; margin-top: 1px; color: #6B6B70; font-size: 7.8px; }
        .items .money { color: #12161F; font-weight: bold; }
        .items .diff { display: block; margin-top: 1px; color: #6B6B70; font-size: 7.4px; }

        .summary { margin-top: 9px; page-break-inside: avoid; }
        .summary > tbody > tr > td { vertical-align: top; }

        .summary-stats { width: 59%; padding-right: 12px; }
        .stats-table { width: 100%; }
        .stats-table td {
            padding: 6px 7px;
            border: 1px solid #E4DFD3;
            background: #FAF8F3;
        }

        .stat-label {
            display: block;
            color: #6B6B70;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .stat-value {
            display: block;
            margin-top: 1px;
            color: #12161F;
            font-size: 10.5px;
            font-weight: bold;
        }

        .total-box { width: 41%; }
        .total-inner {
            padding: 9px 11px;
            border: 1px solid #B89A5E;
            background: #FBF6EA;
        }

        .total-label {
            color: #8A6516;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .total-value {
            margin-top: 1px;
            color: #12161F;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.01em;
        }

        .total-method { margin-top: 2px; color: #6B6B70; font-size: 8.2px; }
        .total-breakdown {
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #E4D8BC;
            color: #6B6B70;
            font-size: 7.8px;
            line-height: 1.5;
        }

        .legal { margin-top: 18px; page-break-inside: avoid; }

        .disclaimer {
            padding: 9px 11px;
            border-left: 3px solid #B89A5E;
            background: #FAF8F3;
            color: #4B4B4B;
            font-size: 8.2px;
            line-height: 1.55;
            text-align: justify;
        }

        .signatures { margin-top: 38px; page-break-inside: avoid; }
        .signatures td { width: 50%; padding-right: 24px; vertical-align: bottom; }
        .signatures td:last-child { padding-right: 0; padding-left: 24px; }

        .sign-line {
            padding-top: 5px;
            border-top: 1px solid #2B2B2B;
            color: #4B4B4B;
            font-size: 8.2px;
        }

        .sign-name { margin-top: 2px; color: #12161F; font-size: 8.8px; font-weight: bold; }

        .footer {
            margin-top: 22px;
            padding-top: 7px;
            border-top: 1px solid #E4DFD3;
            color: #8A8A8F;
            font-size: 7.2px;
            line-height: 1.5;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $itemsCount = $purchase->items->count();
        $grossWeight = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, (string) $item->gross_weight_g, 3), '0.000');
        $netWeight = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, (string) $item->net_weight_g, 3), '0.000');
        $appraisedTotal = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, $item->appraisedAmount(), 2), '0.00');
        $negotiationDifference = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, $item->negotiationDifference(), 2), '0.00');
        $hasNegotiation = bccomp($negotiationDifference, '0', 2) !== 0;
        $differenceIsPositive = bccomp($negotiationDifference, '0', 2) > 0;

        $statusClass = match ($purchase->status) {
            \App\Enums\MetalPurchaseStatus::Paid => 'status--paid',
            \App\Enums\MetalPurchaseStatus::Adjusted => 'status--adjusted',
            \App\Enums\MetalPurchaseStatus::PendingApproval => 'status--pending',
            default => 'status--rejected',
        };

        $statusLabel = match ($purchase->status) {
            \App\Enums\MetalPurchaseStatus::Paid => 'Pagada',
            \App\Enums\MetalPurchaseStatus::Adjusted => 'Ajustada',
            \App\Enums\MetalPurchaseStatus::PendingApproval => 'Pendiente',
            default => 'Rechazada',
        };

        $totalLabel = match ($purchase->status) {
            \App\Enums\MetalPurchaseStatus::PendingApproval => 'Total a pagar',
            \App\Enums\MetalPurchaseStatus::Rejected => 'Total ofrecido',
            default => 'Total pagado',
        };

        $statusMessage = match ($purchase->status) {
            \App\Enums\MetalPurchaseStatus::PendingApproval => 'Operación pendiente de aprobación. Este comprobante no acredita el pago hasta que la compra sea aprobada.',
            \App\Enums\MetalPurchaseStatus::Rejected => 'Operación rechazada. No se registró pago ni ingreso de piezas al inventario.',
            \App\Enums\MetalPurchaseStatus::Adjusted => 'Operación ajustada y auditada. El importe vigente es el total mostrado en este comprobante.',
            default => 'Pago registrado. Este comprobante acredita la compra de las piezas detalladas.',
        };

        $noticeClass = match ($purchase->status) {
            \App\Enums\MetalPurchaseStatus::PendingApproval => 'notice--pending',
            \App\Enums\MetalPurchaseStatus::Rejected => 'notice--rejected',
            \App\Enums\MetalPurchaseStatus::Adjusted => 'notice--adjusted',
            default => '',
        };

        $sellerName = $purchase->customer?->full_name ?? 'Vendedor no registrado';
        $sellerDocument = trim(($purchase->customer?->document_type ?? '').' '.($purchase->customer?->document_number ?? ''));
        $storeName = $purchase->store?->name ?? config('app.name');
        $storeContact = collect([
            $purchase->store?->address,
            $purchase->store?->phone ? 'Tel. '.$purchase->store->phone : null,
        ])->filter()->implode(' · ');
    @endphp

    <table class="header">
        <tr>
            <td>
                <div class="brand">{{ $storeName }}</div>
                <div class="tagline">Compraventa de metales preciosos</div>
                <div class="brand-meta">
                    @if ($storeContact)
                        {{ $storeContact }}<br>
                    @endif
                    Comprobante de compra &middot; Documento interno
                </div>
            </td>
            <td class="doc-cell">
                <div class="doc-box">
                    <div class="doc-label">Comprobante de compra</div>
                    <div class="doc-number">{{ $purchase->number }}</div>
                    <div class="doc-date">{{ $purchase->created_at?->format('d/m/Y H:i') }}</div>
                    <div class="status {{ $statusClass }}">{{ $statusLabel }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="notice {{ $noticeClass }}">{{ $statusMessage }}</div>

    <table class="parties">
        <tr>
            <td class="party">
                <div class="panel">
                    <div class="eyebrow">Vendedor</div>
                    <div class="party-name">{{ $sellerName }}</div>
                    @if ($sellerDocument !== '')
                        <div class="party-line">Documento: {{ $sellerDocument }}</div>
                    @endif
                    @if ($purchase->customer?->phone)
                        <div class="party-line">Teléfono: {{ $purchase->customer->phone }}</div>
                    @endif
                    @if ($purchase->customer?->email)
                        <div class="party-line">Correo: {{ $purchase->customer->email }}</div>
                    @endif
                    @if ($purchase->customer?->address)
                        <div class="party-line">Dirección: {{ $purchase->customer->address }}</div>
                    @endif
                </div>
            </td>
            <td class="party party--right">
                <div class="panel">
                    <div class="eyebrow">Detalle de la operación</div>
                    <table class="meta-table">
                        <tr>
                            <td class="label">Registró</td>
                            <td class="value">{{ $purchase->user?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Método de pago</td>
                            <td class="value">{{ $purchase->payout_method->label() }}</td>
                        </tr>
                        @if ($purchase->approvedBy)
                            <tr>
                                <td class="label">{{ $purchase->status === \App\Enums\MetalPurchaseStatus::Rejected ? 'Rechazó' : 'Aprobó' }}</td>
                                <td class="value">{{ $purchase->approvedBy->name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Fecha decisión</td>
                                <td class="value">{{ $purchase->approved_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Piezas recibidas</div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 20px;">#</th>
                <th>Descripción</th>
                <th style="width: 85px;">Metal / Ley</th>
                <th class="text-right" style="width: 78px;">Bruto / Neto</th>
                <th class="text-right" style="width: 58px;">Precio/g</th>
                <th class="text-right" style="width: 42px;">Desc.</th>
                <th class="text-right" style="width: 64px;">Valuación</th>
                <th class="text-right" style="width: 70px;">Acordado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchase->items as $index => $item)
                @php
                    $itemDifference = $item->negotiationDifference();
                    $itemHasDifference = bccomp($itemDifference, '0', 2) !== 0;
                    $itemDifferenceIsPositive = bccomp($itemDifference, '0', 2) > 0;
                @endphp
                <tr>
                    <td class="num">{{ $index + 1 }}</td>
                    <td>
                        <span class="item-name">{{ $item->description }}</span>
                        <span class="item-sub">{{ $item->destination->label() }}</span>
                    </td>
                    <td>
                        {{ $item->metal?->name ?? '—' }}
                        @if ($item->purity?->code)
                            &middot; {{ $item->purity->code }}
                        @endif
                        <span class="item-sub">Ley {{ $item->fineness_snapshot }}</span>
                    </td>
                    <td class="text-right num nowrap">
                        <span class="money">{{ number_format((float) $item->gross_weight_g, 3) }} g</span>
                        <span class="item-sub">{{ number_format((float) $item->net_weight_g, 3) }} g neto</span>
                    </td>
                    <td class="text-right num nowrap">$ {{ number_format((float) $item->price_per_gram_snapshot, 2) }}</td>
                    <td class="text-right num nowrap">{{ number_format((float) $item->discount_percent_snapshot, 2) }}%</td>
                    <td class="text-right num nowrap">$ {{ number_format((float) $item->appraisedAmount(), 2) }}</td>
                    <td class="text-right num nowrap">
                        <span class="money">$ {{ number_format((float) $item->amount, 2) }}</span>
                        @if ($itemHasDifference)
                            <span class="diff">
                                {{ $itemDifferenceIsPositive ? '+' : '-' }} $ {{ number_format((float) abs((float) $itemDifference), 2) }} vs sistema
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted" style="text-align: center; padding: 12px;">Esta compra no tiene piezas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="summary-stats">
                <table class="stats-table">
                    <tr>
                        <td style="width: 28%;">
                            <span class="stat-label">Piezas</span>
                            <span class="stat-value">{{ number_format($itemsCount) }}</span>
                        </td>
                        <td style="width: 36%;">
                            <span class="stat-label">Peso bruto</span>
                            <span class="stat-value">{{ number_format((float) $grossWeight, 3) }} g</span>
                        </td>
                        <td style="width: 36%;">
                            <span class="stat-label">Peso neto</span>
                            <span class="stat-value">{{ number_format((float) $netWeight, 3) }} g</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="stat-label">Valuación del sistema</span>
                            <span class="stat-value">$ {{ number_format((float) $appraisedTotal, 2) }}</span>
                        </td>
                        <td>
                            <span class="stat-label">Negociación</span>
                            <span class="stat-value">
                                @if ($hasNegotiation)
                                    {{ $differenceIsPositive ? '+' : '-' }} $ {{ number_format((float) abs((float) $negotiationDifference), 2) }}
                                @else
                                    Sin diferencia
                                @endif
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="total-box">
                <div class="total-inner">
                    <div class="total-label">{{ $totalLabel }}</div>
                    <div class="total-value">$ {{ number_format((float) $purchase->total_amount, 2) }}</div>
                    <div class="total-method">Forma de pago: {{ $purchase->payout_method->label() }}</div>
                    @if ($hasNegotiation)
                        <div class="total-breakdown">
                            Valuación sistema: $ {{ number_format((float) $appraisedTotal, 2) }}<br>
                            Diferencia negociada: {{ $differenceIsPositive ? '+' : '-' }} $ {{ number_format((float) abs((float) $negotiationDifference), 2) }}
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="legal">
        <div class="disclaimer">
            {{ $disclaimer ?: 'El vendedor declara ser el legítimo propietario de las piezas entregadas y que éstas no provienen de ningún acto ilícito. Manifiesta haber recibido el importe señalado, quedando la joyería facultada para su uso, transformación o venta.' }}
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <div class="sign-line">Firma del vendedor</div>
                    <div class="sign-name">{{ $sellerName }}</div>
                </td>
                <td>
                    <div class="sign-line">Firma y sello del establecimiento</div>
                    <div class="sign-name">{{ $storeName }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }} &middot; {{ $purchase->number }} &middot; Este comprobante no sustituye una factura fiscal.
    </div>
</body>
</html>
