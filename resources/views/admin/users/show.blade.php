@extends('layouts.admin')

@section('title', 'Usuario: '.$user->name)

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
    $roleDescription = match ($roleName) {
        'Dueño' => 'Acceso total al sistema, incluyendo usuarios, configuración y autorizaciones.',
        'Gerente' => 'Supervisa la operación, inventario, clientes y reportes del negocio.',
        'Vendedor' => 'Enfocado en mostrador: ventas, apartados, clientes, inventario y caja.',
        default => 'Sin un rol asignado actualmente.',
    };
    $isSelf = $user->id === auth()->id();
    $permissionNames = $permissions->pluck('name')->all();
    $permissionLabels = [
        'sales.create' => 'Registrar ventas',
        'sales.void' => 'Anular ventas',
        'layaways.manage' => 'Gestionar apartados',
        'purchases.create' => 'Registrar compras',
        'purchases.approve' => 'Aprobar compras',
        'inventory.view' => 'Consultar inventario',
        'inventory.adjust' => 'Ajustar inventario',
        'products.edit_price' => 'Editar precios',
        'metal_price.override' => 'Modificar precio de metal',
        'cash.open' => 'Abrir caja',
        'cash.close' => 'Cerrar caja',
        'customers.manage' => 'Gestionar clientes',
        'reports.view' => 'Ver reportes',
        'users.manage' => 'Gestionar usuarios',
        'settings.manage' => 'Administrar configuración',
    ];
    $permissionGroups = [
        'Mostrador y ventas' => ['sales.create', 'sales.void', 'layaways.manage', 'customers.manage', 'cash.open', 'cash.close'],
        'Inventario y compras' => ['purchases.create', 'purchases.approve', 'inventory.view', 'inventory.adjust', 'products.edit_price', 'metal_price.override'],
        'Administración' => ['reports.view', 'users.manage', 'settings.manage'],
    ];
@endphp

