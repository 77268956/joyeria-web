@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')

@section('body_class', 'full-viewport users-body')

@section('content')
@php
    $hasFilters = request()->hasAny(['search', 'role', 'status']);
@endphp

<style>
    body.users-body .content-area {
        padding-top: 1.1rem;
        padding-bottom: 1.1rem;
    }

    .users-page {
        gap: 0.7rem;
    }

    .users-header {
        margin-bottom: 0;
        align-items: flex-end;
    }

    .users-header .page-title {
        font-size: 1.65rem;
        line-height: 1.1;
    }

    .users-header .page-subtitle {
        margin-top: 0.15rem;
        font-size: 0.84rem;
    }

    .users-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.55rem;
    }

    .users-stat {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        min-width: 0;
        padding: 0.6rem 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .users-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: rgba(184, 154, 94, 0.12);
        color: var(--color-dorado);
    }

    .users-stat-icon.is-success {
        background-color: #EDF7EE;
        color: #2E7D32;
    }

    .users-stat-icon.is-muted {
        background-color: #F1F0EB;
        color: var(--color-piedra);
    }

    .users-stat-icon.is-info {
        background-color: #EBF3FB;
        color: #205493;
    }

    .users-stat-body {
        min-width: 0;
    }

    .users-stat-label {
        display: block;
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .users-stat-value {
        display: block;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.2rem;
        font-weight: 600;
        line-height: 1.1;
    }

    .users-filter-card {
        padding: 0.55rem 0.75rem;
    }

    .users-filter-form {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .users-filter-field {
        flex: 1 1 130px;
        min-width: 0;
    }

    .users-filter-field--search {
        flex: 2 1 220px;
    }

    .users-filter-field label {
        display: block;
        margin-bottom: 0.15rem;
        color: var(--color-piedra);
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .users-filter-field .form-control,
    .users-filter-field .form-select {
        height: 34px;
        font-size: 0.8rem;
    }

    .users-search {
        position: relative;
    }

    .users-search svg {
        position: absolute;
        top: 50%;
        left: 0.65rem;
        color: var(--color-piedra);
        pointer-events: none;
        transform: translateY(-50%);
    }

    .users-search .form-control {
        padding-left: 2rem;
    }

    .users-filter-actions {
        display: flex;
        flex: 0 0 auto;
        gap: 0.5rem;
    }

    .users-filter-actions .btn {
        height: 34px;
    }

    .users-table-card {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
        padding: 0;
    }

    .paginated-page > .card.users-table-card {
        flex: 1 1 auto;
        min-height: 0;
    }

    .users-table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
        gap: 1rem;
        padding: 0.65rem 0.9rem;
        border-bottom: 1px solid var(--color-linea);
        background: linear-gradient(180deg, rgba(243, 239, 232, 0.55), rgba(255, 255, 255, 0));
    }

    .users-table-title {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        min-width: 0;
    }

    .users-table-title h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.02rem;
    }

    .users-count,
    .users-filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.24rem 0.55rem;
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }

    .users-count {
        border: 1px solid var(--color-linea);
        background-color: var(--color-blanco);
        color: var(--color-piedra);
    }

    .users-filter-chip {
        border: 1px solid rgba(184, 154, 94, 0.28);
        background-color: rgba(184, 154, 94, 0.12);
        color: #8A6D2F;
        text-transform: uppercase;
    }

    .users-table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
    }

    .users-table {
        width: 100%;
        min-width: 860px;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.86rem;
    }

    .users-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        padding: 0.75rem 0.9rem;
        border-bottom: 1px solid var(--color-linea);
        background-color: var(--color-crema);
        color: var(--color-noche);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .users-table tbody td {
        padding: 0.7rem 0.9rem;
        border-bottom: 1px solid var(--color-linea);
        color: var(--color-noche);
        vertical-align: middle;
    }

    .users-table tbody tr {
        transition: background-color 0.15s;
    }

    .users-table tbody tr:hover {
        background-color: #FBF9F5;
    }

    .users-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .users-person {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 0;
    }

    .users-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border: 1px solid rgba(184, 154, 94, 0.45);
        border-radius: 50%;
        background-color: var(--color-noche);
        color: var(--color-dorado);
        font-family: var(--font-serif);
        font-size: 0.86rem;
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    .users-avatar.is-manager {
        border-color: #C9DDF0;
        background-color: #EBF3FB;
        color: #205493;
    }

    .users-avatar.is-seller {
        border-color: var(--color-linea);
        background-color: #F1EFEA;
        color: var(--color-piedra);
    }

    .users-person-body {
        min-width: 0;
    }

    .users-person-name {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--color-noche);
        font-weight: 600;
    }

    .users-person-email {
        max-width: 260px;
        overflow: hidden;
        color: var(--color-piedra);
        font-size: 0.78rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .users-you {
        padding: 0.08rem 0.34rem;
        border-radius: 3px;
        background-color: #EBF3FB;
        color: #205493;
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .users-role {
        display: inline-flex;
        align-items: center;
        padding: 0.24rem 0.55rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .users-role.is-owner {
        border-color: rgba(184, 154, 94, 0.28);
        background-color: rgba(184, 154, 94, 0.15);
        color: #8A6D2F;
    }

    .users-role.is-manager {
        border-color: #C9DDF0;
        background-color: #EBF3FB;
        color: #205493;
    }

    .users-role.is-seller {
        border-color: var(--color-linea);
        background-color: #F1F0EB;
        color: var(--color-piedra);
    }

    .users-store {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--color-piedra);
        font-size: 0.8rem;
    }

    .users-store svg {
        flex-shrink: 0;
        color: var(--color-dorado);
    }

    .users-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.76rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .users-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .users-status.is-active {
        color: #2E7D32;
    }

    .users-status.is-active .users-dot {
        background-color: #2E7D32;
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.12);
    }

    .users-status.is-inactive {
        color: #757575;
    }

    .users-status.is-inactive .users-dot {
        background-color: #9E9E9E;
        box-shadow: 0 0 0 3px rgba(117, 117, 117, 0.12);
    }

    .users-login-date {
        display: block;
        color: var(--color-noche);
        font-size: 0.8rem;
        font-variant-numeric: tabular-nums;
    }

    .users-login-relative {
        display: block;
        margin-top: 0.1rem;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .users-login-empty {
        color: var(--color-piedra);
        font-size: 0.8rem;
        font-style: italic;
    }

    .users-actions-cell {
        text-align: right;
    }

    .users-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.35rem;
    }

    .users-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background-color: var(--color-blanco);
        color: var(--color-piedra);
        cursor: pointer;
        text-decoration: none;
        transition: border-color 0.15s, background-color 0.15s, color 0.15s;
    }

    .users-action:hover {
        border-color: var(--color-dorado);
        background-color: rgba(184, 154, 94, 0.06);
        color: var(--color-dorado);
    }

    .users-action.is-danger:hover {
        border-color: #E0B4B4;
        background-color: #FDEDED;
        color: var(--color-error);
    }

    .users-action.is-success:hover {
        border-color: #C8E6C9;
        background-color: #EDF7EE;
        color: #2E7D32;
    }

    .users-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 3rem 1rem;
        color: var(--color-piedra);
        text-align: center;
    }

    .users-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin-bottom: 0.25rem;
        border-radius: 50%;
        background-color: rgba(184, 154, 94, 0.12);
        color: var(--color-dorado);
    }

    .users-empty strong {
        color: var(--color-noche);
        font-size: 0.95rem;
    }

    .users-empty span {
        font-size: 0.8rem;
    }

    .users-empty .btn {
        margin-top: 0.5rem;
    }

    .users-pagination {
        flex-shrink: 0;
        padding: 0.55rem 0.9rem;
        border-top: 1px solid var(--color-linea);
    }

    .users-modal .modal {
        max-width: 580px;
    }

    .users-modal .modal-body {
        max-height: calc(100vh - 10rem);
        overflow-y: auto;
    }

    .users-modal .modal-header h3 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1.2rem;
    }

    .users-modal-subtitle {
        margin-top: 0.15rem;
        color: var(--color-piedra);
        font-size: 0.78rem;
    }

    .users-form-section {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0.25rem 0 0.85rem;
        color: var(--color-piedra);
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .users-form-section::after {
        content: "";
        flex: 1;
        height: 1px;
        background-color: var(--color-linea);
    }

    .users-form-section:not(:first-child) {
        margin-top: 1.15rem;
    }

    .users-modal .form-group:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 1100px) {
        .users-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .users-table {
            display: block;
            min-width: 0;
        }

        .users-table thead {
            display: none;
        }

        .users-table tbody {
            display: block;
            padding: 0.75rem;
        }

        .users-table tbody tr {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.65rem 0.9rem;
            padding: 0.85rem;
            border: 1px solid var(--color-linea);
            border-radius: 6px;
            background-color: var(--color-blanco);
        }

        .users-table tbody tr + tr {
            margin-top: 0.65rem;
        }

        .users-table tbody td {
            padding: 0;
            border: 0;
        }

        .users-table tbody td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 0.2rem;
            color: var(--color-piedra);
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .users-table tbody td[data-label="Colaborador"] {
            grid-column: 1 / -1;
        }

        .users-table tbody td[data-label="Acciones"] {
            grid-column: 1 / -1;
            padding-top: 0.65rem;
            border-top: 1px dashed var(--color-linea);
            text-align: left;
        }

        .users-table tbody td[data-label="Acciones"]::before {
            display: none;
        }

        .users-table tbody tr:hover {
            background-color: var(--color-blanco);
        }

        .users-person-email {
            max-width: none;
        }

        .users-actions {
            justify-content: flex-start;
        }

        .users-table tbody tr.users-empty-row {
            display: block;
            padding: 0;
            border: 0;
            background-color: transparent;
        }

        .users-table tbody tr.users-empty-row + tr {
            margin-top: 0;
        }

        .users-table tbody tr.users-empty-row td::before {
            display: none;
        }

        .users-table .users-empty {
            padding: 2rem 0.5rem;
        }
    }

    @media (max-width: 720px) {
        .users-filter-field--search {
            flex-basis: 100%;
        }

        .users-filter-actions {
            width: 100%;
        }

        .users-filter-actions .btn {
            flex: 1;
        }

        .users-table-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 560px) {
        .users-stats {
            grid-template-columns: 1fr;
        }

        .users-table tbody tr {
            grid-template-columns: 1fr;
        }

        .users-table tbody td[data-label="Colaborador"],
        .users-table tbody td[data-label="Acciones"] {
            grid-column: auto;
        }

        .users-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .users-header .btn {
            width: 100%;
        }
    }
