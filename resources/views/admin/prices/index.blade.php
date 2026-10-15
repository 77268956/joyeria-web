@extends('layouts.admin')

@section('title', 'Precio del Metal')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Precio del Metal</h1>
        <p class="page-subtitle">Precio vigente por gramo, actualizado diariamente desde el proveedor, con ajuste manual auditado</p>
    </div>
    <div class="page-actions">
        @can('metal_price.override')
            <form action="{{ route('admin.prices.refresh') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    <span>Actualizar ahora</span>
                </button>
            </form>
        @endcan
        @can('settings.manage')
            <a href="{{ route('admin.prices.purchase_settings') }}" class="btn btn-dark">
                <span>Descuentos de compra</span>
            </a>
        @endcan
    </div>
</div>

@include('admin.partials.alerts')

{{-- Precios vigentes --}}
<div class="grid-2">
    @forelse ($metals as $metal)
        @php($price = $current[$metal->code] ?? null)
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div>
                    <div class="form-label" style="margin-bottom: 0.15rem;">{{ $metal->name }}</div>
                    <div class="muted" style="font-size: 0.8rem;">Precio por gramo de metal puro</div>
                </div>
                @if ($price)
                    <span class="badge {{ $price->source === \App\Enums\MetalPriceSource::Api ? 'badge-info' : 'badge-gold' }}">
                        {{ $price->source?->label() }}
                    </span>
                @endif
            </div>

            @if ($price)
                <div class="font-serif" style="font-size: 2.1rem; font-weight: 600; color: var(--color-noche); font-variant-numeric: tabular-nums;">
                    {{ $store->currency }} ${{ number_format((float) $price->price_per_gram, 2) }}<span class="muted" style="font-size: 1rem; font-weight: 400;"> / g</span>
                </div>
                <div class="muted" style="font-size: 0.82rem; margin-top: 0.35rem;">
                    Vigente desde {{ $price->price_date?->format('d/m/Y') }}
                    @if ($price->provider) &middot; {{ $price->provider }} @endif
                    @if ($price->spot_per_ounce) &middot; spot {{ number_format((float) $price->spot_per_ounce, 2) }} USD/oz @endif
                </div>
                @if ($price->setBy)
                    <div class="muted" style="font-size: 0.82rem;">Fijado por {{ $price->setBy->name }} @if ($price->reason) — {{ $price->reason }} @endif</div>
                @endif

                @php($purities = $purityPrices[$metal->code] ?? collect())
                @if ($purities->isNotEmpty())
                    <div class="table-wrap" style="margin-top: 1.25rem;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Pureza</th>
                                    <th class="text-right">Precio por gramo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($metal->purities as $purity)
                                    <tr>
                                        <td>{{ $purity->label ?: $purity->code }}</td>
                                        <td class="text-right num" style="font-weight: 600;">
                                            {{ $store->currency }} ${{ number_format((float) ($purities[$purity->code] ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <p class="muted" style="padding: 1rem 0;">Aún no hay un precio registrado para este metal.</p>
            @endif
        </div>
    @empty
        <div class="card"><p class="muted">No hay metales preciosos configurados.</p></div>
    @endforelse
</div>

@can('metal_price.override')
    {{-- Ajuste manual --}}
    <div class="card">
        <h2 class="font-serif" style="font-size: 1.25rem; font-weight: 500; margin-bottom: 0.35rem;">Fijar precio manual</h2>
        <p class="muted" style="font-size: 0.85rem; margin-bottom: 1.25rem;">Inserta un nuevo precio vigente. El precio anterior se conserva en el historial y la operación queda auditada.</p>

        <form action="{{ route('admin.prices.manual') }}" method="POST">
            @csrf
            <div class="grid-3">
                <div class="form-group">
                    <label class="form-label" for="metal_id">Metal</label>
                    <select name="metal_id" id="metal_id" class="form-select" required>
                        @foreach ($metals as $metal)
                            <option value="{{ $metal->id }}" {{ (string) old('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="price_per_gram">Precio por gramo (metal puro)</label>
                    <input type="number" step="0.0001" min="0.0001" name="price_per_gram" id="price_per_gram" class="form-control" value="{{ old('price_per_gram') }}" placeholder="Ej. 1245.7500" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reason">Motivo del ajuste</label>
                    <input type="text" name="reason" id="reason" class="form-control" value="{{ old('reason') }}" placeholder="Ej. Corrección por caída de mercado" required>
                </div>
            </div>
            <button type="submit" class="btn btn-gold">Fijar precio manual</button>
        </form>
    </div>
@endcan

{{-- Historial --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--color-linea); flex-wrap: wrap;">
        <h2 class="font-serif" style="font-size: 1.15rem; font-weight: 500;">Historial de precios</h2>
        <form action="{{ route('admin.prices.index') }}" method="GET" style="display: flex; gap: 0.75rem; align-items: flex-end;">
            <div>
                <label class="form-label">Metal</label>
                <select name="metal_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach ($metals as $metal)
                        <option value="{{ $metal->id }}" {{ (string) request('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                    @endforeach
                </select>
            </div>
            @if (request()->filled('metal_id'))
                <a href="{{ route('admin.prices.index') }}" class="btn btn-outline">Limpiar</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Metal</th>
                    <th class="text-right">Precio / g</th>
                    <th class="text-right">Spot / oz</th>
                    <th>Origen</th>
                    <th>Proveedor</th>
                    <th>Registrado por</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $record)
                    <tr>
                        <td class="num">{{ $record->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $record->metal?->name }}</td>
                        <td class="text-right num" style="font-weight: 600;">{{ $store->currency }} ${{ number_format((float) $record->price_per_gram, 2) }}</td>
                        <td class="text-right num muted">{{ $record->spot_per_ounce ? number_format((float) $record->spot_per_ounce, 2) : '—' }}</td>
                        <td>
                            <span class="badge {{ $record->source === \App\Enums\MetalPriceSource::Api ? 'badge-info' : 'badge-gold' }}">
                                {{ $record->source?->label() }}
                            </span>
                        </td>
                        <td class="muted">{{ $record->provider ?? '—' }}</td>
                        <td class="muted">{{ $record->setBy?->name ?? 'Sistema' }}</td>
                        <td class="muted">{{ $record->reason ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem;" class="muted">
                            Aún no hay registros de precios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($history->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--color-linea);">
            {{ $history->links() }}
        </div>
    @endif
</div>
@endsection
