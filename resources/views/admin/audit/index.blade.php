@extends('layouts.admin')

@section('title', 'Bitácora de Auditoría')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">Bitácora de Auditoría</h1>
        <p class="page-subtitle">Trazabilidad inmutable de acciones sensibles y cambios en el sistema</p>
    </div>
</div>

{{-- Filtros de búsqueda --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem 1.75rem;">
    <form action="{{ route('admin.audit.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-piedra); margin-bottom: 0.35rem; font-weight: 500;">
                Buscar en descripción
            </label>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Ej. Usuario creado, anuló venta..." 
                style="width: 100%; height: 38px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.88rem; outline: none;"
            >
        </div>

        <div style="min-width: 180px;">
            <label style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-piedra); margin-bottom: 0.35rem; font-weight: 500;">
                Módulo / Canal
            </label>
            <select name="log_name" style="width: 100%; height: 38px; border: 1px solid var(--color-linea); border-radius: 4px; padding: 0 0.75rem; font-size: 0.88rem; outline: none; background-color: var(--color-blanco);">
                <option value="">Todos los módulos</option>
                @foreach ($logNames as $name)
                    <option value="{{ $name }}" {{ request('log_name') === $name ? 'selected' : '' }}>
                        {{ ucfirst($name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" style="height: 38px; padding: 0 1.25rem; background-color: var(--color-noche); color: var(--color-hueso); border: none; border-radius: 4px; font-size: 0.85rem; font-weight: 500; cursor: pointer;">
                Filtrar
            </button>
            @if(request()->hasAny(['search', 'log_name']))
                <a href="{{ route('admin.audit.index') }}" style="height: 38px; padding: 0 1rem; border: 1px solid var(--color-linea); color: var(--color-piedra); border-radius: 4px; font-size: 0.85rem; display: inline-flex; align-items: center; text-decoration: none;">
                    Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Tabla de Auditoría --}}
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
            <thead>
                <tr style="background-color: var(--color-crema); border-bottom: 1px solid var(--color-linea); color: var(--color-noche);">
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Fecha y Hora</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Módulo</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Usuario Responsable</th>
                    <th style="padding: 0.85rem 1.25rem; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">Descripción del Evento</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr style="border-bottom: 1px solid var(--color-linea);">
                        <td style="padding: 0.85rem 1.25rem; color: var(--color-piedra); white-space: nowrap;">
                            {{ $activity->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td style="padding: 0.85rem 1.25rem;">
                            <span style="display: inline-block; padding: 0.2rem 0.55rem; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; border-radius: 3px; background-color: rgba(184, 154, 94, 0.12); color: var(--color-dorado);">
                                {{ $activity->log_name ?? 'Sistema' }}
                            </span>
                        </td>
                        <td style="padding: 0.85rem 1.25rem; font-weight: 500;">
                            @if($activity->causer)
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 26px; height: 26px; border-radius: 50%; background-color: var(--color-noche); color: var(--color-dorado); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold;">
                                        {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $activity->causer->name }}</span>
                                </div>
                            @else
                                <span style="color: var(--color-piedra); font-style: italic;">Sistema automático</span>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1.25rem; color: var(--color-noche);">
                            {{ $activity->description }}
                            @if(!empty($activity->properties) && count($activity->properties) > 0)
                                <div style="font-size: 0.75rem; color: var(--color-piedra); margin-top: 0.25rem; font-family: monospace;">
                                    {{ json_encode($activity->properties, JSON_UNESCAPED_UNICODE) }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 3rem; color: var(--color-piedra);">
                            No hay registros de auditoría que coincidan con la búsqueda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($activities->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--color-linea);">
            {{ $activities->links() }}
        </div>
    @endif
</div>
@endsection
