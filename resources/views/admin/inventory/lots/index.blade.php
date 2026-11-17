@extends('layouts.admin')

@section('title', 'Materia prima')

@section('body_class', 'full-viewport')

@php
    $filterKeys = ['search', 'metal_id', 'status'];
    $activeFilterCount = collect($filterKeys)->filter(fn ($key) => request()->filled($key))->count();
    $hasFilters = $activeFilterCount > 0;
@endphp

@section('content')
<style>
    .lots-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .lots-stat {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.65rem 0.85rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: var(--color-blanco);
    }

    .lots-stat-body {
        min-width: 0;
    }

    .lots-stat-label {
        display: block;
        color: var(--color-piedra);
        font-size: 0.63rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .lots-stat-value {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.3rem;
        font-weight: 600;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .lots-stat-hint {
        display: block;
        margin-top: 0.08rem;
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.68rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lots-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        margin-left: auto;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
        order: 2;
    }

    .lots-stat--open .lots-stat-icon {
        background: #EDF7EE;
        color: var(--color-exito);
    }

    .lots-stat--melting .lots-stat-icon {
        background: #FBF3E2;
        color: #9A6B1F;
    }

    .lots-filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.45fr) repeat(2, minmax(140px, 1fr)) auto;
        gap: 0.6rem;
        align-items: end;
    }

    .lots-field {
        min-width: 0;
    }

    .lots-field label {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
        font-weight: 600;
    }

    .lots-field .form-control,
    .lots-field .form-select {
        width: 100%;
        height: 36px;
        font-size: 0.83rem;
    }

    .lots-filter-actions {
        display: flex;
        gap: 0.4rem;
    }

    .lots-filter-actions .btn {
        height: 36px;
        padding: 0 0.85rem;
        font-size: 0.8rem;
        white-space: nowrap;
    }

    .lots-table-head {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 1rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .lots-table-head h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.05rem;
    }

    .lots-table-head p {
        margin: 0.12rem 0 0;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .lots-filter-chip {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        gap: 0.35rem;
        padding: 0.22rem 0.55rem;
        border: 1px solid #E4D8BC;
        border-radius: 999px;
        background: #FBF6EA;
        color: #8A6516;
        font-size: 0.68rem;
        font-weight: 600;
    }

    .lots-table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .lots-table-wrap::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .lots-table-wrap::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #D9D2C3;
    }

    .lots-table-wrap::-webkit-scrollbar-track {
        background: transparent;
    }

    .lots-table {
        width: 100%;
        min-width: 880px;
        border-collapse: collapse;
        font-size: 0.84rem;
    }

    .lots-table thead tr {
        border-bottom: 1px solid var(--color-linea);
        background: var(--color-crema);
    }

    .lots-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 0.6rem 0.85rem;
        background: var(--color-crema);
        box-shadow: inset 0 -1px 0 var(--color-linea);
        color: var(--color-noche);
        font-size: 0.64rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .lots-table td {
        padding: 0.62rem 0.85rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .lots-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .lots-table tbody tr:hover {
        background: #FBF9F5;
    }

    .lots-table .text-right {
        text-align: right;
    }

    .lots-code {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .lots-code-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border: 1px solid rgba(184, 154, 94, 0.3);
        border-radius: 4px;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .lots-code-main {
        min-width: 0;
    }

    .lots-code-value {
        display: block;
        color: var(--color-noche);
        font-weight: 600;
        letter-spacing: 0.01em;
        white-space: nowrap;
    }

    .lots-code-date {
        display: block;
        margin-top: 0.08rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
        white-space: nowrap;
    }

    .lots-metal {
        color: var(--color-noche);
        font-weight: 500;
    }

    .lots-purity {
        display: inline-block;
        margin-left: 0.2rem;
        color: var(--color-piedra);
        font-size: 0.72rem;
    }

    .lots-weight {
        color: var(--color-noche);
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .lots-weight-hint {
        display: block;
        margin-top: 0.08rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
        font-weight: 400;
        white-space: nowrap;
    }

    .lots-initial {
        color: var(--color-piedra);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .lots-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.3rem;
    }

    .lots-icon-btn {
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
        transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease;
    }

    .lots-icon-btn:hover {
        border-color: var(--color-dorado);
        background: #FBF6EA;
        color: var(--color-dorado);
    }

    .lots-icon-btn--dark {
        border-color: var(--color-noche);
        background: var(--color-noche);
        color: var(--color-hueso);
    }

    .lots-icon-btn--dark:hover {
        border-color: var(--color-noche);
        background: #232A38;
        color: var(--color-hueso);
    }

    .lots-empty {
        padding: 2.75rem 1rem;
        text-align: center;
    }

    .lots-empty-icon {
        display: grid;
        place-items: center;
        width: 56px;
        height: 56px;
        margin: 0 auto 0.8rem;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .lots-empty strong {
        display: block;
        color: var(--color-noche);
        font-size: 0.95rem;
    }

    .lots-empty p {
        margin: 0.25rem 0 0.8rem;
        color: var(--color-piedra);
        font-size: 0.82rem;
    }

    @media (max-width: 1200px) {
        .lots-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lots-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lots-field--search {
            grid-column: span 2;
        }

        .lots-filter-actions {
            justify-content: flex-end;
        }
    }

    @media (max-width: 900px) {
        .lots-filter-form {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .lots-table {
            min-width: 0;
        }

        .lots-table thead {
            display: none;
        }

        .lots-table,
        .lots-table tbody,
        .lots-table tr,
        .lots-table td {
            display: block;
            width: 100%;
        }

        .lots-table tbody tr {
            margin: 0 0 0.55rem;
            padding: 0.75rem 0.85rem;
            border: 1px solid var(--color-linea);
            border-radius: 6px;
            background: var(--color-blanco);
        }

        .lots-table tbody tr:last-child {
            margin-bottom: 0;
        }

        .lots-table td {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.24rem 0;
            border: 0;
            text-align: right;
        }

        .lots-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: var(--color-piedra);
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .lots-table td.lots-cell-code,
        .lots-table td.lots-cell-actions {
            display: block;
            padding: 0;
            text-align: left;
        }

        .lots-table td.lots-cell-code {
            margin-bottom: 0.45rem;
        }

        .lots-table td.lots-cell-code::before,
        .lots-table td.lots-cell-actions::before {
            display: none;
        }

        .lots-table td.lots-cell-actions {
            margin-top: 0.55rem;
            padding-top: 0.55rem;
            border-top: 1px solid var(--color-linea);
        }

        .lots-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .lots-stats {
            grid-template-columns: minmax(0, 1fr);
        }

        .lots-filter-form {
            grid-template-columns: minmax(0, 1fr);
        }

        .lots-field--search {
            grid-column: auto;
        }

        .lots-filter-actions .btn {
            flex: 1;
        }

        .lots-table-head {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.35rem;
        }
    }
</style>

<div class="paginated-page">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Materia prima</h1>
            <p class="page-subtitle">Lotes de metal controlados por peso y listos para taller o fundición.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Inventario</a>
            @can('inventory.adjust')
                <a href="{{ route('admin.inventory.lots.create') }}" class="btn btn-gold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Nuevo Lote</span>
                </a>
            @endcan
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="lots-stats">
        <div class="lots-stat">
            <div class="lots-stat-body">
                <span class="lots-stat-label">Lotes</span>
                <strong class="lots-stat-value">{{ number_format($summary['count']) }}</strong>
                <span class="lots-stat-hint">En el filtro actual</span>
            </div>
            <span class="lots-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 3 9 5-9 5-9-5 9-5Z"></path>
                    <path d="m3 12 9 5 9-5"></path>
                    <path d="m3 16 9 5 9-5"></path>
                </svg>
            </span>
        </div>

        <div class="lots-stat lots-stat--open">
            <div class="lots-stat-body">
                <span class="lots-stat-label">Abiertos</span>
                <strong class="lots-stat-value">{{ number_format($summary['open_count']) }}</strong>
                <span class="lots-stat-hint">Disponibles para ajustar</span>
            </div>
            <span class="lots-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7h16v12H4z"></path>
                    <path d="M8 7V5h8v2"></path>
                    <path d="M8 12h8"></path>
                </svg>
            </span>
        </div>

        <div class="lots-stat">
            <div class="lots-stat-body">
                <span class="lots-stat-label">Peso disponible</span>
                <strong class="lots-stat-value">{{ number_format((float) $summary['open_weight'], 3) }} g</strong>
                <span class="lots-stat-hint">Suma de lotes abiertos</span>
            </div>
            <span class="lots-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v3"></path>
                    <path d="M6.5 6h11l2.5 4a7 7 0 0 1-11 0l2.5-4Z"></path>
                    <path d="M5 20h14"></path>
                    <path d="M12 13v7"></path>
                </svg>
            </span>
        </div>

        <div class="lots-stat lots-stat--melting">
            <div class="lots-stat-body">
                <span class="lots-stat-label">En fundición</span>
                <strong class="lots-stat-value">{{ number_format($summary['melting_count']) }}</strong>
                <span class="lots-stat-hint">Lotes ya enviados</span>
            </div>
            <span class="lots-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3c2.5 3.5 1 5.5 0 7-1-1.5-2.5-3.5 0-7Z"></path>
                    <path d="M7 14a5 5 0 0 0 10 0c0-2-1.5-3.4-2.5-4.5C13.5 11 12 12 12 12s-1.5-1-2.5-2.5C8.5 10.6 7 12 7 14Z"></path>
                    <path d="M5 21h14"></path>
                </svg>
            </span>
        </div>
    </div>

    <div class="card paginated-filter-card">
        <form action="{{ route('admin.inventory.lots.index') }}" method="GET" class="lots-filter-form">
            <div class="lots-field lots-field--search">
                <label for="lots-search">Buscar por código</label>
                <input id="lots-search" type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ej. LOT-001-00001">
            </div>

            <div class="lots-field">
                <label for="lots-metal">Metal</label>
                <select id="lots-metal" name="metal_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($metals as $metal)
                        <option value="{{ $metal->id }}" {{ (string) request('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lots-field">
                <label for="lots-status">Estado</label>
                <select id="lots-status" name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lots-filter-actions">
                <button type="submit" class="btn btn-dark">Filtrar</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card paginated-table-card">
        <div class="lots-table-head">
            <div>
                <h2>Lotes registrados</h2>
                <p>{{ number_format($lots->total()) }} {{ $lots->total() === 1 ? 'resultado' : 'resultados' }} · Ordenados por registro más reciente</p>
            </div>
            @if ($hasFilters)
                <span class="lots-filter-chip">{{ $activeFilterCount }} {{ $activeFilterCount === 1 ? 'filtro activo' : 'filtros activos' }}</span>
            @endif
        </div>

        <div class="lots-table-wrap">
            <table class="lots-table">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Metal / Pureza</th>
                        <th class="text-right">Peso inicial</th>
                        <th class="text-right">Peso actual</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lots as $lot)
                        @php
                            $badge = match ($lot->status) {
                                \App\Enums\MaterialLotStatus::Open => 'badge-success',
                                \App\Enums\MaterialLotStatus::SentToMelting => 'badge-gold',
                                default => 'badge-muted',
                            };
                        @endphp
                        <tr>
                            <td class="lots-cell-code">
                                <div class="lots-code">
                                    <span class="lots-code-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m12 3 9 5-9 5-9-5 9-5Z"></path>
                                            <path d="m3 12 9 5 9-5"></path>
                                            <path d="m3 16 9 5 9-5"></path>
                                        </svg>
                                    </span>
                                    <div class="lots-code-main">
                                        <span class="lots-code-value">{{ $lot->code }}</span>
                                        <span class="lots-code-date">{{ $lot->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Metal / Pureza">
                                <span class="lots-metal">{{ $lot->metal?->name ?? 'Sin metal' }}</span>
                                @if ($lot->purity)
                                    <span class="lots-purity">{{ $lot->purity->code }}</span>
                                @endif
                            </td>
                            <td data-label="Peso inicial" class="text-right lots-initial">{{ number_format((float) $lot->initial_weight_g, 3) }} g</td>
                            <td data-label="Peso actual" class="text-right">
                                <span class="lots-weight">{{ number_format((float) $lot->current_weight_g, 3) }} g</span>
                                <span class="lots-weight-hint">de {{ number_format((float) $lot->initial_weight_g, 3) }} g iniciales</span>
                            </td>
                            <td data-label="Ubicación" class="muted">{{ $lot->location?->name ?? 'Sin ubicación' }}</td>
                            <td data-label="Estado">
                                <span class="badge {{ $badge }}">{{ $lot->status?->label() ?? 'Sin estado' }}</span>
                            </td>
                            <td class="text-right lots-cell-actions">
                                <div class="lots-actions">
                                    <a href="{{ route('admin.inventory.lots.show', $lot) }}" class="lots-icon-btn lots-icon-btn--dark" title="Ver lote" aria-label="Ver lote {{ $lot->code }}">
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
                                <div class="lots-empty">
                                    <span class="lots-empty-icon" aria-hidden="true">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m12 3 9 5-9 5-9-5 9-5Z"></path>
                                            <path d="m3 12 9 5 9-5"></path>
                                            <path d="m3 16 9 5 9-5"></path>
                                        </svg>
                                    </span>
                                    <strong>No hay lotes registrados</strong>
                                    <p>{{ $hasFilters ? 'Prueba con otros criterios o limpia los filtros.' : 'Registra el primer lote de materia prima para empezar.' }}</p>
                                    @if ($hasFilters)
                                        <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Limpiar filtros</a>
                                    @else
                                        @can('inventory.adjust')
                                            <a href="{{ route('admin.inventory.lots.create') }}" class="btn btn-gold">Nuevo Lote</a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($lots->hasPages())
        <div class="paginated-pagination" style="padding: 0.65rem 1.1rem; border: 1px solid var(--color-linea); border-radius: 5px; background: var(--color-blanco);">
            {{ $lots->links('vendor.pagination.admin') }}
        </div>
    @endif
</div>
@endsection
