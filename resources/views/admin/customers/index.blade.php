@extends('layouts.admin')

@section('title', 'Clientes')

@section('content')
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

<div class="card" style="padding: 1.25rem 1.75rem;">
    <form action="{{ route('admin.customers.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 240px;">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nombre, documento o teléfono">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-dark">Buscar</button>
            @if (request('search'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">Limpiar</a>
            @endif
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrap">
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
                        <td>{{ $customer->full_name }}</td>
                        <td>{{ $customer->document_type }} {{ $customer->document_number }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td>{{ $customer->email ?? '—' }}</td>
                        <td class="text-right num">{{ $customer->sales_count }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline btn-sm">Ver</a>
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-outline btn-sm">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted" style="text-align: center; padding: 1.5rem;">No hay clientes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1rem;">
    {{ $customers->links() }}
</div>
@endsection
