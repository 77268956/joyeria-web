<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $sale->number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Jost', system-ui, -apple-system, sans-serif;
            background-color: #E9E6DE;
            padding: 2rem 1rem;
            color: #12161F;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar { display: flex; flex-wrap: wrap; gap: 0.6rem; justify-content: center; margin-bottom: 1.5rem; }
        .btn {
            height: 40px; padding: 0 1.25rem; border: none; border-radius: 4px;
            font-family: inherit; font-size: 0.85rem; font-weight: 600; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center;
        }
        .btn-dark { background-color: #12161F; color: #F7F4EE; }
        .btn-outline { background: #fff; border: 1px solid #D8D3C7; color: #4A4A4F; }

        .sheet {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 210mm;
            max-width: 100%;
            min-height: 297mm;
            margin: 0 auto;
            padding: 16mm 15mm 12mm;
            background-color: #FFFFFF;
            box-shadow: 0 18px 45px rgba(18, 22, 31, 0.12);
            overflow: hidden;
        }

        /* Header */
        .invoice-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10mm;
            padding-bottom: 7mm;
            border-bottom: 2px solid #12161F;
        }
        .brand { display: flex; align-items: center; gap: 4mm; }
        .brand-mark {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 15mm;
            height: 15mm;
            border-radius: 50%;
            background-color: #12161F;
            color: #E3D6B4;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 20pt;
            font-weight: 700;
        }
        .brand-name {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 17pt;
            font-weight: 600;
            line-height: 1.15;
        }
        .brand-meta { margin-top: 1.2mm; color: #6B6B70; font-size: 9pt; line-height: 1.45; }

        .doc-box {
            min-width: 64mm;
            padding: 4.5mm 5mm;
            border: 1px solid #E4DFD3;
            border-left: 3px solid #B89A5E;
            background-color: #FAF8F3;
            text-align: right;
        }
        .doc-label {
            color: #B89A5E;
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .doc-number {
            margin-top: 1mm;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 17pt;
            font-weight: 600;
            line-height: 1.1;
        }
        .doc-date { margin-top: 1mm; color: #6B6B70; font-size: 9pt; }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 1.5mm;
            margin-top: 2.5mm;
            padding: 1mm 2.5mm;
            border-radius: 999px;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .status-pill::before {
            content: '';
            width: 1.6mm;
            height: 1.6mm;
            border-radius: 50%;
            background-color: currentColor;
        }
        .status-pill.is-complete { background-color: #EDF5EE; color: #2F6B3D; }
        .status-pill.is-void { background-color: #FBEBEB; color: #A63D40; }

        /* Void banner + watermark */
        .void-banner {
            margin-top: 5mm;
            padding: 3mm 4mm;
            border: 1px solid #E6B8B8;
            background-color: #FBEBEB;
            color: #A63D40;
            font-size: 9pt;
            line-height: 1.5;
        }
        .void-banner strong {
            display: block;
            font-size: 10pt;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-24deg);
            color: #A63D40;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 88pt;
            font-weight: 700;
            letter-spacing: 0.12em;
            opacity: 0.07;
            pointer-events: none;
            white-space: nowrap;
        }

        /* Parties */
        .parties {
            display: flex;
            justify-content: space-between;
            gap: 12mm;
            padding: 7mm 0;
            border-bottom: 1px solid #E4DFD3;
        }
        .party { flex: 1; min-width: 0; }
        .party--right { flex: 0 0 66mm; }
        .section-label {
            margin-bottom: 2mm;
            color: #B89A5E;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .party-name { font-size: 12pt; font-weight: 600; line-height: 1.3; }
        .party-meta { margin-top: 1mm; color: #6B6B70; font-size: 9pt; line-height: 1.5; }
        .meta-list { display: grid; gap: 1.3mm; font-size: 9pt; }
        .meta-list > div { display: flex; justify-content: space-between; gap: 4mm; }
        .meta-list dt { color: #6B6B70; }
        .meta-list dd { font-weight: 600; text-align: right; }

        /* Items */
        .items-section { margin-top: 6mm; }
        .table-scroll { overflow-x: auto; }
        table.items { width: 100%; border-collapse: collapse; font-size: 9pt; }
        table.items thead tr { background-color: #F3EFE8; }
        table.items th {
            padding: 2.6mm 2.4mm;
            color: #4A4A4F;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-align: left;
            text-transform: uppercase;
            border-bottom: 1px solid #D8D3C7;
        }
        table.items td {
            padding: 3mm 2.4mm;
            border-bottom: 1px solid #EDE9E0;
            vertical-align: top;
        }
        table.items tbody tr:last-child td { border-bottom: 1px solid #D8D3C7; }
        .col-index { width: 7mm; }
        .item-name { display: block; font-size: 9.5pt; font-weight: 600; }
        .item-sku { display: block; margin-top: 0.5mm; color: #6B6B70; font-size: 7.5pt; letter-spacing: 0.04em; }
        .item-metal { display: block; }
        .item-purity { display: block; margin-top: 0.5mm; color: #6B6B70; font-size: 8pt; }
        .text-right { text-align: right; }
        .num { font-variant-numeric: tabular-nums; }
        .muted { color: #6B6B70; }
        .line-total { font-weight: 600; }

        /* Summary */
        .summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12mm;
            margin-top: 6mm;
            padding-top: 5mm;
            border-top: 1px solid #E4DFD3;
        }
        .summary-left { flex: 1; min-width: 0; }
        .payments { display: grid; gap: 2mm; }
        .payment {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 4mm;
            padding: 2mm 3mm;
            border: 1px solid #EDE9E0;
            border-radius: 3px;
            background-color: #FAF8F3;
            font-size: 9pt;
        }
        .payment strong { display: block; font-weight: 600; }
        .payment-ref { display: block; margin-top: 0.5mm; color: #6B6B70; font-size: 7.5pt; }
        .payment-amount { text-align: right; }
        .payment-amount > span { display: block; font-weight: 600; }
        .payment-amount small { display: block; margin-top: 0.5mm; color: #6B6B70; font-size: 7.5pt; }

        .totals { flex: 0 0 72mm; font-size: 9.5pt; }
        .totals .row {
            display: flex;
            justify-content: space-between;
            gap: 4mm;
            padding: 1.6mm 0;
        }
        .totals .row span:first-child { color: #6B6B70; }
        .totals .grand {
            margin-top: 1.5mm;
            padding-top: 2.5mm;
            border-top: 2px solid #12161F;
            color: #12161F;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 14pt;
            font-weight: 700;
        }
        .totals .grand span:first-child { color: #12161F; }
        .totals .paid { color: #2F6B3D; }
        .totals .paid span:first-child { color: #2F6B3D; }
        .totals .balance { color: #A63D40; font-weight: 700; }
        .totals .balance span:first-child { color: #A63D40; }

        /* Footer */
        .sheet-footer {
            margin-top: auto;
            padding-top: 8mm;
        }
        .footer-note {
            padding: 4mm 0;
            border-top: 1px solid #E4DFD3;
            color: #4A4A4F;
            font-size: 9pt;
            line-height: 1.5;
        }
        .footer-meta {
            display: flex;
            justify-content: space-between;
            gap: 6mm;
            padding-top: 3mm;
            border-top: 1px dashed #E4DFD3;
            color: #6B6B70;
            font-size: 7.5pt;
        }

        @media (max-width: 760px) {
            body { padding: 1rem 0.5rem; }
            .sheet { width: 100%; min-height: auto; padding: 8mm; }
            .invoice-head,
            .parties,
            .summary { flex-direction: column; }
            .doc-box { width: 100%; min-width: 0; text-align: left; }
            .party--right { flex: none; width: 100%; }
            .totals { width: 100%; flex: none; }
            .watermark { font-size: 46pt; }
        }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
                overflow: visible;
            }
            table.items tr,
            .summary,
            .parties { break-inside: avoid; }
            @page { size: A4; margin: 14mm; }
        }
    </style>
</head>
<body>
    @php
        $itemsCount = $sale->items->count();
        $totalWeight = $sale->items->reduce(fn ($carry, $item) => bcadd($carry, (string) $item->weight_g, 3), '0.000');
        $paidTotal = $sale->payments->reduce(fn ($carry, $payment) => bcadd($carry, (string) $payment->amount, 2), '0.00');
        $changeTotal = '0.00';
        foreach ($sale->payments as $payment) {
            if ($payment->received !== null) {
                $difference = bcsub((string) $payment->received, (string) $payment->amount, 2);
                if (bccomp($difference, '0', 2) === 1) {
                    $changeTotal = bcadd($changeTotal, $difference, 2);
                }
            }
        }
        $balance = bcsub((string) $sale->total, $paidTotal, 2);
        $hasBalance = bccomp($balance, '0', 2) === 1;
        $isVoided = $sale->isVoided();
        $storeName = $store?->name ?? 'Joyería Web';
    @endphp

    <div class="toolbar">
        <button type="button" class="btn btn-dark" onclick="window.print()">Imprimir factura</button>
        <a href="{{ route('admin.sales.ticket', $sale) }}" class="btn btn-outline">Ver ticket</a>
        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline">Volver a la venta</a>
    </div>

    <div class="sheet">
        @if ($isVoided)
            <div class="watermark">ANULADA</div>
        @endif

        <header class="invoice-head">
            <div class="brand">
                <div class="brand-mark">{{ mb_strtoupper(mb_substr($storeName, 0, 1)) }}</div>
                <div>
                    <div class="brand-name">{{ $storeName }}</div>
                    <div class="brand-meta">
                        @if ($store?->address)<div>{{ $store->address }}</div>@endif
                        @if ($store?->phone)<div>Tel: {{ $store->phone }}</div>@endif
                        @if ($store?->code)<div>Código de tienda: {{ $store->code }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="doc-box">
                <div class="doc-label">Factura de venta</div>
                <div class="doc-number num">{{ $sale->number }}</div>
                <div class="doc-date num">Emitida el {{ $sale->created_at?->format('d/m/Y H:i') }}</div>
                <span class="status-pill {{ $isVoided ? 'is-void' : 'is-complete' }}">
                    {{ $isVoided ? 'Anulada' : 'Completada' }}
                </span>
            </div>
        </header>

        @if ($isVoided)
            <div class="void-banner">
                <strong>Venta anulada</strong>
                Anulada el {{ $sale->voided_at?->format('d/m/Y H:i') }} por {{ $sale->voidedBy?->name ?? '—' }}.
                @if ($sale->void_reason)<br>Motivo: {{ $sale->void_reason }}@endif
            </div>
        @endif

        <section class="parties">
            <div class="party">
                <div class="section-label">Facturado a</div>
                <div class="party-name">{{ $sale->customer?->full_name ?? 'Consumidor final' }}</div>
                <div class="party-meta">
                    @if ($sale->customer?->document_number)
                        <div>{{ $sale->customer->document_type }} {{ $sale->customer->document_number }}</div>
                    @endif
                    @if ($sale->customer?->phone)<div>Tel: {{ $sale->customer->phone }}</div>@endif
                    @if ($sale->customer?->email)<div>{{ $sale->customer->email }}</div>@endif
                    @if ($sale->customer?->address)<div>{{ $sale->customer->address }}</div>@endif
                </div>
            </div>
            <div class="party party--right">
                <div class="section-label">Detalles</div>
                <dl class="meta-list">
                    <div><dt>Vendedor</dt><dd>{{ $sale->user?->name ?? '—' }}</dd></div>
                    <div><dt>Fecha</dt><dd class="num">{{ $sale->created_at?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Piezas</dt><dd class="num">{{ number_format($itemsCount) }}</dd></div>
                    <div><dt>Peso total</dt><dd class="num">{{ number_format((float) $totalWeight, 2) }} g</dd></div>
                </dl>
            </div>
        </section>

        <section class="items-section">
            <div class="table-scroll">
                <table class="items">
                    <thead>
                        <tr>
                            <th class="col-index">#</th>
                            <th>Pieza</th>
                            <th>Metal / pureza</th>
                            <th class="text-right">Peso</th>
                            <th class="text-right">Precio</th>
                            <th class="text-right">Desc.</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale->items as $item)
                            <tr>
                                <td class="num muted">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="item-name">{{ $item->name_snapshot }}</span>
                                    @if ($item->product?->sku)
                                        <span class="item-sku">SKU {{ $item->product->sku }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="item-metal">{{ $item->metal_snapshot }}</span>
                                    @if ($item->purity_snapshot)
                                        <span class="item-purity">{{ $item->purity_snapshot }}</span>
                                    @endif
                                </td>
                                <td class="text-right num">{{ number_format((float) $item->weight_g, 3) }} g</td>
                                <td class="text-right num">$ {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="text-right num">$ {{ number_format((float) $item->discount, 2) }}</td>
                                <td class="text-right num line-total">$ {{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="muted" style="text-align: center; padding: 8mm;">Esta venta no tiene piezas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="summary">
            <div class="summary-left">
                <div class="section-label">Pagos</div>
                <div class="payments">
                    @forelse ($sale->payments as $payment)
                        @php($change = $payment->received !== null ? bcsub((string) $payment->received, (string) $payment->amount, 2) : null)
                        <div class="payment">
                            <div>
                                <strong>{{ $payment->method->label() }}</strong>
                                @if ($payment->reference)
                                    <span class="payment-ref">Ref. {{ $payment->reference }}</span>
                                @endif
                            </div>
                            <div class="payment-amount">
                                <span class="num">$ {{ number_format((float) $payment->amount, 2) }}</span>
                                @if ($payment->received !== null)
                                    <small class="num">Recibido $ {{ number_format((float) $payment->received, 2) }}</small>
                                    @if (bccomp((string) $change, '0', 2) === 1)
                                        <small class="num">Cambio $ {{ number_format((float) $change, 2) }}</small>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="muted" style="font-size: 9pt;">No hay pagos registrados.</p>
                    @endforelse
                </div>
            </div>

            <div class="totals">
                <div class="row"><span>Subtotal</span><span class="num">$ {{ number_format((float) $sale->subtotal, 2) }}</span></div>
                <div class="row"><span>Descuento</span><span class="num">- $ {{ number_format((float) $sale->discount_total, 2) }}</span></div>
                <div class="row grand"><span>Total</span><span class="num">$ {{ number_format((float) $sale->total, 2) }}</span></div>
                @if ($sale->payments->isNotEmpty())
                    <div class="row paid"><span>Pagado</span><span class="num">$ {{ number_format((float) $paidTotal, 2) }}</span></div>
                @endif
                @if (bccomp($changeTotal, '0', 2) === 1)
                    <div class="row"><span>Cambio</span><span class="num">$ {{ number_format((float) $changeTotal, 2) }}</span></div>
                @endif
                @if ($hasBalance)
                    <div class="row balance"><span>Saldo pendiente</span><span class="num">$ {{ number_format((float) $balance, 2) }}</span></div>
                @endif
            </div>
        </section>

        <footer class="sheet-footer">
            <div class="footer-note">{{ $settings?->ticket_footer_text ?? 'Gracias por su compra.' }}</div>
            <div class="footer-meta">
                <span>Documento generado electrónicamente por {{ $settings?->system_name ?? 'Joyería Web' }}.</span>
                <span class="num">{{ $sale->number }} · {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </footer>
    </div>
</body>
</html>
