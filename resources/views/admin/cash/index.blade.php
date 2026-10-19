@extends('layouts.admin')

@section('title', 'Caja y Turnos')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Caja y Turnos</h1>
        <p class="page-subtitle">Apertura y cierre de turno, movimientos de efectivo y arqueo comparativo</p>
    </div>
</div>

@include('admin.partials.alerts')

@if ($session)
    <div class="card">
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="badge badge-success">Turno abierto</span>
                <p class="page-subtitle" style="margin-top: 0.6rem;">
                    Abierto por <strong>{{ $session->user?->name }}</strong>
                    el {{ $session->opened_at?->format('d/m/Y H:i') }}
                </p>
            </div>
            <div style="text-align: right;">
                <div class="form-hint">Efectivo inicial</div>
                <div class="font-serif" style="font-size: 1.6rem; font-variant-numeric: tabular-nums;">
                    $ {{ number_format((float) $session->opening_amount, 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="grid-3">
        <div class="card">
            <div class="form-hint">Esperado en efectivo</div>
            <div class="font-serif" style="font-size: 1.7rem; font-variant-numeric: tabular-nums;">$ {{ number_format((float) $expected['cash'], 2) }}</div>
        </div>
        <div class="card">
            <div class="form-hint">Esperado en tarjeta</div>
            <div class="font-serif" style="font-size: 1.7rem; font-variant-numeric: tabular-nums;">$ {{ number_format((float) $expected['card'], 2) }}</div>
        </div>
        <div class="card">
            <div class="form-hint">Esperado en transferencia</div>
            <div class="font-serif" style="font-size: 1.7rem; font-variant-numeric: tabular-nums;">$ {{ number_format((float) $expected['transfer'], 2) }}</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <h2 class="font-serif" style="font-size: 1.2rem; margin-bottom: 1rem;">Registrar movimiento</h2>
            <form action="{{ route('admin.cash.movement') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="type">Tipo</label>
                    <select name="type" id="type" class="form-select" required>
                        @foreach ($movementTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount">Monto</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reason">Motivo</label>
                    <input type="text" name="reason" id="reason" class="form-control" maxlength="255" required>
                </div>
                <button type="submit" class="btn btn-dark">Registrar movimiento</button>
            </form>
        </div>

        <div class="card">
            <h2 class="font-serif" style="font-size: 1.2rem; margin-bottom: 1rem;">Cerrar turno (arqueo)</h2>
            <form action="{{ route('admin.cash.close') }}" method="POST" id="form-close-cash">
                @csrf
                <input type="hidden" name="supervisor_pin" id="close_supervisor_pin">
                <div class="grid-3">
                    <div class="form-group">
                        <label class="form-label" for="counted_cash">Efectivo contado</label>
                        <input type="number" step="0.01" min="0" name="counted_cash" id="counted_cash" class="form-control" value="{{ number_format((float) $expected['cash'], 2, '.', '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="counted_card">Tarjeta contada</label>
                        <input type="number" step="0.01" min="0" name="counted_card" id="counted_card" class="form-control" value="{{ number_format((float) $expected['card'], 2, '.', '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="counted_transfer">Transferencia contada</label>
                        <input type="number" step="0.01" min="0" name="counted_transfer" id="counted_transfer" class="form-control" value="{{ number_format((float) $expected['transfer'], 2, '.', '') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="close_note">Nota de cierre (obligatoria si hay diferencia)</label>
                    <input type="text" name="close_note" id="close_note" class="form-control" maxlength="255">
                </div>
                <button type="button" id="btn-trigger-close" class="btn btn-gold">Cerrar turno</button>
            </form>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($session->movements as $movement)
                        <tr>
                            <td class="num">{{ $movement->created_at?->format('H:i') }}</td>
                            <td>
                                <span class="badge {{ $movement->type->value === 'in' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $movement->type->label() }}
                                </span>
                            </td>
                            <td>{{ $movement->reason }}</td>
                            <td>{{ $movement->user?->name }}</td>
                            <td class="text-right num">$ {{ number_format((float) $movement->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted" style="text-align: center; padding: 1.5rem;">Sin movimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="card" style="max-width: 520px;">
        <h2 class="font-serif" style="font-size: 1.3rem; margin-bottom: 0.5rem;">Abrir turno de caja</h2>
        <p class="page-subtitle" style="margin-bottom: 1.25rem;">Declara el efectivo inicial con el que comienzas el turno.</p>
        <form action="{{ route('admin.cash.open') }}" method="POST" id="form-open-cash">
            @csrf
            <div class="form-group">
                <label class="form-label" for="opening_amount">Efectivo inicial</label>
                <input type="number" step="0.01" min="0" name="opening_amount" id="opening_amount" class="form-control" value="0.00" required autofocus>
            </div>
            <button type="button" id="btn-trigger-open" class="btn btn-gold">Abrir turno</button>
        </form>
    </div>
@endif

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--color-linea);">
        <h2 class="font-serif" style="font-size: 1.2rem;">Historial de turnos cerrados</h2>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cajero</th>
                    <th>Apertura</th>
                    <th>Cierre</th>
                    <th class="text-right">Esperado</th>
                    <th class="text-right">Contado</th>
                    <th class="text-right">Diferencia</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $closed)
                    <tr>
                        <td>{{ $closed->user?->name }}</td>
                        <td class="num">{{ $closed->opened_at?->format('d/m/Y H:i') }}</td>
                        <td class="num">{{ $closed->closed_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-right num">$ {{ number_format((float) $closed->expected_cash, 2) }}</td>
                        <td class="text-right num">$ {{ number_format((float) $closed->counted_cash, 2) }}</td>
                        <td class="text-right num" style="color: {{ (float) $closed->difference_total == 0.0 ? 'inherit' : 'var(--color-error)' }};">
                            $ {{ number_format((float) $closed->difference_total, 2) }}
                            @if ($closed->supervisor)
                                <div class="muted" style="font-size: 0.72rem; line-height: 1.1; margin-top: 2px;">
                                    Aut: {{ $closed->supervisor->name }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted" style="text-align: center; padding: 1.5rem;">Aún no hay turnos cerrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1rem;">
    {{ $history->links() }}
</div>

{{-- Modal Confirmar Apertura --}}
<div id="modal-confirm-open" style="display: none; position: fixed; inset: 0; background: rgba(18, 16, 12, 0.65); align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(2px);">
    <div style="background: #FFFFFF; border-radius: 8px; width: 100%; max-width: 420px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-linea); display: flex; justify-content: space-between; align-items: center;">
            <strong style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--color-noche);">Confirmar Apertura de Caja</strong>
            <button type="button" class="btn-close-open-modal" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--color-piedra);">&times;</button>
        </div>
        <div style="padding: 1.5rem; text-align: center;">
            <p class="muted" style="font-size: 0.9rem; margin-bottom: 0.75rem;">¿Confirmas iniciar tu turno con el siguiente fondo de efectivo inicial?</p>
            <div id="open-amount-display" class="font-serif" style="font-size: 2rem; color: var(--color-dorado); font-weight: 700; margin-bottom: 1.25rem;">$ 0.00</div>
            <div style="display: flex; gap: 0.75rem; justify-content: center;">
                <button type="button" class="btn btn-outline btn-close-open-modal">Cancelar</button>
                <button type="button" id="btn-submit-open-confirmed" class="btn btn-gold">Confirmar y Abrir Turno</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Confirmar Cierre Exacto --}}
<div id="modal-confirm-close-exact" style="display: none; position: fixed; inset: 0; background: rgba(18, 16, 12, 0.65); align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(2px);">
    <div style="background: #FFFFFF; border-radius: 8px; width: 100%; max-width: 420px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-linea); display: flex; justify-content: space-between; align-items: center;">
            <strong style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--color-noche);">Confirmar Cierre de Turno</strong>
            <button type="button" class="btn-close-exact-modal" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--color-piedra);">&times;</button>
        </div>
        <div style="padding: 1.5rem; text-align: center;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #EDF7EE; color: var(--color-exito); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 0.75rem;">✓</div>
            <p style="font-size: 0.95rem; color: var(--color-noche); margin-bottom: 0.35rem; font-weight: 600;">Arqueo Exacto ($ 0.00 de diferencia)</p>
            <p class="muted" style="font-size: 0.85rem; margin-bottom: 1.25rem;">Los valores contados coinciden exactamente con lo registrado en el sistema. ¿Deseas cerrar tu turno?</p>
            <div style="display: flex; gap: 0.75rem; justify-content: center;">
                <button type="button" class="btn btn-outline btn-close-exact-modal">Cancelar</button>
                <button type="button" id="btn-submit-close-exact" class="btn btn-gold">Confirmar Cierre</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Supervisión con PIN para Descuadre --}}
