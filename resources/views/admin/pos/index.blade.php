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

    <div id="discount-warning" style="display: none; background-color: #FEF3C7; border-left: 4px solid var(--color-aviso); color: #92400E; padding: 0.75rem 1rem; border-radius: 4px; font-size: 0.85rem; margin-bottom: 1rem; align-items: center; justify-content: space-between; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>⚠️</span>
            <span id="discount-warning-text">El descuento supera el límite autorizado para tu rol. Requerirá autorización de Gerencia para cobrar.</span>
        </div>
        <span class="badge" style="background-color: var(--color-aviso); color: #fff;">Autorización requerida</span>
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

<!-- Modal de Autorización Híbrida de Descuento (PIN Mostrador o Remoto) -->
<div id="modal-supervisor-auth" class="modal-backdrop" style="display: none;">
    <div class="modal" style="max-width: 480px;">
        <div class="modal-header">
            <div>
                <h3 class="font-serif" style="font-size: 1.25rem; margin: 0; color: var(--color-noche);">Autorización de Descuento</h3>
                <span class="muted" style="font-size: 0.78rem;">Se requiere aprobación de Gerencia para este descuento</span>
            </div>
            <button type="button" class="modal-close" id="btn-close-auth-modal">✕</button>
        </div>

        <div class="modal-body">
            <!-- Resumen de montos -->
            <div style="background-color: var(--color-crema); border-radius: 4px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; border: 1px solid var(--color-linea);">
                <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.25rem;">
                    <span class="muted">Subtotal:</span>
                    <strong class="num" id="auth-subtotal">$ 0.00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.25rem; color: var(--color-aviso);">
                    <span>Descuento solicitado:</span>
                    <strong class="num" id="auth-discount">$ 0.00 (0%)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.95rem; font-weight: 600; border-top: 1px solid var(--color-linea); padding-top: 0.35rem; margin-top: 0.35rem;">
                    <span>Total a pagar:</span>
                    <strong class="font-serif num" style="font-size: 1.25rem; color: var(--color-noche);" id="auth-total">$ 0.00</strong>
                </div>
            </div>

            <!-- Selector de Modo: PIN Presencial vs Solicitud Remota -->
            <div style="display: flex; border-bottom: 1px solid var(--color-linea); margin-bottom: 1.25rem;">
                <button type="button" id="tab-pin" style="flex: 1; padding: 0.6rem 0.5rem; border: none; background: none; font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid var(--color-dorado); cursor: pointer; color: var(--color-noche);">
                    🔑 PIN Presencial (Mostrador)
                </button>
                <button type="button" id="tab-remote" style="flex: 1; padding: 0.6rem 0.5rem; border: none; background: none; color: var(--color-piedra); font-size: 0.85rem; cursor: pointer;">
                    📱 Solicitud Remota
                </button>
            </div>

            <!-- Panel 1: PIN Presencial -->
            <div id="panel-pin">
                <p style="font-size: 0.85rem; color: var(--color-piedra); margin-bottom: 1rem; text-align: center;">
                    Si el Gerente o Administrador está presente, solicita que ingrese su <strong>PIN de 4 dígitos</strong>:
                </p>
                <div style="text-align: center; margin-bottom: 1rem;">
                    <input type="password" id="supervisor-pin" maxlength="4" pattern="[0-9]*" inputmode="numeric" placeholder="••••" autocomplete="off" style="width: 150px; height: 50px; font-size: 1.8rem; text-align: center; letter-spacing: 0.4em; border: 2px solid var(--color-dorado); border-radius: 6px; outline: none; margin: 0 auto; display: block; font-family: monospace;">
                </div>
                <p id="pin-error" style="color: var(--color-error); font-size: 0.85rem; text-align: center; display: none; margin-bottom: 0.75rem;"></p>
                <button type="button" id="btn-submit-pin" class="btn btn-gold" style="width: 100%; height: 42px;">
                    Autorizar y Cobrar Venta
                </button>
            </div>

            <!-- Panel 2: Solicitud Remota -->
            <div id="panel-remote" style="display: none;">
                <div id="remote-initial">
                    <p style="font-size: 0.85rem; color: var(--color-piedra); margin-bottom: 1rem;">
                        Envía una solicitud en tiempo real al panel del Gerente para que la apruebe a distancia:
                    </p>
                    <button type="button" id="btn-send-remote" class="btn btn-dark" style="width: 100%; height: 42px;">
                        Enviar solicitud a Gerencia
                    </button>
                </div>

                <div id="remote-waiting" style="display: none; text-align: center; padding: 1.5rem 0.5rem;">
                    <div style="width: 42px; height: 42px; border: 3px solid rgba(184, 154, 94, 0.25); border-top-color: var(--color-dorado); border-radius: 50%; animation: spin-loader 0.9s linear infinite; margin: 0 auto 1rem;"></div>
                    <strong style="display: block; font-size: 1.05rem; color: var(--color-noche); margin-bottom: 0.35rem;">Esperando respuesta de Gerencia...</strong>
                    <p class="muted" style="font-size: 0.82rem; margin-bottom: 1rem;">La solicitud se envió al panel del supervisor. Esta pantalla se actualizará automáticamente en cuanto responda.</p>
                    <button type="button" id="btn-cancel-remote" class="btn btn-outline btn-sm">Cancelar espera</button>
                </div>

                <div id="remote-rejected" style="display: none; text-align: center; padding: 1rem 0;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #FDEDED; color: var(--color-error); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 0.75rem;">✕</div>
                    <strong style="color: var(--color-error); font-size: 1rem; display: block;">Solicitud Rechazada por Gerencia</strong>
                    <p id="rejected-reason-text" class="muted" style="font-size: 0.85rem; margin: 0.5rem 0 1rem;"></p>
                    <button type="button" id="btn-retry-auth" class="btn btn-outline btn-sm">Ajustar descuento</button>
                </div>

                <div id="remote-approved" style="display: none; text-align: center; padding: 1.25rem 0;">
                    <div style="width: 46px; height: 46px; border-radius: 50%; background-color: #EDF7EE; color: var(--color-exito); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 0.75rem;">✓</div>
                    <strong style="color: var(--color-exito); font-size: 1.1rem; display: block;" id="approved-by-label">¡Descuento Aprobado!</strong>
                    <p class="muted" style="font-size: 0.85rem; margin-top: 0.35rem;">Procesando venta en este momento...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const routes = {
        search: @json(route('admin.pos.search')),
        store: @json(route('admin.pos.store')),
        validatePin: @json(route('admin.pos.authorization.pin')),
        requestRemote: @json(route('admin.pos.authorization.store')),
        checkStatus: @json(url('admin/pos/autorizacion')),
    };

    const maxDiscountPercent = Number(@json((float) $maxDiscountPercent)) || 10;
    const canOverrideDiscount = Boolean(@json($canOverrideDiscount));

    let approvedAuthorizationId = null;
    let supervisorPinCode = null;
    let activePollingInterval = null;
    let currentAuthRequestId = null;

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
                resetAuthorizations();
                renderCart();
            });
        });

        body.querySelectorAll('[data-remove]').forEach((button) => {
            button.addEventListener('click', () => {
                cart.splice(Number(button.dataset.remove), 1);
                resetAuthorizations();
                renderCart();
            });
        });

        recalc();
    }

    function resetAuthorizations() {
        approvedAuthorizationId = null;
        supervisorPinCode = null;
        if (activePollingInterval) {
            clearInterval(activePollingInterval);
            activePollingInterval = null;
        }
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

        // Warning banner for seller when discount exceeds limit
        const discountPercent = subtotal > 0 ? (discountTotal / subtotal) * 100 : 0;
        const warning = el('discount-warning');
        if (discountPercent > maxDiscountPercent && !canOverrideDiscount && !approvedAuthorizationId && !supervisorPinCode) {
            warning.style.display = 'flex';
            el('discount-warning-text').textContent = 'El descuento (' + discountPercent.toFixed(1) + '%) supera tu límite permitido (' + maxDiscountPercent + '%). Requerirá autorización de Gerencia para cobrar.';
        } else {
            warning.style.display = 'none';
        }

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
        resetAuthorizations();
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

    el('discount_total').addEventListener('input', () => {
        resetAuthorizations();
        recalc();
    });
    el('add-payment').addEventListener('click', () => addPaymentRow());

    // --- Modal de Autorización Híbrida ---
    function openAuthModal(subtotal, discountTotal, total, percent) {
        el('auth-subtotal').textContent = money(subtotal);
        el('auth-discount').textContent = money(discountTotal) + ' (' + percent.toFixed(1) + '%)';
        el('auth-total').textContent = money(total);

        el('supervisor-pin').value = '';
        el('pin-error').style.display = 'none';

        // Switch to PIN tab by default
        switchAuthTab('pin');

        el('remote-initial').style.display = 'block';
        el('remote-waiting').style.display = 'none';
        el('remote-approved').style.display = 'none';
        el('remote-rejected').style.display = 'none';

        el('modal-supervisor-auth').style.display = 'flex';
        el('supervisor-pin').focus();
    }

    function closeAuthModal() {
        el('modal-supervisor-auth').style.display = 'none';
        if (activePollingInterval) {
            clearInterval(activePollingInterval);
            activePollingInterval = null;
        }
    }

    function switchAuthTab(tab) {
        if (tab === 'pin') {
            el('panel-pin').style.display = 'block';
            el('panel-remote').style.display = 'none';
            el('tab-pin').style.borderBottom = '2px solid var(--color-dorado)';
            el('tab-pin').style.fontWeight = '600';
            el('tab-pin').style.color = 'var(--color-noche)';
            el('tab-remote').style.borderBottom = 'none';
            el('tab-remote').style.fontWeight = '400';
            el('tab-remote').style.color = 'var(--color-piedra)';
            el('supervisor-pin').focus();
        } else {
            el('panel-pin').style.display = 'none';
            el('panel-remote').style.display = 'block';
            el('tab-remote').style.borderBottom = '2px solid var(--color-dorado)';
            el('tab-remote').style.fontWeight = '600';
            el('tab-remote').style.color = 'var(--color-noche)';
            el('tab-pin').style.borderBottom = 'none';
            el('tab-pin').style.fontWeight = '400';
            el('tab-pin').style.color = 'var(--color-piedra)';
        }
    }

    el('tab-pin').addEventListener('click', () => switchAuthTab('pin'));
    el('tab-remote').addEventListener('click', () => switchAuthTab('remote'));
    el('btn-close-auth-modal').addEventListener('click', closeAuthModal);

    // Validador PIN
    el('btn-submit-pin').addEventListener('click', () => {
        const pin = el('supervisor-pin').value.trim();
        const pinError = el('pin-error');
        pinError.style.display = 'none';

        if (pin.length !== 4) {
            pinError.textContent = 'Ingresa un PIN de 4 dígitos.';
            pinError.style.display = 'block';
            return;
        }

        el('btn-submit-pin').disabled = true;

        fetch(routes.validatePin, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ pin: pin }),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok || !data.valid) {
                    throw new Error(data.message || 'PIN incorrecto.');
                }
                supervisorPinCode = pin;
                closeAuthModal();
                executeSale();
            })
            .catch((err) => {
                pinError.textContent = err.message;
                pinError.style.display = 'block';
                el('supervisor-pin').value = '';
                el('supervisor-pin').focus();
            })
            .finally(() => {
                el('btn-submit-pin').disabled = false;
            });
    });

    el('supervisor-pin').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            el('btn-submit-pin').click();
        }
    });

    // Enviar Solicitud Remota
    el('btn-send-remote').addEventListener('click', () => {
        const subtotal = cart.reduce((sum, item) => sum + item.price, 0);
        const lineDiscounts = cart.reduce((sum, item) => sum + item.discount, 0);
        const globalDiscount = Math.max(0, Number(el('discount_total').value) || 0);
        const discountTotal = lineDiscounts + globalDiscount;
        const percent = subtotal > 0 ? (discountTotal / subtotal) * 100 : 0;
        const reason = el('discount_reason').value.trim();

        if (!reason) {
            alert('Debes ingresar el motivo del descuento antes de solicitar autorización.');
            closeAuthModal();
            el('discount_reason').focus();
            return;
        }

        const payments = paymentRows().map((row) => ({
            method: row.querySelector('[data-method]').value,
            amount: row.querySelector('[data-amount]').value,
            received: row.querySelector('[data-received]').value || null,
            reference: row.querySelector('[data-reference]').value || null,
        }));

        el('remote-initial').style.display = 'none';
        el('remote-waiting').style.display = 'block';

        fetch(routes.requestRemote, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                sale_data: {
                    customer_id: el('customer_id').value || null,
                    items: cart.map((item) => ({ product_id: item.id, discount: item.discount })),
                    discount_total: el('discount_total').value || '0',
                    discount_reason: reason,
                    payments: payments,
                },
                subtotal: subtotal.toFixed(2),
                discount_amount: discountTotal.toFixed(2),
                discount_percent: percent.toFixed(2),
                reason: reason,
            }),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo crear la solicitud.');
                }
                currentAuthRequestId = data.id;
                startPollingStatus(data.id);
            })
            .catch((err) => {
                alert(err.message);
                el('remote-initial').style.display = 'block';
                el('remote-waiting').style.display = 'none';
            });
    });

    el('btn-cancel-remote').addEventListener('click', () => {
        if (activePollingInterval) {
            clearInterval(activePollingInterval);
            activePollingInterval = null;
        }
        el('remote-waiting').style.display = 'none';
        el('remote-initial').style.display = 'block';
    });

    el('btn-retry-auth').addEventListener('click', () => {
        closeAuthModal();
        el('discount_total').focus();
    });

    function startPollingStatus(authId) {
        if (activePollingInterval) clearInterval(activePollingInterval);

        activePollingInterval = setInterval(() => {
            fetch(routes.checkStatus + '/' + authId + '/estado', {
                headers: { 'Accept': 'application/json' },
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data.status === 'approved') {
                        clearInterval(activePollingInterval);
                        activePollingInterval = null;
                        approvedAuthorizationId = authId;

                        el('remote-waiting').style.display = 'none';
                        el('remote-approved').style.display = 'block';
                        el('approved-by-label').textContent = '¡Aprobado por ' + (data.authorized_by_name || 'Gerencia') + '!';

                        setTimeout(() => {
                            closeAuthModal();
                            executeSale();
                        }, 1200);
                    } else if (data.status === 'rejected') {
                        clearInterval(activePollingInterval);
                        activePollingInterval = null;

                        el('remote-waiting').style.display = 'none';
                        el('remote-rejected').style.display = 'block';
                        el('rejected-reason-text').textContent = data.rejection_reason ? 'Motivo: ' + data.rejection_reason : 'Sin motivo especificado.';
                    }
                })
                .catch(() => {});
        }, 2500);
    }

    // --- Ejecutar Venta ---
    el('confirm-sale').addEventListener('click', () => {
        const error = el('pos-error');
        error.style.display = 'none';

        if (cart.length === 0) {
            error.textContent = 'Agrega al menos una pieza.';
            error.style.display = 'flex';
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + item.price, 0);
        const lineDiscounts = cart.reduce((sum, item) => sum + item.discount, 0);
        const globalDiscount = Math.max(0, Number(el('discount_total').value) || 0);
        const discountTotal = lineDiscounts + globalDiscount;
        const total = Math.max(0, subtotal - discountTotal);
        const percent = subtotal > 0 ? (discountTotal / subtotal) * 100 : 0;

        if (discountTotal > 0 && !el('discount_reason').value.trim()) {
            error.textContent = 'El motivo del descuento es obligatorio.';
            error.style.display = 'flex';
            el('discount_reason').focus();
            return;
        }

        // Check if authorization is needed
        if (percent > maxDiscountPercent && !canOverrideDiscount && !approvedAuthorizationId && !supervisorPinCode) {
            openAuthModal(subtotal, discountTotal, total, percent);
            return;
        }

        executeSale();
    });

    function executeSale() {
        const error = el('pos-error');
        error.style.display = 'none';

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
            supervisor_pin: supervisorPinCode,
            discount_authorization_id: approvedAuthorizationId,
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
                resetAuthorizations();
            });
    }

    addPaymentRow('cash');
    renderCart();
})();
</script>
@endpush
