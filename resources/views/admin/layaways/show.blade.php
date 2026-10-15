@extends('layouts.admin')

@section('title', 'Apartado '.$layaway->number)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Apartado {{ $layaway->number }}</h1>
        <p class="page-subtitle">{{ $layaway->created_at?->format('d/m/Y H:i') }} · {{ $layaway->user?->name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.layaways.index') }}" class="btn btn-outline">Volver</a>
        @if ($layaway->completedSale)
            <a href="{{ route('admin.sales.show', $layaway->completedSale) }}" class="btn btn-dark">Ver venta</a>
        @endif
    </div>
</div>

@include('admin.partials.alerts')

<div class="grid-2">
    <div class="card">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 0.75rem;">Datos</h2>
        <p><span class="muted">Cliente:</span> {{ $layaway->customer?->full_name }}</p>
        <p><span class="muted">Documento:</span> {{ $layaway->customer?->document_type }} {{ $layaway->customer?->document_number }}</p>
        <p><span class="muted">Pieza:</span> {{ $layaway->product?->name }} · {{ $layaway->product?->sku }}</p>
        <p><span class="muted">Vence:</span> {{ $layaway->due_date?->format('d/m/Y') }}</p>
        <p>
            <span class="muted">Estado:</span>
            @php
                $badge = match ($layaway->status) {
                    \App\Enums\LayawayStatus::Completed => 'badge-success',
                    \App\Enums\LayawayStatus::Active => 'badge-gold',
                    default => 'badge-danger',
                };
            @endphp
            <span class="badge {{ $badge }}">{{ $layaway->status->label() }}</span>
        </p>
        @if ($layaway->isCancelled() && $layaway->cancel_reason)
            <p><span class="muted">Motivo de cancelación:</span> {{ $layaway->cancel_reason }}</p>
        @endif
    </div>
    <div class="card">
        <div style="display: grid; gap: 0.5rem;">
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Precio total</span>
                <span class="num">$ {{ number_format((float) $layaway->total_price, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Abonado</span>
                <span class="num">$ {{ number_format((float) $layaway->paid_total, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--color-linea); padding-top: 0.5rem;">
                <strong class="font-serif">Saldo pendiente</strong>
                <strong class="font-serif num" style="font-size: 1.3rem;">$ {{ number_format((float) $layaway->balance, 2) }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Vence</span>
                <span class="num">{{ $layaway->due_date?->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-linea);">
        <h2 class="font-serif" style="font-size: 1.1rem; margin: 0;">Abonos registrados</h2>
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
                @forelse ($layaway->payments as $payment)
                    <tr>
                        <td class="num">{{ $payment->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $payment->method->label() }}</td>
                        <td>{{ $payment->reference ?? '—' }}</td>
                        <td>{{ $payment->user?->name }}</td>
                        <td class="text-right num">$ {{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted" style="text-align: center; padding: 1.5rem;">Aún no se han registrado abonos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($layaway->isActive())
    <div class="grid-2">
        @unless ($layaway->isFullyPaid())
            <div class="card">
                <h2 class="font-serif" style="font-size: 1.1rem; margin-bottom: 0.75rem;">Registrar abono</h2>
                <form action="{{ route('admin.layaways.payment', $layaway) }}" method="POST">
                    @csrf
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="method">Método</label>
                            <select name="method" id="method" class="form-select">
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="amount">Monto</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $layaway->balance }}" name="amount" id="amount" class="form-control num" value="{{ $layaway->balance }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reference">Referencia (opcional)</label>
                        <input type="text" name="reference" id="reference" class="form-control" maxlength="80">
                    </div>
                    <button type="submit" class="btn btn-gold">Registrar abono</button>
                </form>
            </div>
        @endunless

        <div class="card">
            <h2 class="font-serif" style="font-size: 1.1rem; margin-bottom: 0.75rem;">Entrega</h2>
            @if ($canComplete)
                <p class="page-subtitle" style="margin-bottom: 1rem;">El saldo está liquidado. Al entregar, la pieza pasa a vendida y se genera la venta final.</p>
                <form action="{{ route('admin.layaways.complete', $layaway) }}" method="POST" onsubmit="return confirm('¿Confirmas entregar el apartado {{ $layaway->number }}?');">
                    @csrf
                    <button type="submit" class="btn btn-dark">Entregar pieza</button>
                </form>
            @else
                <p class="page-subtitle">La pieza podrá entregarse cuando el saldo pendiente llegue a cero.</p>
            @endif

            <hr style="border: none; border-top: 1px solid var(--color-linea); margin: 1.25rem 0;">

            <h2 class="font-serif" style="font-size: 1.1rem; margin-bottom: 0.75rem;">Cancelar apartado</h2>
            <p class="page-subtitle" style="margin-bottom: 1rem;">La pieza vuelve al inventario disponible. Los abonos registrados no se reintegran automáticamente.</p>
            <form action="{{ route('admin.layaways.cancel', $layaway) }}" method="POST" onsubmit="return confirm('¿Confirmas cancelar el apartado {{ $layaway->number }}?');">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="reason">Motivo de la cancelación <span style="color: var(--color-error);">*</span></label>
                    <input type="text" name="reason" id="reason" class="form-control" maxlength="255" required>
                </div>
                <button type="submit" class="btn btn-danger">Cancelar apartado</button>
            </form>
        </div>
    </div>
@elseif ($layaway->isCancelled() && $layaway->cancel_reason)
    <div class="card">
        <p><span class="muted">Motivo de la cancelación:</span> {{ $layaway->cancel_reason }}</p>
    </div>
@endif
@endsection