</style>

<div class="paginated-page users-page">
    <div class="page-header users-header">
        <div>
            <h1 class="page-title font-serif">Gestión de Usuarios</h1>
            <p class="page-subtitle">Administra colaboradores, roles y accesos del equipo</p>
        </div>
        @can('users.manage')
            <a href="{{ route('admin.users.create') }}" class="btn btn-gold">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Nuevo usuario</span>
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="2" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span style="font-weight: 500;">{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: var(--color-piedra);" aria-label="Cerrar aviso">✕</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <div>
                <div style="font-weight: 600; margin-bottom: 0.35rem;">Por favor corrige los siguientes errores:</div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="users-stats">
        <div class="users-stat">
            <span class="users-stat-icon" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </span>
            <div class="users-stat-body">
                <span class="users-stat-label">Colaboradores</span>
                <strong class="users-stat-value">{{ number_format($summary['total']) }}</strong>
            </div>
        </div>

        <div class="users-stat">
            <span class="users-stat-icon is-success" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </span>
            <div class="users-stat-body">
                <span class="users-stat-label">Activos</span>
                <strong class="users-stat-value">{{ number_format($summary['active']) }}</strong>
            </div>
        </div>

        <div class="users-stat">
            <span class="users-stat-icon is-muted" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M8 12h8"></path>
                </svg>
            </span>
            <div class="users-stat-body">
                <span class="users-stat-label">Inactivos</span>
                <strong class="users-stat-value">{{ number_format($summary['inactive']) }}</strong>
            </div>
        </div>

        <div class="users-stat">
            <span class="users-stat-icon is-info" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
            </span>
            <div class="users-stat-body">
                <span class="users-stat-label">Roles</span>
                <strong class="users-stat-value">{{ number_format($summary['roles']) }}</strong>
            </div>
        </div>
    </div>

    <div class="card users-filter-card">
        <form action="{{ route('admin.users.index') }}" method="GET" class="users-filter-form">
            <div class="users-filter-field users-filter-field--search">
                <label for="users-search">Buscar</label>
                <div class="users-search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="M21 21l-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        id="users-search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nombre o correo del colaborador"
                        class="form-control"
                    >
                </div>
            </div>

            <div class="users-filter-field">
                <label for="users-role">Rol</label>
                <select name="role" id="users-role" class="form-select">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="users-filter-field">
                <label for="users-status">Estado</label>
                <select name="status" id="users-status" class="form-select">
                    <option value="">Todos</option>
                    <option value="active" @selected(request('status') === 'active')>Activos</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactivos</option>
                </select>
            </div>

            <div class="users-filter-actions">
                <button type="submit" class="btn btn-dark">Filtrar</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card paginated-table-card users-table-card">
        <div class="users-table-header">
            <div class="users-table-title">
                <h2>Directorio de colaboradores</h2>
                <span class="users-count">
                    {{ number_format($users->total()) }} {{ $users->total() === 1 ? 'colaborador' : 'colaboradores' }}
                </span>
                @if ($hasFilters)
                    <span class="users-filter-chip">Filtros activos</span>
                @endif
            </div>
        </div>

        <div class="table-wrap paginated-table-wrap users-table-wrap">
            <table class="data-table users-table">
                <thead>
                    <tr>
                        <th scope="col">Colaborador</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Sucursal</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Último acceso</th>
                        <th scope="col" class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $roleName = $user->roles->first()?->name ?? 'Sin rol';
                            $roleTone = match ($roleName) {
                                'Dueño' => 'is-owner',
                                'Gerente' => 'is-manager',
                                default => 'is-seller',
                            };
                            $initials = collect(preg_split('/\s+/', trim($user->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->implode('');
                        @endphp
                        <tr class="users-row">
                            <td data-label="Colaborador">
                                <div class="users-person">
                                    <span class="users-avatar {{ $roleTone }}">{{ $initials }}</span>
                                    <div class="users-person-body">
                                        <div class="users-person-name">
                                            {{ $user->name }}
                                            @if ($user->id === auth()->id())
                                                <span class="users-you">Tú</span>
                                            @endif
                                        </div>
                                        <div class="users-person-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Rol">
                                <span class="users-role {{ $roleTone }}">{{ $roleName }}</span>
                            </td>

                            <td data-label="Sucursal">
                                <span class="users-store">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 21h18"></path>
                                        <path d="M5 21V7l7-4 7 4v14"></path>
                                        <path d="M9 21v-6h6v6"></path>
                                    </svg>
                                    {{ $user->store?->name ?? 'Principal' }}
                                </span>
                            </td>

                            <td data-label="Estado">
                                <span class="users-status {{ $user->is_active ? 'is-active' : 'is-inactive' }}">
                                    <span class="users-dot"></span>
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td data-label="Último acceso">
                                @if ($user->last_login_at)
                                    <span class="users-login-date">{{ $user->last_login_at->format('d/m/Y H:i') }}</span>
                                    <span class="users-login-relative">{{ $user->last_login_at->diffForHumans() }}</span>
                                @else
                                    <span class="users-login-empty">Sin ingresos</span>
                                @endif
                            </td>

                            <td data-label="Acciones" class="users-actions-cell">
                                <div class="users-actions">
                                    @can('users.manage')
                                        <a
                                            href="{{ route('admin.users.show', $user) }}"
                                            class="users-action"
                                            title="Ver perfil"
                                            aria-label="Ver perfil de {{ $user->name }}"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>

                                        <a
                                            href="{{ route('admin.users.edit', $user) }}"
                                            class="users-action"
                                            title="Editar usuario"
                                            aria-label="Editar a {{ $user->name }}"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                            </svg>
                                        </a>

                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.toggle_status', $user) }}" method="POST" onsubmit="return confirmStatusToggle(event, @js($user->name), {{ $user->is_active ? 'false' : 'true' }})">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="users-action {{ $user->is_active ? 'is-danger' : 'is-success' }}"
                                                    title="{{ $user->is_active ? 'Desactivar cuenta' : 'Activar cuenta' }}"
                                                    aria-label="{{ $user->is_active ? 'Desactivar' : 'Activar' }} a {{ $user->name }}"
                                                >
                                                    @if ($user->is_active)
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                                            <line x1="12" y1="2" x2="12" y2="12"></line>
                                                        </svg>
                                                    @else
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                                            <line x1="12" y1="2" x2="12" y2="12"></line>
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirmDelete(event, @js($user->name))">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="users-action is-danger"
                                                    title="Eliminar usuario"
                                                    aria-label="Eliminar a {{ $user->name }}"
                                                >
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="users-empty-row">
                            <td colspan="6">
                                <div class="users-empty">
                                    <span class="users-empty-icon" aria-hidden="true">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <line x1="19" y1="8" x2="19" y2="14"></line>
                                            <line x1="22" y1="11" x2="16" y2="11"></line>
                                        </svg>
                                    </span>
                                    <strong>No se encontraron colaboradores</strong>
                                    <span>Ajusta los filtros o registra un nuevo usuario para comenzar.</span>
                                    @if ($hasFilters)
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">Limpiar filtros</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="paginated-pagination users-pagination">
                {{ $users->links('vendor.pagination.admin') }}
            </div>
        @endif
    </div>
</div>

<script>
    function confirmStatusToggle(e, userName, willBeActive) {
        const actionText = willBeActive ? 'activar' : 'desactivar';

        return confirm(`¿Estás seguro de que deseas ${actionText} la cuenta de "${userName}"?`);
    }

    function confirmDelete(e, userName) {
        return confirm(`¿Estás seguro de que deseas eliminar al colaborador "${userName}"? Esta acción se registrará en la bitácora.`);
    }
</script>
@endsection
