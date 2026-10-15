@extends('layouts.admin')

@section('title', 'Ajustar compra '.$purchase->number)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Ajustar compra {{ $purchase->number }}</h1>
        <p class="page-subtitle">{{ $purchase->customer?->full_name }} · Total actual: $ {{ number_format((float) $purchase->total_amount, 2) }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="card" style="max-width: 560px;">
    <p class="page-subtitle" style="margin-bottom: 1rem;">
        La corrección queda auditada con el valor anterior y el nuevo. Si la compra fue en efectivo, la diferencia se ajusta en la caja del turno.
    </p>
    <form action="{{ route('admin.purchases.adjust.store', $purchase) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label class="form-label" for="total_amount">Nuevo total <span style="color: var(--color-error);">*</span></label>
            <input type="number" step="0.01" min="0" name="total_amount" id="total_amount" class="form-control" value="{{ old('total_amount', $purchase->total_amount) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="reason">Motivo del ajuste <span style="color: var(--color-error);">*</span></label>
            <textarea name="reason" id="reason" class="form-control" rows="3" maxlength="2000" required>{{ old('reason') }}</textarea>
        </div>
        <button type="submit" class="btn btn-dark">Guardar ajuste</button>
    </form>
</div>

@if ($purchase->adjustments->isNotEmpty())
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-linea);">
            <h2 class="font-serif" style="font-size: 1.1rem; margin: 0;">Ajustes previos</h2>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th class="text-right">Diferencia</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->adjustments as $adjustment)
                        <tr>
                            <td class="num">{{ $adjustment->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $adjustment->user?->name }}</td>
                            <td class="text-right num">$ {{ number_format((float) $adjustment->amount_difference, 2) }}</td>
                            <td>{{ $adjustment->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
