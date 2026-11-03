@extends('layouts.admin')

@section('title', 'Nuevo Cliente')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Nuevo Cliente</h1>
        <p class="page-subtitle">Registra la ficha del cliente</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="card">
    <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data" id="customer-create-form">
        @csrf
        @include('admin.customers._form')
        <button type="submit" class="btn btn-gold" id="customer-save-btn">Guardar cliente</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('customer-create-form');
        const btn = document.getElementById('customer-save-btn');

        if (form && btn) {
            form.addEventListener('submit', function () {
                if (btn.disabled) {
                    return false;
                }
                btn.disabled = true;
                btn.innerHTML = 'Guardando...';
                setTimeout(function () {
                    btn.disabled = false;
                    btn.innerHTML = 'Guardar cliente';
                }, 8000);
            });
        }
    })();
</script>
@endpush
