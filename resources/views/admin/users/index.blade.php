@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Gestión de Usuarios</h1>
        <p class="page-subtitle">Administración de colaboradores, asignación de roles y control de acceso</p>
    </div>
    @can('users.manage')
        <button type="button" class="btn-primary" onclick="openCreateModal()" style="background-color: var(--color-dorado); color: var(--color-noche); border: none; padding: 0.65rem 1.4rem; border-radius: 4px; font-weight: 600; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; letter-spacing: 0.04em;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Nuevo Usuario</span>
        </button>
    @endcan
</div>

{{-- Mensajes flash de éxito y error --}}
@if (session('status'))
    <div style="background-color: rgba(184, 154, 94, 0.15); border-left: 4px solid var(--color-dorado); color: var(--color-noche); padding: 1rem 1.25rem; border-radius: 4px; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-dorado)" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span style="font-weight: 500;">{{ session('status') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: var(--color-piedra);">✕</button>
    </div>
@endif

@if ($errors->any())
    <div style="background-color: #FDEDED; border-left: 4px solid var(--color-error); color: #5F2120; padding: 1rem 1.25rem; border-radius: 4px; margin-bottom: 1.5rem;">
        <div style="font-weight: 600; margin-bottom: 0.35rem;">Por favor corrige los siguientes errores:</div>
        <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Filtros de búsqueda --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem 1.75rem;">
    <form action="{{ route('admin.users.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-piedra); margin-bottom: 0.35rem; font-weight: 500;">
                Buscar por Nombre o Correo
            </label>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Ej. Carlos Administrador, maria@..." 
                style="width: 100%; height: 38px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.88rem; outline: none;"
            >
        </div>

        <div style="min-width: 160px;">
            <label style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-piedra); margin-bottom: 0.35rem; font-weight: 500;">
                Rol de Usuario
            </label>
            <select name="role" style="width: 100%; height: 38px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.88rem; outline: none; background-color: var(--color-blanco);">
                <option value="">Todos los roles</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                        {{ $r->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="min-width: 140px;">
            <label style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-piedra); margin-bottom: 0.35rem; font-weight: 500;">
                Estado
            </label>
            <select name="status" style="width: 100%; height: 38px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.88rem; outline: none; background-color: var(--color-blanco);">
                <option value="">Todos</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" style="height: 38px; padding: 0 1.25rem; background-color: var(--color-noche); color: var(--color-hueso); border: none; border-radius: 4px; font-size: 0.85rem; font-weight: 500; cursor: pointer;">
                Filtrar
            </button>
            @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" style="height: 38px; padding: 0 1rem; border: 1px solid var(--color-linea); color: var(--color-piedra); border-radius: 4px; font-size: 0.85rem; display: inline-flex; align-items: center; text-decoration: none;">
                    Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Tabla de Usuarios --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
            <thead>
                <tr style="background-color: var(--color-crema); border-bottom: 1px solid var(--color-linea); color: var(--color-noche);">
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Colaborador</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Rol</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Sucursal</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Estado</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Último Acceso</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr style="border-bottom: 1px solid var(--color-linea); transition: background-color 0.15s;" onmouseover="this.style.backgroundColor='#FBF9F5'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 1rem 1.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.85rem;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background-color: var(--color-noche); color: var(--color-dorado); display: flex; align-items: center; justify-content: center; font-family: var(--font-serif); font-size: 1rem; font-weight: 600; border: 1px solid var(--color-dorado-claro);">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--color-noche);">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span style="font-size: 0.7rem; background-color: #EBF3FB; color: #205493; padding: 0.1rem 0.4rem; border-radius: 3px; margin-left: 0.35rem; font-weight: 500;">Tú</span>
                                        @endif
                                    </div>
                                    <div style="color: var(--color-piedra); font-size: 0.82rem;">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <td style="padding: 1rem 1.25rem;">
                            @php
                                $roleName = $user->roles->first()?->name ?? 'Sin Rol';
                                $roleBg = match($roleName) {
                                    'Dueño' => 'rgba(184, 154, 94, 0.15)',
                                    'Gerente' => '#EDF2F7',
                                    default => '#F1EFEA',
                                };
                                $roleColor = match($roleName) {
                                    'Dueño' => 'var(--color-dorado)',
                                    'Gerente' => '#2B6CB0',
                                    default => 'var(--color-piedra)',
                                };
                            @endphp
                            <span style="display: inline-block; padding: 0.25rem 0.65rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; border-radius: 4px; background-color: {{ $roleBg }}; color: {{ $roleColor }};">
                                {{ $roleName }}
                            </span>
                        </td>

                        <td style="padding: 1rem 1.25rem; color: var(--color-piedra);">
                            {{ $user->store?->name ?? 'Principal' }}
                        </td>

                        <td style="padding: 1rem 1.25rem;">
                            @if($user->is_active)
                                <span style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; font-weight: 600; color: #2E7D32;">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #2E7D32;"></span>
                                    Activo
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; font-weight: 600; color: #757575;">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #757575;"></span>
                                    Inactivo
                                </span>
                            @endif
                        </td>

                        <td style="padding: 1rem 1.25rem; color: var(--color-piedra); font-size: 0.82rem;">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca ha ingresado' }}
                        </td>

                        <td style="padding: 1rem 1.25rem; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                @can('users.manage')
                                    <!-- Editar -->
                                    <button 
                                        type="button" 
                                        onclick="openEditModal({{ json_encode([
                                            'id' => $user->id,
                                            'name' => $user->name,
                                            'email' => $user->email,
                                            'role' => $user->roles->first()?->name ?? 'Vendedor',
                                            'store_id' => $user->store_id,
                                            'is_active' => $user->is_active,
                                        ]) }})"
                                        style="background: none; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0.35rem 0.6rem; color: var(--color-noche); cursor: pointer; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.3rem;"
                                        title="Editar usuario"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                        <span>Editar</span>
                                    </button>

                                    <!-- Alternar Estado -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle_status', $user) }}" method="POST" style="display: inline;" onsubmit="return confirmStatusToggle(event, '{{ $user->name }}', {{ $user->is_active ? 'false' : 'true' }})">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                style="background: none; border: 1px solid {{ $user->is_active ? '#E0B4B4' : '#C8E6C9' }}; border-radius: 4px; padding: 0.35rem 0.6rem; color: {{ $user->is_active ? '#C62828' : '#2E7D32' }}; cursor: pointer; font-size: 0.8rem;"
                                                title="{{ $user->is_active ? 'Desactivar cuenta' : 'Activar cuenta' }}"
                                            >
                                                {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>

                                        <!-- Eliminar -->
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirmDelete(event, '{{ $user->name }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                style="background: none; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0.35rem 0.5rem; color: var(--color-piedra); cursor: pointer; font-size: 0.8rem;"
                                                title="Eliminar usuario"
                                            >
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--color-piedra);">
                            No se encontraron colaboradores con los criterios seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--color-linea);">
            {{ $users->links() }}
        </div>
    @endif
