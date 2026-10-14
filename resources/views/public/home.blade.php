<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joyería Web — Joyería Fina & Metales Preciosos</title>

    <!-- Google Fonts: Cormorant Garamond & Jost -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-noche: #12161F;
            --color-hueso: #F7F4EE;
            --color-blanco: #FFFFFF;
            --color-dorado: #B89A5E;
            --color-dorado-claro: #E3D6B4;
            --color-piedra: #6B6B70;
            --color-linea: #E4DFD3;
            --font-serif: 'Cormorant Garamond', Georgia, serif;
            --font-sans: 'Jost', system-ui, -apple-system, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--color-hueso);
            color: var(--color-noche);
            line-height: 1.6;
        }

        /* Navbar Pública */
        .public-navbar {
            background-color: var(--color-blanco);
            border-bottom: 1px solid var(--color-linea);
            padding: 1.25rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-family: var(--font-serif);
            font-size: 1.8rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            color: var(--color-noche);
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .navbar-link {
            text-decoration: none;
            color: var(--color-noche);
            font-size: 0.95rem;
            font-weight: 400;
            letter-spacing: 0.05em;
            transition: color 0.2s;
        }

        .navbar-link:hover {
            color: var(--color-dorado);
        }

        .btn-access {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--color-noche);
            color: var(--color-hueso);
            padding: 0.6rem 1.25rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-access:hover {
            background-color: #1e2533;
        }

        /* Hero Section */
        .hero {
            padding: 6rem 3rem;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .hero-badge {
            display: inline-block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--color-dorado);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .hero-title {
            font-family: var(--font-serif);
            font-size: 3.8rem;
            font-weight: 300;
            line-height: 1.15;
            margin-bottom: 1.5rem;
            color: var(--color-noche);
        }

        .hero-title span {
            font-style: italic;
            color: var(--color-dorado);
        }

        .hero-description {
            max-width: 620px;
            margin: 0 auto 2.5rem;
            font-size: 1.15rem;
            color: var(--color-piedra);
            font-weight: 300;
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
        }

        .btn-gold {
            background-color: var(--color-dorado);
            color: var(--color-noche);
            padding: 0.85rem 2rem;
            border-radius: 4px;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .btn-gold:hover {
            opacity: 0.9;
        }

        .btn-outline {
            background-color: transparent;
            color: var(--color-noche);
            border: 1px solid var(--color-noche);
            padding: 0.85rem 2rem;
            border-radius: 4px;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-outline:hover {
            background-color: var(--color-dorado-claro);
        }

        /* Banner de Compra de Oro */
        .gold-banner {
            background-color: var(--color-noche);
            color: var(--color-hueso);
            padding: 4rem 3rem;
            margin-top: 4rem;
        }

        .gold-banner-content {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .gold-banner h2 {
            font-family: var(--font-serif);
            font-size: 2.4rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }

        .gold-banner p {
            color: #B3B0A6;
            font-size: 1rem;
            max-width: 550px;
        }
    </style>
</head>
<body>
    <!-- Barra de Navegación Pública -->
    <header class="public-navbar">
        <a href="{{ route('home') }}" class="navbar-brand">Joyería Web</a>

        <ul class="navbar-nav">
            <li><a href="{{ route('public.catalog') }}" class="navbar-link">Catálogo</a></li>
            <li><a href="{{ route('public.sell_gold') }}" class="navbar-link">Vender mi Oro</a></li>
            <li><a href="{{ route('public.store') }}" class="navbar-link">Sucursal</a></li>
            <li><a href="{{ route('public.contact') }}" class="navbar-link">Contacto</a></li>
        </ul>

        <div>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="btn-access">
                    Ir al Panel &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-access">
                    Acceso Personal &rarr;
                </a>
            @endauth
        </div>
    </header>

    <!-- Sección Principal Hero -->
    <section class="hero">
        <span class="hero-badge">Colección & Fina Joyería</span>
        <h1 class="hero-title">
            El arte de la orfebrería, la <span>nobleza</span> de los metales.
        </h1>
        <p class="hero-description">
            Descubre piezas de alta joyería en oro y plata de ley. Diseños atemporales, garantía de pureza y valuación experta certificada.
        </p>

        <div class="hero-actions">
            <a href="{{ route('public.catalog') }}" class="btn-gold">Explorar Catálogo</a>
            <a href="{{ route('public.sell_gold') }}" class="btn-outline">Cotizar mi Oro</a>
        </div>
    </section>

    <!-- Franja Noche de Compra de Oro -->
    <section class="gold-banner">
        <div class="gold-banner-content">
            <div>
                <h2>¿Deseas vender tus piezas de oro o plata?</h2>
                <p>Te ofrecemos la mejor cotización basada directamente en el precio del mercado del día, con pesaje de precisión y pago transparente.</p>
            </div>
            <div>
                <a href="{{ route('public.sell_gold') }}" class="btn-gold">Cotizar en Línea</a>
            </div>
        </div>
    </section>
</body>
</html>
