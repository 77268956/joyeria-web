@extends('layouts.admin')

@section('title', 'Nuevo Lote')

@section('content')
@php
    $puritiesByMetal = $metals->mapWithKeys(function ($metal) {
        return [$metal->id => $metal->purities->map(fn ($purity) => [
            'id' => $purity->id,
            'code' => $purity->code,
            'label' => $purity->label,
        ])->values()];
    });
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Nuevo Lote de Materia Prima</h1>
        <p class="page-subtitle">El peso inicial se asienta automáticamente como movimiento de entrada</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="card" style="max-width: 720px;">
    <form action="{{ route('admin.inventory.lots.store') }}" method="POST">
        @csrf

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Metal *</label>
                <select name="metal_id" id="metal_id" class="form-select" required>
                    <option value="">Selecciona…</option>
                    @foreach ($metals as $metal)
                        <option value="{{ $metal->id }}" {{ (string) old('metal_id') === (string) $metal->id ? 'selected' : '' }}>{{ $metal->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Pureza *</label>
                <select name="purity_id" id="purity_id" class="form-select" required>
                    <option value="">Selecciona…</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Peso inicial (g) *</label>
                <input type="number" step="0.001" min="0.001" name="initial_weight_g" value="{{ old('initial_weight_g') }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <select name="location_id" class="form-select">
                    <option value="">—</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" {{ (string) old('location_id') === (string) $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Código del lote</label>
            <input type="text" name="code" value="{{ old('code') }}" class="form-control" maxlength="40" placeholder="Se genera automáticamente si se deja vacío">
        </div>

        <div class="form-group">
            <label class="form-label">Notas</label>
            <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--color-linea); padding-top: 1.25rem;">
            <a href="{{ route('admin.inventory.lots.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-gold">Registrar Lote</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const puritiesByMetal = @json($puritiesByMetal);
    const selectedPurity = "{{ (string) old('purity_id') }}";

    function syncPurities() {
        const metalId = document.getElementById('metal_id').value;
        const puritySelect = document.getElementById('purity_id');
        const purities = puritiesByMetal[metalId] || [];

        puritySelect.innerHTML = '<option value="">Selecciona…</option>';
        purities.forEach(function (purity) {
            const option = document.createElement('option');
            option.value = purity.id;
            option.textContent = purity.label || purity.code;
            if (String(purity.id) === selectedPurity) {
                option.selected = true;
            }
            puritySelect.appendChild(option);
        });
    }

    document.getElementById('metal_id').addEventListener('change', syncPurities);
    syncPurities();
</script>
@endpush
@endsection
