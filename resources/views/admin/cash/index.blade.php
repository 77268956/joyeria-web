@extends('layouts.admin')

@section('title', 'Caja y Turnos')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .cash-page {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        gap: 0.65rem;
    }

    /* --- Contenedor Unificado Superior (Título + Turno + Métricas + Acciones) --- */
    .cash-unified-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 0.65rem 1.15rem;
        margin-bottom: 0;
        flex-shrink: 0;
        background-color: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
    }

    .cash-header-main {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        min-width: 210px;
    }

    .cash-title {
        font-size: 1.35rem;
        font-weight: 600;
        margin: 0;
        color: var(--color-noche);
        line-height: 1.2;
    }

    .cash-meta {
        font-size: 0.78rem;
        color: var(--color-piedra);
        margin: 0;
        line-height: 1.2;
    }

    .cash-meta strong {
        color: var(--color-noche);
    }

    .cash-header-metrics {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .cash-metric-item {
        display: flex;
        flex-direction: column;
        text-align: right;
    }

    .cash-metric-label {
        font-size: 0.68rem;
        color: var(--color-piedra);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1.1;
    }

    .cash-metric-value {
        font-size: 1.15rem;
        font-family: var(--font-serif);
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        color: var(--color-noche);
        line-height: 1.2;
    }

    .cash-header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    /* --- Panel inferior: Tabla de Historial a pantalla completa --- */
    .cash-panel-full {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        padding: 0;
        margin-bottom: 0;
        overflow: hidden;
    }

    .cash-panel-head {
        padding: 0.7rem 1.15rem;
        border-bottom: 1px solid var(--color-linea);
        flex-shrink: 0;
    }

    .cash-panel-body {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: auto;
    }

    .cash-table-full {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .cash-table-full thead th {
        position: sticky;
        top: 0;
        background-color: var(--color-crema);
        z-index: 2;
        padding: 0.75rem 0.95rem;
        font-size: 0.74rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-noche);
        border-bottom: 1px solid var(--color-linea);
    }

    .cash-table-full tbody td {
        padding: 0.85rem 0.95rem;
        font-size: 0.88rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
    }

    .cash-table-full tbody tr:hover td {
        background-color: #FBF9F5;
    }

    .cash-history-footer {
        flex-shrink: 0;
        border-top: 1px solid var(--color-linea);
        padding: 0.65rem 1.15rem;
        background-color: var(--color-blanco);
    }

    /* --- Modales --- */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(18, 22, 31, 0.65);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    }

    .modal-backdrop.is-active {
        display: flex;
    }

    @media (max-width: 1024px) {
        .cash-unified-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .cash-header-metrics {
            width: 100%;
            justify-content: space-between;
        }

        .cash-header-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .cash-panel-body {
            overflow: visible;
        }
    }
</style>

