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
            <form action="{{ route('admin.cash.close') }}" method="POST">
                @csrf
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
                <button type="submit" class="btn btn-gold">Cerrar turno</button>
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
        <form action="{{ route('admin.cash.open') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="opening_amount">Efectivo inicial</label>
                <input type="number" step="0.01" min="0" name="opening_amount" id="opening_amount" class="form-control" value="0.00" required autofocus>
            </div>
            <button type="submit" class="btn btn-gold">Abrir turno</button>
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
@endsection
