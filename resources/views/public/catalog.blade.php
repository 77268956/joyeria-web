@extends('layouts.public')

@section('title', 'Catálogo — Joyería Web')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-head" data-animate>
                <span class="eyebrow">Catálogo</span>
                <h2 class="section-title">Nuestras <em>piezas</em></h2>
                <p class="section-lead">Explora joyería fina en oro y plata. Filtra por metal o categoría.</p>
            </div>

            <!-- Filtros -->
            <div class="filter-bar" data-animate>
                <a href="{{ route('public.catalog', array_filter(['category' => $selectedCategory])) }}"
                   class="filter-chip @if (! $selectedMetal) is-active @endif">Todos los metales</a>
                @foreach ($metals as $metal)
                    <a href="{{ route('public.catalog', array_filter(['metal' => $metal->code, 'category' => $selectedCategory])) }}"
                       class="filter-chip @if ($selectedMetal === $metal->code) is-active @endif">{{ $metal->name }}</a>
                @endforeach

                <span class="filter-group-label">Categorías</span>
                <a href="{{ route('public.catalog', array_filter(['metal' => $selectedMetal])) }}"
                   class="filter-chip @if (! $selectedCategory) is-active @endif">Todas</a>
                @foreach ($categories as $category)
                    <a href="{{ route('public.catalog', array_filter(['metal' => $selectedMetal, 'category' => $category->slug])) }}"
                       class="filter-chip @if ($selectedCategory === $category->slug) is-active @endif">{{ $category->name }}</a>
                @endforeach
            </div>

            @if ($products->isEmpty())
                <div class="empty-state" data-animate>
                    <p class="font-serif" style="font-size: 1.6rem;">No hay piezas que coincidan con tu búsqueda.</p>
                    <p class="muted">Prueba con otro filtro o visítanos en tienda.</p>
                </div>
            @else
                <div class="grid grid-3">
                    @foreach ($products as $product)
                        <div data-animate>
                            @include('public.partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $products->links('public.partials.pagination') }}
                </div>
            @endif
        </div>
    </section>
@endsection
