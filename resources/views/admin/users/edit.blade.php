@extends('layouts.admin')

@section('title', 'Editar Usuario · '.$user->name)

@section('body_class', 'full-viewport')

@php
    $initials = collect(preg_split('/\s+/', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $roleName = $user->roles->first()?->name ?? 'Sin rol';
    $roleTone = match ($roleName) {
        'Dueño' => 'is-owner',
        'Gerente' => 'is-manager',
        default => 'is-seller',
    };
@endphp

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

    .usr-hero-avatar {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--color-noche);
        color: var(--color-hueso);
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
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
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem 0.6rem;
        margin: 0.15rem 0 0;
        color: var(--color-piedra);
        font-size: 0.76rem;
    }

    .usr-hero p strong {
        color: var(--color-noche);
    }

    .usr-mini-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.28rem;
        padding: 0.14rem 0.45rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .usr-mini-badge.is-active {
        border-color: #C8E6C9;
        background: #EDF7EE;
        color: #2E7D32;
    }

    .usr-mini-badge.is-inactive {
        border-color: #E0B4B4;
        background: #FDEDED;
        color: var(--color-error);
    }

    .usr-mini-badge.is-owner {
        border-color: rgba(184, 154, 94, 0.28);
        background: rgba(184, 154, 94, 0.15);
        color: #8A6D2F;
    }

    .usr-mini-badge.is-manager {
        border-color: #C9DDF0;
        background: #EBF3FB;
        color: #205493;
    }

    .usr-mini-badge.is-seller {
        border-color: var(--color-linea);
        background: #F1F0EB;
        color: var(--color-piedra);
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
            <span class="usr-hero-avatar" aria-hidden="true">{{ $initials ?: 'US' }}</span>
            <div>
                <h1>Editar colaborador</h1>
                <p>
                    <strong>{{ $user->name }}</strong>
                    <span class="usr-mini-badge {{ $roleTone }}">{{ $roleName }}</span>
                    <span class="usr-mini-badge {{ $user->is_active ? 'is-active' : 'is-inactive' }}">
                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="usr-hero-actions">
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Ver perfil</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Directorio</span>
            </a>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="usr-form-scrollable">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['user' => $user])
        </form>
    </div>
</div>
@endsection
