@extends('layouts.admin')

@section('title', 'Punto de Venta')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Punto de Venta</h1>
        <p class="page-subtitle">
            Escanea o busca una pieza, arma el carrito y cobra.
            @if ($session)
                Turno abierto desde {{ $session->opened_at?->format('H:i') }}.
            @else
                <span style="color: var(--color-error);">No hay un turno de caja abierto.</span>
            @endif
        </p>
    </div>
    <div class="page-actions">
        @can('layaways.manage')
            <a href="{{ route('admin.layaways.create') }}" class="btn btn-outline">Apartar pieza</a>
        @endcan
        <a href="{{ route('admin.cash.index') }}" class="btn btn-outline">Caja</a>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-outline" target="_blank">Nuevo cliente</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="grid-2" style="grid-template-columns: 1.15fr 1fr; align-items: start;">
    <div class="card">
        <label class="form-label" for="pos-search">Buscar pieza (código de barras, SKU o nombre)</label>
        <input type="text" id="pos-search" class="form-control" placeholder="Escanea o escribe y presiona Enter" autocomplete="off" autofocus>
        <div id="pos-results" style="margin-top: 0.75rem;"></div>
    </div>

    <div class="card">
        <h2 class="font-serif" style="font-size: 1.2rem; margin-bottom: 1rem;">Carrito</h2>
        <div class="table-wrap">
            <table class="data-table" id="cart-table">
                <thead>
                    <tr>
                        <th>Pieza</th>
                        <th class="text-right">Precio</th>
                        <th style="width: 120px;">Descuento</th>
                        <th class="text-right">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cart-body">
                    <tr id="cart-empty"><td colspan="5" class="muted" style="text-align: center; padding: 1.25rem;">El carrito está vacío.</td></tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1rem; display: grid; gap: 0.6rem;">
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Subtotal</span>
                <span class="num" id="sum-subtotal">$ 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Descuento total</span>
                <span class="num" id="sum-discount">$ 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--color-linea); padding-top: 0.6rem;">
                <strong class="font-serif" style="font-size: 1.2rem;">Total</strong>
                <strong class="font-serif num" style="font-size: 1.4rem;" id="sum-total">$ 0.00</strong>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h2 class="font-serif" style="font-size: 1.2rem; margin-bottom: 1rem;">Cobro</h2>

    <div class="grid-2">
        <div class="form-group">
            <label class="form-label" for="customer_id">Cliente (opcional)</label>
            <select id="customer_id" class="form-select">
                <option value="">Consumidor final</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->full_name }} — {{ $customer->document_type }} {{ $customer->document_number }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label" for="discount_total">Descuento global</label>
                <input type="number" step="0.01" min="0" id="discount_total" class="form-control" value="0.00">
            </div>
            <div class="form-group">
                <label class="form-label" for="discount_reason">Motivo del descuento</label>
                <input type="text" id="discount_reason" class="form-control" maxlength="255" placeholder="Obligatorio si hay descuento">
            </div>
        </div>
    </div>

    <div style="margin-bottom: 1rem;">
        <label class="form-label">Pagos</label>
        <div id="payments"></div>
        <button type="button" class="btn btn-outline btn-sm" id="add-payment" style="margin-top: 0.5rem;">Agregar pago</button>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div class="form-hint">
            Total pagado: <span class="num" id="sum-paid">$ 0.00</span> ·
            <span id="pay-status" class="muted">Faltante $ 0.00</span>
            @if (! $canOverrideDiscount)
                <br>Descuento máximo para tu rol: {{ number_format((float) $maxDiscountPercent, 2) }}%
            @endif
        </div>
        <button type="button" class="btn btn-gold" id="confirm-sale">Registrar venta</button>
    </div>

    <p id="pos-error" class="alert alert-error" style="display: none; margin-top: 1rem;"></p>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const routes = {
        search: @json(route('admin.pos.search')),
        store: @json(route('admin.pos.store')),
    };

    const cart = [];
    let searchController = null;

    const el = (id) => document.getElementById(id);
    const money = (n) => '$ ' + (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);

    function renderCart() {
        const body = el('cart-body');
        body.innerHTML = '';

        if (cart.length === 0) {
            body.innerHTML = '<tr id="cart-empty"><td colspan="5" class="muted" style="text-align: center; padding: 1.25rem;">El carrito está vacío.</td></tr>';
            recalc();
            return;
        }

        cart.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + item.name + '<div class="form-hint">' + item.sku + '</div></td>' +
                '<td class="text-right num">' + money(item.price) + '</td>' +
                '<td><input type="number" step="0.01" min="0" class="form-control" style="height:34px;" value="' + item.discount.toFixed(2) + '" data-index="' + index + '"></td>' +
                '<td class="text-right num">' + money(item.price - item.discount) + '</td>' +
                '<td class="text-right"><button type="button" class="btn btn-danger btn-sm" data-remove="' + index + '">×</button></td>';
            body.appendChild(tr);
        });

        body.querySelectorAll('[data-index]').forEach((input) => {
            input.addEventListener('input', (event) => {
                const index = Number(event.target.dataset.index);
                cart[index].discount = Math.max(0, Number(event.target.value) || 0);
                renderCart();
            });
        });

        body.querySelectorAll('[data-remove]').forEach((button) => {
            button.addEventListener('click', () => {
                cart.splice(Number(button.dataset.remove), 1);
                renderCart();
            });
        });

        recalc();
    }

    function recalc() {
        const subtotal = cart.reduce((sum, item) => sum + item.price, 0);
        const lineDiscounts = cart.reduce((sum, item) => sum + item.discount, 0);
        const globalDiscount = Math.max(0, Number(el('discount_total').value) || 0);
        const discountTotal = lineDiscounts + globalDiscount;
        const total = Math.max(0, subtotal - discountTotal);

        el('sum-subtotal').textContent = money(subtotal);
        el('sum-discount').textContent = money(discountTotal);
        el('sum-total').textContent = money(total);

        updatePaid(total);
    }

    function paymentRows() {
        return Array.from(el('payments').querySelectorAll('[data-payment]'));
    }

    function updatePaid(total) {
        let paid = 0;
        paymentRows().forEach((row) => {
            paid += Number(row.querySelector('[data-amount]').value) || 0;
        });

        el('sum-paid').textContent = money(paid);

        const diff = Math.round((paid - total) * 100) / 100;
        const status = el('pay-status');
        if (diff === 0) {
            status.textContent = 'Pago exacto';
            status.style.color = 'var(--color-exito)';
        } else if (diff < 0) {
            status.textContent = 'Faltante ' + money(-diff);
            status.style.color = 'var(--color-error)';
        } else {
            status.textContent = 'Cambio ' + money(diff);
            status.style.color = 'var(--color-info)';
        }
    }

    function addPaymentRow(method, amount) {
        const row = document.createElement('div');
        row.setAttribute('data-payment', '');
        row.style.cssText = 'display: grid; grid-template-columns: 1.1fr 1fr 1fr 1fr auto; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;';
        row.innerHTML =
            '<select class="form-select" data-method>' +
                '<option value="cash">Efectivo</option>' +
                '<option value="card">Tarjeta</option>' +
                '<option value="transfer">Transferencia</option>' +
            '</select>' +
            '<input type="number" step="0.01" min="0" class="form-control" data-amount placeholder="Monto">' +
            '<input type="number" step="0.01" min="0" class="form-control" data-received placeholder="Recibido (efectivo)">' +
            '<input type="text" class="form-control" data-reference placeholder="Referencia">' +
            '<button type="button" class="btn btn-danger btn-sm" data-drop>×</button>';

        if (method) {
            row.querySelector('[data-method]').value = method;
        }
        if (amount) {
            row.querySelector('[data-amount]').value = amount;
        }

        row.querySelector('[data-method]').addEventListener('change', recalc);
        row.querySelector('[data-amount]').addEventListener('input', () => updatePaid(currentTotal()));
        row.querySelector('[data-drop]').addEventListener('click', () => {
            row.remove();
            updatePaid(currentTotal());
        });

        el('payments').appendChild(row);
    }

    function currentTotal() {
        const raw = el('sum-total').textContent.replace(/[^0-9.]/g, '');
        return Number(raw) || 0;
    }

    function addProductChip(product) {
        if (cart.some((item) => item.id === product.id)) {
            el('pos-error').textContent = 'Esa pieza ya está en el carrito.';
            el('pos-error').style.display = 'flex';
            return;
        }

        cart.push({
            id: product.id,
            name: product.name,
            sku: product.sku + (product.metal ? ' · ' + product.metal : ''),
            price: Number(product.price),
            discount: 0,
        });

        el('pos-search').value = '';
        el('pos-results').innerHTML = '';
        el('pos-error').style.display = 'none';
        renderCart();
    }

    function runSearch(term) {
        if (!term) {
            el('pos-results').innerHTML = '';
            return;
        }

        if (searchController) {
            searchController.abort();
        }
        searchController = new AbortController();

        fetch(routes.search + '?q=' + encodeURIComponent(term), {
            headers: { 'Accept': 'application/json' },
            signal: searchController.signal,
        })
            .then((response) => response.json())
            .then((data) => {
                const box = el('pos-results');
                box.innerHTML = '';

                if (!data.products || data.products.length === 0) {
                    box.innerHTML = '<p class="muted">Sin resultados disponibles.</p>';
                    return;
                }

                data.products.forEach((product) => {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'btn btn-outline';
                    chip.style.cssText = 'display: flex; justify-content: space-between; width: 100%; margin-bottom: 0.4rem; height: auto; padding: 0.6rem 0.8rem; text-align: left;';
                    chip.innerHTML = '<span>' + product.name + '<br><small class="muted">' + product.sku + ' · ' + (product.weight_g) + ' g</small></span><strong class="num">' + money(product.price) + '</strong>';
                    chip.addEventListener('click', () => addProductChip(product));
                    box.appendChild(chip);
                });
            })
            .catch(() => {});
    }

    let debounce = null;
    el('pos-search').addEventListener('input', (event) => {
        clearTimeout(debounce);
        debounce = setTimeout(() => runSearch(event.target.value.trim()), 250);
    });
    el('pos-search').addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch(event.target.value.trim());
        }
    });

    el('discount_total').addEventListener('input', recalc);
    el('add-payment').addEventListener('click', () => addPaymentRow());

    el('confirm-sale').addEventListener('click', () => {
        const error = el('pos-error');
        error.style.display = 'none';

        if (cart.length === 0) {
            error.textContent = 'Agrega al menos una pieza.';
            error.style.display = 'flex';
            return;
        }

        const payments = paymentRows().map((row) => ({
            method: row.querySelector('[data-method]').value,
            amount: row.querySelector('[data-amount]').value,
            received: row.querySelector('[data-received]').value || null,
            reference: row.querySelector('[data-reference]').value || null,
        }));

        if (payments.length === 0) {
            error.textContent = 'Registra al menos un pago.';
            error.style.display = 'flex';
            return;
        }

        const payload = {
            customer_id: el('customer_id').value || null,
            items: cart.map((item) => ({ product_id: item.id, discount: item.discount })),
            discount_total: el('discount_total').value || '0',
            discount_reason: el('discount_reason').value || null,
            payments: payments,
        };

        el('confirm-sale').disabled = true;

        fetch(routes.store, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(payload),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo registrar la venta.');
                }
                window.open(data.ticket_url, '_blank');
                window.location.reload();
            })
            .catch((exception) => {
                error.textContent = exception.message;
                error.style.display = 'flex';
                el('confirm-sale').disabled = false;
            });
    });

    addPaymentRow('cash');
    renderCart();
})();
</script>
@endpush
