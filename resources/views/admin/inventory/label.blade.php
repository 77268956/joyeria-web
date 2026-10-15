<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta — {{ $product->sku }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Jost', system-ui, sans-serif;
            background-color: #E9E6DE;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            gap: 1.5rem;
        }
        .toolbar { display: flex; gap: 0.75rem; }
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            height: 40px; padding: 0 1.25rem; border: none; border-radius: 4px;
            font-size: 0.86rem; font-weight: 600; cursor: pointer; text-decoration: none;
        }
        .btn-dark { background-color: #12161F; color: #F7F4EE; }
        .btn-outline { background: #fff; border: 1px solid #D8D3C7; color: #6B6B70; }
        .label {
            width: 62mm;
            background-color: #FFFFFF;
            border: 1px solid #12161F;
            padding: 4mm;
            text-align: center;
            color: #12161F;
        }
        .label-brand { font-size: 8pt; letter-spacing: 0.18em; text-transform: uppercase; color: #6B6B70; }
        .label-name { font-family: Georgia, serif; font-size: 13pt; font-weight: 600; margin: 2mm 0 1mm; line-height: 1.15; }
        .label-meta { font-size: 8pt; color: #4A4A4F; margin-bottom: 2mm; }
        .label-barcode svg { width: 100%; height: auto; display: block; }
        .label-code { font-size: 8pt; letter-spacing: 0.1em; margin-top: 1mm; font-variant-numeric: tabular-nums; }
        .label-price { font-size: 15pt; font-weight: 700; margin-top: 2mm; font-variant-numeric: tabular-nums; }
        .label-tag { font-size: 7pt; color: #6B6B70; text-transform: uppercase; letter-spacing: 0.12em; margin-top: 1mm; }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .label { border: none; }
            @page { margin: 4mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn btn-dark" onclick="window.print()">Imprimir etiqueta</button>
        <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-outline">Volver al detalle</a>
    </div>

    <div class="label">
        <div class="label-brand">Joyería Web</div>
        <div class="label-name">{{ $product->name }}</div>
        <div class="label-meta">
            {{ $product->metal?->name }}@if ($product->purity) · {{ $product->purity->label }}@endif
            · {{ $product->weight_g }} g
        </div>
        <div class="label-barcode">{!! $barcodeSvg !!}</div>
        <div class="label-code">{{ $product->barcode }} &middot; {{ $product->sku }}</div>
        <div class="label-price">$ {{ number_format((float) $product->price, 2) }}</div>
        <div class="label-tag">Precio de venta</div>
    </div>
</body>
</html>
