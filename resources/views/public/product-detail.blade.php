@extends('layouts.public')

@section('title', $product->name.' — Joyería Web')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? $product->name), 150))

@section('content')
    <section class="section">
        <div class="container">
            <p class="muted" style="margin-bottom: 1.5rem;">
                <a href="{{ route('public.catalog') }}" style="text-decoration: none;">&larr; Volver al catálogo</a>
            </p>

            <div class="detail-layout">
                <div data-animate>
                    @if ($product->photos->isNotEmpty())
                        <div class="swiper js-gallery-swiper gallery-swiper">
                            <div class="swiper-wrapper">
                                @foreach ($product->photos as $photo)
                                    <div class="swiper-slide">
                                        <div class="swiper-zoom-container">
                                            <img src="{{ $photo->url }}" alt="{{ $product->name }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>
                    @else
                        <div class="gallery-swiper" style="aspect-ratio: 1 / 1; display: flex; align-items: center; justify-content: center;">
                            <span class="product-media-placeholder" style="font-size: 4rem;">{{ mb_substr($product->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>

                <div data-animate>
                    <span class="eyebrow">{{ $product->category?->name }}</span>
                    <h1 class="section-title" style="font-size: 2.4rem;">{{ $product->name }}</h1>

                    <p class="product-price" style="font-size: 2rem; margin-top: 0.5rem;">
                        $ {{ number_format((float) $product->price, 2) }}
                    </p>

                    @if ($product->description)
                        <p class="section-lead" style="margin-top: 1.25rem;">{{ $product->description }}</p>
                    @endif

                    <dl class="spec-list">
                        <li>
                            <span class="spec-label">Metal</span>
                            <span class="spec-value">{{ $product->metal?->name }}</span>
                        </li>
                        @if ($product->purity)
                            <li>
                                <span class="spec-label">Ley / Pureza</span>
                                <span class="spec-value">{{ $product->purity->label }}</span>
                            </li>
                        @endif
                        <li>
                            <span class="spec-label">Peso</span>
                            <span class="spec-value">{{ rtrim(rtrim(number_format((float) $product->weight_g, 3), '0'), '.') }} g</span>
                        </li>
                        <li>
                            <span class="spec-label">Código</span>
                            <span class="spec-value">{{ $product->sku }}</span>
                        </li>
                        <li>
                            <span class="spec-label">Disponibilidad</span>
                            <span class="spec-value" style="color: var(--color-exito);">Disponible</span>
                        </li>
                    </dl>

                    <div class="hero-actions" style="justify-content: flex-start;">
                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" class="btn-gold" target="_blank" rel="noopener">Consultar por WhatsApp</a>
                        @endif
                        <a href="{{ route('public.store') }}" class="btn-outline">Visitar tienda</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
