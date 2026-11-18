@extends('layouts.admin')

@section('title', 'Inventario')

@section('body_class', 'full-viewport')

@php
    $filterKeys = ['search', 'metal_id', 'category_id', 'status', 'origin'];
    $activeFilterCount = collect($filterKeys)->filter(fn ($key) => request()->filled($key))->count();
    $hasFilters = $activeFilterCount > 0;
@endphp

@section('content')
<style>
    .inv-filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.45fr) repeat(4, minmax(115px, 1fr)) auto;
        gap: 0.6rem;
        align-items: end;
    }

    .inv-field {
        min-width: 0;
    }

    .inv-field label {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--color-piedra);
    }

    .inv-field .form-control,
    .inv-field .form-select {
        width: 100%;
        height: 36px;
        font-size: 0.83rem;
    }

    .inv-filter-actions {
        display: flex;
        gap: 0.4rem;
    }

    .inv-filter-actions .btn {
        height: 36px;
        padding: 0 0.85rem;
        font-size: 0.8rem;
        white-space: nowrap;
    }

    .inv-table-head {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 1rem;
        border-bottom: 1px solid var(--color-linea);
    }

    .inv-table-head h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .inv-table-head p {
        margin: 0.1rem 0 0;
        font-size: 0.72rem;
        color: var(--color-piedra);
    }

    .inv-filter-chip {
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

    .inv-table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .inv-table-wrap::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .inv-table-wrap::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #D9D2C3;
    }

    .inv-table-wrap::-webkit-scrollbar-track {
        background: transparent;
    }

    .inv-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        font-size: 0.84rem;
    }

    .inv-table thead tr {
        background-color: var(--color-tabla-header);
        border-bottom: 1px solid var(--color-linea);
    }

    .inv-table th {
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

    .inv-table th.text-right {
        text-align: right;
    }

    .inv-table td {
        padding: 0.6rem 0.85rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .inv-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .inv-table tbody tr:hover {
        background-color: #FBF9F5;
    }

    .inv-table .text-right {
        text-align: right;
    }

    .inv-product {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 0;
    }

    .inv-photo,
    .inv-photo--empty {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
    }

    .inv-photo {
        object-fit: cover;
        background: var(--color-crema);
    }

    .inv-photo--empty {
        display: grid;
        place-items: center;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .inv-product-main {
        min-width: 0;
    }

    .inv-product-name {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        min-width: 0;
        font-weight: 600;
        color: var(--color-noche);
    }

    .inv-product-name span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .inv-product-meta {
        margin-top: 0.12rem;
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.72rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .inv-origin {
        flex-shrink: 0;
        padding: 0.1rem 0.35rem;
        border-radius: 999px;
        background: rgba(184, 154, 94, 0.16);
        color: #8A6516;
        font-size: 0.6rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .inv-price {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .inv-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.3rem;
    }

    .inv-icon-btn {
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

    .inv-icon-btn:hover {
        border-color: var(--color-dorado);
        background: #FBF6EA;
        color: var(--color-dorado);
    }

    .inv-icon-btn--dark {
        border-color: var(--color-noche);
        background: var(--color-noche);
        color: var(--color-hueso);
    }

    .inv-icon-btn--dark:hover {
        border-color: var(--color-noche);
        background: #232A38;
        color: var(--color-hueso);
    }

    .inv-empty {
        padding: 2.5rem 1rem;
        text-align: center;
    }

    .inv-empty-icon {
        display: grid;
        place-items: center;
        width: 56px;
        height: 56px;
        margin: 0 auto 0.8rem;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .inv-empty strong {
        display: block;
        font-size: 0.95rem;
        color: var(--color-noche);
    }

    .inv-empty p {
        margin: 0.25rem 0 0.8rem;
        font-size: 0.82rem;
        color: var(--color-piedra);
    }

    @media (max-width: 1250px) {
        .inv-filter-form {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .inv-field--search {
            grid-column: span 2;
        }

        .inv-filter-actions {
            justify-content: flex-end;
        }
    }

    @media (max-width: 900px) {
        .inv-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .inv-field--search {
            grid-column: span 2;
        }

        .inv-table {
            min-width: 0;
        }

        .inv-table thead {
            display: none;
        }

        .inv-table,
        .inv-table tbody,
        .inv-table tr,
        .inv-table td {
            display: block;
            width: 100%;
        }

        .inv-table tbody tr {
            margin: 0 0 0.55rem;
            padding: 0.75rem 0.85rem;
            border: 1px solid var(--color-linea);
            border-radius: 6px;
            background: var(--color-blanco);
        }

        .inv-table tbody tr:last-child {
            margin-bottom: 0;
        }

        .inv-table td {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.24rem 0;
            border: 0;
            text-align: right;
        }

        .inv-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: var(--color-piedra);
            font-size: 0.64rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .inv-table td.inv-cell-product,
        .inv-table td.inv-cell-actions {
            display: block;
            padding: 0;
            text-align: left;
        }

        .inv-table td.inv-cell-product {
            margin-bottom: 0.45rem;
        }

        .inv-table td.inv-cell-product::before,
        .inv-table td.inv-cell-actions::before {
            display: none;
        }

        .inv-table td.inv-cell-actions {
            margin-top: 0.55rem;
            padding-top: 0.55rem;
            border-top: 1px solid var(--color-linea);
        }

        .inv-actions {
            justify-content: flex-start;
        }

        .inv-table .text-right {
            text-align: right;
        }
    }

    @media (max-width: 600px) {
        .inv-filter-form {
            grid-template-columns: minmax(0, 1fr);
        }

        .inv-field--search {
            grid-column: auto;
        }

        .inv-filter-actions {
            justify-content: stretch;
        }

        .inv-filter-actions .btn {
            flex: 1;
        }

        .inv-table-head {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.35rem;
        }
    }
</style>

<div class="paginated-page">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Inventario</h1>
            <p class="page-subtitle">Piezas disponibles, apartadas, vendidas o en fundición, con existencias controladas por movimientos.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.inventory.export', request()->query()) }}" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Exportar Excel</span>
            </a>
            <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Materia Prima</a>
            @can('inventory.adjust')
                <a href="{{ route('admin.inventory.create') }}" class="btn btn-gold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Nueva Pieza</span>
                </a>
            @endcan
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="card paginated-filter-card">
        <form action="{{ route('admin.inventory.index') }}" method="GET" class="inv-filter-form">
            <div class="inv-field inv-field--search">
                <label for="inventory-search">Buscar</label>
                <input id="inventory-search" type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nombre, SKU o código de barras">
            </div>

            <div class="inv-field">
                <label for="inventory-metal">Metal</label>
                <select id="inventory-metal" name="metal_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($metals as $metal)
                        <option value="{{ $metal->id }}" {{ (string) request('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="inv-field">
                <label for="inventory-category">Categoría</label>
                <select id="inventory-category" name="category_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="inv-field">
                <label for="inventory-status">Estado</label>
                <select id="inventory-status" name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="inv-field">
                <label for="inventory-origin">Origen</label>
                <select id="inventory-origin" name="origin" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($origins as $origin)
                        <option value="{{ $origin->value }}" {{ request('origin') === $origin->value ? 'selected' : '' }}>{{ $origin->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="inv-filter-actions">
                <button type="submit" class="btn btn-dark">Filtrar</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card paginated-table-card">
        <div class="inv-table-head">
            <div>
                <h2 class="font-serif">Piezas registradas</h2>
                <p>{{ number_format($products->total()) }} {{ $products->total() === 1 ? 'resultado' : 'resultados' }} · Ordenadas por registro más reciente</p>
            </div>
            @if ($hasFilters)
                <span class="inv-filter-chip">{{ $activeFilterCount }} {{ $activeFilterCount === 1 ? 'filtro activo' : 'filtros activos' }}</span>
            @endif
        </div>

        <div class="inv-table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Pieza</th>
                        <th>Metal / Pureza</th>
                        <th class="text-right">Peso</th>
                        <th class="text-right">Precio</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $badge = match ($product->status) {
                                \App\Enums\ProductStatus::Available => 'badge-success',
                                \App\Enums\ProductStatus::Reserved => 'badge-info',
                                \App\Enums\ProductStatus::Melting => 'badge-gold',
                                default => 'badge-muted',
                            };
                            $isUsed = $product->origin === \App\Enums\ProductOrigin::UsedPurchase;
                        @endphp
                        <tr>
                            <td class="inv-cell-product">
                                <div class="inv-product">
                                    @if ($product->primaryPhoto)
                                        <img src="{{ asset('storage/'.$product->primaryPhoto->path) }}" alt="{{ $product->name }}" class="inv-photo">
                                    @else
                                        <span class="inv-photo--empty" aria-hidden="true">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M6 3h12l3 6-9 12L3 9l3-6Z"></path>
                                                <path d="M3 9h18M9 3l3 18M15 3l-3 18"></path>
                                            </svg>
                                        </span>
                                    @endif
                                    <div class="inv-product-main">
                                        <div class="inv-product-name">
                                            <span>{{ $product->name }}</span>
                                            @if ($isUsed)
                                                <span class="inv-origin">Ocasión</span>
                                            @endif
                                        </div>
                                        <div class="inv-product-meta">{{ $product->sku }} · {{ $product->category?->name ?? 'Sin categoría' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Metal / Pureza">
                                {{ $product->metal?->name ?? 'Sin metal' }}
                                @if ($product->purity)
                                    <span class="muted">({{ $product->purity->code }})</span>
                                @endif
                            </td>
                            <td data-label="Peso" class="text-right num">{{ number_format((float) $product->weight_g, 3) }} g</td>
                            <td data-label="Precio" class="text-right inv-price">$ {{ number_format((float) $product->price, 2) }}</td>
                            <td data-label="Ubicación" class="muted">{{ $product->location?->name ?? 'Sin ubicación' }}</td>
                            <td data-label="Estado">
                                <span class="badge {{ $badge }}">{{ $product->status?->label() ?? 'Sin estado' }}</span>
                            </td>
                            <td class="text-right inv-cell-actions">
                                <div class="inv-actions">
                                    <a href="{{ route('admin.inventory.show', $product) }}" class="inv-icon-btn" title="Ver detalle" aria-label="Ver detalle de {{ $product->name }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.inventory.label', $product) }}" target="_blank" class="inv-icon-btn" title="Imprimir etiqueta" aria-label="Imprimir etiqueta de {{ $product->name }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M20.6 13.4 12 22l-9-9 8.6-8.6a2 2 0 0 1 1.4-.6H20a2 2 0 0 1 2 2v6.2a2 2 0 0 1-.6 1.4Z"></path>
                                            <circle cx="16" cy="8" r="1"></circle>
                                        </svg>
                                    </a>
                                    @can('inventory.adjust')
                                        <a href="{{ route('admin.inventory.adjust', $product) }}" class="inv-icon-btn" title="Ajustar existencias" aria-label="Ajustar existencias de {{ $product->name }}">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M4 6h10M18 6h2M4 12h2M10 12h10M4 18h10M18 18h2"></path>
                                                <circle cx="16" cy="6" r="2"></circle>
                                                <circle cx="8" cy="12" r="2"></circle>
                                                <circle cx="16" cy="18" r="2"></circle>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $product) }}" class="inv-icon-btn inv-icon-btn--dark" title="Editar pieza" aria-label="Editar {{ $product->name }}">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="m14 4 6 6L8 22H2v-6L14 4Z"></path>
                                                <path d="m12 6 6 6"></path>
                                            </svg>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="inv-empty">
                                    <span class="inv-empty-icon" aria-hidden="true">
                                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="7"></circle>
                                            <path d="m20 20-3.5-3.5"></path>
                                        </svg>
                                    </span>
                                    <strong>No se encontraron piezas</strong>
                                    <p>Prueba con otros criterios de búsqueda o limpia los filtros.</p>
                                    @if ($hasFilters)
                                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline">Limpiar filtros</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($products->hasPages())
        <div class="paginated-pagination" style="padding: 0.65rem 1.1rem; border: 1px solid var(--color-linea); border-radius: 5px; background: var(--color-blanco);">
            {{ $products->links('vendor.pagination.admin') }}
        </div>
    @endif
</div>
@endsection
