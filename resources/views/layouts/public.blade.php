<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Joyería fina en oro y plata, valuación experta y compra de metales preciosos.')">
    <title>@yield('title', 'Joyería Web — Joyería Fina & Metales Preciosos')</title>

    <!-- Google Fonts: Cormorant Garamond & Jost -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite('resources/js/public.js')
    @stack('styles')
</head>
<body>
    <!-- Barra de Navegación Pública -->
    <header class="public-navbar" id="public-navbar">
        <a href="{{ route('home') }}" class="navbar-brand">{{ $publicSettings?->system_name ?? 'Joyería Web' }}</a>

        <button type="button" class="navbar-toggle" id="navbar-toggle" aria-label="Abrir menú" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="navbar-menu" id="navbar-menu">
            <ul class="navbar-nav">
                <li><a href="{{ route('public.catalog') }}" class="navbar-link @if(request()->routeIs('public.catalog')) is-active @endif">Catálogo</a></li>
                <li><a href="{{ route('public.sell_gold') }}" class="navbar-link @if(request()->routeIs('public.sell_gold')) is-active @endif">Vender mi Oro</a></li>
                <li><a href="{{ route('public.store') }}" class="navbar-link @if(request()->routeIs('public.store')) is-active @endif">Sucursal</a></li>
                <li><a href="{{ route('public.contact') }}" class="navbar-link @if(request()->routeIs('public.contact')) is-active @endif">Contacto</a></li>
            </ul>

            <div class="navbar-access">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn-access">Ir al Panel &rarr;</a>
                @else
                    <a href="{{ route('login') }}" class="btn-access">Acceso Personal &rarr;</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <!-- Pie de Página -->
    <footer class="public-footer">
        <div class="footer-inner">
            <div class="footer-col">
                <span class="footer-brand">{{ $publicSettings?->system_name ?? 'Joyería Web' }}</span>
                <p class="footer-text">Oro y plata de ley, valuación experta y compra directa de metales preciosos.</p>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Navegación</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('public.catalog') }}">Catálogo</a></li>
                    <li><a href="{{ route('public.sell_gold') }}">Vender mi oro</a></li>
                    <li><a href="{{ route('public.store') }}">Sucursal</a></li>
                    <li><a href="{{ route('public.contact') }}">Contacto</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Visítanos</h4>
                <ul class="footer-links">
                    @if ($publicStore?->address)
                        <li>{{ $publicStore->address }}</li>
                    @endif
                    @if ($publicStore?->phone)
                        <li><a href="tel:{{ $publicStore->phone }}">{{ $publicStore->phone }}</a></li>
                    @endif
                    @if ($publicSettings?->whatsapp_number)
                        <li><a href="https://wa.me/{{ preg_replace('/\D+/', '', $publicSettings->whatsapp_number) }}" target="_blank" rel="noopener">WhatsApp {{ $publicSettings->whatsapp_number }}</a></li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; {{ date('Y') }} {{ $publicSettings?->system_name ?? 'Joyería Web' }}. Todos los derechos reservados.
        </div>
    </footer>

    @if ($publicSettings?->whatsapp_number)
        <a
            href="https://wa.me/{{ preg_replace('/\D+/', '', $publicSettings->whatsapp_number) }}?text={{ rawurlencode('Hola, quisiera más información sobre sus piezas y la compra de oro.') }}"
            class="whatsapp-float"
            target="_blank"
            rel="noopener"
            aria-label="Escríbenos por WhatsApp"
        >
            <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
        </a>
    @endif

    @stack('scripts')
</body>
</html>
