<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ingreso al Sistema') — Joyería Web</title>

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
            --color-exito: #3E7C59;
            --color-error: #A63D40;
            --color-aviso: #B7791F;
            --color-info: #3F5F7A;
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
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, .font-serif {
            font-family: var(--font-serif);
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
