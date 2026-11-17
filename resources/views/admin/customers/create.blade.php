@extends('layouts.admin')

@section('title', 'Nuevo Cliente')

@section('body_class', 'full-viewport')

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

    .cust-hero-avatar-icon {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
        flex-shrink: 0;
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
        gap: 0.5rem;
    }

    .cust-hero-actions .btn {
        height: 32px;
        padding: 0 0.85rem;
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
        .cust-hero-actions .btn { width: 100%; justify-content: center; }
    }
</style>

<div class="cust-page-container">
    {{-- Hero header --}}
    <header class="cust-hero-card">
        <div class="cust-hero-left">
            <span class="cust-hero-avatar-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" y1="8" x2="19" y2="14"></line>
                    <line x1="22" y1="11" x2="16" y2="11"></line>
                </svg>
            </span>
            <div class="cust-hero-titles">
                <h1>Nuevo Cliente</h1>
                <p>Registro de datos de contacto e identificación para compras, ventas y apartados</p>
            </div>
        </div>

        <div class="cust-hero-actions">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver al directorio</span>
            </a>
        </div>
    </header>

    @include('admin.partials.alerts')

    {{-- Formulario --}}
    <div class="cust-form-scrollable">
        <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data" id="customer-create-form" style="display: flex; flex-direction: column; min-height: 100%;">
            @csrf
            @include('admin.customers._form')
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('customer-create-form');
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
