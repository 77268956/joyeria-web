@extends('layouts.public')

@section('title', 'Joyería Web — Joyería Fina & Metales Preciosos')

@section('content')
    <!-- Hero -->
    <section class="hero">
        @if ($hero?->primaryPhoto)
            <div class="hero-media">
                <img src="{{ $hero->primaryPhoto->url }}" alt="{{ $hero->name }}">
            </div>
        @endif

        <div class="hero-inner" data-animate>
            <span class="eyebrow">Colección &amp; Fina Joyería</span>
            <h1 class="hero-title">El arte de la orfebrería, la <em>nobleza</em> de los metales.</h1>
            <p class="hero-description">
                Descubre piezas de alta joyería en oro y plata de ley. Diseños atemporales, garantía de pureza y valuación experta certificada.
            </p>
            <div class="hero-actions">
                <a href="{{ route('public.catalog') }}" class="btn-gold">Explorar Catálogo</a>
                <a href="{{ route('public.sell_gold') }}" class="btn-outline">Cotizar mi Oro</a>
            </div>
        </div>
    </section>

    <!-- Piezas Destacadas -->
    @if ($featured->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head" data-animate>
                    <span class="eyebrow">Selección Curada</span>
                    <h2 class="section-title">Piezas <em>destacadas</em></h2>
                    <p class="section-lead">Una selección de nuestras joyas más finas, disponibles en tienda.</p>
                </div>

                <div class="swiper js-featured-swiper" data-per-view="3">
                    <div class="swiper-wrapper">
                        @foreach ($featured as $product)
                            <div class="swiper-slide">
                                @include('public.partials.product-card', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('public.catalog') }}" class="btn-outline">Ver todo el catálogo</a>
                </div>
            </div>
        </section>
    @endif

    <!-- Banner de Compra de Oro -->
    <section class="section-dark">
        <div class="container section" data-animate>
            <div class="section-head" style="max-width: 720px;">
                <span class="eyebrow">Compra de Metales</span>
                <h2 class="section-title" style="color: var(--color-hueso);">¿Deseas vender tus piezas de <em>oro</em> o plata?</h2>
                <p class="section-lead">
                    Te ofrecemos la mejor cotización basada directamente en el precio del mercado del día, con pesaje de precisión y pago transparente.
                </p>
                <div class="hero-actions mt-3">
                    <a href="{{ route('public.sell_gold') }}" class="btn-gold">Cotizar en Línea</a>
                    <a href="{{ route('public.store') }}" class="btn-outline" style="border-color: rgba(247,244,238,0.4); color: var(--color-hueso);">Visitar Sucursal</a>
                </div>
            </div>
        </div>
    </section>
@endsection
