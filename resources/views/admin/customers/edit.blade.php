@extends('layouts.admin')

@section('title', 'Editar Cliente · '.$customer->full_name)

@section('body_class', 'full-viewport')

@php
    $initials = collect(explode(' ', $customer->full_name))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');
    if (empty($initials)) {
        $initials = 'CL';
    }
@endphp

@section('content')
<style>
    .cust-page-container {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
        gap: 0.65rem;
    }

    .cust-page-container .alert {
        margin-bottom: 0;
        padding: 0.5rem 0.85rem;
        flex-shrink: 0;
    }

    .cust-hero-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        padding: 0.65rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
        flex-shrink: 0;
    }

    .cust-hero-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .cust-hero-avatar {
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--color-noche);
        color: var(--color-hueso);
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
        letter-spacing: 0.04em;
    }

    .cust-hero-titles h1 {
        margin: 0;
        font-family: var(--font-serif);
        font-size: 1.35rem;
        font-weight: 500;
        color: var(--color-noche);
        line-height: 1.15;
    }

    .cust-hero-titles p {
        margin: 0.1rem 0 0;
        font-size: 0.75rem;
        color: var(--color-piedra);
    }

    .cust-hero-actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .cust-hero-actions .btn {
        height: 32px;
        padding: 0 0.8rem;
        font-size: 0.78rem;
    }

    .cust-form-scrollable {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: #D9D2C3 transparent;
    }

    .cust-form-scrollable::-webkit-scrollbar { width: 6px; }
    .cust-form-scrollable::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .cust-form-scrollable::-webkit-scrollbar-track { background: transparent; }

    @media (max-width: 700px) {
        body.full-viewport { overflow: auto; }
        .full-viewport .main-wrapper { height: auto; overflow: visible; }
        .full-viewport .content-area { display: block; overflow: visible; }
        .cust-page-container { overflow: visible; height: auto; }
        .cust-hero-card { flex-direction: column; align-items: flex-start; }
        .cust-hero-actions { width: 100%; }
        .cust-hero-actions .btn { flex: 1; justify-content: center; }
    }
</style>

<div class="cust-page-container">
    {{-- Hero header --}}
    <header class="cust-hero-card">
        <div class="cust-hero-left">
            <span class="cust-hero-avatar" aria-hidden="true">{{ $initials }}</span>
            <div class="cust-hero-titles">
                <h1>Editar Cliente</h1>
                <p>
                    <strong>{{ $customer->full_name }}</strong> ·
                    <span class="num">{{ $customer->document_type }} {{ $customer->document_number }}</span>
                    @if ($customer->created_at)
                        · Cliente desde {{ $customer->created_at->format('d/m/Y') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="cust-hero-actions">
            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Ver Ficha</span>
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Directorio</span>
            </a>
        </div>
    </header>

    @include('admin.partials.alerts')

    {{-- Formulario --}}
    <div class="cust-form-scrollable">
        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" enctype="multipart/form-data" id="customer-edit-form" style="display: flex; flex-direction: column; min-height: 100%;">
            @csrf
            @method('PUT')
            @include('admin.customers._form', ['customer' => $customer])
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('customer-edit-form');
        const btn = document.getElementById('customer-submit-btn');

        if (form && btn) {
            form.addEventListener('submit', function () {
                if (btn.disabled) {
                    return false;
                }
                btn.disabled = true;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = `
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite; margin-right: 0.3rem;">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                    </svg>
                    <span>Guardando...</span>
                `;
                setTimeout(function () {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }, 8000);
            });
        }
    })();
</script>
<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush
