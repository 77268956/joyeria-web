@extends('layouts.admin')

@section('title', 'Inventario')

@section('body_class', 'full-viewport')

@section('content')
<div class="paginated-page">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Inventario</h1>
            <p class="page-subtitle">Catálogo interno de piezas con control de existencias por movimientos inmutables</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.inventory.export', request()->query()) }}" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Exportar Excel</span>
            </a>
            <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">
                <span>Materia Prima</span>
            </a>
            @can('inventory.adjust')
                <a href="{{ route('admin.inventory.create') }}" class="btn btn-gold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Nueva Pieza</span>
                </a>
            @endcan
        </div>
    </div>

    @include('admin.partials.alerts')

    {{-- Filtros --}}
    <div class="card paginated-filter-card">
        <form action="{{ route('admin.inventory.index') }}" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Buscar (nombre, SKU o código)</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ej. Anillo oro, PRD-001-00001" style="height: 36px; font-size: 0.85rem;">
            </div>

            <div style="min-width: 140px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Metal</label>
                <select name="metal_id" class="form-select" style="height: 36px; font-size: 0.85rem;">
                    <option value="">Todos</option>
                    @foreach ($metals as $metal)
                        <option value="{{ $metal->id }}" {{ (string) request('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 150px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Categoría</label>
                <select name="category_id" class="form-select" style="height: 36px; font-size: 0.85rem;">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 130px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Estado</label>
                <select name="status" class="form-select" style="height: 36px; font-size: 0.85rem;">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 120px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Origen</label>
                <select name="origin" class="form-select" style="height: 36px; font-size: 0.85rem;">
                    <option value="">Todos</option>
                    @foreach ($origins as $origin)
                        <option value="{{ $origin->value }}" {{ request('origin') === $origin->value ? 'selected' : '' }}>{{ $origin->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-dark" style="height: 36px;">Filtrar</button>
                @if (request()->hasAny(['search', 'metal_id', 'category_id', 'status', 'origin']))
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline" style="height: 36px; display: inline-flex; align-items: center;">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card paginated-table-card">
        <div class="paginated-table-wrap">
            <table class="data-table">
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
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    @if ($product->primaryPhoto)
                                        <img src="{{ asset('storage/'.$product->primaryPhoto->path) }}" alt="{{ $product->name }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-linea);">
                                    @else
                                        <div style="width: 44px; height: 44px; border-radius: 4px; background-color: var(--color-crema); border: 1px solid var(--color-linea); display: flex; align-items: center; justify-content: center; color: var(--color-dorado);">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight: 600;">{{ $product->name }}</div>
                                        <div class="muted" style="font-size: 0.8rem;">{{ $product->sku }} &middot; {{ $product->category?->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $product->metal?->name }}
                                @if ($product->purity)
                                    <span class="muted">({{ $product->purity->code }})</span>
                                @endif
                            </td>
                            <td class="text-right num">{{ $product->weight_g }} g</td>
                            <td class="text-right num" style="font-weight: 600;">$ {{ number_format((float) $product->price, 2) }}</td>
                            <td class="muted">{{ $product->location?->name ?? '—' }}</td>
                            <td>
                                @php
                                    $badge = match ($product->status) {
                                        \App\Enums\ProductStatus::Available => 'badge-success',
                                        \App\Enums\ProductStatus::Reserved => 'badge-info',
                                        \App\Enums\ProductStatus::Melting => 'badge-gold',
                                        default => 'badge-muted',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $product->status?->label() }}</span>
                            </td>
                            <td class="text-right">
                                <div style="display: inline-flex; gap: 0.4rem; flex-wrap: wrap; justify-content: flex-end;">
                                    <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('admin.inventory.label', $product) }}" target="_blank" class="btn btn-sm btn-outline">Etiqueta</a>
                                    @can('inventory.adjust')
                                        <a href="{{ route('admin.inventory.adjust', $product) }}" class="btn btn-sm btn-outline">Ajustar</a>
                                        <a href="{{ route('admin.inventory.edit', $product) }}" class="btn btn-sm btn-dark">Editar</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem;" class="muted">
                                No se encontraron piezas con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($products->hasPages())
        <div class="paginated-pagination">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
