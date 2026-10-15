@extends('layouts.admin')

@section('title', 'Conteo #'.$count->id)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Conteo físico #{{ $count->id }}</h1>
        <p class="page-subtitle">
            {{ $count->started_at?->format('d/m/Y H:i') }} · {{ $count->startedBy?->name }}
            @php
                $badge = match ($count->status) {
                    \App\Enums\StockCountStatus::Applied => 'badge-success',
                    \App\Enums\StockCountStatus::Finished => 'badge-gold',
                    default => 'badge-danger',
                };
            @endphp
            <span class="badge {{ $badge }}" style="margin-left: 0.5rem;">{{ $count->status->label() }}</span>
        </p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.stock-counts.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="grid-2" style="grid-template-columns: repeat(5, 1fr);">
    <div class="card">
        <p class="muted" style="margin: 0;">Esperadas</p>
        <p class="font-serif num" style="font-size: 1.6rem; margin: 0.25rem 0 0;">{{ $stats['expected'] }}</p>
    </div>
    <div class="card">
        <p class="muted" style="margin: 0;">Contadas</p>
        <p class="font-serif num" style="font-size: 1.6rem; margin: 0.25rem 0 0;">{{ $stats['counted'] }}</p>
    </div>
    <div class="card">
        <p class="muted" style="margin: 0;">Sin contar</p>
        <p class="font-serif num" style="font-size: 1.6rem; margin: 0.25rem 0 0;">{{ $stats['pending'] }}</p>
    </div>
    <div class="card">
        <p class="muted" style="margin: 0;">Faltantes</p>
        <p class="font-serif num" style="font-size: 1.6rem; margin: 0.25rem 0 0; color: var(--color-error);">{{ $stats['missing'] }}</p>
    </div>
    <div class="card">
        <p class="muted" style="margin: 0;">Sobrantes</p>
        <p class="font-serif num" style="font-size: 1.6rem; margin: 0.25rem 0 0; color: var(--color-info);">{{ $stats['surplus'] }}</p>
    </div>
</div>

@if ($count->isOpen())
    <div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <p class="page-subtitle" style="margin: 0;">Marca cada pieza como presente o faltante y guarda el avance. Al finalizar podrás aplicar los ajustes.</p>
        <form action="{{ route('admin.stock-counts.finish', $count) }}" method="POST" onsubmit="return confirm('¿Finalizar el conteo #{{ $count->id }}? Ya no podrás editar las piezas.');">
            @csrf
            <button type="submit" class="btn btn-gold">Finalizar conteo</button>
        </form>
    </div>
@elseif ($count->isFinished())
    <div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <p class="page-subtitle" style="margin: 0;">Conteo finalizado el {{ $count->finished_at?->format('d/m/Y H:i') }}. Aplicar generará ajustes de inventario por las diferencias.</p>
        <form action="{{ route('admin.stock-counts.apply', $count) }}" method="POST" onsubmit="return confirm('¿Aplicar el conteo #{{ $count->id }}? Se ajustará el inventario según las diferencias.');">
            @csrf
            <button type="submit" class="btn btn-dark">Aplicar conteo</button>
        </form>
    </div>
@else
    <div class="card">
        <p class="page-subtitle" style="margin: 0;">Conteo aplicado el {{ $count->applied_at?->format('d/m/Y H:i') }} por {{ $count->appliedBy?->name }}. El inventario ya refleja las diferencias.</p>
    </div>
@endif

@if ($count->isOpen())
    <form action="{{ route('admin.stock-counts.record', $count) }}" method="POST">
        @csrf
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pieza</th>
                            <th>Metal / Pureza</th>
                            <th class="text-right">Peso</th>
                            <th>Esperada</th>
                            <th style="width: 180px;">Resultado</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($count->items as $index => $item)
                            <tr>
                                <td>
                                    {{ $item->product?->name ?? '—' }}
                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                </td>
                                <td>{{ $item->product?->metal?->name }}@if ($item->product?->purity?->code) · {{ $item->product->purity->code }}@endif</td>
                                <td class="text-right num">{{ $item->product?->weight_g }} g</td>
                                <td>{{ $item->expected ? 'Sí' : 'No (sobrante)' }}</td>
                                <td>
                                    <select name="items[{{ $index }}][found]" class="form-select">
                                        <option value="" {{ $item->found === null ? 'selected' : '' }}>Sin contar</option>
                                        <option value="1" {{ $item->found === true ? 'selected' : '' }}>Presente</option>
                                        <option value="0" {{ $item->found === false ? 'selected' : '' }}>Faltante</option>
                                    </select>
                                </td>
                                <td><input type="text" name="items[{{ $index }}][note]" class="form-control" value="{{ $item->note }}" maxlength="255"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-top: 1rem;">
            <button type="submit" class="btn btn-gold">Guardar conteo</button>
        </div>
    </form>

    @if ($candidates->isNotEmpty())
        <div class="card">
            <h2 class="font-serif" style="font-size: 1.1rem; margin-bottom: 0.75rem;">Registrar sobrante (pieza no esperada)</h2>
            <form action="{{ route('admin.stock-counts.record', $count) }}" method="POST" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                @csrf
                <input type="hidden" name="items[0][found]" value="1">
                <div class="form-group" style="flex: 1; min-width: 240px; margin: 0;">
                    <label class="form-label" for="surplus_product">Pieza encontrada</label>
                    <select name="items[0][product_id]" id="surplus_product" class="form-select" required>
                        <option value="">Selecciona una pieza</option>
                        @foreach ($candidates as $candidate)
                            <option value="{{ $candidate->id }}">{{ $candidate->name }} · {{ $candidate->sku }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="min-width: 200px; margin: 0;">
                    <label class="form-label" for="surplus_note">Nota</label>
                    <input type="text" name="items[0][note]" id="surplus_note" class="form-control" maxlength="255">
                </div>
                <button type="submit" class="btn btn-outline">Registrar sobrante</button>
            </form>
        </div>
    @endif
@else
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pieza</th>
                        <th>Metal / Pureza</th>
                        <th>Esperada</th>
                        <th>Encontrada</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($count->items as $item)
                        <tr>
                            <td>{{ $item->product?->name ?? '—' }}</td>
                            <td>{{ $item->product?->metal?->name }}@if ($item->product?->purity?->code) · {{ $item->product->purity->code }}@endif</td>
                            <td>{{ $item->expected ? 'Sí' : 'No' }}</td>
                            <td>
                                @if ($item->found === null)
                                    <span class="badge badge-danger">Sin contar</span>
                                @elseif (! $item->expected && $item->found)
                                    <span class="badge" style="background: var(--color-info);">Sobrante</span>
                                @elseif ($item->expected && ! $item->found)
                                    <span class="badge badge-danger">Faltante</span>
                                @else
                                    <span class="badge badge-success">Presente</span>
                                @endif
                            </td>
                            <td>{{ $item->note ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted" style="text-align: center; padding: 1.5rem;">El conteo no tiene piezas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
