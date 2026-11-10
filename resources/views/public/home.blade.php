@extends('layouts.public')

@section('title', 'Joyería Web — Joyería Fina & Metales Preciosos')

@section('meta_description', 'Joyería fina en oro y plata de ley. Descubre nuestra colección, cotiza tu oro en línea y visítanos en sucursal.')

@section('content')
    <!-- Hero -->
    @if ($heroSlides->isNotEmpty())
        <section class="hero-slider swiper js-hero-swiper">
            <div class="swiper-wrapper">
                @foreach ($heroSlides as $product)
                    <div class="swiper-slide">
                        <div class="hero-slide">
                            <div class="hero-slide-media">
                                <img src="{{ $product->primaryPhoto->url }}" alt="{{ $product->name }}">
                            </div>
                            <div class="hero-slide-overlay"></div>

                            <div class="container hero-slide-content">
                                <span class="eyebrow">{{ $product->category?->name ?? 'Pieza destacada' }}</span>
                                <h1 class="hero-slide-title">{{ $product->name }}</h1>
                                <p class="hero-slide-meta">
                                    {{ $product->purity?->label ?? $product->metal?->name }}
                                    &middot; {{ rtrim(rtrim(number_format((float) $product->weight_g, 3), '0'), '.') }} g
                                </p>
                                <p class="hero-slide-price">$ {{ number_format((float) $product->price, 2) }}</p>

                                <div class="hero-actions">
                                    <a href="{{ route('public.product', $product) }}" class="btn-gold">Ver pieza</a>
                                    <a href="{{ route('public.catalog') }}" class="btn-outline btn-on-dark">Explorar catálogo</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </section>
    @else
        <section class="hero">
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
    @endif

    <!-- Categorías -->
    @if ($categories->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head" data-animate>
                    <span class="eyebrow">Descubre nuestras joyas</span>
                    <h2 class="section-title">Explora por <em>categoría</em></h2>
                    <p class="section-lead">Encuentra la pieza que buscas navegando entre nuestras colecciones.</p>
                </div>

                <div class="grid grid-4">
                    @foreach ($categories as $category)
                        @php($categoryProduct = $categoryImages[$category->id] ?? null)
                        <a href="{{ route('public.catalog', ['category' => $category->slug]) }}" class="category-tile" data-animate>
                            <div class="category-tile-media">
                                @if ($categoryProduct?->primaryPhoto)
                                    <img src="{{ $categoryProduct->primaryPhoto->url }}" alt="{{ $category->name }}" loading="lazy">
                                @else
                                    <div class="category-tile-placeholder">{{ mb_substr($category->name, 0, 1) }}</div>
                                @endif
                            </div>
                            <div class="category-tile-body">
                                <h3 class="category-tile-name">{{ $category->name }}</h3>
                                <span class="category-tile-count">{{ $category->products_count }} {{ $category->products_count === 1 ? 'pieza' : 'piezas' }}</span>
                                <span class="category-tile-link">Ver más &rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Piezas Destacadas -->
    @if ($featured->isNotEmpty())
        <section class="section section-cream">
            <div class="container">
                <div class="section-head" data-animate>
                    <span class="eyebrow">Selección Curada</span>
                    <h2 class="section-title">Piezas <em>destacadas</em></h2>
                    <p class="section-lead">Una selección de nuestras joyas más finas, disponibles en tienda.</p>
                </div>

                <div class="grid grid-4">
                    @foreach ($featured as $product)
                        <div data-animate>
                            @include('public.partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('public.catalog') }}" class="btn-outline">Ver todo el catálogo</a>
                </div>
            </div>
        </section>
    @endif

    <!-- Piezas únicas -->
    <section class="section">
        <div class="container split story-split">
            <div class="story-media" data-animate>
                @if ($hero?->primaryPhoto)
                    <img src="{{ $hero->primaryPhoto->url }}" alt="{{ $hero->name }}">
                @else
                    <div class="story-media-placeholder">Joyería Fina</div>
                @endif

                @if ($goldPricePerGram)
                    <div class="story-price">
                        <span class="story-price-label">Oro hoy</span>
                        <span class="story-price-value">$ {{ number_format((float) $goldPricePerGram, 2) }}</span>
                        <span class="story-price-unit">/ g · 24k</span>
                    </div>
                @endif
            </div>

            <div data-animate>
                <span class="eyebrow">Piezas Únicas</span>
                <h2 class="section-title">Joyería con <em>historia</em></h2>
                <p class="section-lead">
                    En nuestra joyería la calidad y la autenticidad son prioridad. Seleccionamos cada pieza en oro y plata de ley para acompañarte en los momentos que importan.
                </p>

                <ul class="story-list">
                    <li>Piezas verificadas en ley y quilataje.</li>
                    <li>Diseños atemporales y ediciones seleccionadas.</li>
                    <li>Asesoría personalizada en tienda.</li>
                </ul>

                <div class="hero-actions" style="justify-content: flex-start;">
                    <a href="{{ route('public.store') }}" class="btn-gold">Visitar Sucursal</a>
                    <a href="{{ route('public.sell_gold') }}" class="btn-outline">Vender mi Oro</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Valores -->
    <section class="section section-cream">
        <div class="container">
            <div class="section-head" data-animate>
                <span class="eyebrow">Nuestra Promesa</span>
                <h2 class="section-title">¿Por qué nuestras <em>joyas</em>?</h2>
                <p class="section-lead">Descubre los valores que definen cada una de nuestras piezas y nuestra promesa contigo.</p>
            </div>

            <div class="grid grid-3">
                <article class="feature-card" data-animate>
                    <span class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3l2.4 5.2 5.6.7-4.2 3.9 1.1 5.6L12 15.8 7.1 18.4l1.1-5.6L4 8.9l5.6-.7z"/>
                        </svg>
                    </span>
                    <h3 class="feature-title">Calidad y autenticidad</h3>
                    <p class="feature-text">Compromiso prioritario con la autenticidad y la calidad, reflejado en piezas verificadas y con garantía.</p>
                </article>

                <article class="feature-card" data-animate>
                    <span class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 21s-7-4.35-7-10a7 7 0 0114 0c0 5.65-7 10-7 10z"/>
                            <circle cx="12" cy="11" r="2.6"/>
                        </svg>
                    </span>
                    <h3 class="feature-title">Exclusividad y prestigio</h3>
                    <p class="feature-text">Una selección cuidada de piezas que destacan por su distinción y su atención al detalle.</p>
                </article>

                <article class="feature-card" data-animate>
                    <span class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7l9-4 9 4-9 4z"/>
                            <path d="M3 7v6l9 4 9-4V7"/>
                            <path d="M12 11v10"/>
                        </svg>
                    </span>
                    <h3 class="feature-title">Diseño y seguridad</h3>
                    <p class="feature-text">Diseños originales y procesos transparentes para que compres con total confianza.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- Compra de Oro -->
    <section class="section-dark">
        <div class="container section">
            <div class="split">
                <div data-animate>
                    <span class="eyebrow">Compra de Metales</span>
                    <h2 class="section-title" style="color: var(--color-hueso);">¿Deseas vender tus piezas de <em>oro</em> o plata?</h2>
                    <p class="section-lead">
                        Te ofrecemos la mejor cotización basada directamente en el precio del mercado del día, con pesaje de precisión y pago transparente.
                    </p>

                    @if ($goldPricePerGram)
                        <div class="price-pill">
                            <span class="price-pill-label">Oro hoy</span>
                            <span class="price-pill-value">$ {{ number_format((float) $goldPricePerGram, 2) }} <small>/ g · 24k</small></span>
                        </div>
                    @endif

                    <div class="hero-actions mt-3" style="justify-content: flex-start;">
                        <a href="{{ route('public.sell_gold') }}" class="btn-gold">Cotizar en Línea</a>
                        <a href="{{ route('public.store') }}" class="btn-outline btn-on-dark">Visitar Sucursal</a>
                    </div>
                </div>

                <ol class="steps steps-on-dark" data-animate>
                    <li>
                        <span class="step-number">1</span>
                        <div>
                            <strong>Cotiza en línea</strong>
                            <p class="muted">Conoce el valor aproximado de tus piezas en segundos.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-number">2</span>
                        <div>
                            <strong>Verificamos en tienda</strong>
                            <p class="muted">Pesamos y confirmamos la ley con equipo certificado.</p>
                        </div>
                    </li>
                    <li>
                        <span class="step-number">3</span>
                        <div>
                            <strong>Recibe tu pago</strong>
                            <p class="muted">Pago inmediato y transparente, sin sorpresas.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <!-- Sucursal -->
    <section class="section">
        <div class="container">
            <div class="section-head" data-animate>
                <span class="eyebrow">Visítanos</span>
                <h2 class="section-title">Nuestra <em>sucursal</em></h2>
                <p class="section-lead">Te esperamos para asesorarte, valuar tus piezas y mostrarte nuestra colección.</p>
            </div>

            <div class="store-grid">
                <div data-animate>
                    <ul class="info-list">
                        <li>
                            <span class="info-icon">&#9737;</span>
                            <div>
                                <strong>Dirección</strong>
                                <p class="muted">{{ $publicStore?->address ?? 'Centro Histórico' }}</p>
                            </div>
                        </li>
                        <li>
                            <span class="info-icon">&#9742;</span>
                            <div>
                                <strong>Teléfono</strong>
                                <p class="muted">
                                    @if ($publicStore?->phone)
                                        <a href="tel:{{ $publicStore->phone }}" style="text-decoration: none;">{{ $publicStore->phone }}</a>
                                    @else
                                        Contáctanos por WhatsApp
                                    @endif
                                </p>
                            </div>
                        </li>
                        <li>
                            <span class="info-icon">&#9200;</span>
                            <div>
                                <strong>Horarios de atención</strong>
                                <p class="muted">Lunes a Viernes: 9:00 – 18:00<br>Sábado: 9:00 – 14:00<br>Domingo: cerrado</p>
                            </div>
                        </li>
                        @if ($publicSettings?->whatsapp_number)
                            <li>
                                <span class="info-icon">&#9990;</span>
                                <div>
                                    <strong>WhatsApp</strong>
                                    <p class="muted">
                                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $publicSettings->whatsapp_number) }}" target="_blank" rel="noopener" style="text-decoration: none;">{{ $publicSettings->whatsapp_number }}</a>
                                    </p>
                                </div>
                            </li>
                        @endif
                    </ul>

                    <div class="hero-actions mt-3" style="justify-content: flex-start;">
                        <a href="{{ route('public.contact') }}" class="btn-gold">Enviar mensaje</a>
                        <a href="{{ route('public.store') }}" class="btn-outline">Ver sucursal</a>
                    </div>
                </div>

                <div data-animate>
                    @if ($publicStore?->address)
                        <iframe
                            class="map-frame"
                            src="https://maps.google.com/maps?q={{ rawurlencode($publicStore->address) }}&output=embed"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Ubicación de la sucursal"
                        ></iframe>
                    @else
                        <div class="map-frame" style="display: flex; align-items: center; justify-content: center;">
                            <span class="muted">Mapa no disponible.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Llamado final -->
    <section class="section section-cream">
        <div class="container">
            <div class="cta-band" data-animate>
                <div>
                    <span class="eyebrow">Atención Personalizada</span>
                    <h2 class="section-title">Encuentra la pieza <em>perfecta</em></h2>
                    <p class="section-lead">Agenda una visita o escríbenos y te asesoramos para elegir la joya ideal.</p>
                </div>
                <div class="cta-actions">
                    <a href="{{ route('public.catalog') }}" class="btn-gold">Ver Catálogo</a>
                    <a href="{{ route('public.contact') }}" class="btn-outline">Contáctanos</a>
                </div>
            </div>
        </div>
    </section>
@endsection
