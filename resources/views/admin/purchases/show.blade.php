@extends('layouts.admin')

@section('title', 'Compra '.$purchase->number)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Compra {{ $purchase->number }}</h1>
        <p class="page-subtitle">{{ $purchase->created_at?->format('d/m/Y H:i') }} · {{ $purchase->user?->name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline">Volver</a>
        <a href="{{ route('admin.purchases.receipt', $purchase) }}" class="btn btn-dark" target="_blank">Comprobante PDF</a>
        @if ($canApprove && in_array($purchase->status, [\App\Enums\MetalPurchaseStatus::Paid, \App\Enums\MetalPurchaseStatus::Adjusted], true))
            <a href="{{ route('admin.purchases.adjust', $purchase) }}" class="btn btn-outline">Ajustar</a>
        @endif
    </div>
</div>

@include('admin.partials.alerts')

<div class="grid-2">
    <div class="card">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 0.75rem;">Datos</h2>
        <p><span class="muted">Cliente:</span> {{ $purchase->customer?->full_name }}</p>
        <p><span class="muted">Documento:</span> {{ $purchase->customer?->document_type }} {{ $purchase->customer?->document_number }}</p>
        <p><span class="muted">Método de pago:</span> {{ $purchase->payout_method->label() }}</p>
        <p>
            <span class="muted">Estado:</span>
            @php
                $badge = match ($purchase->status) {
                    \App\Enums\MetalPurchaseStatus::Paid, \App\Enums\MetalPurchaseStatus::Adjusted => 'badge-success',
                    \App\Enums\MetalPurchaseStatus::PendingApproval => 'badge-gold',
                    default => 'badge-danger',
                };
            @endphp
            <span class="badge {{ $badge }}">{{ $purchase->status->label() }}</span>
        </p>
        @if ($purchase->notes)
            <p><span class="muted">Notas:</span> {{ $purchase->notes }}</p>
        @endif
    </div>
    <div class="card">
        <div style="display: grid; gap: 0.5rem;">
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Método de pago</span>
                <span>{{ $purchase->payout_method->label() }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Registrado por</span>
                <span>{{ $purchase->user?->name }}</span>
            </div>
            @if ($purchase->approved_at)
                <div style="display: flex; justify-content: space-between;">
                    <span class="muted">{{ $purchase->status === \App\Enums\MetalPurchaseStatus::Rejected ? 'Rechazado por' : 'Aprobado por' }}</span>
                    <span>{{ $purchase->approvedBy?->name }} · {{ $purchase->approved_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
            @if ($purchase->approval_note)
                <div style="display: flex; justify-content: space-between;">
                    <span class="muted">Nota de decisión</span>
                    <span>{{ $purchase->approval_note }}</span>
                </div>
            @endif
            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--color-linea); padding-top: 0.5rem;">
                <strong class="font-serif">Total</strong>
                <strong class="font-serif num" style="font-size: 1.3rem;">$ {{ number_format((float) $purchase->total_amount, 2) }}</strong>
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
                    <th class="text-right">Bruto</th>
                    <th class="text-right">Neto</th>
                    <th>Origen</th>
                    <th class="text-right">Precio/g</th>
                    <th class="text-right">Desc.</th>
                    <th class="text-right">Monto</th>
                    <th>Destino</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->metal?->name }}@if ($item->purity?->code) · {{ $item->purity->code }}@endif <span class="muted">(ley {{ $item->fineness_snapshot }})</span></td>
                        <td class="text-right num">{{ $item->gross_weight_g }} g</td>
                        <td class="text-right num">{{ $item->net_weight_g }} g</td>
                        <td>{{ $item->weight_source->label() }}</td>
                        <td class="text-right num">$ {{ number_format((float) $item->price_per_gram_snapshot, 2) }}</td>
                        <td class="text-right num">{{ $item->discount_percent_snapshot }}%</td>
                        <td class="text-right num">$ {{ number_format((float) $item->amount, 2) }}</td>
                        <td>
                            @if ($item->product_id)
                                <a href="{{ route('admin.inventory.show', $item->product_id) }}">Ocasión</a>
                            @elseif ($item->material_lot_id)
                                <a href="{{ route('admin.inventory.lots.show', $item->material_lot_id) }}">Fundición</a>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if ($purchase->adjustments->isNotEmpty())
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-linea);">
            <h2 class="font-serif" style="font-size: 1.1rem; margin: 0;">Ajustes</h2>
        </div>
        <div class="table-wrap">
            <table class="data-table">
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
                        <tr>
                            <td class="num">{{ $adjustment->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $adjustment->user?->name }}</td>
                            <td class="text-right num">$ {{ number_format((float) ($adjustment->old_values['total_amount'] ?? 0), 2) }}</td>
                            <td class="text-right num">$ {{ number_format((float) ($adjustment->new_values['total_amount'] ?? 0), 2) }}</td>
                            <td class="text-right num">$ {{ number_format((float) $adjustment->amount_difference, 2) }}</td>
                            <td>{{ $adjustment->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if ($canApprove && $purchase->isPendingApproval())
    <div class="grid-2">
        <div class="card">
            <h2 class="font-serif" style="font-size: 1.1rem; margin-bottom: 0.75rem;">Aprobar compra</h2>
            <p class="page-subtitle" style="margin-bottom: 1rem;">Al aprobar, las piezas entran al inventario y el efectivo sale de la caja.</p>
            <form action="{{ route('admin.purchases.approve', $purchase) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="approval_note">Nota (opcional)</label>
                    <input type="text" name="approval_note" id="approval_note" class="form-control" maxlength="2000">
                </div>
                <button type="submit" class="btn btn-dark">Aprobar</button>
            </form>
        </div>
        <div class="card">
            <h2 class="font-serif" style="font-size: 1.1rem; margin-bottom: 0.75rem;">Rechazar compra</h2>
            <p class="page-subtitle" style="margin-bottom: 1rem;">El efectivo no se mueve y la compra queda registrada como rechazada.</p>
            <form action="{{ route('admin.purchases.reject', $purchase) }}" method="POST" onsubmit="return confirm('¿Confirmas rechazar la compra {{ $purchase->number }}?');">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="reason">Motivo del rechazo <span style="color: var(--color-error);">*</span></label>
                    <input type="text" name="reason" id="reason" class="form-control" maxlength="255" required>
                </div>
                <button type="submit" class="btn btn-danger">Rechazar compra</button>
            </form>
        </div>
    </div>
@endif
@endsection
