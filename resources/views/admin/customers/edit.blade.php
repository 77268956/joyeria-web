@extends('layouts.admin')

@section('title', 'Editar Cliente')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Editar Cliente</h1>
        <p class="page-subtitle">{{ $customer->full_name }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@include('admin.partials.alerts')

<div class="card">
    <form action="{{ route('admin.customers.update', $customer) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.customers._form', ['customer' => $customer])
        <button type="submit" class="btn btn-gold">Guardar cambios</button>
    </form>
</div>
@endsection
