@extends('layouts.admin')

@section('title', 'Venta '.$sale->number)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Venta {{ $sale->number }}</h1>
        <p class="page-subtitle">{{ $sale->created_at?->format('d/m/Y H:i') }} · {{ $sale->user?->name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline">Volver</a>
        <a href="{{ route('admin.sales.ticket', $sale) }}" target="_blank" class="btn btn-dark">Ticket</a>
        <a href="{{ route('admin.sales.ticket', ['sale' => $sale, 'format' => 'invoice']) }}" target="_blank" class="btn btn-dark">Factura</a>
    </div>
</div>

@include('admin.partials.alerts')

@if ($sale->isVoided())
    <div class="alert alert-error" style="display: block;">
        <strong>Venta anulada</strong> el {{ $sale->voided_at?->format('d/m/Y H:i') }} por {{ $sale->voidedBy?->name }}.
        <br>Motivo: {{ $sale->void_reason }}
    </div>
@endif

<div class="grid-2">
    <div class="card">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 0.75rem;">Datos</h2>
        <p><span class="muted">Cliente:</span> {{ $sale->customer?->full_name ?? 'Consumidor final' }}</p>
        <p><span class="muted">Estado:</span> <span class="badge {{ $sale->isVoided() ? 'badge-danger' : 'badge-success' }}">{{ $sale->status->label() }}</span></p>
        @if ($sale->notes)
            <p><span class="muted">Notas:</span> {{ $sale->notes }}</p>
        @endif
    </div>
    <div class="card">
        <div style="display: grid; gap: 0.5rem;">
            <div style="display: flex; justify-content: space-between;"><span class="muted">Subtotal</span><span class="num">$ {{ number_format((float) $sale->subtotal, 2) }}</span></div>
            <div style="display: flex; justify-content: space-between;"><span class="muted">Descuento</span><span class="num">$ {{ number_format((float) $sale->discount_total, 2) }}</span></div>
            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--color-linea); padding-top: 0.5rem;">
                <strong class="font-serif">Total</strong>
                <strong class="font-serif num" style="font-size: 1.3rem;">$ {{ number_format((float) $sale->total, 2) }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
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
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--color-linea);">
        <h2 class="font-serif" style="font-size: 1.15rem;">Pagos</h2>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th class="text-right">Recibido</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->payments as $payment)
                    <tr>
                        <td>{{ $payment->method->label() }}</td>
                        <td>{{ $payment->reference ?? '—' }}</td>
                        <td class="text-right num">{{ $payment->received ? '$ '.number_format((float) $payment->received, 2) : '—' }}</td>
                        <td class="text-right num">$ {{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@can('sales.void')
    @unless ($sale->isVoided())
        <div class="card" style="max-width: 560px;">
            <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 0.75rem;">Anular venta</h2>
            <p class="page-subtitle" style="margin-bottom: 1rem;">Las piezas volverán al inventario disponible. Esta acción es irreversible.</p>
            <form action="{{ route('admin.sales.void', $sale) }}" method="POST" onsubmit="return confirm('¿Confirmas anular la venta {{ $sale->number }}?');">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="reason">Motivo de la anulación</label>
                    <input type="text" name="reason" id="reason" class="form-control" maxlength="255" required>
                </div>
                <button type="submit" class="btn btn-danger">Anular venta</button>
            </form>
        </div>
    @endunless
@endcan
@endsection
