<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $purchase->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #2b2b2b; margin: 0; padding: 24px; }
        h1 { font-size: 18px; margin: 0 0 2px; letter-spacing: 1px; }
        .muted { color: #6b6b6b; }
        .header { border-bottom: 2px solid #b89a5e; padding-bottom: 10px; margin-bottom: 14px; }
        .header .store { font-size: 16px; font-weight: bold; }
        .doc-title { text-align: right; }
        .doc-title .number { font-size: 15px; font-weight: bold; color: #b89a5e; }
        .row { width: 100%; }
        .row td { vertical-align: top; }
        table { width: 100%; border-collapse: collapse; }
        .items { margin-top: 12px; }
        .items th { background: #f1f0eb; border: 1px solid #d9d6cc; padding: 5px 6px; text-align: left; font-size: 10px; }
        .items td { border: 1px solid #e4e1d8; padding: 5px 6px; }
        .text-right { text-align: right; }
        .total-box { margin-top: 12px; width: 260px; float: right; }
        .total-box td { padding: 4px 6px; }
        .total-box .grand { border-top: 2px solid #b89a5e; font-size: 14px; font-weight: bold; }
        .section-title { font-size: 12px; font-weight: bold; margin: 16px 0 6px; color: #1e1e1e; }
        .data-table { margin-top: 8px; }
        .data-table td { padding: 3px 0; }
        .legal { margin-top: 150px; page-break-inside: avoid; }
        .legal .disclaimer { font-size: 10px; color: #4b4b4b; margin-bottom: 40px; }
        .sign-line { border-top: 1px solid #2b2b2b; width: 260px; padding-top: 4px; margin-top: 40px; font-size: 10px; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <table class="row">
            <tr>
                <td>
                    <div class="store">{{ $purchase->store?->name ?? config('app.name') }}</div>
                    <div class="muted">Comprobante de compraventa de metales</div>
                </td>
                <td class="doc-title">
                    <div class="number">{{ $purchase->number }}</div>
                    <div class="muted">{{ $purchase->created_at?->format('d/m/Y H:i') }}</div>
                    <div class="muted">Registró: {{ $purchase->user?->name }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Datos del vendedor</div>
    <table class="data-table">
        <tr>
            <td style="width: 130px;" class="muted">Nombre</td>
            <td>{{ $purchase->customer?->full_name }}</td>
        </tr>
        <tr>
            <td class="muted">Documento</td>
            <td>{{ $purchase->customer?->document_type }} {{ $purchase->customer?->document_number }}</td>
        </tr>
        @if ($purchase->customer?->address)
            <tr>
                <td class="muted">Dirección</td>
                <td>{{ $purchase->customer->address }}</td>
            </tr>
        @endif
        <tr>
            <td class="muted">Método de pago</td>
            <td>{{ $purchase->payout_method->label() }}</td>
        </tr>
    </table>

    <div class="section-title">Piezas recibidas</div>
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Descripción</th>
                <th>Metal / Ley</th>
                <th class="text-right">Bruto</th>
                <th class="text-right">Neto</th>
                <th class="text-right">Precio/g</th>
                <th class="text-right">Desc.</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->metal?->name }}@if ($item->purity?->code) · {{ $item->purity->code }}@endif (ley {{ $item->fineness_snapshot }})</td>
                    <td class="text-right">{{ $item->gross_weight_g }} g</td>
                    <td class="text-right">{{ $item->net_weight_g }} g</td>
                    <td class="text-right">$ {{ number_format((float) $item->price_per_gram_snapshot, 2) }}</td>
                    <td class="text-right">{{ $item->discount_percent_snapshot }}%</td>
                    <td class="text-right">$ {{ number_format((float) $item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total-box">
        <tr class="grand">
            <td>Total pagado</td>
            <td class="text-right">$ {{ number_format((float) $purchase->total_amount, 2) }}</td>
        </tr>
    </table>
    <div class="clear"></div>

    <div class="legal">
        <div class="disclaimer">
            {{ $disclaimer ?: 'El vendedor declara ser el legítimo propietario de las piezas entregadas y que éstas no provienen de ningún acto ilícito. Manifiesta haber recibido el importe señalado, quedando la joyería facultada para su uso, transformación o venta.' }}
        </div>

        <table class="row">
            <tr>
                <td>
                    <div class="sign-line">Firma del vendedor</div>
                    <div class="muted" style="font-size: 9px; margin-top: 2px;">{{ $purchase->customer?->full_name }}</div>
                </td>
                <td>
                    <div class="sign-line">Firma y sello del establecimiento</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
