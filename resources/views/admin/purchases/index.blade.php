@extends('layouts.admin')

@section('title', 'Compra de Metales')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .purchases-page {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        gap: 0.85rem;
    }

    .purchases-page .page-header {
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .purchases-page .purchases-filter-card {
        padding: 0.85rem 1.25rem;
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .purchases-page .purchases-table-card {
        padding: 0;
        margin-bottom: 0;
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .purchases-page .purchases-table-wrap {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: auto;
    }

    .purchases-page .data-table {
        margin-bottom: 0;
    }

    .purchases-page .data-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: var(--color-crema);
        box-shadow: inset 0 -1px 0 var(--color-linea);
    }

    .purchases-page .purchases-pagination {
        flex-shrink: 0;
        margin-top: 0;
    }

    @media (max-width: 1024px) {
        .purchases-page {
            display: block;
        }

        .purchases-page .purchases-table-card {
            max-height: 60vh;
            margin-bottom: 1rem;
        }
    }
</style>

<div class="purchases-page">
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

    <div class="card purchases-filter-card">
        <form action="{{ route('admin.purchases.index') }}" method="GET" style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 220px; margin: 0;">
                <label class="form-label" for="search" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Buscar</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Folio o nombre del cliente" style="height: 36px; font-size: 0.85rem;">
            </div>
            <div class="form-group" style="min-width: 180px; margin: 0;">
                <label class="form-label" for="status" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Estado</label>
                <select name="status" id="status" class="form-select" style="height: 36px; font-size: 0.85rem;">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-dark" style="height: 36px;">Filtrar</button>
                @if (request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline" style="height: 36px; display: inline-flex; align-items: center;">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card purchases-table-card">
        <div class="purchases-table-wrap">
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
                            <td class="num"><a href="{{ route('admin.purchases.show', $purchase) }}" style="color: var(--color-noche); font-weight: 600;">{{ $purchase->number }}</a></td>
                            <td class="num">{{ $purchase->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $purchase->customer?->full_name ?? '—' }}</td>
                            <td class="text-right num">{{ $purchase->items_count }}</td>
                            <td class="text-right num" style="font-weight: 600;">$ {{ number_format((float) $purchase->total_amount, 2) }}</td>
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
                        <tr><td colspan="8" class="muted" style="text-align: center; padding: 2rem;">No hay compras registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="purchases-pagination">
        {{ $purchases->links() }}
    </div>
</div>
@endsection
