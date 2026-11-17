@extends('layouts.admin')

@section('title', 'Ajustar compra '.$purchase->number)

@section('content')
@php
    $badge = match ($purchase->status) {
        \App\Enums\MetalPurchaseStatus::Paid, \App\Enums\MetalPurchaseStatus::Adjusted => 'badge-success',
        \App\Enums\MetalPurchaseStatus::PendingApproval => 'badge-gold',
        default => 'badge-danger',
    };
    $appraisedTotal = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, $item->appraisedAmount(), 2), '0.00');
    $negotiationDifference = $purchase->items->reduce(fn ($carry, $item) => bcadd($carry, $item->negotiationDifference(), 2), '0.00');
@endphp

<style>
    .adjust-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 1rem;
        align-items: start;
    }

    .adjust-card {
        padding: 1rem 1.15rem;
        margin-bottom: 0;
    }

    .adjust-card-title {
        margin: 0 0 0.7rem;
        font-size: 1.08rem;
    }

    .adjust-current {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed var(--color-linea);
    }

    .adjust-current span {
        color: var(--color-piedra);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .adjust-current strong {
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.4rem;
    }

    .adjust-reference {
        display: grid;
        gap: 0.2rem;
        margin-top: 0.6rem;
        color: var(--color-piedra);
        font-size: 0.78rem;
    }

    .adjust-reference > div {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .adjust-reference strong {
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .adjust-preview {
        display: grid;
        gap: 0.45rem;
        margin-top: 0.75rem;
        font-size: 0.85rem;
    }

    .adjust-preview-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .adjust-preview-row span {
        color: var(--color-piedra);
    }

    .adjust-preview-row strong {
        font-variant-numeric: tabular-nums;
    }

    .adjust-preview-row.is-negative strong {
        color: var(--color-error);
    }

    .adjust-preview-row.is-positive strong {
        color: #2F6B3D;
    }

    .adjust-note {
        margin-top: 0.85rem;
        padding: 0.65rem 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: #FAF8F3;
        color: var(--color-piedra);
        font-size: 0.78rem;
        line-height: 1.45;
    }

    .adjust-history {
        margin-top: 1rem;
        padding: 0;
        overflow: hidden;
    }

    .adjust-history-head {
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .adjust-history-head h2 {
        margin: 0;
        font-size: 1.08rem;
    }

    .adjust-amount-positive {
        color: #2F6B3D;
        font-weight: 600;
    }

    .adjust-amount-negative {
        color: var(--color-error);
        font-weight: 600;
    }

    .adjust-title-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    @media (max-width: 1000px) {
        .adjust-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div>
        <div class="adjust-title-row">
            <h1 class="page-title font-serif">Ajustar compra {{ $purchase->number }}</h1>
            <span class="badge {{ $badge }}">{{ $purchase->status->label() }}</span>
        </div>
        <p class="page-subtitle">{{ $purchase->customer?->full_name ?? '—' }} · Registrada el {{ $purchase->created_at?->format('d/m/Y H:i') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="adjust-layout">
    <div class="card adjust-card">
        <h2 class="font-serif adjust-card-title">Corrección auditada</h2>
        <p class="page-subtitle" style="margin-bottom: 1rem;">
            La corrección queda registrada con el valor anterior y el nuevo. Si la compra fue en efectivo, la diferencia se ajusta en la caja del turno.
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
                <textarea name="reason" id="reason" class="form-control" rows="3" maxlength="2000" required placeholder="Explica qué originó la corrección">{{ old('reason') }}</textarea>
            </div>
            <button type="submit" class="btn btn-dark">Guardar ajuste</button>
        </form>
    </div>

    <aside>
        <div class="card adjust-card">
            <h2 class="font-serif adjust-card-title">Impacto del ajuste</h2>
            <div class="adjust-current">
                <span>Total actual</span>
                <strong class="num">$ {{ number_format((float) $purchase->total_amount, 2) }}</strong>
            </div>
            <div class="adjust-reference">
                <div>
                    <span>Valuación del sistema</span>
                    <strong class="num">$ {{ number_format((float) $appraisedTotal, 2) }}</strong>
                </div>
                <div>
                    <span>Negociación registrada</span>
                    <strong class="num {{ bccomp($negotiationDifference, '0', 2) >= 0 ? 'adjust-amount-positive' : 'adjust-amount-negative' }}">
                        {{ bccomp($negotiationDifference, '0', 2) > 0 ? '+' : '' }}$ {{ number_format((float) $negotiationDifference, 2) }}
                    </strong>
                </div>
            </div>
            <div class="adjust-preview">
                <div class="adjust-preview-row" id="preview-new-row">
                    <span>Nuevo total</span>
                    <strong class="num" id="preview-new">$ {{ number_format((float) $purchase->total_amount, 2) }}</strong>
                </div>
                <div class="adjust-preview-row" id="preview-difference-row">
                    <span>Diferencia</span>
                    <strong class="num" id="preview-difference">$ 0.00</strong>
                </div>
            </div>
            <div class="adjust-note" id="adjust-note">
                @if ($purchase->payout_method === \App\Enums\PayoutMethod::Cash)
                    La diferencia se registrará en la caja del turno.
                @else
                    El ajuste no mueve efectivo porque la compra fue por transferencia.
                @endif
            </div>
        </div>
    </aside>
</div>

@if ($purchase->adjustments->isNotEmpty())
    <div class="card adjust-history">
        <div class="adjust-history-head">
            <h2 class="font-serif">Ajustes previos</h2>
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
                        @php($difference = (float) $adjustment->amount_difference)
                        <tr>
                            <td class="num">{{ $adjustment->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $adjustment->user?->name }}</td>
                            <td class="text-right num">$ {{ number_format((float) ($adjustment->old_values['total_amount'] ?? 0), 2) }}</td>
                            <td class="text-right num">$ {{ number_format((float) ($adjustment->new_values['total_amount'] ?? 0), 2) }}</td>
                            <td class="text-right num {{ $difference >= 0 ? 'adjust-amount-positive' : 'adjust-amount-negative' }}">
                                {{ $difference >= 0 ? '+' : '' }}$ {{ number_format($difference, 2) }}
                            </td>
                            <td>{{ $adjustment->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('total_amount');
    var current = {{ (float) $purchase->total_amount }};
    var newValue = document.getElementById('preview-new');
    var difference = document.getElementById('preview-difference');
    var differenceRow = document.getElementById('preview-difference-row');
    var note = document.getElementById('adjust-note');
    var isCash = @json($purchase->payout_method === \App\Enums\PayoutMethod::Cash);

    function money(value) {
        return '$ ' + Math.abs(value).toFixed(2);
    }

    function refresh() {
        var value = Number(input.value) || 0;
        var delta = value - current;
        newValue.textContent = money(value);
        difference.textContent = (delta < 0 ? '- ' : '') + money(delta);
        differenceRow.classList.toggle('is-negative', delta < 0);
        differenceRow.classList.toggle('is-positive', delta > 0);

        if (delta === 0) {
            note.textContent = 'Sin cambios respecto al total actual.';
        } else if (!isCash) {
            note.textContent = 'El ajuste no mueve efectivo porque la compra fue por transferencia.';
        } else if (delta < 0) {
            note.textContent = 'Se ingresarán ' + money(delta) + ' a la caja del turno.';
        } else {
            note.textContent = 'Se retirarán ' + money(delta) + ' de la caja del turno.';
        }
    }

    input.addEventListener('input', refresh);
    refresh();
})();
</script>
@endpush
