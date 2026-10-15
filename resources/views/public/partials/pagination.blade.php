@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="is-disabled" aria-disabled="true">&larr; Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr; Anterior</a>
        @endif

        <span class="pagination-current">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente &rarr;</a>
        @else
            <span class="is-disabled" aria-disabled="true">Siguiente &rarr;</span>
        @endif
    </nav>
@endif
