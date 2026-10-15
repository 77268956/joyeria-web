@extends('layouts.admin')

@section('title', 'Nueva Compra de Metales')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Nueva compra de metales</h1>
        <p class="page-subtitle">Identifica al cliente, registra cada pieza con su foto y confirma la valuación del día.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<form id="purchase-form" action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="card">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 1rem;">1. Identificación del cliente</h2>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label" for="customer_id">Cliente <span style="color: var(--color-error);">*</span></label>
                <select name="customer_id" id="customer_id" class="form-select" required>
                    <option value="">Selecciona un cliente…</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ (string) old('customer_id') === (string) $customer->id ? 'selected' : '' }}>
                            {{ $customer->full_name }} — {{ $customer->document_type }} {{ $customer->document_number }}
                        </option>
                    @endforeach
                </select>
                <p class="form-hint">El cliente debe estar registrado con su documento. <a href="{{ route('admin.customers.create') }}" target="_blank">Registrar cliente</a>.</p>
            </div>
            <div class="form-group">
                <label class="form-label" for="payout_method">Método de pago <span style="color: var(--color-error);">*</span></label>
                <select name="payout_method" id="payout_method" class="form-select" required>
                    <option value="cash" {{ old('payout_method') === 'cash' ? 'selected' : '' }}>Efectivo (egresa de caja)</option>
                    <option value="transfer" {{ old('payout_method') === 'transfer' ? 'selected' : '' }}>Transferencia</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="notes">Notas</label>
            <textarea name="notes" id="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 class="font-serif" style="font-size: 1.15rem; margin: 0;">2. Piezas compradas</h2>
            <button type="button" class="btn btn-outline btn-sm" id="add-item">Añadir pieza</button>
        </div>

        <div id="items"></div>

        <p class="muted" style="font-size: 0.88rem; margin-top: 0.5rem;">
            Cada pieza requiere foto obligatoria. El peso puede tomarse de la balanza o justificarse como manual.
        </p>
    </div>

    <div class="card">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 1rem;">3. Valuación y confirmación</h2>

        <div style="display: grid; gap: 0.6rem; max-width: 420px;">
            <div style="display: flex; justify-content: space-between;">
                <span class="muted">Total a pagar</span>
                <strong class="font-serif num" style="font-size: 1.4rem;" id="grand-total">$ 0.00</strong>
            </div>
        </div>

        <p id="approval-notice" class="muted" style="display: none; margin-top: 0.75rem;"></p>
        <p id="form-error" class="alert alert-error" style="display: none; margin-top: 0.75rem;"></p>

        <div style="margin-top: 1rem;">
            <button type="submit" class="btn btn-dark" id="submit-purchase">Registrar compra</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const quoteUrl = @json(route('admin.purchases.quote'));
    const metals = @json($metalsJson);
    const approvalLimit = @json($approvalLimit);
    const canApprove = @json($canApprove);

    const el = (id) => document.getElementById(id);
    const money = (n) => '$ ' + (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);

    function metalOptions() {
        return metals.map((m) => '<option value="' + m.id + '">' + m.name + '</option>').join('');
    }

    function purityOptions(metalId) {
        const metal = metals.find((m) => String(m.id) === String(metalId));
        if (!metal) {
            return '<option value="">—</option>';
        }
        return metal.purities.map((p) => '<option value="' + p.id + '">' + p.code + '</option>').join('');
    }

    function createRow() {
        const row = document.createElement('div');
        row.className = 'purchase-item';
        row.style.cssText = 'border: 1px solid var(--color-linea); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;';
        row.innerHTML =
            '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">' +
                '<strong class="font-serif">Pieza <span class="item-index"></span></strong>' +
                '<button type="button" class="btn btn-danger btn-sm" data-remove>Eliminar</button>' +
            '</div>' +
            '<div class="grid-2">' +
                '<div class="form-group"><label class="form-label">Metal</label><select class="form-select" data-field="metal_id">' + metalOptions() + '</select></div>' +
                '<div class="form-group"><label class="form-label">Quilataje / Pureza</label><select class="form-select" data-field="purity_id"></select></div>' +
            '</div>' +
            '<div class="form-group"><label class="form-label">Descripción</label><input type="text" class="form-control" data-field="description" maxlength="150" placeholder="Ej: Cadena rota 18k"></div>' +
            '<div class="grid-3">' +
                '<div class="form-group"><label class="form-label">Peso bruto (g)</label><input type="number" step="0.001" min="0.001" class="form-control" data-field="gross_weight_g"></div>' +
                '<div class="form-group"><label class="form-label">Peso neto (g)</label><input type="number" step="0.001" min="0.001" class="form-control" data-field="net_weight_g"></div>' +
                '<div class="form-group"><label class="form-label">Origen del peso</label><select class="form-select" data-field="weight_source"><option value="scale">Balanza</option><option value="manual">Manual</option></select></div>' +
            '</div>' +
            '<div class="grid-2">' +
                '<div class="form-group"><label class="form-label">Destino</label><select class="form-select" data-field="destination"><option value="used_inventory">Inventario de ocasión</option><option value="melting">Materia prima (fundición)</option></select></div>' +
                '<div class="form-group"><label class="form-label">Foto de la pieza</label><input type="file" class="form-control" data-field="photo" accept="image/*"></div>' +
            '</div>' +
            '<div class="item-quote muted" style="border-top: 1px dashed var(--color-linea); padding-top: 0.6rem; font-size: 0.9rem;">Completa metal, pureza y pesos para calcular la valuación.</div>';

        const metalSelect = row.querySelector('[data-field="metal_id"]');
        const puritySelect = row.querySelector('[data-field="purity_id"]');
        puritySelect.innerHTML = purityOptions(metalSelect.value);

        metalSelect.addEventListener('change', () => {
            puritySelect.innerHTML = purityOptions(metalSelect.value);
            scheduleQuote(row);
        });

        ['purity_id', 'gross_weight_g', 'net_weight_g'].forEach((field) => {
            row.querySelector('[data-field="' + field + '"]').addEventListener('input', () => scheduleQuote(row));
            row.querySelector('[data-field="' + field + '"]').addEventListener('change', () => scheduleQuote(row));
        });

        row.querySelector('[data-remove]').addEventListener('click', () => {
            row.remove();
            reindex();
            recalcTotal();
        });

        el('items').appendChild(row);
        reindex();
        return row;
    }

    let timers = new WeakMap();

    function scheduleQuote(row) {
        clearTimeout(timers.get(row));
        timers.set(row, setTimeout(() => quoteRow(row), 350));
    }

    function quoteRow(row) {
        const metalId = row.querySelector('[data-field="metal_id"]').value;
        const purityId = row.querySelector('[data-field="purity_id"]').value;
        const gross = row.querySelector('[data-field="gross_weight_g"]').value;
        const net = row.querySelector('[data-field="net_weight_g"]').value;
        const box = row.querySelector('.item-quote');

        if (!metalId || !purityId || !gross || !net) {
            row.dataset.amount = '0';
            box.textContent = 'Completa metal, pureza y pesos para calcular la valuación.';
            recalcTotal();
            return;
        }

        fetch(quoteUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ metal_id: metalId, purity_id: purityId, gross_weight_g: gross, net_weight_g: net }),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'No se pudo valuar la pieza.');
                }
                row.dataset.amount = data.amount;
                box.innerHTML =
                    'Ley ' + data.fineness_snapshot + ' · ' + money(data.price_per_gram_snapshot) + '/g · ' +
                    'descuento ' + data.discount_percent_snapshot + '% → <strong class="num">' + money(data.amount) + '</strong>';
                recalcTotal();
            })
            .catch((exception) => {
                row.dataset.amount = '0';
                box.textContent = exception.message;
                recalcTotal();
            });
    }

    function reindex() {
        const rows = el('items').querySelectorAll('.purchase-item');
        rows.forEach((row, index) => {
            row.querySelector('.item-index').textContent = (index + 1);
            row.querySelectorAll('[data-field]').forEach((field) => {
                field.name = 'items[' + index + '][' + field.dataset.field + ']';
            });
        });
    }

    function recalcTotal() {
        const rows = el('items').querySelectorAll('.purchase-item');
        let total = 0;
        rows.forEach((row) => {
            total += Number(row.dataset.amount) || 0;
        });
        el('grand-total').textContent = money(total);

        const notice = el('approval-notice');
        if (Number(total) > Number(approvalLimit) && !canApprove) {
            notice.textContent = 'Este monto supera el límite de $ ' + money(approvalLimit) + '. La compra quedará pendiente de aprobación de gerencia.';
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
        }
    }

    el('add-item').addEventListener('click', createRow);

    el('purchase-form').addEventListener('submit', (event) => {
        const error = el('form-error');
        error.style.display = 'none';

        if (el('items').querySelectorAll('.purchase-item').length === 0) {
            event.preventDefault();
            error.textContent = 'Agrega al menos una pieza.';
            error.style.display = 'block';
        }
    });

    createRow();
})();
</script>
@endpush
