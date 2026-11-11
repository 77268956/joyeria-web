@extends('layouts.admin')

@section('title', 'Clientes')

@section('body_class', 'full-viewport')

@section('content')
<div class="paginated-page">
    <div class="page-header">
        <div>
            <h1 class="page-title font-serif">Clientes</h1>
            <p class="page-subtitle">Fichas con historial de compras</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-gold">Nuevo Cliente</a>
        </div>
    </div>

    @include('admin.partials.alerts')

    <div class="card paginated-filter-card">
        <form action="{{ route('admin.customers.index') }}" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 220px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nombre, documento o teléfono" style="height: 36px; font-size: 0.85rem;">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-dark" style="height: 36px;">Buscar</button>
                @if (request('search'))
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline" style="height: 36px; display: inline-flex; align-items: center;">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card paginated-table-card">
        <div class="paginated-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th class="text-right">Compras</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td style="font-weight: 600;">{{ $customer->full_name }}</td>
                            <td class="num">{{ $customer->document_type }} {{ $customer->document_number }}</td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td class="text-right num" style="font-weight: 600;">{{ $customer->sales_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline btn-sm">Ver</a>
                                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-outline btn-sm">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted" style="text-align: center; padding: 2rem;">No hay clientes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="paginated-pagination">
        {{ $customers->links() }}
    </div>
</div>
@endsection
