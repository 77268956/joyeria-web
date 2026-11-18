@extends('layouts.admin')

@section('title', 'Conteos de Inventario')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .sc-stats {
        display: grid;
        flex-shrink: 0;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .sc-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        min-width: 0;
        padding: 0.75rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 5px;
    }

    .sc-stat-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .sc-stat-label {
        display: block;
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--color-piedra);
    }

    .sc-stat-value {
        display: block;
        margin-top: 0.15rem;
        font-size: 1.02rem;
        font-weight: 600;
        color: var(--color-noche);
        font-variant-numeric: tabular-nums;
    }

    .sc-stat-hint {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.74rem;
        color: var(--color-piedra);
    }

    .sc-table-head {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 1rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .sc-table-head h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .sc-table-head p {
        margin: 0.1rem 0 0;
        font-size: 0.72rem;
        color: var(--color-piedra);
    }

    .sc-table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .sc-table-wrap::-webkit-scrollbar { width: 7px; height: 7px; }
    .sc-table-wrap::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .sc-table-wrap::-webkit-scrollbar-track { background: transparent; }

    .sc-table {
        width: 100%;
        min-width: 700px;
        border-collapse: collapse;
        font-size: 0.84rem;
    }

    .sc-table thead tr {
        background-color: var(--color-tabla-header);
        border-bottom: 1px solid var(--color-linea);
    }

    .sc-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 0.6rem 0.85rem;
        background-color: var(--color-tabla-header);
        box-shadow: inset 0 -1px 0 var(--color-linea);
        color: var(--color-noche);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .sc-table th.text-right { text-align: right; }

    .sc-table td {
        padding: 0.6rem 0.85rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .sc-table tbody tr:last-child td { border-bottom: 0; }
    .sc-table tbody tr:hover { background-color: #FBF9F5; }
    .sc-table .text-right { text-align: right; }

    .sc-folio {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 600;
        color: var(--color-noche);
        text-decoration: none;
    }

    .sc-folio:hover { color: var(--color-dorado); }

    .sc-folio-icon {
        display: grid;
        place-items: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
        flex-shrink: 0;
    }

    .sc-date {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .sc-user {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .sc-user-avatar {
        display: grid;
        place-items: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--color-noche);
        color: var(--color-hueso);
        font-size: 0.58rem;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .sc-pieces {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .sc-progress {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sc-progress-bar {
        flex: 1;
        height: 5px;
        background: var(--color-linea);
        border-radius: 999px;
        overflow: hidden;
        min-width: 50px;
    }

    .sc-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: var(--color-dorado);
        transition: width 0.3s ease;
    }

    .sc-progress-fill.is-complete { background: var(--color-exito); }

    .sc-progress-text {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--color-piedra);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .sc-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.3rem;
    }

    .sc-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: transparent;
        color: var(--color-piedra);
        text-decoration: none;
        transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
    }

    .sc-icon-btn:hover {
        border-color: var(--color-dorado);
        background: #FBF6EA;
        color: var(--color-dorado);
    }

    .sc-empty {
        padding: 2.5rem 1rem;
        text-align: center;
    }

    .sc-empty-icon {
        display: grid;
        place-items: center;
        width: 56px;
        height: 56px;
        margin: 0 auto 0.8rem;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .sc-empty strong {
        display: block;
        font-size: 0.95rem;
        color: var(--color-noche);
    }

    .sc-empty p {
        margin: 0.25rem 0 0.8rem;
        font-size: 0.82rem;
        color: var(--color-piedra);
    }

    .sc-new-btn svg { margin-right: 0.35rem; }

    @media (max-width: 900px) {
        .sc-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        .sc-table { min-width: 0; }
        .sc-table thead { display: none; }
        .sc-table, .sc-table tbody, .sc-table tr, .sc-table td { display: block; width: 100%; }
        .sc-table tbody tr {
            margin: 0 0 0.55rem;
            padding: 0.75rem 0.85rem;
            border: 1px solid var(--color-linea);
            border-radius: 6px;
            background: var(--color-blanco);
        }
        .sc-table tbody tr:last-child { margin-bottom: 0; }
        .sc-table td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.24rem 0;
            border: 0;
            text-align: right;
        }
        .sc-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: var(--color-piedra);
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .sc-table td.sc-cell-folio,
        .sc-table td.sc-cell-actions { display: block; padding: 0; text-align: left; }
        .sc-table td.sc-cell-folio { margin-bottom: 0.45rem; }
        .sc-table td.sc-cell-folio::before,
        .sc-table td.sc-cell-actions::before { display: none; }
        .sc-table td.sc-cell-actions {
            margin-top: 0.55rem;
            padding-top: 0.55rem;
            border-top: 1px solid var(--color-linea);
        }
        .sc-actions { justify-content: flex-start; }
        .sc-table .text-right { text-align: right; }
    }

    @media (max-width: 600px) {
        .sc-stats { grid-template-columns: minmax(0, 1fr); }
    }

    .sc-confirm-hero {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .sc-confirm-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .sc-confirm-hero h3 {
        margin: 0;
        color: var(--color-noche);
        font-size: 1rem;
        font-weight: 600;
    }

    .sc-confirm-hero p {
        margin: 0.25rem 0 0;
        color: var(--color-piedra);
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .sc-confirm-list {
        display: grid;
        gap: 0.55rem;
        margin: 1rem 0 0;
        padding: 0.85rem 1rem;
        list-style: none;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-crema);
    }

    .sc-confirm-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        color: var(--color-noche);
        font-size: 0.84rem;
        line-height: 1.4;
    }

    .sc-confirm-list svg {
        flex-shrink: 0;
        margin-top: 0.15rem;
        color: var(--color-dorado);
    }
</style>

@php
    $totalCounts = $openCount + $finishedCount + $appliedCount;
@endphp

<div class="paginated-page">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Conteos de Inventario</h1>
            <p class="page-subtitle">Auditoría física que concilia las existencias del sistema con las piezas presentes en la tienda.</p>
        </div>
        <div class="page-actions">
            @can('inventory.adjust')
                <form action="{{ route('admin.stock-counts.store') }}" method="POST" id="form-start-stock-count" style="display: inline;" data-supervisor-action="el conteo físico">
                    @csrf
                    <button type="button" class="btn btn-gold sc-new-btn" data-stock-count-open>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Nuevo conteo</span>
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="sc-stats">
        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Total de conteos</span>
                <strong class="sc-stat-value">{{ number_format($totalCounts) }}</strong>
                <span class="sc-stat-hint">Auditorías registradas</span>
            </div>
            <span class="sc-stat-icon" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                    <rect x="9" y="3" width="6" height="4" rx="1" />
                </svg>
            </span>
        </div>

        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Abiertos</span>
                <strong class="sc-stat-value" style="{{ $openCount > 0 ? 'color: var(--color-error);' : '' }}">{{ $openCount }}</strong>
                <span class="sc-stat-hint">En progreso</span>
            </div>
            <span class="sc-stat-icon" aria-hidden="true" style="background: #FDEDED; color: var(--color-error);">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
            </span>
        </div>

        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Finalizados</span>
                <strong class="sc-stat-value">{{ $finishedCount }}</strong>
                <span class="sc-stat-hint">Pendientes de aplicar</span>
            </div>
            <span class="sc-stat-icon" aria-hidden="true" style="background: #FFF8E1; color: var(--color-aviso);">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 7 9 18l-5-5" />
                </svg>
            </span>
        </div>

        <div class="sc-stat">
            <div style="min-width: 0; flex: 1;">
                <span class="sc-stat-label">Aplicados</span>
                <strong class="sc-stat-value">{{ $appliedCount }}</strong>
                <span class="sc-stat-hint">Inventario ajustado</span>
            </div>
            <span class="sc-stat-icon" aria-hidden="true" style="background: #EDF7EE; color: var(--color-exito);">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <path d="M22 4 12 14.01l-3-3" />
                </svg>
            </span>
        </div>
    </div>

    <div class="card paginated-table-card">
        <div class="sc-table-head">
            <div>
                <h2 class="font-serif">Historial de conteos</h2>
                <p>{{ number_format($totalCounts) }} {{ $totalCounts === 1 ? 'conteo' : 'conteos' }} · Ordenados por más reciente</p>
            </div>
        </div>

        <div class="sc-table-wrap">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha de inicio</th>
                        <th>Iniciado por</th>
                        <th class="text-right">Piezas</th>
                        <th>Estado</th>
                        <th>Notas</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($counts as $count)
                        @php
                            $badge = match ($count->status) {
                                \App\Enums\StockCountStatus::Applied => 'badge-success',
                                \App\Enums\StockCountStatus::Finished => 'badge-gold',
                                default => 'badge-danger',
                            };
                            $initials = collect(explode(' ', $count->startedBy?->name ?? '??'))
                                ->map(fn ($word) => mb_substr($word, 0, 1))
                                ->take(2)
                                ->implode('');
                        @endphp
                        <tr>
                            <td class="sc-cell-folio">
                                <a href="{{ route('admin.stock-counts.show', $count) }}" class="sc-folio">
                                    <span class="sc-folio-icon" aria-hidden="true">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                                            <rect x="9" y="3" width="6" height="4" rx="1" />
                                        </svg>
                                    </span>
                                    #{{ $count->id }}
                                </a>
                            </td>
                            <td data-label="Inicio" class="sc-date">{{ $count->started_at?->format('d/m/Y H:i') }}</td>
                            <td data-label="Iniciado por">
                                <div class="sc-user">
                                    <span class="sc-user-avatar">{{ $initials }}</span>
                                    {{ $count->startedBy?->name ?? '—' }}
                                </div>
                            </td>
                            <td data-label="Piezas" class="text-right sc-pieces">{{ number_format($count->items_count) }}</td>
                            <td data-label="Estado">
                                <span class="badge {{ $badge }}">{{ $count->status->label() }}</span>
                            </td>
                            <td data-label="Notas" class="muted" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $count->notes ?? '—' }}</td>
                            <td class="text-right sc-cell-actions">
                                <div class="sc-actions">
                                    <a href="{{ route('admin.stock-counts.show', $count) }}" class="sc-icon-btn" title="Ver conteo" aria-label="Ver conteo #{{ $count->id }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="sc-empty">
                                    <span class="sc-empty-icon" aria-hidden="true">
                                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                                            <rect x="9" y="3" width="6" height="4" rx="1" />
                                        </svg>
                                    </span>
                                    <strong>No hay conteos registrados</strong>
                                    <p>Inicia un nuevo conteo físico para conciliar las piezas del sistema con las que están en la tienda.</p>
                                    @can('inventory.adjust')
                                        <button type="button" class="btn btn-gold" data-stock-count-open>Nuevo conteo</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($counts->hasPages())
        <div class="paginated-pagination" style="padding: 0.65rem 1.1rem; border: 1px solid var(--color-linea); border-radius: 5px; background: var(--color-blanco);">
            {{ $counts->links('vendor.pagination.admin') }}
        </div>
    @endif
</div>

@can('inventory.adjust')
<div class="modal-backdrop" id="stock-count-confirm-modal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="stock-count-confirm-title" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <h2 id="stock-count-confirm-title" style="font-size: 1.05rem; margin: 0;">Iniciar conteo físico</h2>
                <p class="muted" style="font-size: 0.78rem; margin: 0.2rem 0 0;">Inventario actual</p>
            </div>
            <button type="button" class="modal-close" data-stock-count-cancel aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <div class="sc-confirm-hero">
                <span class="sc-confirm-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                        <rect x="9" y="3" width="6" height="4" rx="1" />
                        <path d="m9 14 2 2 4-4" />
                    </svg>
                </span>
                <div>
                    <h3>¿Todo listo para comenzar?</h3>
                    <p>Se creará una lista con las piezas disponibles y apartadas de esta sucursal para que registres lo que encuentres en tienda.</p>
                </div>
            </div>

            <ul class="sc-confirm-list">
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span>El inventario no se modifica hasta que finalices y apliques el conteo.</span>
                </li>
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span>Podrás marcar piezas encontradas, faltantes y agregar piezas que no aparezcan en la lista.</span>
                </li>
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span>La apertura del conteo quedará registrada en la bitácora de auditoría.</span>
                </li>
            </ul>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-stock-count-cancel>Cancelar</button>
            <button type="button" class="btn btn-gold" data-stock-count-confirm>Iniciar conteo</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('stock-count-confirm-modal');
    const form = document.getElementById('form-start-stock-count');

    if (!modal || !form) {
        return;
    }

    const openButtons = document.querySelectorAll('[data-stock-count-open]');
    const cancelButtons = modal.querySelectorAll('[data-stock-count-cancel]');
    const confirmButton = modal.querySelector('[data-stock-count-confirm]');

    let submitting = false;
    let closeTimer = null;

    function openModal() {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        submitting = false;
        modal.style.display = 'flex';
        modal.classList.remove('is-closing');
        modal.classList.add('is-active');

        window.setTimeout(() => confirmButton?.focus(), 80);
    }

    function closeModal() {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
        }

        modal.classList.add('is-closing');
        modal.classList.remove('is-active');

        closeTimer = window.setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('is-closing');
            closeTimer = null;
        }, 280);
    }

    openButtons.forEach((button) => {
        button.addEventListener('click', openModal);
    });

    cancelButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-active')) {
            closeModal();
        }
    });

    confirmButton?.addEventListener('click', () => {
        if (submitting) {
            return;
        }

        submitting = true;
        closeModal();

        window.setTimeout(() => {
            form.requestSubmit();
        }, 200);
    });
});
</script>
@endcan
@endsection
