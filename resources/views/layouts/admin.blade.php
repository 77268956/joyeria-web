<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — Joyería Web</title>

    <!-- Google Fonts: Cormorant Garamond & Jost -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-noche:        #12161F;
            --color-hueso:        #F7F4EE;
            --color-crema:        #F3EFE8; /* slightly darker than hueso, for table heads & filter bars */
            --color-blanco:       #FFFFFF;
            --color-dorado:       #B89A5E;
            --color-dorado-claro: #E3D6B4;
            --color-piedra:       #6B6B70;
            --color-linea:        #E4DFD3;
            --color-exito:        #3E7C59;
            --color-error:        #A63D40;
            --color-aviso:        #B7791F;
            --color-info:         #3F5F7A;
            --font-serif: 'Cormorant Garamond', Georgia, serif;
            --font-sans:  'Jost', system-ui, -apple-system, sans-serif;
            --sidebar-width: 250px;
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
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, .font-serif {
            font-family: var(--font-serif);
        }

        /* ------------------ SIDEBAR ------------------ */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--color-noche);
            color: var(--color-hueso);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            border-right: 1px solid rgba(228, 223, 211, 0.1);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 0.75rem;
            border-bottom: 1px solid rgba(228, 223, 211, 0.1);
        }

        .sidebar-logo {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid rgba(184, 154, 94, 0.45);
            background-color: var(--color-blanco);
        }

        .sidebar-brand-text {
            flex: 1;
            min-width: 0;
        }

        .sidebar-brand h1 {
            font-size: 1.1rem;
            font-weight: 500;
            letter-spacing: 0.03em;
            color: #FDFCF9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-brand span {
            font-size: 0.56rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--color-dorado);
            display: block;
            margin-top: 0.15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: #B3B0A6;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 400;
            letter-spacing: 0.02em;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: var(--color-hueso);
            background-color: rgba(255, 255, 255, 0.04);
        }

        .nav-link.active {
            color: var(--color-hueso);
            background-color: rgba(184, 154, 94, 0.12);
            border-left-color: var(--color-dorado);
            font-weight: 500;
        }

        .nav-link svg {
            flex-shrink: 0;
            color: var(--color-dorado);
            opacity: 0.85;
        }

        /* ------------------ SIDEBAR RETRÁCTIL ------------------ */
        .sidebar,
        .main-wrapper {
            transition: width 0.25s ease, margin-left 0.25s ease;
        }

        .sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: none;
            border: 1px solid rgba(228, 223, 211, 0.15);
            border-radius: 4px;
            color: #B3B0A6;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .sidebar-toggle:hover {
            border-color: var(--color-dorado);
            color: var(--color-dorado);
        }

        body.sidebar-collapsed .sidebar {
            width: 68px;
        }

        body.sidebar-collapsed .main-wrapper {
            margin-left: 68px;
        }

        body.sidebar-collapsed .sidebar-brand {
            flex-direction: column;
            gap: 0.5rem;
            padding: 0.85rem 0.35rem;
        }

        body.sidebar-collapsed .sidebar-brand-text {
            display: none;
        }

        body.sidebar-collapsed .sidebar-toggle {
            display: none;
        }

        body.sidebar-collapsed .sidebar-logo {
            width: 40px;
            height: 40px;
            cursor: pointer;
        }

        body.sidebar-collapsed .nav-link {
            justify-content: center;
            padding: 0.75rem 0;
            gap: 0;
        }

        body.sidebar-collapsed .nav-link span {
            display: none;
        }

        /* ------------------ MAIN WRAPPER ------------------ */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ------------------ TOPBAR ------------------ */
        .topbar {
            height: 64px;
            background-color: var(--color-blanco);
            border-bottom: 1px solid var(--color-linea);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .gold-ticker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            background-color: var(--color-hueso);
            border: 1px solid var(--color-linea);
            border-radius: 4px;
            font-size: 0.82rem;
            font-weight: 500;
        }

        .gold-ticker-label {
            color: var(--color-piedra);
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
        }

        .gold-ticker-value {
            color: var(--color-noche);
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .cash-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.04em;
        }

        .cash-status-closed {
            background-color: #F1F0EB;
            color: var(--color-piedra);
            border: 1px solid var(--color-linea);
        }

        .cash-status-open {
            background-color: #EDF7EE;
            color: var(--color-exito);
            border: 1px solid rgba(62, 124, 89, 0.3);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-profile {
            display: flex;
            flex-direction: column;
            text-align: right;
        }

        .user-name {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--color-noche);
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--color-dorado);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 500;
        }

        .btn-logout {
            background: transparent;
            border: 1px solid var(--color-linea);
            border-radius: 4px;
            padding: 0.45rem 0.85rem;
            font-family: var(--font-sans);
            font-size: 0.82rem;
            color: var(--color-piedra);
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-logout:hover {
            border-color: var(--color-error);
            color: var(--color-error);
            background-color: #FDEDED;
        }

        /* ------------------ CONTENT ------------------ */
        .content-area {
            flex: 1;
            padding: 2.25rem 2rem;
        }

        /* ------------------ FULL VIEWPORT (one-screen pages) ------------------ */
        body.full-viewport {
            overflow: hidden;
        }

        .full-viewport .main-wrapper {
            height: 100vh;
            overflow: hidden;
        }

        .full-viewport .content-area {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 1.25rem 1.5rem;
        }

        @media (max-width: 1024px) {
            body.full-viewport {
                overflow: auto;
            }

            .full-viewport .main-wrapper {
                height: auto;
                overflow: visible;
            }

            .full-viewport .content-area {
                display: block;
                overflow: visible;
                padding: 2.25rem 2rem;
            }
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 500;
            color: var(--color-noche);
            letter-spacing: 0.02em;
        }

        .page-subtitle {
            font-size: 0.9rem;
            color: var(--color-piedra);
            margin-top: 0.25rem;
        }

        /* ------------------ UI CARDS ------------------ */
        .card {
            background-color: var(--color-blanco);
            border: 1px solid var(--color-linea);
            border-radius: 4px;
            padding: 1.75rem;
            box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
            margin-bottom: 1.5rem;
        }

        /* ------------------ BUTTONS ------------------ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            height: 40px;
            padding: 0 1.25rem;
            border-radius: 4px;
            font-family: var(--font-sans);
            font-size: 0.86rem;
            font-weight: 500;
            letter-spacing: 0.02em;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-sm {
            height: 32px;
            padding: 0 0.7rem;
            font-size: 0.78rem;
        }

        .btn-gold { background-color: var(--color-dorado); color: var(--color-noche); font-weight: 600; }
        .btn-gold:hover { background-color: #a98a4e; }

        .btn-dark { background-color: var(--color-noche); color: var(--color-hueso); }
        .btn-dark:hover { background-color: #232a38; }

        .btn-outline { background: transparent; border-color: var(--color-linea); color: var(--color-piedra); }
        .btn-outline:hover { border-color: var(--color-dorado); color: var(--color-dorado); }

        .btn-danger { background: transparent; border-color: #E0B4B4; color: var(--color-error); }
        .btn-danger:hover { background-color: #FDEDED; }

        /* ------------------ FORMS ------------------ */
        .form-group { margin-bottom: 1.1rem; }

        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--color-noche);
            margin-bottom: 0.4rem;
        }

        .form-control, .form-select {
            width: 100%;
            height: 42px;
            border: 1px solid var(--color-linea);
            border-radius: 4px;
            padding: 0 0.75rem;
            font-family: var(--font-sans);
            font-size: 0.9rem;
            color: var(--color-noche);
            background-color: var(--color-blanco);
            outline: none;
            transition: border-color 0.2s;
        }

        textarea.form-control { height: auto; padding: 0.6rem 0.75rem; resize: vertical; }

        .form-control:focus, .form-select:focus { border-color: var(--color-dorado); }

        .form-hint { font-size: 0.78rem; color: var(--color-piedra); margin-top: 0.3rem; }

        .form-check { display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.88rem; color: var(--color-noche); }
        .form-check input { width: 16px; height: 16px; accent-color: var(--color-dorado); }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }

        /* ------------------ TABLES ------------------ */
        .table-wrap { overflow-x: auto; }
        table.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; }
        table.data-table thead tr { background-color: var(--color-crema); border-bottom: 1px solid var(--color-linea); }
        table.data-table th {
            padding: 0.85rem 1.1rem;
            font-weight: 600;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--color-noche);
        }
        table.data-table td { padding: 0.9rem 1.1rem; border-bottom: 1px solid var(--color-linea); color: var(--color-noche); }
        table.data-table tbody tr:hover { background-color: #FBF9F5; }
        .num { font-variant-numeric: tabular-nums; }

        /* ------------------ BADGES & ALERTS ------------------ */
        .badge {
            display: inline-block;
            padding: 0.22rem 0.6rem;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-radius: 4px;
            background-color: #F1EFEA;
            color: var(--color-piedra);
        }
        .badge-gold { background-color: rgba(184, 154, 94, 0.15); color: var(--color-dorado); }
        .badge-success { background-color: #EDF7EE; color: var(--color-exito); }
        .badge-danger { background-color: #FDEDED; color: var(--color-error); }
        .badge-info { background-color: #EBF3FB; color: #205493; }
        .badge-muted { background-color: #F1F0EB; color: var(--color-piedra); }

        .alert { padding: 1rem 1.25rem; border-radius: 4px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; gap: 1rem; }
        .alert-success { background-color: rgba(184, 154, 94, 0.15); border-left: 4px solid var(--color-dorado); color: var(--color-noche); }
        .alert-error { background-color: #FDEDED; border-left: 4px solid var(--color-error); color: #5F2120; }
        .alert-error ul { margin: 0.35rem 0 0; padding-left: 1.25rem; font-size: 0.88rem; }

        .muted { color: var(--color-piedra); }
        .text-right { text-align: right; }

        /* ------------------ MODAL ------------------ */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(18, 22, 31, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
        .modal {
            background-color: var(--color-blanco);
            border-radius: 6px;
            width: 100%;
            max-width: 540px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            margin: 1rem;
        }
        .modal-header {
            padding: 1.15rem 1.5rem;
            background-color: var(--color-crema);
            border-bottom: 1px solid var(--color-linea);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-body { padding: 1.5rem; }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            border-top: 1px solid var(--color-linea);
            padding: 1.15rem 1.5rem;
        }
        .modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--color-piedra); }

        .page-actions { display: flex; gap: 0.6rem; flex-wrap: wrap; }

        /* ------------------ BALANZA (Web Serial) ------------------ */
        .scale-panel {
            border: 1px dashed var(--color-linea);
            border-radius: 8px;
            padding: 1rem 1.1rem;
            margin-bottom: 1.25rem;
            background: linear-gradient(180deg, rgba(243, 239, 232, 0.6), rgba(255, 255, 255, 0));
        }

        .scale-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .scale-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--color-piedra);
        }

        .scale-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background-color: var(--color-piedra);
            box-shadow: 0 0 0 3px rgba(107, 107, 112, 0.12);
        }

        .scale-status[data-scale-status="connected"] .scale-dot {
            background-color: var(--color-exito);
            box-shadow: 0 0 0 3px rgba(62, 124, 89, 0.15);
            animation: scale-pulse 1.6s ease-in-out infinite;
        }

        .scale-status[data-scale-status="connecting"] .scale-dot { background-color: var(--color-aviso); }

        .scale-status[data-scale-status="error"] .scale-dot,
        .scale-status[data-scale-status="unsupported"] .scale-dot { background-color: var(--color-error); }

        @keyframes scale-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }

        .scale-reading {
            display: inline-flex;
            align-items: baseline;
            gap: 0.3rem;
            font-family: var(--font-serif);
            font-size: 2rem;
            line-height: 1;
            color: var(--color-noche);
        }

        .scale-reading.is-live { color: var(--color-exito); }

        .scale-unit { font-size: 1rem; color: var(--color-piedra); }

        .scale-actions {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-top: 0.9rem;
            flex-wrap: wrap;
        }

        .scale-actions .form-select { width: auto; height: 32px; }
    </style>

    @stack('styles')
</head>
<body class="@yield('body_class')">
    <script>
        if (localStorage.getItem('sidebar-collapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
        }
    </script>

    <!-- Sidebar / Menú Lateral -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo-placeholder.svg') }}" alt="Logo de la joyería" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <h1>Joyería Web</h1>
                <span>Alta Gama &amp; Control</span>
            </div>
            <button type="button" id="sidebar-toggle" class="sidebar-toggle" title="Mostrar u ocultar menú" aria-label="Mostrar u ocultar menú">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.pos.index') }}" class="nav-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span>Punto de Venta</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.cash.index') }}" class="nav-link {{ request()->routeIs('admin.cash.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                    <span>Caja</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.sales.index') }}" class="nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                        <line x1="9" y1="11" x2="15" y2="11"></line>
                    </svg>
                    <span>Ventas</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.layaways.index') }}" class="nav-link {{ request()->routeIs('admin.layaways.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Apartados</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.purchases.index') }}" class="nav-link {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span>Compra Metales</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.inventory.index') }}" class="nav-link {{ request()->routeIs('admin.inventory.*') && ! request()->routeIs('admin.inventory.lots.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    <span>Inventario</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.inventory.lots.index') }}" class="nav-link {{ request()->routeIs('admin.inventory.lots.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    <span>Materia Prima</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.stock-counts.index') }}" class="nav-link {{ request()->routeIs('admin.stock-counts.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span>Conteos</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                    <span>Clientes</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.prices.index') }}" class="nav-link {{ request()->routeIs('admin.prices.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    <span>Precio del Metal</span>
                </a>
            </li>

            @hasanyrole('Dueño|Gerente')
            <li class="nav-item">
                <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    <span>Reportes</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.audit.index') }}" class="nav-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Auditoría</span>
                </a>
            </li>
            @endhasanyrole

            @hasrole('Dueño')
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <span>Usuarios</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Configuración</span>
                </a>
            </li>
            @endhasrole
        </ul>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar Superior -->
        <header class="topbar">
            <div class="topbar-left">
                <!-- Ticker de metales del día -->
                @php($tickerCurrency = Auth::user()?->store?->currency ?? 'USD')
                @if ($goldMetalPrice ?? null)
                    <div class="gold-ticker" title="Precio vigente por gramo de oro puro (24k) — {{ $goldMetalPrice->price_date?->format('d/m/Y') }}">
                        <span class="gold-ticker-label">Oro 24k</span>
                        <span class="gold-ticker-value">{{ $tickerCurrency }} ${{ number_format((float) $goldMetalPrice->price_per_gram, 2) }} / g</span>
                    </div>
                @endif
                @if ($silverMetalPrice ?? null)
                    <div class="gold-ticker" title="Precio vigente por gramo de plata pura (1000) — {{ $silverMetalPrice->price_date?->format('d/m/Y') }}">
                        <span class="gold-ticker-label">Plata</span>
                        <span class="gold-ticker-value">{{ $tickerCurrency }} ${{ number_format((float) $silverMetalPrice->price_per_gram, 2) }} / g</span>
                    </div>
                @endif

                <!-- Estado de Caja -->
                @if ($openCashSession ?? null)
                    <a href="{{ route('admin.cash.index') }}" class="cash-status-badge cash-status-open" style="text-decoration: none;">
                        <svg width="8" height="8" viewBox="0 0 10 10" fill="currentColor">
                            <circle cx="5" cy="5" r="5"></circle>
                        </svg>
                        <span>Turno Abierto</span>
                    </a>
                @else
                    <a href="{{ route('admin.cash.index') }}" class="cash-status-badge cash-status-closed" style="text-decoration: none;">
                        <svg width="8" height="8" viewBox="0 0 10 10" fill="currentColor">
                            <circle cx="5" cy="5" r="5"></circle>
                        </svg>
                        <span>Caja Cerrada</span>
                    </a>
                @endif
            </div>

            <div class="topbar-right">
                @can('products.edit_price')
                <!-- Campana de Notificaciones de Autorizaciones de Descuento -->
                <div style="position: relative;" id="supervisor-notif-container">
                    <button type="button" id="btn-toggle-notifs" title="Solicitudes de descuento pendientes" style="background: none; border: 1px solid var(--color-linea); border-radius: 4px; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--color-piedra); position: relative; transition: all 0.2s;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span id="notif-badge" style="display: none; position: absolute; top: -5px; right: -5px; background-color: var(--color-error); color: #fff; font-size: 0.65rem; font-weight: 700; border-radius: 10px; padding: 1px 5px; min-width: 16px; text-align: center; border: 2px solid #fff;">0</span>
                    </button>

                    <!-- Dropdown de Solicitudes -->
                    <div id="notif-dropdown" style="display: none; position: absolute; top: calc(100% + 8px); right: 0; width: 360px; background-color: var(--color-blanco); border: 1px solid var(--color-linea); border-radius: 6px; box-shadow: 0 10px 25px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden;">
                        <div style="padding: 0.85rem 1.1rem; background-color: var(--color-crema); border-bottom: 1px solid var(--color-linea); display: flex; align-items: center; justify-content: space-between;">
                            <strong style="font-size: 0.88rem; color: var(--color-noche);">Descuentos Pendientes</strong>
                            <span id="notif-count-badge" class="badge badge-gold" style="font-size: 0.7rem;">0 pendientes</span>
                        </div>
                        <div id="notif-list" style="max-height: 380px; overflow-y: auto;">
                            <div style="padding: 2rem 1rem; text-align: center; color: var(--color-piedra); font-size: 0.85rem;">
                                No hay solicitudes de descuento pendientes.
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

                <div class="user-profile">
                    <span class="user-name">{{ Auth::user()->name ?? 'Usuario' }}</span>
                    <span class="user-role">{{ Auth::user()->roles->first()->name ?? 'Colaborador' }}</span>
                </div>

                <!-- Botón de Salir -->
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout" title="Cerrar sesión actual">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Salir</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Contenido Específico -->
        <main class="content-area">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    @can('products.edit_price')
    <script>
    (function () {
        const notifContainer = document.getElementById('supervisor-notif-container');
        if (!notifContainer) return;

        const btnToggle = document.getElementById('btn-toggle-notifs');
        const notifBadge = document.getElementById('notif-badge');
        const notifDropdown = document.getElementById('notif-dropdown');
        const notifList = document.getElementById('notif-list');
        const notifCountBadge = document.getElementById('notif-count-badge');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        const countUrl = @json(route('admin.authorizations.count'));
        const pendingUrl = @json(route('admin.authorizations.pending'));
        const baseUrl = @json(url('admin/autorizaciones'));

        let isOpen = false;

        function updateBadge() {
            fetch(countUrl, { headers: { 'Accept': 'application/json' } })
                .then((r) => r.json())
                .then((data) => {
                    const count = data.count || 0;
                    if (count > 0) {
                        notifBadge.textContent = count;
                        notifBadge.style.display = 'inline-block';
                        btnToggle.style.borderColor = 'var(--color-dorado)';
                        btnToggle.style.color = 'var(--color-dorado)';
                    } else {
                        notifBadge.style.display = 'none';
                        btnToggle.style.borderColor = 'var(--color-linea)';
                        btnToggle.style.color = 'var(--color-piedra)';
                    }
                    if (isOpen) {
                        loadPendingList();
                    }
                })
                .catch(() => {});
        }

        function loadPendingList() {
            fetch(pendingUrl, { headers: { 'Accept': 'application/json' } })
                .then((r) => r.json())
                .then((data) => {
                    const list = data.authorizations || [];
                    notifCountBadge.textContent = list.length + ' pendientes';

                    if (list.length === 0) {
                        notifList.innerHTML = '<div style="padding: 2rem 1rem; text-align: center; color: var(--color-piedra); font-size: 0.85rem;">No hay solicitudes de descuento pendientes.</div>';
                        return;
                    }

                    notifList.innerHTML = '';
                    list.forEach((auth) => {
                        const item = document.createElement('div');
                        item.style.cssText = 'padding: 0.9rem 1.1rem; border-bottom: 1px solid var(--color-linea); font-size: 0.85rem;';
                        item.innerHTML =
                            '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem;">' +
                                '<strong>' + auth.requester_name + '</strong>' +
                                '<span class="badge badge-gold" style="font-size: 0.72rem;">' + auth.discount_percent + '% dto.</span>' +
                            '</div>' +
                            '<div style="color: var(--color-piedra); font-size: 0.82rem; margin-bottom: 0.4rem;">' +
                                'Subtotal: $' + Number(auth.subtotal).toFixed(2) + ' · Descuento: <strong style="color: var(--color-aviso);">$' + Number(auth.discount_amount).toFixed(2) + '</strong>' +
                            '</div>' +
                            '<div style="background-color: var(--color-crema); padding: 0.4rem 0.6rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.65rem; color: var(--color-noche);">' +
                                '<em>Motivo: ' + auth.reason + '</em>' +
                            '</div>' +
                            '<div style="display: flex; justify-content: flex-end; gap: 0.5rem;">' +
                                '<button type="button" class="btn btn-sm btn-outline" style="border-color: #E0B4B4; color: var(--color-error);" data-reject="' + auth.id + '">Rechazar</button>' +
                                '<button type="button" class="btn btn-sm btn-gold" data-approve="' + auth.id + '">Aprobar</button>' +
                            '</div>';
                        notifList.appendChild(item);
                    });

                    // Event handlers for approve/reject
                    notifList.querySelectorAll('[data-approve]').forEach((btn) => {
                        btn.addEventListener('click', () => {
                            const id = btn.dataset.approve;
                            btn.disabled = true;
                            fetch(baseUrl + '/' + id + '/aprobar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                            })
                                .then((r) => r.json())
                                .then(() => { updateBadge(); })
                                .catch((e) => alert(e.message));
                        });
                    });

                    notifList.querySelectorAll('[data-reject]').forEach((btn) => {
                        btn.addEventListener('click', () => {
                            const id = btn.dataset.reject;
                            const reason = prompt('Motivo del rechazo de la solicitud:');
                            if (!reason || !reason.trim()) return;

                            btn.disabled = true;
                            fetch(baseUrl + '/' + id + '/rechazar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                                body: JSON.stringify({ reason: reason.trim() })
                            })
                                .then((r) => r.json())
                                .then(() => { updateBadge(); })
                                .catch((e) => alert(e.message));
                        });
                    });
                })
                .catch(() => {});
        }

        btnToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen = !isOpen;
            notifDropdown.style.display = isOpen ? 'block' : 'none';
            if (isOpen) {
                loadPendingList();
            }
        });

        document.addEventListener('click', (e) => {
            if (isOpen && !notifContainer.contains(e.target)) {
                isOpen = false;
                notifDropdown.style.display = 'none';
            }
        });

        // Polling cada 5 segundos
        updateBadge();
        setInterval(updateBadge, 5000);
    })();
    </script>
    @endcan

    <script>
        (function () {
            document.querySelectorAll('.sidebar .nav-link').forEach(function (link) {
                var label = link.querySelector('span');
                if (label) {
                    link.title = label.textContent.trim();
                }
            });

            function toggleSidebar() {
                var collapsed = document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');

                // Forzar redimensionamiento de gráficas y vistas tras la transición
                setTimeout(function () {
                    window.dispatchEvent(new Event('resize'));
                }, 300);
            }

            var toggle = document.getElementById('sidebar-toggle');
            if (toggle) {
                toggle.addEventListener('click', toggleSidebar);
            }

            // Con el menú colapsado, el logo reemplaza a la flecha como botón para expandir
            var logo = document.querySelector('.sidebar-logo');
            if (logo) {
                logo.addEventListener('click', function () {
                    if (document.body.classList.contains('sidebar-collapsed')) {
                        toggleSidebar();
                    }
                });
            }
        })();
    </script>
</body>
</html>