@section('content')
<style>
    .usr-show-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 0;
        overflow: hidden;
    }

    .usr-show-page .alert {
        flex-shrink: 0;
        margin-bottom: 0;
        padding: 0.5rem 0.85rem;
    }

    .usr-show-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-shrink: 0;
        padding: 0.85rem 1.1rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .usr-show-hero-main {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        min-width: 0;
    }

    .usr-show-avatar {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--color-noche);
        color: var(--color-hueso);
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        box-shadow: 0 2px 4px rgba(18, 22, 31, 0.1);
    }

    .usr-show-kicker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.25rem;
    }

    .usr-show-title {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: clamp(1.4rem, 2vw, 1.8rem);
        font-weight: 500;
        line-height: 1.15;
    }

    .usr-show-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem 0.9rem;
        margin-top: 0.35rem;
        color: var(--color-piedra);
        font-size: 0.76rem;
    }

    .usr-show-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .usr-show-meta strong {
        color: var(--color-noche);
    }

    .usr-show-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }

    .usr-show-actions .btn {
        height: 34px;
        padding: 0 0.85rem;
        font-size: 0.8rem;
    }

    .usr-show-actions .btn-danger-outline {
        border-color: rgba(196, 30, 58, 0.32);
        color: var(--color-error);
    }

    .usr-show-actions .btn-danger-outline:hover {
        border-color: var(--color-error);
        background: #FFF5F5;
        color: var(--color-error);
    }

    .usr-show-actions .btn-success-outline {
        border-color: #C8E6C9;
        color: #2E7D32;
    }

    .usr-show-actions .btn-success-outline:hover {
        border-color: #2E7D32;
        background: #EDF7EE;
        color: #2E7D32;
    }

    .usr-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.28rem;
        padding: 0.16rem 0.5rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        font-size: 0.67rem;
        font-weight: 600;
        line-height: 1.45;
    }

    .usr-badge.is-active {
        border-color: #C8E6C9;
        background: #EDF7EE;
        color: #2E7D32;
    }

    .usr-badge.is-inactive {
        border-color: #E0B4B4;
        background: #FDEDED;
        color: var(--color-error);
    }

    .usr-badge.is-owner {
        border-color: rgba(184, 154, 94, 0.28);
        background: rgba(184, 154, 94, 0.15);
        color: #8A6D2F;
    }

    .usr-badge.is-manager {
        border-color: #C9DDF0;
        background: #EBF3FB;
        color: #205493;
    }

    .usr-badge.is-seller {
        border-color: var(--color-linea);
        background: #F1F0EB;
        color: var(--color-piedra);
    }

    .usr-show-stats {
        display: grid;
        flex-shrink: 0;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.6rem;
    }

    .usr-show-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        min-width: 0;
        padding: 0.6rem 0.85rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 5px;
    }

    .usr-show-stat-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .usr-show-stat-label {
        display: block;
        color: var(--color-piedra);
        font-size: 0.63rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .usr-show-stat-value {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-noche);
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .usr-show-grid {
        display: grid;
        flex: 1;
        grid-template-columns: minmax(0, 1.5fr) minmax(320px, 1fr);
        gap: 0.8rem;
        min-height: 0;
        overflow: hidden;
    }

    .usr-show-column {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        min-width: 0;
        min-height: 0;
        padding-right: 0.15rem;
        overflow-y: auto;
        scrollbar-color: #D9D2C3 transparent;
        scrollbar-width: thin;
    }

    .usr-show-column::-webkit-scrollbar { width: 6px; }
    .usr-show-column::-webkit-scrollbar-thumb { border-radius: 999px; background: #D9D2C3; }
    .usr-show-column::-webkit-scrollbar-track { background: transparent; }

    .usr-show-card {
        flex-shrink: 0;
        overflow: hidden;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .usr-show-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        padding: 0.7rem 1rem;
        border-bottom: 1px solid var(--color-linea);
        background: #FCFBF8;
    }

    .usr-show-card-header h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.02rem;
        font-weight: 500;
    }

    .usr-show-card-header p {
        margin: 0.08rem 0 0;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .usr-show-card-body {
        padding: 0.9rem 1rem;
    }

    .usr-data-list {
        display: grid;
        gap: 0.7rem;
    }

    .usr-data-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.7rem;
        border-bottom: 1px dashed var(--color-linea);
    }

    .usr-data-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .usr-data-label {
        color: var(--color-piedra);
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .usr-data-value {
        color: var(--color-noche);
        font-size: 0.82rem;
        font-weight: 500;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .usr-data-value.is-muted {
        color: var(--color-piedra);
        font-weight: 400;
        font-style: italic;
    }

    .usr-role-summary {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.7rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: #FCFBF8;
    }

    .usr-role-summary-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--color-noche);
        color: var(--color-hueso);
    }

    .usr-role-summary strong {
        display: block;
        color: var(--color-noche);
        font-size: 0.84rem;
    }

    .usr-role-summary p {
        margin: 0.12rem 0 0;
        color: var(--color-piedra);
        font-size: 0.72rem;
        line-height: 1.4;
    }

    .usr-permission-group + .usr-permission-group {
        margin-top: 0.8rem;
        padding-top: 0.8rem;
        border-top: 1px dashed var(--color-linea);
    }

    .usr-permission-group h3 {
        margin: 0 0 0.4rem;
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .usr-permission-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .usr-permission {
        display: inline-flex;
        align-items: center;
        gap: 0.28rem;
        padding: 0.22rem 0.5rem;
        border: 1px solid var(--color-linea);
        border-radius: 999px;
        background: #FCFBF8;
        color: var(--color-noche);
        font-size: 0.7rem;
    }

    .usr-permission svg {
        color: #2E7D32;
        flex-shrink: 0;
    }

    .usr-empty-note {
        margin: 0;
        color: var(--color-piedra);
        font-size: 0.76rem;
        font-style: italic;
    }

    .usr-activity-list {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .usr-activity-item {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding-bottom: 0.55rem;
        border-bottom: 1px dashed var(--color-linea);
    }

    .usr-activity-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .usr-activity-dot {
        flex-shrink: 0;
        width: 7px;
        height: 7px;
        margin-top: 0.35rem;
        border-radius: 50%;
        background: var(--color-dorado);
    }

    .usr-activity-body {
        min-width: 0;
    }

    .usr-activity-body strong {
        display: block;
        color: var(--color-noche);
        font-size: 0.76rem;
        font-weight: 500;
        line-height: 1.35;
    }

    .usr-activity-meta {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.68rem;
    }

    .usr-security-note {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.55rem 0.65rem;
        border: 1px solid #E6DFC9;
        border-radius: 4px;
        background: #FBF6EA;
        color: #7A5E1F;
        font-size: 0.72rem;
        line-height: 1.4;
    }

    @media (max-width: 1100px) {
        .usr-show-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1024px) {
        body.full-viewport { overflow: auto; }
        .full-viewport .main-wrapper { height: auto; overflow: visible; }
        .full-viewport .content-area { display: block; overflow: visible; }
        .usr-show-page { height: auto; overflow: visible; }
        .usr-show-grid { grid-template-columns: 1fr; overflow: visible; }
        .usr-show-column { overflow: visible; }
    }

    @media (max-width: 700px) {
        .usr-show-hero { flex-direction: column; align-items: flex-start; }
        .usr-show-actions { width: 100%; }
        .usr-show-actions .btn { flex: 1; justify-content: center; }
        .usr-show-stats { grid-template-columns: 1fr; }
        .usr-data-row { flex-direction: column; gap: 0.2rem; }
        .usr-data-value { text-align: left; }
    }
</style>

<div class="usr-show-page">
    <header class="usr-show-hero">
        <div class="usr-show-hero-main">
            <span class="usr-show-avatar" aria-hidden="true">{{ $initials ?: 'US' }}</span>
            <div>
                <div class="usr-show-kicker">
                    <span class="usr-badge {{ $roleTone }}">{{ $roleName }}</span>
                    <span class="usr-badge {{ $user->is_active ? 'is-active' : 'is-inactive' }}">
                        {{ $user->is_active ? 'Cuenta activa' : 'Cuenta inactiva' }}
                    </span>
                    @if ($isSelf)
                        <span class="usr-badge">Tu cuenta</span>
                    @endif
                </div>
                <h1 class="usr-show-title">{{ $user->name }}</h1>
                <div class="usr-show-meta">
                    <span>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 4h16v16H4z"></path>
                            <path d="m4 6 8 6 8-6"></path>
                        </svg>
                        {{ $user->email }}
                    </span>
                    <span>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l7-4 7 4v14"></path>
                            <path d="M9 21v-6h6v6"></path>
                        </svg>
                        <strong>{{ $user->store?->name ?? 'Sin sucursal' }}</strong>
                    </span>
                    <span>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 8v4l2.5 2.5"></path>
                        </svg>
                        {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Sin ingresos registrados' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="usr-show-actions">
            @can('users.manage')
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                    <span>Editar</span>
                </a>

                @if (! $isSelf)
                    <form action="{{ route('admin.users.toggle_status', $user) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas {{ $user->is_active ? 'desactivar' : 'activar' }} la cuenta de &quot;{{ $user->name }}&quot;?');">
                        @csrf
                        <button type="submit" class="btn btn-outline {{ $user->is_active ? 'btn-danger-outline' : 'btn-success-outline' }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                <line x1="12" y1="2" x2="12" y2="12"></line>
                            </svg>
                            <span>{{ $user->is_active ? 'Desactivar' : 'Activar' }}</span>
                        </button>
                    </form>
                @endif
            @endcan

            <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver</span>
            </a>
        </div>
    </header>

    @include('admin.partials.alerts')

    <div class="usr-show-stats">
        <div class="usr-show-stat">
            <div>
                <span class="usr-show-stat-label">Rol</span>
                <strong class="usr-show-stat-value">{{ $roleName }}</strong>
            </div>
            <span class="usr-show-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
            </span>
        </div>

        <div class="usr-show-stat">
            <div>
                <span class="usr-show-stat-label">Estado</span>
                <strong class="usr-show-stat-value">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</strong>
            </div>
            <span class="usr-show-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </span>
        </div>

        <div class="usr-show-stat">
            <div>
                <span class="usr-show-stat-label">Sucursal</span>
                <strong class="usr-show-stat-value">{{ $user->store?->name ?? 'Sin sucursal' }}</strong>
            </div>
            <span class="usr-show-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18"></path>
                    <path d="M5 21V7l7-4 7 4v14"></path>
                    <path d="M9 21v-6h6v6"></path>
                </svg>
            </span>
        </div>

        <div class="usr-show-stat">
            <div>
                <span class="usr-show-stat-label">Último acceso</span>
                <strong class="usr-show-stat-value">{{ $user->last_login_at?->format('d/m/Y') ?? 'Sin ingresos' }}</strong>
            </div>
            <span class="usr-show-stat-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 8v4l2.5 2.5"></path>
                </svg>
            </span>
        </div>
    </div>

    <div class="usr-show-grid">
        <div class="usr-show-column">
            <section class="usr-show-card">
                <header class="usr-show-card-header">
                    <div>
                        <h2>Datos del colaborador</h2>
                        <p>Información general de la cuenta</p>
                    </div>
                </header>
                <div class="usr-show-card-body">
                    <div class="usr-data-list">
                        <div class="usr-data-row">
                            <span class="usr-data-label">Nombre</span>
                            <span class="usr-data-value">{{ $user->name }}</span>
                        </div>
                        <div class="usr-data-row">
                            <span class="usr-data-label">Correo</span>
                            <span class="usr-data-value">{{ $user->email }}</span>
                        </div>
                        <div class="usr-data-row">
                            <span class="usr-data-label">Sucursal</span>
                            <span class="usr-data-value {{ $user->store ? '' : 'is-muted' }}">{{ $user->store?->name ?? 'Sin sucursal asignada' }}</span>
                        </div>
                        <div class="usr-data-row">
                            <span class="usr-data-label">Alta en el sistema</span>
                            <span class="usr-data-value">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                        <div class="usr-data-row">
                            <span class="usr-data-label">Última actualización</span>
                            <span class="usr-data-value">{{ $user->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                        <div class="usr-data-row">
                            <span class="usr-data-label">Último acceso</span>
                            <span class="usr-data-value {{ $user->last_login_at ? '' : 'is-muted' }}">
                                {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Sin ingresos registrados' }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="usr-show-card">
                <header class="usr-show-card-header">
                    <div>
                        <h2>Actividad reciente</h2>
                        <p>Movimientos registrados sobre esta cuenta</p>
                    </div>
                </header>
                <div class="usr-show-card-body">
                    @if ($recentActivity->isEmpty())
                        <p class="usr-empty-note">Todavía no hay actividad registrada para este colaborador.</p>
                    @else
                        <div class="usr-activity-list">
                            @foreach ($recentActivity as $activity)
                                <div class="usr-activity-item">
                                    <span class="usr-activity-dot" aria-hidden="true"></span>
                                    <div class="usr-activity-body">
                                        <strong>{{ $activity->description }}</strong>
                                        <span class="usr-activity-meta">
                                            {{ $activity->created_at?->format('d/m/Y H:i') }}
                                            @if ($activity->causer)
                                                · por {{ $activity->causer->name }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="usr-show-column">
            <section class="usr-show-card">
                <header class="usr-show-card-header">
                    <div>
                        <h2>Acceso y permisos</h2>
                        <p>Acciones habilitadas por su rol</p>
                    </div>
                </header>
                <div class="usr-show-card-body">
                    <div class="usr-role-summary">
                        <span class="usr-role-summary-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                        </span>
                        <div>
                            <strong>{{ $roleName }}</strong>
                            <p>{{ $roleDescription }}</p>
                        </div>
                    </div>

                    @php($hasPermissions = false)
                    @foreach ($permissionGroups as $groupName => $groupPermissions)
                        @php($visiblePermissions = array_values(array_filter($groupPermissions, fn ($permission) => in_array($permission, $permissionNames, true))))
                        @if (count($visiblePermissions) > 0)
                            @php($hasPermissions = true)
                            <div class="usr-permission-group">
                                <h3>{{ $groupName }}</h3>
                                <div class="usr-permission-list">
                                    @foreach ($visiblePermissions as $permission)
                                        <span class="usr-permission">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            {{ $permissionLabels[$permission] ?? $permission }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @unless ($hasPermissions)
                        <p class="usr-empty-note" style="margin-top: 0.8rem;">Este colaborador no tiene permisos asignados.</p>
                    @endunless
                </div>
            </section>

            <section class="usr-show-card">
                <header class="usr-show-card-header">
                    <div>
                        <h2>Seguridad</h2>
                        <p>Credenciales y estado de la cuenta</p>
                    </div>
                </header>
                <div class="usr-show-card-body">
                    <div class="usr-data-list">
                        <div class="usr-data-row">
                            <span class="usr-data-label">Contraseña</span>
                            <span class="usr-data-value">Protegida</span>
                        </div>
                        <div class="usr-data-row">
                            <span class="usr-data-label">PIN de supervisor</span>
                            <span class="usr-data-value {{ $user->supervisor_pin ? '' : 'is-muted' }}">
                                {{ $user->supervisor_pin ? 'Configurado' : 'No configurado' }}
                            </span>
                        </div>
                        <div class="usr-data-row">
                            <span class="usr-data-label">Estado de la cuenta</span>
                            <span class="usr-data-value">{{ $user->is_active ? 'Puede iniciar sesión' : 'Acceso bloqueado' }}</span>
                        </div>
                    </div>

                    @if ($isSelf)
                        <div class="usr-security-note" style="margin-top: 0.8rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M12 8v4"></path>
                                <path d="M12 16h.01"></path>
                            </svg>
                            <span>Tu propia cuenta no puede desactivarse mientras mantengas una sesión activa.</span>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
