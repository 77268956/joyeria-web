@extends('layouts.admin')

@section('title', 'Compra de Metales')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Compra de Metales</h1>
        <p class="page-subtitle">Registro de piezas compradas al público con identificación obligatoria y valuación por quilates.</p>
    </div>
    <div class="page-actions">
        @can('purchases.create')
            <a href="{{ route('admin.purchases.create') }}" class="btn btn-dark">Nueva compra</a>
        @endcan
    </div>
</div>

@include('admin.partials.alerts')

<div class="card">
    <form action="{{ route('admin.purchases.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 220px; margin: 0;">
            <label class="form-label" for="search">Buscar</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Folio o nombre del cliente">
        </div>
        <div class="form-group" style="min-width: 200px; margin: 0;">
            <label class="form-label" for="status">Estado</label>
            <select name="status" id="status" class="form-select">
                <option value="">Todos</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-outline">Filtrar</button>
            @if (request()->filled('search') || request()->filled('status'))
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline">Limpiar</a>
            @endif
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th class="text-right">Piezas</th>
                    <th class="text-right">Total</th>
                    <th>Pago</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td class="num"><a href="{{ route('admin.purchases.show', $purchase) }}" style="color: var(--color-noche);">{{ $purchase->number }}</a></td>
                        <td class="num">{{ $purchase->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $purchase->customer?->full_name ?? '—' }}</td>
                        <td class="text-right num">{{ $purchase->items_count }}</td>
                        <td class="text-right num">$ {{ number_format((float) $purchase->total_amount, 2) }}</td>
                        <td>{{ $purchase->payout_method->label() }}</td>
                        <td>
                            @php
                                $badge = match ($purchase->status) {
                                    \App\Enums\MetalPurchaseStatus::Paid, \App\Enums\MetalPurchaseStatus::Adjusted => 'badge-success',
                                    \App\Enums\MetalPurchaseStatus::PendingApproval => 'badge-gold',
                                    default => 'badge-danger',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $purchase->status->label() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-outline btn-sm">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted" style="text-align: center; padding: 1.5rem;">No hay compras registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1rem;">
    {{ $purchases->links() }}
</div>
@endsection
