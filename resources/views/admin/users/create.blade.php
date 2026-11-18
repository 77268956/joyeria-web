@extends('layouts.admin')

@section('title', 'Nuevo Usuario')

@section('body_class', 'full-viewport')

@section('content')
<style>
    .usr-page-container {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.7rem;
        min-height: 0;
        overflow: hidden;
    }

    .usr-page-container .alert {
        flex-shrink: 0;
        margin-bottom: 0;
        padding: 0.5rem 0.85rem;
    }

    .usr-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.9rem;
        flex-shrink: 0;
        padding: 0.7rem 1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .usr-hero-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .usr-hero-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .usr-hero h1 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.4rem;
        font-weight: 500;
        line-height: 1.15;
    }

    .usr-hero p {
        margin: 0.1rem 0 0;
        color: var(--color-piedra);
        font-size: 0.76rem;
    }

    .usr-hero-actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .usr-hero-actions .btn {
        height: 32px;
        padding: 0 0.8rem;
        font-size: 0.78rem;
    }

    .usr-form-scrollable {
        flex: 1;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        scrollbar-color: #D9D2C3 transparent;
        scrollbar-width: thin;
    }

    .usr-form-scrollable::-webkit-scrollbar { width: 6px; }
    .usr-form-scrollable::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .usr-form-scrollable::-webkit-scrollbar-track { background: transparent; }

    @media (max-width: 700px) {
        body.full-viewport { overflow: auto; }
        .full-viewport .main-wrapper { height: auto; overflow: visible; }
        .full-viewport .content-area { display: block; overflow: visible; }
        .usr-page-container { height: auto; overflow: visible; }
        .usr-hero { flex-direction: column; align-items: flex-start; }
        .usr-hero-actions { width: 100%; }
        .usr-hero-actions .btn { flex: 1; justify-content: center; }
    }
</style>

<div class="usr-page-container">
    <header class="usr-hero">
        <div class="usr-hero-main">
            <span class="usr-hero-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" y1="8" x2="19" y2="14"></line>
                    <line x1="22" y1="11" x2="16" y2="11"></line>
                </svg>
            </span>
            <div>
                <h1>Nuevo colaborador</h1>
                <p>Crea la cuenta y define su nivel de acceso al sistema</p>
            </div>
        </div>

        <div class="usr-hero-actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver al directorio</span>
            </a>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="usr-form-scrollable">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            @include('admin.users._form')
        </form>
    </div>
</div>
@endsection
