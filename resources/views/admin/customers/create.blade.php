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
    <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.customers._form')
        <button type="submit" class="btn btn-gold">Guardar cliente</button>
    </form>
</div>
@endsection
