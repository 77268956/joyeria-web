<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $sale->number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Jost', system-ui, sans-serif;
            background-color: #E9E6DE;
            padding: 2rem 1rem;
            color: #12161F;
        }
        .toolbar { display: flex; gap: 0.75rem; justify-content: center; margin-bottom: 1.5rem; }
        .btn {
            height: 40px; padding: 0 1.25rem; border: none; border-radius: 4px;
            font-size: 0.86rem; font-weight: 600; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center;
        }
        .btn-dark { background-color: #12161F; color: #F7F4EE; }
        .btn-outline { background: #fff; border: 1px solid #D8D3C7; color: #6B6B70; }
        .sheet {
            width: 210mm;
            min-height: 120mm;
            margin: 0 auto;
            background-color: #FFFFFF;
            padding: 18mm 16mm;
            color: #12161F;
        }
        .head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8mm; }
        .brand { font-family: Georgia, serif; font-size: 20pt; font-weight: 600; }
        .muted { color: #6B6B70; font-size: 9pt; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-family: Georgia, serif; font-size: 16pt; }
        table { width: 100%; border-collapse: collapse; font-size: 9.5pt; }
        thead tr { background-color: #F3EFE8; }
        th, td { padding: 2.5mm 3mm; text-align: left; border-bottom: 1px solid #E4DFD3; }
        th { font-size: 8pt; text-transform: uppercase; letter-spacing: 0.05em; }
        .text-right { text-align: right; }
        .num { font-variant-numeric: tabular-nums; }
        .totals { width: 70mm; margin-left: auto; margin-top: 6mm; font-size: 10pt; }
        .totals .row { display: flex; justify-content: space-between; padding: 1.5mm 0; }
        .totals .grand { border-top: 1px solid #12161F; margin-top: 2mm; padding-top: 2mm; font-weight: 700; font-size: 12pt; }
        .footer { margin-top: 10mm; font-size: 8.5pt; color: #6B6B70; }
        .parties { display: flex; justify-content: space-between; gap: 10mm; margin-bottom: 8mm; font-size: 9.5pt; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; padding: 12mm; width: auto; min-height: auto; }
            @page { margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn btn-dark" onclick="window.print()">Imprimir factura</button>
        <a href="{{ route('admin.sales.ticket', $sale) }}" class="btn btn-outline">Ver ticket</a>
    </div>

    <div class="sheet">
        <div class="head">
            <div>
                <div class="brand">{{ $store?->name ?? 'Joyería Web' }}</div>
                @if ($store?->address)<div class="muted">{{ $store->address }}</div>@endif
                @if ($store?->phone)<div class="muted">Tel: {{ $store->phone }}</div>@endif
            </div>
            <div class="doc-title">
                <h1>Factura de venta</h1>
                <div class="num">{{ $sale->number }}</div>
                <div class="muted num">{{ $sale->created_at?->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div class="parties">
            <div>
                <div class="muted">Cliente</div>
                <strong>{{ $sale->customer?->full_name ?? 'Consumidor final' }}</strong>
                @if ($sale->customer)
                    <div class="muted">{{ $sale->customer->document_type }} {{ $sale->customer->document_number }}</div>
                    @if ($sale->customer->address)<div class="muted">{{ $sale->customer->address }}</div>@endif
                @endif
            </div>
            <div style="text-align: right;">
                <div class="muted">Atendido por</div>
                <strong>{{ $sale->user?->name }}</strong>
                @if ($sale->isVoided())<div style="color: #A63D40; font-weight: 700;">VENTA ANULADA</div>@endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Pieza</th>
                    <th>Metal / Pureza</th>
                    <th class="text-right">Peso</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Desc.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>{{ $item->name_snapshot }}</td>
                        <td>{{ $item->metal_snapshot }}@if ($item->purity_snapshot) · {{ $item->purity_snapshot }}@endif</td>
                        <td class="text-right num">{{ $item->weight_g }} g</td>
                        <td class="text-right num">$ {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-right num">$ {{ number_format((float) $item->discount, 2) }}</td>
                        <td class="text-right num">$ {{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><span class="muted">Subtotal</span><span class="num">$ {{ number_format((float) $sale->subtotal, 2) }}</span></div>
            <div class="row"><span class="muted">Descuento</span><span class="num">$ {{ number_format((float) $sale->discount_total, 2) }}</span></div>
            <div class="row grand"><span>Total</span><span class="num">$ {{ number_format((float) $sale->total, 2) }}</span></div>
        </div>

        <div class="footer">
            @if ($store?->systemSettings?->ticket_footer_text)
                <p>{{ $store->systemSettings->ticket_footer_text }}</p>
            @endif
            <p>Documento generado electrónicamente por Joyería Web.</p>
        </div>
    </div>
</body>
</html>
