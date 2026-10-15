@extends('layouts.admin')

@section('title', 'Nuevo Apartado')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Nuevo Apartado</h1>
        <p class="page-subtitle">Reserva una pieza con un abono inicial y una fecha límite de pago.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.layaways.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="grid-2">
    <div class="card">
        <form action="{{ route('admin.layaways.store') }}" method="POST" id="layaway-form">
            @csrf

            <div class="form-group">
                <label class="form-label" for="product_id">Pieza a apartar <span style="color: var(--color-error);">*</span></label>
                <select name="product_id" id="product_id" class="form-select" required>
                    <option value="">Selecciona una pieza disponible</option>
                    @foreach ($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-price="{{ (string) $product->price }}"
                            {{ (string) old('product_id', $selectedProductId) === (string) $product->id ? 'selected' : '' }}>
                            {{ $product->name }} · {{ $product->sku }}@if ($product->weight_g) · {{ $product->weight_g }} g @endif
                        </option>
                    @endforeach
                </select>
                @error('product_id') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="customer_id">Cliente <span style="color: var(--color-error);">*</span></label>
                <select name="customer_id" id="customer_id" class="form-select" required>
                    <option value="">Selecciona un cliente</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ (string) old('customer_id') === (string) $customer->id ? 'selected' : '' }}>
                            {{ $customer->full_name }} · {{ $customer->document_type }} {{ $customer->document_number }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                <p class="muted" style="margin-top: 0.35rem; font-size: 0.85rem;">¿Cliente nuevo? <a href="{{ route('admin.customers.create') }}" target="_blank">Regístralo aquí</a> y luego vuelve a esta pantalla.</p>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="due_date">Fecha límite <span style="color: var(--color-error);">*</span></label>
                    <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', $defaultDueDate) }}" max="{{ now()->addDays($maxDays)->toDateString() }}" required>
                    @error('due_date') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                    <p class="muted" style="margin-top: 0.35rem; font-size: 0.85rem;">Máximo {{ $maxDays }} días.</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="initial_amount">Abono inicial <span style="color: var(--color-error);">*</span></label>
                    <input type="number" step="0.01" min="0" name="initial_amount" id="initial_amount" class="form-control num" value="{{ old('initial_amount', '0.00') }}" required>
                    @error('initial_amount') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="initial_method">Método del abono inicial</label>
                    <select name="initial_method" id="initial_method" class="form-select">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->value }}" {{ old('initial_method', 'cash') === $method->value ? 'selected' : '' }}>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                    @error('initial_method') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="reference">Referencia (opcional)</label>
                    <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference') }}" maxlength="80">
                    @error('reference') <p class="muted" style="color: var(--color-error); margin-top: 0.35rem;">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-gold">Registrar apartado</button>
        </form>
    </div>

    <div class="card" style="align-self: start;">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 0.75rem;">Resumen</h2>
        <div style="display: grid; gap: 0.6rem;">
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Precio de la pieza</span>
                <span class="num" id="sum-total">$ 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Abono inicial</span>
                <span class="num" id="sum-initial">$ 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--color-linea); padding-top: 0.6rem;">
                <strong class="font-serif">Saldo pendiente</strong>
                <strong class="font-serif num" style="font-size: 1.3rem;" id="sum-balance">$ 0.00</strong>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const money = (n) => '$ ' + (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);
    const product = document.getElementById('product_id');
    const initial = document.getElementById('initial_amount');

    function price() {
        const option = product.options[product.selectedIndex];
        return option ? Number(option.dataset.price || 0) : 0;
    }

    function refresh() {
        const total = price();
        const paid = Math.max(0, Number(initial.value) || 0);
        document.getElementById('sum-total').textContent = money(total);
        document.getElementById('sum-initial').textContent = money(paid);
        document.getElementById('sum-balance').textContent = money(Math.max(0, total - paid));
    }

    product.addEventListener('change', refresh);
    initial.addEventListener('input', refresh);
    refresh();
})();
</script>
@endpush
