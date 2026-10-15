@extends('layouts.admin')

@section('title', $customer->full_name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">{{ $customer->full_name }}</h1>
        <p class="page-subtitle">{{ $customer->document_type }} {{ $customer->document_number }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">Volver</a>
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-gold">Editar</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="grid-2">
    <div class="card">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 0.75rem;">Datos de contacto</h2>
        <p><span class="muted">Teléfono:</span> {{ $customer->phone ?? '—' }}</p>
        <p><span class="muted">Correo:</span> {{ $customer->email ?? '—' }}</p>
        <p><span class="muted">Dirección:</span> {{ $customer->address ?? '—' }}</p>
        <p><span class="muted">Nacimiento:</span> {{ $customer->birth_date?->format('d/m/Y') ?? '—' }}</p>
        @if ($customer->notes)
            <p><span class="muted">Notas:</span> {{ $customer->notes }}</p>
        @endif
    </div>
    <div class="card">
        <h2 class="font-serif" style="font-size: 1.15rem; margin-bottom: 0.75rem;">Identificación</h2>
        @if ($customer->id_photo_path)
            <img src="{{ route('admin.customers.photo', $customer) }}" alt="Identificación" style="max-width: 100%; border: 1px solid var(--color-linea); border-radius: 4px;">
        @else
            <p class="muted">Sin foto de identificación cargada.</p>
        @endif
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--color-linea);">
        <h2 class="font-serif" style="font-size: 1.15rem;">Historial de compras</h2>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th class="text-right">Total</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customer->sales as $sale)
                    <tr>
                        <td class="num">{{ $sale->number }}</td>
                        <td class="num">{{ $sale->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-right num">$ {{ number_format((float) $sale->total, 2) }}</td>
                        <td><span class="badge {{ $sale->isVoided() ? 'badge-danger' : 'badge-success' }}">{{ $sale->status->label() }}</span></td>
                        <td class="text-right"><a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline btn-sm">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted" style="text-align: center; padding: 1.5rem;">Este cliente aún no tiene compras.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
