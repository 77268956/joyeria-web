@extends('layouts.admin')

@section('title', 'Descuentos de compra')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Descuentos de compra</h1>
        <p class="page-subtitle">Porcentaje que la tienda retiene al comprar metal a un cliente, por metal y pureza</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.prices.index') }}" class="btn btn-outline">
            <span>&larr; Volver a Precios</span>
        </a>
    </div>
</div>

@include('admin.partials.alerts')

<form action="{{ route('admin.prices.purchase_settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    @foreach ($metals as $metal)
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--color-linea);">
                <h2 class="font-serif" style="font-size: 1.15rem; font-weight: 500;">{{ $metal->name }}</h2>
                <p class="muted" style="font-size: 0.82rem; margin-top: 0.15rem;">El valor por defecto aplica cuando la pieza no tiene una pureza registrada.</p>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pureza</th>
                            <th style="width: 220px;">Descuento (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($defaultSetting = $settings->get($metal->id.'-default'))
                        <tr>
                            <td style="font-weight: 600;">Valor por defecto</td>
                            <td>
                                <input type="number" step="0.01" min="0" max="100"
                                       name="discounts[{{ $metal->id }}-default]"
                                       value="{{ old('discounts.'.$metal->id.'-default', $defaultSetting?->discount_percent) }}"
                                       class="form-control" placeholder="0.00">
                            </td>
                        </tr>
                        @foreach ($metal->purities as $purity)
                            @php($setting = $settings->get($metal->id.'-'.$purity->id))
                            <tr>
                                <td>
                                    {{ $purity->label ?: $purity->code }}
                                    <span class="muted">({{ $purity->code }})</span>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="100"
                                           name="discounts[{{ $metal->id }}-{{ $purity->id }}]"
                                           value="{{ old('discounts.'.$metal->id.'-'.$purity->id, $setting?->discount_percent) }}"
                                           class="form-control" placeholder="0.00">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div style="display: flex; gap: 0.75rem;">
        <button type="submit" class="btn btn-gold">Guardar descuentos</button>
        <a href="{{ route('admin.prices.index') }}" class="btn btn-outline">Cancelar</a>
    </div>
</form>
@endsection