</div>

{{-- MODAL: NUEVO USUARIO --}}
<div id="createModal" style="display: none; position: fixed; inset: 0; background-color: rgba(18, 22, 31, 0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background-color: var(--color-blanco); border-radius: 6px; width: 100%; max-width: 520px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 1rem;">
        <div style="padding: 1.25rem 1.5rem; background-color: var(--color-crema); border-bottom: 1px solid var(--color-linea); display: flex; align-items: center; justify-content: space-between;">
            <h3 class="font-serif" style="font-size: 1.25rem; margin: 0; color: var(--color-noche);">Registrar Nuevo Colaborador</h3>
            <button type="button" onclick="closeCreateModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--color-piedra);">✕</button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" style="padding: 1.5rem;">
            @csrf

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                    Nombre Completo *
                </label>
                <input type="text" name="name" required placeholder="Ej. Roberto Morales" style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                    Correo Electrónico *
                </label>
                <input type="email" name="email" required placeholder="colaborador@joyeriaweb.com" style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                        Rol de Acceso *
                    </label>
                    <select name="role" required style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none; background-color: var(--color-blanco);">
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                        Sucursal
                    </label>
                    <select name="store_id" style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none; background-color: var(--color-blanco);">
                        @foreach ($stores as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                        Contraseña *
                    </label>
                    <input type="password" name="password" required minlength="8" placeholder="••••••••" style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                        Confirmar Contraseña *
                    </label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="••••••••" style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.88rem; color: var(--color-noche); user-select: none;">
                    <input type="checkbox" name="is_active" value="1" checked style="accent-color: var(--color-dorado); width: 16px; height: 16px;">
                    <span>Usuario activo para inicio de sesión inmediato</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--color-linea); padding-top: 1.25rem;">
                <button type="button" onclick="closeCreateModal()" style="height: 40px; padding: 0 1.25rem; border: 1px solid var(--color-linea); border-radius: 4px; font-size: 0.88rem; background: none; cursor: pointer; color: var(--color-piedra);">
                    Cancelar
                </button>
                <button type="submit" style="height: 40px; padding: 0 1.5rem; background-color: var(--color-dorado); color: var(--color-noche); border: none; border-radius: 4px; font-size: 0.88rem; font-weight: 600; cursor: pointer;">
                    Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: EDITAR USUARIO --}}
