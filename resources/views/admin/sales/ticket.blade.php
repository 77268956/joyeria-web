<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $sale->number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', ui-monospace, monospace;
            background-color: #E9E6DE;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            gap: 1rem;
            color: #12161F;
        }
        .toolbar { display: flex; gap: 0.75rem; }
        .btn {
            height: 40px; padding: 0 1.25rem; border: none; border-radius: 4px;
            font-size: 0.86rem; font-weight: 600; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center;
        }
        .btn-dark { background-color: #12161F; color: #F7F4EE; }
        .btn-outline { background: #fff; border: 1px solid #D8D3C7; color: #6B6B70; }
        .ticket {
            width: 80mm;
            background-color: #FFFFFF;
            padding: 4mm;
            font-size: 10pt;
            line-height: 1.35;
        }
        .center { text-align: center; }
        .brand { font-size: 13pt; font-weight: 700; letter-spacing: 0.05em; }
        .muted { color: #4A4A4F; font-size: 9pt; }
        .sep { border-top: 1px dashed #12161F; margin: 2mm 0; }
        .row { display: flex; justify-content: space-between; gap: 0.5rem; }
        .row .label { color: #4A4A4F; }
        .num { font-variant-numeric: tabular-nums; }
        .item { margin-bottom: 1.5mm; }
        .item .name { font-weight: 600; }
        .total { font-size: 13pt; font-weight: 700; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .ticket { width: 80mm; padding: 0; }
            @page { margin: 3mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn btn-dark" onclick="window.print()">Imprimir ticket</button>
        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline">Volver a la venta</a>
    </div>

    <div class="ticket">
        <div class="center">
            <div class="brand">{{ $store?->name ?? 'Joyería Web' }}</div>
            @if ($store?->address)<div class="muted">{{ $store->address }}</div>@endif
            @if ($store?->phone)<div class="muted">Tel: {{ $store->phone }}</div>@endif
        </div>

        <div class="sep"></div>

        <div class="row"><span class="label">Ticket</span><span class="num">{{ $sale->number }}</span></div>
        <div class="row"><span class="label">Fecha</span><span class="num">{{ $sale->created_at?->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span class="label">Cajero</span><span>{{ $sale->user?->name }}</span></div>
        <div class="row"><span class="label">Cliente</span><span>{{ $sale->customer?->full_name ?? 'Consumidor final' }}</span></div>

        <div class="sep"></div>

        @foreach ($sale->items as $item)
            <div class="item">
                <div class="name">{{ $item->name_snapshot }}</div>
                <div class="row muted">
                    <span>{{ $item->metal_snapshot }}@if ($item->purity_snapshot) {{ $item->purity_snapshot }}@endif · {{ $item->weight_g }} g</span>
                    <span class="num">$ {{ number_format((float) $item->unit_price, 2) }}</span>
                </div>
                @if ((float) $item->discount > 0)
                    <div class="row muted"><span>Descuento</span><span class="num">- $ {{ number_format((float) $item->discount, 2) }}</span></div>
                @endif
            </div>
        @endforeach

        <div class="sep"></div>

        <div class="row"><span class="label">Subtotal</span><span class="num">$ {{ number_format((float) $sale->subtotal, 2) }}</span></div>
        <div class="row"><span class="label">Descuento</span><span class="num">$ {{ number_format((float) $sale->discount_total, 2) }}</span></div>
        <div class="row total"><span>TOTAL</span><span class="num">$ {{ number_format((float) $sale->total, 2) }}</span></div>

        <div class="sep"></div>

        @foreach ($sale->payments as $payment)
            <div class="row muted">
                <span>{{ $payment->method->label() }}@if ($payment->reference) ({{ $payment->reference }})@endif</span>
                <span class="num">$ {{ number_format((float) $payment->amount, 2) }}</span>
            </div>
        @endforeach

        @if ($sale->isVoided())
            <div class="sep"></div>
            <div class="center" style="font-weight: 700;">** VENTA ANULADA **</div>
        @endif

        <div class="sep"></div>

        <div class="center muted">
            {{ $settings?->ticket_footer_text ?? 'Gracias por su compra.' }}
        </div>
    </div>
</body>
</html>