<div id="modal-supervisor-cash-close" style="display: none; position: fixed; inset: 0; background: rgba(18, 16, 12, 0.65); align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(2px);">
    <div style="background: #FFFFFF; border-radius: 8px; width: 100%; max-width: 460px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-linea); display: flex; justify-content: space-between; align-items: center;">
            <strong style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--color-noche);">Autorización de Supervisor (Descuadre)</strong>
            <button type="button" class="btn-close-supervisor-modal" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--color-piedra);">&times;</button>
        </div>
        <div style="padding: 1.5rem;">
            <div style="background: #FFF4E5; border-left: 4px solid #D97706; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; color: #92400E; font-size: 0.9rem;">Diferencia en Arqueo:</span>
                    <strong id="modal-diff-display" style="font-size: 1.15rem; font-family: monospace;">$ 0.00</strong>
                </div>
                <small id="modal-diff-type" style="color: #B45309; display: block; margin-top: 2px;">Faltante en caja</small>
            </div>

            <p class="muted" style="font-size: 0.85rem; margin-bottom: 1rem;">
                Por política de seguridad de la joyería, todo cierre con descuadre exige una justificación y la validación presencial del Gerente o Dueño con su PIN de 4 dígitos.
            </p>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="modal_close_note" style="font-size: 0.85rem;">Nota explicativa de la diferencia <span style="color: var(--color-error);">*</span></label>
                <input type="text" id="modal_close_note" class="form-control" placeholder="Ej: Faltante de $50 por error en cambio con cliente" maxlength="255">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="modal_supervisor_pin" style="font-size: 0.85rem;">PIN de Supervisor (Gerente / Dueño) <span style="color: var(--color-error);">*</span></label>
                <input type="password" id="modal_supervisor_pin" class="form-control" maxlength="4" placeholder="••••" style="letter-spacing: 6px; text-align: center; font-size: 1.3rem;">
            </div>

            <div id="cash-pin-error" style="display: none; padding: 0.5rem 0.75rem; background: #FDEDED; border-radius: 4px; color: var(--color-error); font-size: 0.82rem; margin-bottom: 1rem;"></div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="btn btn-outline btn-close-supervisor-modal">Volver a contar</button>
                <button type="button" id="btn-submit-supervisor-close" class="btn btn-gold">Autorizar y Cerrar Turno</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const validatePinUrl = @json(route('admin.pos.authorization.pin'));
    const el = (id) => document.getElementById(id);
    const money = (n) => '$ ' + (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);

    // --- 1. Confirmación de Apertura ---
    const btnTriggerOpen = el('btn-trigger-open');
    if (btnTriggerOpen) {
        btnTriggerOpen.addEventListener('click', () => {
            const amountInput = el('opening_amount');
            const val = Number(amountInput.value) || 0;
            if (val < 0) {
                alert('El efectivo inicial no puede ser negativo.');
                amountInput.focus();
                return;
            }
            el('open-amount-display').textContent = money(val);
            el('modal-confirm-open').style.display = 'flex';
        });

        document.querySelectorAll('.btn-close-open-modal').forEach((btn) => {
            btn.addEventListener('click', () => {
                el('modal-confirm-open').style.display = 'none';
            });
        });

        el('btn-submit-open-confirmed')?.addEventListener('click', () => {
            el('form-open-cash').submit();
        });
    }

    // --- 2. Confirmación / Supervisión de Cierre ---
    const btnTriggerClose = el('btn-trigger-close');
    if (btnTriggerClose) {
        @if ($session)
        const expCash = Number(@json((float) ($expected['cash'] ?? 0)));
        const expCard = Number(@json((float) ($expected['card'] ?? 0)));
        const expTrans = Number(@json((float) ($expected['transfer'] ?? 0)));

        btnTriggerClose.addEventListener('click', () => {
            const cntCash = Number(el('counted_cash').value) || 0;
            const cntCard = Number(el('counted_card').value) || 0;
            const cntTrans = Number(el('counted_transfer').value) || 0;

            const diff = (cntCash - expCash) + (cntCard - expCard) + (cntTrans - expTrans);

            if (Math.abs(diff) < 0.005) {
                // Arqueo exacto
                el('modal-confirm-close-exact').style.display = 'flex';
            } else {
                // Hay descuadre: requiere nota y PIN de supervisor
                const isShortage = diff < 0;
                el('modal-diff-display').textContent = (isShortage ? '-' : '+') + money(Math.abs(diff));
                el('modal-diff-display').style.color = isShortage ? 'var(--color-error)' : '#D97706';
                el('modal-diff-type').textContent = isShortage ? 'Faltante de dinero en el arqueo' : 'Sobrante de dinero en el arqueo';

                // Pre-llenar nota si ya escribió algo
                el('modal_close_note').value = el('close_note').value.trim();
                el('modal_supervisor_pin').value = '';
                el('cash-pin-error').style.display = 'none';

                el('modal-supervisor-cash-close').style.display = 'flex';
                el('modal_supervisor_pin').focus();
            }
        });

        // Modales cerrar
        document.querySelectorAll('.btn-close-exact-modal').forEach((btn) => {
            btn.addEventListener('click', () => {
                el('modal-confirm-close-exact').style.display = 'none';
            });
        });

        el('btn-submit-close-exact')?.addEventListener('click', () => {
            el('form-close-cash').submit();
        });

        document.querySelectorAll('.btn-close-supervisor-modal').forEach((btn) => {
            btn.addEventListener('click', () => {
                el('modal-supervisor-cash-close').style.display = 'none';
            });
        });

        el('btn-submit-supervisor-close')?.addEventListener('click', async () => {
            const note = el('modal_close_note').value.trim();
            const pin = el('modal_supervisor_pin').value.trim();
            const errorBox = el('cash-pin-error');
            errorBox.style.display = 'none';

            if (!note) {
                errorBox.textContent = 'Debes ingresar la nota explicativa de la diferencia.';
                errorBox.style.display = 'block';
                el('modal_close_note').focus();
                return;
            }

            if (!pin || pin.length !== 4) {
                errorBox.textContent = 'El PIN de supervisor debe contener exactamente 4 dígitos.';
                errorBox.style.display = 'block';
                el('modal_supervisor_pin').focus();
                return;
            }

            // Validar PIN en el backend
            try {
                const response = await fetch(validatePinUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ pin: pin }),
                });

                const data = await response.json();

                if (!response.ok || !data.valid) {
                    errorBox.textContent = data.message || 'PIN incorrecto o el usuario no tiene permisos de supervisor.';
                    errorBox.style.display = 'block';
                    el('modal_supervisor_pin').select();
                    return;
                }

                // PIN válido: transferir datos al formulario principal y enviar
                el('close_note').value = note;
                el('close_supervisor_pin').value = pin;
                el('form-close-cash').submit();
            } catch (err) {
                errorBox.textContent = 'Error al verificar el PIN. Intenta nuevamente.';
                errorBox.style.display = 'block';
            }
        });
        @endif
    }
})();
</script>
@endpush