<div id="editModal" style="display: none; position: fixed; inset: 0; background-color: rgba(18, 22, 31, 0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background-color: var(--color-blanco); border-radius: 6px; width: 100%; max-width: 520px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; margin: 1rem;">
        <div style="padding: 1.25rem 1.5rem; background-color: var(--color-crema); border-bottom: 1px solid var(--color-linea); display: flex; align-items: center; justify-content: space-between;">
            <h3 class="font-serif" style="font-size: 1.25rem; margin: 0; color: var(--color-noche);">Editar Colaborador</h3>
            <button type="button" onclick="closeEditModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--color-piedra);">✕</button>
        </div>

        <form id="editForm" method="POST" style="padding: 1.5rem;">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit_user_id" name="id">

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                    Nombre Completo *
                </label>
                <input type="text" id="edit_name" name="name" required style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                    Correo Electrónico *
                </label>
                <input type="email" id="edit_email" name="email" required style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                        Rol de Acceso *
                    </label>
                    <select id="edit_role" name="role" required style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none; background-color: var(--color-blanco);">
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-noche); margin-bottom: 0.4rem;">
                        Sucursal
                    </label>
                    <select id="edit_store_id" name="store_id" style="width: 100%; height: 40px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.9rem; outline: none; background-color: var(--color-blanco);">
                        @foreach ($stores as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="background-color: var(--color-crema); padding: 0.85rem; border-radius: 4px; margin-bottom: 1rem; border: 1px dashed var(--color-linea);">
                <span style="font-size: 0.78rem; color: var(--color-piedra); display: block; margin-bottom: 0.5rem;">
                    Dejar en blanco si no deseas cambiar la contraseña actual del usuario.
                </span>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <input type="password" name="password" minlength="8" placeholder="Nueva contraseña" style="width: 100%; height: 36px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.65rem; font-size: 0.85rem; outline: none;">
                    </div>
                    <div>
                        <input type="password" name="password_confirmation" minlength="8" placeholder="Confirmar nueva" style="width: 100%; height: 36px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.65rem; font-size: 0.85rem; outline: none;">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.88rem; color: var(--color-noche); user-select: none;">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" style="accent-color: var(--color-dorado); width: 16px; height: 16px;">
                    <span>Usuario activo</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--color-linea); padding-top: 1.25rem;">
                <button type="button" onclick="closeEditModal()" style="height: 40px; padding: 0 1.25rem; border: 1px solid var(--color-linea); border-radius: 4px; font-size: 0.88rem; background: none; cursor: pointer; color: var(--color-piedra);">
                    Cancelar
                </button>
                <button type="submit" style="height: 40px; padding: 0 1.5rem; background-color: var(--color-dorado); color: var(--color-noche); border: none; border-radius: 4px; font-size: 0.88rem; font-weight: 600; cursor: pointer;">
                    Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').style.display = 'flex';
    }

    function closeCreateModal() {
        document.getElementById('createModal').style.display = 'none';
    }

    function openEditModal(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_store_id').value = user.store_id || '';
        document.getElementById('edit_is_active').checked = Boolean(user.is_active);

        const form = document.getElementById('editForm');
        form.action = "{{ url('admin/usuarios') }}/" + user.id;

        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function confirmStatusToggle(e, userName, willBeActive) {
        const actionText = willBeActive ? 'activar' : 'desactivar';
        return confirm(`¿Estás seguro de que deseas ${actionText} la cuenta de "${userName}"?`);
    }

    function confirmDelete(e, userName) {
        return confirm(`¿Estás seguro de que deseas eliminar al colaborador "${userName}"? Esta acción se registrará en la bitácora.`);
    }

    // Cerrar modales con Escape o clic en fondo
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });

    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('createModal')) closeCreateModal();
        if (e.target === document.getElementById('editModal')) closeEditModal();
    });
</script>
@endsection
