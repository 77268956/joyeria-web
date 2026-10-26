@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            @if ($paginator->onFirstPage())
                <span class="btn btn-outline btn-sm" style="opacity: 0.45; pointer-events: none;" aria-disabled="true">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">Anterior</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">Siguiente</a>
            @else
                <span class="btn btn-outline btn-sm" style="opacity: 0.45; pointer-events: none;" aria-disabled="true">Siguiente</span>
            @endif
        </div>

        <span class="muted" style="font-size: 0.82rem;">
            Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
            · {{ $paginator->total() }} {{ $paginator->total() === 1 ? 'registro' : 'registros' }}
        </span>
    </nav>
@endif