<div class="cash-page">
    @include('admin.partials.alerts')

    {{-- Contenedor Unificado Superior --}}
    <div class="card cash-unified-header">
        {{-- Título, badge y datos del turno --}}
        <div class="cash-header-main">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <h1 class="font-serif cash-title">Caja y Turnos</h1>
                @if ($session)
                    <span class="badge badge-success">Turno abierto</span>
                @else
                    <span class="badge" style="background: #E5E7EB; color: #4B5563;">Sin turno abierto</span>
                @endif
            </div>

            @if ($session)
                <p class="cash-meta">
                    Cajero: <strong>{{ $session->user?->name }}</strong> · Iniciado el {{ $session->opened_at?->format('d/m/Y H:i') }}
                </p>
            @else
                <p class="cash-meta">
                    No hay un turno de caja activo en este momento. Abre un turno para iniciar ventas y cobros.
                </p>
            @endif
        </div>

        {{-- Métricas del turno activo --}}
        @if ($session)
            <div class="cash-header-metrics">
                <div class="cash-metric-item">
                    <span class="cash-metric-label">Efectivo inicial</span>
                    <span class="cash-metric-value">$ {{ number_format((float) $session->opening_amount, 2) }}</span>
                </div>
                <div class="cash-metric-item">
                    <span class="cash-metric-label">Esperado efectivo</span>
                    <span class="cash-metric-value">$ {{ number_format((float) $expected['cash'], 2) }}</span>
                </div>
                <div class="cash-metric-item">
                    <span class="cash-metric-label">Esperado tarjeta / transf.</span>
                    <span class="cash-metric-value">$ {{ number_format((float) $expected['card'] + (float) $expected['transfer'], 2) }}</span>
                </div>
            </div>
        @endif

        {{-- Acciones (Modales) --}}
        <div class="cash-header-actions">
            @if ($session)
                <button type="button" class="btn btn-outline btn-sm" id="btn-open-movements">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px; vertical-align: -2px;">
                        <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                    Movimientos de turno ({{ $session->movements->count() }})
                </button>
                <button type="button" class="btn btn-gold btn-sm" id="btn-open-close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px; vertical-align: -2px;">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Cerrar turno
                </button>
            @else
                <button type="button" class="btn btn-gold btn-sm" id="btn-open-shift">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px; vertical-align: -2px;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Abrir turno de caja
                </button>
            @endif
        </div>
    </div>

    {{-- Tabla de Historial a Toda la Vista con Paginación --}}
    <div class="card cash-panel-full">
        <div class="cash-panel-head">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h2 class="font-serif" style="font-size: 1.1rem; margin: 0; color: var(--color-noche);">Historial de turnos cerrados</h2>
                    <p class="page-subtitle" style="margin: 0; font-size: 0.78rem;">Registro de arqueos de caja anteriores, desglose comparativo y autorizaciones de supervisor</p>
                </div>
                <div>
                    <span class="muted" style="font-size: 0.82rem;">
                        Total: <strong>{{ $history->total() }}</strong> {{ $history->total() === 1 ? 'turno registrado' : 'turnos registrados' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="cash-panel-body">
            <table class="cash-table-full data-table">
                <thead>
                    <tr>
                        <th>Cajero</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th class="text-right">Fondo Inicial</th>
                        <th class="text-right">Esperado</th>
                        <th class="text-right">Contado</th>
                        <th class="text-right">Diferencia</th>
                        <th>Nota / Observación</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $closed)
                        @php
                            $diff = (float) $closed->difference_total;
                            $diffZero = abs($diff) < 0.005;
                            $diffNegative = $diff < 0;
                        @endphp
                        <tr>
                            <td>
                                <strong style="color: var(--color-noche);">{{ $closed->user?->name }}</strong>
                                @if ($closed->closedBy && $closed->closedBy->id !== $closed->user_id)
                                    <div class="muted" style="font-size: 0.72rem;">Cerró: {{ $closed->closedBy->name }}</div>
                                @endif
                            </td>
                            <td class="num">{{ $closed->opened_at?->format('d/m/Y H:i') }}</td>
                            <td class="num">{{ $closed->closed_at?->format('d/m/Y H:i') }}</td>
                            <td class="num text-right">$ {{ number_format((float) $closed->opening_amount, 2) }}</td>
                            <td class="num text-right">$ {{ number_format((float) $closed->expected_cash, 2) }}</td>
                            <td class="num text-right">$ {{ number_format((float) $closed->counted_cash, 2) }}</td>
                            <td class="num text-right">
                                @if ($diffZero)
                                    <span style="color: var(--color-exito); font-weight: 600;">$ 0.00</span>
                                @else
                                    <span style="color: {{ $diffNegative ? 'var(--color-error)' : 'var(--color-aviso)' }}; font-weight: 600;">
                                        {{ $diffNegative ? '-' : '+' }}$ {{ number_format(abs($diff), 2) }}
                                    </span>
                                @endif

                                @if ($closed->supervisor)
                                    <div style="font-size: 0.72rem; color: #92400E; margin-top: 2px;">
                                        Aut: {{ $closed->supervisor->name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($closed->close_note)
                                    <span title="{{ $closed->close_note }}" style="display: inline-block; max-width: 250px; overflow: hidden; text-overflow: ellipsis; vertical-align: bottom;">
                                        {{ $closed->close_note }}
                                    </span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm btn-view-details"
                                    data-cajero="{{ $closed->user?->name ?? '—' }}"
                                    data-apertura="{{ $closed->opened_at?->format('d/m/Y H:i') ?? '—' }}"
                                    data-cierre="{{ $closed->closed_at?->format('d/m/Y H:i') ?? '—' }}"
                                    data-opening="{{ number_format((float) $closed->opening_amount, 2) }}"
                                    data-exp-cash="{{ number_format((float) $closed->expected_cash, 2) }}"
                                    data-cnt-cash="{{ number_format((float) $closed->counted_cash, 2) }}"
                                    data-exp-card="{{ number_format((float) $closed->expected_card, 2) }}"
                                    data-cnt-card="{{ number_format((float) $closed->counted_card, 2) }}"
                                    data-exp-trans="{{ number_format((float) $closed->expected_transfer, 2) }}"
                                    data-cnt-trans="{{ number_format((float) $closed->counted_transfer, 2) }}"
                                    data-diff="{{ number_format((float) $closed->difference_total, 2) }}"
                                    data-note="{{ $closed->close_note ?? '' }}"
                                    data-supervisor="{{ $closed->supervisor?->name ?? '' }}"
                                    data-closed-by="{{ $closed->closedBy?->name ?? $closed->user?->name ?? '—' }}"
                                >
                                    Ver arqueo
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="muted" style="text-align: center; padding: 2.5rem;">
                                Aún no hay turnos cerrados en el historial.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($history->hasPages())
            <div class="cash-history-footer">
                {{ $history->links('vendor.pagination.admin') }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL 1: Movimientos del Turno --}}
@if ($session)
<div id="modal-movements" class="modal-backdrop">
    <div class="modal" style="max-width: 780px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <div>
                <h3 class="font-serif" style="font-size: 1.25rem; margin: 0; color: var(--color-noche);">Movimientos del Turno Activo</h3>
                <p class="muted" style="font-size: 0.8rem; margin: 0;">Entradas y salidas de efectivo justificadas</p>
            </div>
            <button type="button" class="modal-close" data-close-modal="modal-movements">&times;</button>
        </div>

        <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 1.25rem;">
            {{-- Formulario para registrar nuevo movimiento --}}
            <div style="background: var(--color-crema); border: 1px solid var(--color-linea); border-radius: 6px; padding: 1rem 1.15rem; margin-bottom: 1.25rem;">
                <h4 class="font-serif" style="font-size: 1.05rem; margin-bottom: 0.65rem; color: var(--color-noche);">Registrar nuevo movimiento</h4>
                <form action="{{ route('admin.cash.movement') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 160px 140px 1fr auto; gap: 0.75rem; align-items: flex-end;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="type" style="font-size: 0.75rem;">Tipo</label>
                            <select name="type" id="type" class="form-select" required style="height: 38px;">
                                @foreach ($movementTypes as $type)
                                    <option value="{{ $type->value }}" {{ old('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="amount" style="font-size: 0.75rem;">Monto</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" placeholder="0.00" value="{{ old('amount') }}" required style="height: 38px;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="reason" style="font-size: 0.75rem;">Motivo / Justificación</label>
                            <input type="text" name="reason" id="reason" class="form-control" placeholder="Ej: Cambio de sencillo, mensajería..." maxlength="255" value="{{ old('reason') }}" required style="height: 38px;">
                        </div>
                        <button type="submit" class="btn btn-dark" style="height: 38px; white-space: nowrap;">
                            Registrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla de movimientos registrados --}}
            <div style="border: 1px solid var(--color-linea); border-radius: 6px; overflow: hidden;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
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
                                <td class="text-right num" style="font-weight: 600;">$ {{ number_format((float) $movement->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted" style="text-align: center; padding: 2rem;">
                                    No hay movimientos registrados en este turno.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($session->movements->isNotEmpty())
                @php
                    $totalIn = $session->movements->where('type.value', 'in')->sum('amount');
                    $totalOut = $session->movements->where('type.value', 'out')->sum('amount');
                @endphp
                <div style="display: flex; justify-content: flex-end; gap: 1.5rem; margin-top: 0.85rem; padding: 0.5rem 0.75rem; background: #FAF8F5; border-radius: 4px; font-size: 0.85rem;">
                    <div>Total Ingresos: <strong style="color: var(--color-exito);">$ {{ number_format((float) $totalIn, 2) }}</strong></div>
                    <div>Total Retiros: <strong style="color: var(--color-error);">$ {{ number_format((float) $totalOut, 2) }}</strong></div>
                </div>
            @endif
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-close-modal="modal-movements">Cerrar</button>
        </div>
    </div>
</div>

{{-- MODAL 2: Cerrar Turno (Arqueo) --}}
<div id="modal-close-shift" class="modal-backdrop">
    <div class="modal" style="max-width: 620px; max-height: 92vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <div>
                <h3 class="font-serif" style="font-size: 1.25rem; margin: 0; color: var(--color-noche);">Cerrar Turno de Caja (Arqueo)</h3>
                <p class="muted" style="font-size: 0.8rem; margin: 0;">Ingresa los valores contados físicamente para comparar con el sistema</p>
            </div>
            <button type="button" class="modal-close" data-close-modal="modal-close-shift">&times;</button>
        </div>

        <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 1.25rem;">
            {{-- Resumen esperado por el sistema --}}
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; background: var(--color-crema); padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; border: 1px solid var(--color-linea);">
                <div>
                    <div style="font-size: 0.72rem; color: var(--color-piedra); text-transform: uppercase;">Esperado Efectivo</div>
                    <div class="font-serif" style="font-size: 1.2rem; font-weight: 600; color: var(--color-noche);">$ {{ number_format((float) $expected['cash'], 2) }}</div>
                </div>
                <div>
                    <div style="font-size: 0.72rem; color: var(--color-piedra); text-transform: uppercase;">Esperado Tarjeta</div>
                    <div class="font-serif" style="font-size: 1.2rem; font-weight: 600; color: var(--color-noche);">$ {{ number_format((float) $expected['card'], 2) }}</div>
                </div>
                <div>
                    <div style="font-size: 0.72rem; color: var(--color-piedra); text-transform: uppercase;">Esperado Transf.</div>
                    <div class="font-serif" style="font-size: 1.2rem; font-weight: 600; color: var(--color-noche);">$ {{ number_format((float) $expected['transfer'], 2) }}</div>
                </div>
            </div>

            <form action="{{ route('admin.cash.close') }}" method="POST" id="form-close-cash">
                @csrf
                <input type="hidden" name="supervisor_pin" id="close_supervisor_pin">

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="counted_cash">Efectivo contado <span style="color: var(--color-error);">*</span></label>
                        <input type="number" step="0.01" min="0" name="counted_cash" id="counted_cash" class="form-control arqueo-input" value="{{ old('counted_cash', number_format((float) $expected['cash'], 2, '.', '')) }}" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="counted_card">Tarjeta contada <span style="color: var(--color-error);">*</span></label>
                        <input type="number" step="0.01" min="0" name="counted_card" id="counted_card" class="form-control arqueo-input" value="{{ old('counted_card', number_format((float) $expected['card'], 2, '.', '')) }}" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="counted_transfer">Transferencia contada <span style="color: var(--color-error);">*</span></label>
                        <input type="number" step="0.01" min="0" name="counted_transfer" id="counted_transfer" class="form-control arqueo-input" value="{{ old('counted_transfer', number_format((float) $expected['transfer'], 2, '.', '')) }}" required>
                    </div>
                </div>

                {{-- Indicador dinámico de diferencia en arqueo --}}
                <div id="arqueo-status-box" style="padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--color-linea); background: #FAF9F5;">
                    <div>
                        <div id="arqueo-status-title" style="font-weight: 600; font-size: 0.92rem; color: var(--color-exito);">✓ Arqueo exacto</div>
                        <div id="arqueo-status-desc" class="muted" style="font-size: 0.8rem; margin-top: 2px;">Los valores contados coinciden con lo esperado en el sistema.</div>
                    </div>
                    <div id="arqueo-status-diff" class="font-serif" style="font-size: 1.35rem; font-weight: 700; color: var(--color-exito);">$ 0.00</div>
                </div>

                {{-- Nota de cierre --}}
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="modal_close_note">
                        Nota de cierre <span id="note-required-star" style="color: var(--color-error); display: none;">* (obligatoria por diferencia)</span>
                    </label>
                    <input type="text" name="close_note" id="modal_close_note" class="form-control" placeholder="Observaciones del arqueo..." maxlength="255" value="{{ old('close_note') }}">
                </div>

                {{-- Sección de autorización de supervisor (cuando hay descuadre) --}}
                <div id="supervisor-section" style="display: none; background: #FFF4E5; border: 1px solid #F59E0B; border-radius: 6px; padding: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; gap: 0.6rem; align-items: flex-start; margin-bottom: 0.75rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <strong style="color: #92400E; font-size: 0.88rem; display: block;" id="sup-title">Se requiere autorización de supervisor</strong>
                            <p style="color: #B45309; font-size: 0.8rem; margin: 2px 0 0;" id="sup-desc">
                                El arqueo presenta diferencia. Ingresa el PIN de 4 dígitos del Gerente o Administrador para autorizar el cierre.
                            </p>
                        </div>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="modal_supervisor_pin" style="color: #92400E; font-size: 0.82rem;">PIN de Supervisor (4 dígitos)</label>
                        <input type="password" id="modal_supervisor_pin" class="form-control" maxlength="4" placeholder="••••" style="max-width: 180px; letter-spacing: 6px; text-align: center; font-size: 1.3rem; font-weight: 700;">
                    </div>

                    <div id="modal-pin-error" style="display: none; margin-top: 0.6rem; color: var(--color-error); font-size: 0.82rem; font-weight: 500;"></div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-close-modal="modal-close-shift">Cancelar</button>
            <button type="button" id="btn-submit-close-cash" class="btn btn-gold">Confirmar y Cerrar Turno</button>
        </div>
    </div>
</div>
@endif

{{-- MODAL 3: Abrir Turno de Caja (Cuando no hay turno abierto) --}}
@if (!$session)
<div id="modal-open-shift" class="modal-backdrop">
    <div class="modal" style="max-width: 440px;">
        <div class="modal-header">
            <div>
                <h3 class="font-serif" style="font-size: 1.25rem; margin: 0; color: var(--color-noche);">Apertura de Turno de Caja</h3>
                <p class="muted" style="font-size: 0.8rem; margin: 0;">Inicia un nuevo turno con tu fondo de efectivo inicial</p>
            </div>
            <button type="button" class="modal-close" data-close-modal="modal-open-shift">&times;</button>
        </div>

        <form action="{{ route('admin.cash.open') }}" method="POST" id="form-open-cash">
            @csrf
            <div class="modal-body" style="padding: 1.5rem; text-align: center;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background-color: var(--color-crema); color: var(--color-dorado); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="7" width="18" height="13" rx="2"></rect>
                        <path d="M3 11h18"></path>
                        <circle cx="12" cy="15" r="1.5"></circle>
                    </svg>
                </div>

                <label class="form-label" for="opening_amount" style="font-size: 0.88rem; margin-bottom: 0.5rem; display: block;">
                    Efectivo inicial en caja
                </label>
                <div style="position: relative; max-width: 240px; margin: 0 auto 1.25rem;">
                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 1.4rem; color: var(--color-piedra); font-family: var(--font-serif);">$</span>
                    <input type="number" step="0.01" min="0" name="opening_amount" id="opening_amount" class="form-control" value="{{ old('opening_amount', '0.00') }}" required autofocus style="height: 52px; font-size: 1.5rem; text-align: center; font-family: var(--font-serif); font-weight: 600; padding-left: 36px;">
                </div>
                <p class="muted" style="font-size: 0.8rem; margin: 0;">Verifica que el dinero físico en caja coincida antes de confirmar.</p>
            </div>

            <div class="modal-footer" style="justify-content: space-between;">
                <button type="button" class="btn btn-outline" data-close-modal="modal-open-shift">Cancelar</button>
                <button type="submit" class="btn btn-gold">Confirmar y Abrir Turno</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL 4: Detalle de Arqueo de Turno Cerrado --}}
<div id="modal-shift-details" class="modal-backdrop">
    <div class="modal" style="max-width: 580px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <div>
                <h3 class="font-serif" style="font-size: 1.25rem; margin: 0; color: var(--color-noche);">Detalle de Arqueo y Cierre</h3>
                <p class="muted" style="font-size: 0.8rem; margin: 0;" id="det-subtitle">Auditoría completa del turno</p>
            </div>
            <button type="button" class="modal-close" data-close-modal="modal-shift-details">&times;</button>
        </div>

        <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 1.25rem;">
            {{-- Encabezado con datos del turno --}}
            <div style="background: var(--color-crema); padding: 0.85rem 1rem; border-radius: 6px; border: 1px solid var(--color-linea); margin-bottom: 1.25rem; display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; font-size: 0.85rem;">
                <div>
                    <span class="muted" style="display: block; font-size: 0.74rem;">Cajero:</span>
                    <strong id="det-cajero" style="color: var(--color-noche); font-size: 0.95rem;">—</strong>
                </div>
                <div>
                    <span class="muted" style="display: block; font-size: 0.74rem;">Cerrado por:</span>
                    <span id="det-closed-by" style="color: var(--color-noche);">—</span>
                </div>
                <div>
                    <span class="muted" style="display: block; font-size: 0.74rem;">Fecha de apertura:</span>
                    <span id="det-apertura" class="num">—</span>
                </div>
                <div>
                    <span class="muted" style="display: block; font-size: 0.74rem;">Fecha de cierre:</span>
                    <span id="det-cierre" class="num">—</span>
                </div>
            </div>

            {{-- Tabla Comparativa Esperado vs Contado --}}
            <h4 class="font-serif" style="font-size: 1.05rem; margin-bottom: 0.6rem; color: var(--color-noche);">Desglose del Arqueo</h4>
            <div style="border: 1px solid var(--color-linea); border-radius: 6px; overflow: hidden; margin-bottom: 1.25rem;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Método</th>
                            <th class="text-right">Esperado</th>
                            <th class="text-right">Contado</th>
                            <th class="text-right">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Efectivo</strong></td>
                            <td class="text-right num" id="det-exp-cash">$ 0.00</td>
                            <td class="text-right num" id="det-cnt-cash">$ 0.00</td>
                            <td class="text-right num" id="det-diff-cash">$ 0.00</td>
                        </tr>
                        <tr>
                            <td><strong>Tarjeta</strong></td>
                            <td class="text-right num" id="det-exp-card">$ 0.00</td>
                            <td class="text-right num" id="det-cnt-card">$ 0.00</td>
                            <td class="text-right num" id="det-diff-card">$ 0.00</td>
                        </tr>
                        <tr>
                            <td><strong>Transferencia</strong></td>
                            <td class="text-right num" id="det-exp-trans">$ 0.00</td>
                            <td class="text-right num" id="det-cnt-trans">$ 0.00</td>
                            <td class="text-right num" id="det-diff-trans">$ 0.00</td>
                        </tr>
                        <tr style="background: #FAF8F5; font-weight: 600;">
                            <td>Diferencia Total</td>
                            <td colspan="2"></td>
                            <td class="text-right num" id="det-diff-total" style="font-size: 1rem;">$ 0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Fondo Inicial y Notas --}}
            <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.88rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; background: #FAF9F5; border-radius: 4px; border: 1px solid var(--color-linea);">
                    <span class="muted">Fondo inicial de efectivo declarado:</span>
                    <strong class="num" id="det-opening">$ 0.00</strong>
                </div>

                <div id="det-note-wrapper" style="padding: 0.75rem; background: #FFFDF9; border-radius: 4px; border: 1px solid var(--color-linea);">
                    <span class="muted" style="display: block; font-size: 0.75rem; margin-bottom: 2px;">Nota explicativa del cierre:</span>
                    <div id="det-note" style="color: var(--color-noche); font-style: italic;">Sin observaciones adicionales.</div>
                </div>

                <div id="det-supervisor-wrapper" style="display: none; padding: 0.75rem; background: #FFF4E5; border-radius: 4px; border: 1px solid #F59E0B;">
                    <span style="display: block; font-size: 0.75rem; color: #92400E; font-weight: 600;">Autorizado por Supervisor:</span>
                    <div id="det-supervisor" style="color: #78350F; font-weight: 600;">—</div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-close-modal="modal-shift-details">Cerrar</button>
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

    // --- Funciones para abrir y cerrar modales ---
    function openModal(id) {
        const m = el(id);
        if (m) {
            m.classList.add('is-active');
            m.style.display = 'flex';
            const input = m.querySelector('input:not([type="hidden"]), select, textarea');
            if (input) setTimeout(() => input.focus(), 60);
        }
    }

    function closeModal(id) {
        const m = el(id);
        if (m) {
            m.classList.remove('is-active');
            m.style.display = 'none';
        }
    }

    // Botones con data-close-modal
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-close-modal');
            closeModal(targetId);
        });
    });

    // Cerrar al hacer clic en el backdrop fuera del modal
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                closeModal(backdrop.id);
            }
        });
    });

    // Cerrar con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.is-active').forEach(m => closeModal(m.id));
        }
    });

    // --- Disparadores de Modales Superiores ---
    el('btn-open-movements')?.addEventListener('click', () => openModal('modal-movements'));
    el('btn-open-close')?.addEventListener('click', () => {
        openModal('modal-close-shift');
        calculateDifference();
    });
    el('btn-open-shift')?.addEventListener('click', () => openModal('modal-open-shift'));

    // --- Lógica de Cierre de Turno y Arqueo ---
    @if ($session)
    const expCash = Number(@json((float) ($expected['cash'] ?? 0)));
    const expCard = Number(@json((float) ($expected['card'] ?? 0)));
    const expTrans = Number(@json((float) ($expected['transfer'] ?? 0)));
    const canSelfAuthorize = @json(Auth::user()?->can('products.edit_price') ?? false);

    function calculateDifference() {
        const cntCash = Number(el('counted_cash')?.value) || 0;
        const cntCard = Number(el('counted_card')?.value) || 0;
        const cntTrans = Number(el('counted_transfer')?.value) || 0;

        const diffTotal = (cntCash - expCash) + (cntCard - expCard) + (cntTrans - expTrans);
        const statusBox = el('arqueo-status-box');
        const statusTitle = el('arqueo-status-title');
        const statusDesc = el('arqueo-status-desc');
        const statusDiff = el('arqueo-status-diff');
        const supSection = el('supervisor-section');
        const noteStar = el('note-required-star');

        const isExact = Math.abs(diffTotal) < 0.005;

        if (isExact) {
            statusBox.style.background = '#F3FAF4';
            statusBox.style.borderColor = '#A7D7B5';
            statusTitle.style.color = 'var(--color-exito)';
            statusTitle.textContent = '✓ Arqueo exacto ($ 0.00)';
            statusDesc.textContent = 'Los valores contados coinciden con lo esperado en el sistema.';
            statusDiff.style.color = 'var(--color-exito)';
            statusDiff.textContent = '$ 0.00';
            supSection.style.display = 'none';
            noteStar.style.display = 'none';
        } else {
            const isShortage = diffTotal < 0;
            const absDiff = Math.abs(diffTotal).toFixed(2);
            statusBox.style.background = isShortage ? '#FEF2F2' : '#FFFBEB';
            statusBox.style.borderColor = isShortage ? '#FCA5A5' : '#FDE68A';
            statusTitle.style.color = isShortage ? 'var(--color-error)' : '#D97706';
            statusTitle.textContent = isShortage ? '⚠ Faltante en arqueo' : '⚠ Sobrante en arqueo';
            statusDesc.textContent = 'Existe diferencia. Se requiere nota explicativa' + (canSelfAuthorize ? '.' : ' y PIN de supervisor.');
            statusDiff.style.color = isShortage ? 'var(--color-error)' : '#D97706';
            statusDiff.textContent = (isShortage ? '-$' : '+$') + absDiff;
            noteStar.style.display = 'inline';

            supSection.style.display = 'block';
            if (canSelfAuthorize) {
                el('sup-title').textContent = 'Autorización de Gerencia / Dueño';
                el('sup-desc').textContent = 'Tienes permisos de supervisor: puedes ingresar tu PIN de 4 dígitos o autorizar directamente con tu nota explicativa.';
            } else {
                el('sup-title').textContent = 'Se requiere autorización de supervisor';
                el('sup-desc').textContent = 'El arqueo presenta diferencia. Ingresa el PIN de 4 dígitos del Gerente o Administrador para autorizar el cierre.';
            }
        }
    }

    // Escuchar cambios en los inputs de conteo
    document.querySelectorAll('.arqueo-input').forEach(input => {
        input.addEventListener('input', calculateDifference);
        input.addEventListener('change', calculateDifference);
    });

    // Enviar formulario de cierre
    el('btn-submit-close-cash')?.addEventListener('click', async () => {
        const cntCash = Number(el('counted_cash')?.value) || 0;
        const cntCard = Number(el('counted_card')?.value) || 0;
        const cntTrans = Number(el('counted_transfer')?.value) || 0;
        const diffTotal = (cntCash - expCash) + (cntCard - expCard) + (cntTrans - expTrans);
        const isExact = Math.abs(diffTotal) < 0.005;
        const note = el('modal_close_note')?.value.trim();
        const pinInput = el('modal_supervisor_pin');
        const pinError = el('modal-pin-error');
        const submitBtn = el('btn-submit-close-cash');

        pinError.style.display = 'none';

        if (!isExact) {
            if (!note) {
                alert('Debes ingresar la nota explicativa obligatoria de la diferencia.');
                el('modal_close_note')?.focus();
                return;
            }

            const pin = pinInput?.value.trim() || '';

            if (!canSelfAuthorize) {
                if (!pin || pin.length !== 4) {
                    pinError.textContent = 'El PIN de supervisor debe tener exactamente 4 dígitos.';
                    pinError.style.display = 'block';
                    pinInput?.focus();
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Validando PIN...';

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
                        pinError.textContent = data.message || 'PIN de supervisor incorrecto.';
                        pinError.style.display = 'block';
                        pinInput?.select();
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Confirmar y Cerrar Turno';
                        return;
                    }

                    el('close_supervisor_pin').value = pin;
                } catch (err) {
                    pinError.textContent = 'Error de conexión al verificar el PIN. Intenta nuevamente.';
                    pinError.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Confirmar y Cerrar Turno';
                    return;
                }
            } else {
                if (pin && pin.length === 4) {
                    el('close_supervisor_pin').value = pin;
                }
            }
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Cerrando turno...';
        el('form-close-cash').submit();
    });
    @endif

    // --- Modal de Detalles de Turno Cerrado ---
    document.querySelectorAll('.btn-view-details').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            el('det-cajero').textContent = d.cajero;
            el('det-closed-by').textContent = d.closedBy;
            el('det-apertura').textContent = d.apertura;
            el('det-cierre').textContent = d.cierre;
            el('det-subtitle').textContent = 'Turno de ' + d.cajero + ' · Cerrado el ' + d.cierre;

            el('det-opening').textContent = '$ ' + d.opening;
            el('det-exp-cash').textContent = '$ ' + d.expCash;
            el('det-cnt-cash').textContent = '$ ' + d.cntCash;
            const diffCash = (Number(d.cntCash.replace(/,/g, '')) - Number(d.expCash.replace(/,/g, ''))).toFixed(2);
            el('det-diff-cash').textContent = (diffCash >= 0 ? '+' : '') + '$ ' + diffCash;

            el('det-exp-card').textContent = '$ ' + d.expCard;
            el('det-cnt-card').textContent = '$ ' + d.cntCard;
            const diffCard = (Number(d.cntCard.replace(/,/g, '')) - Number(d.expCard.replace(/,/g, ''))).toFixed(2);
            el('det-diff-card').textContent = (diffCard >= 0 ? '+' : '') + '$ ' + diffCard;

            el('det-exp-trans').textContent = '$ ' + d.expTrans;
            el('det-cnt-trans').textContent = '$ ' + d.cntTrans;
            const diffTrans = (Number(d.cntTrans.replace(/,/g, '')) - Number(d.expTrans.replace(/,/g, ''))).toFixed(2);
            el('det-diff-trans').textContent = (diffTrans >= 0 ? '+' : '') + '$ ' + diffTrans;

            const totalDiff = Number(d.diff.replace(/,/g, ''));
            const totalDiffEl = el('det-diff-total');
            totalDiffEl.textContent = (totalDiff >= 0 ? (totalDiff === 0 ? '' : '+') : '') + '$ ' + d.diff;
            totalDiffEl.style.color = totalDiff === 0 ? 'var(--color-exito)' : (totalDiff < 0 ? 'var(--color-error)' : 'var(--color-aviso)');

            if (d.note && d.note.trim() !== '') {
                el('det-note').textContent = '"' + d.note + '"';
                el('det-note-wrapper').style.display = 'block';
            } else {
                el('det-note').textContent = 'Sin observaciones adicionales.';
            }

            if (d.supervisor && d.supervisor.trim() !== '') {
                el('det-supervisor').textContent = d.supervisor;
                el('det-supervisor-wrapper').style.display = 'block';
            } else {
                el('det-supervisor-wrapper').style.display = 'none';
            }

            openModal('modal-shift-details');
        });
    });

    // Reabrir modales automáticamente en caso de error de validación tras envío
    @if ($errors->has('type') || $errors->has('amount') || $errors->has('reason'))
        openModal('modal-movements');
    @elseif ($errors->has('counted_cash') || $errors->has('counted_card') || $errors->has('counted_transfer') || $errors->has('close_note') || $errors->has('supervisor_pin'))
        openModal('modal-close-shift');
    @elseif (!$session && $errors->has('opening_amount'))
        openModal('modal-open-shift');
    @endif
})();
</script>
@endpush
