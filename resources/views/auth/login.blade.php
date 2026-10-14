@extends('layouts.auth')

@section('title', 'Iniciar Sesión')

@section('content')
<style>
    .login-container {
        display: flex;
        width: 100vw;
        min-height: 100vh;
    }

    /* Columna Izquierda: Imagen editorial de joyería */
    .login-visual {
        flex: 1.1;
        background: radial-gradient(circle at 40% 30%, #2A2F3D 0%, #12161F 70%, #0A0D13 100%);
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 4rem;
        overflow: hidden;
    }

    /* Patrón decorativo y brillo dorado sutil */
    .login-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        background: 
            radial-gradient(circle at 80% 20%, rgba(184, 154, 94, 0.18) 0%, transparent 50%),
            radial-gradient(circle at 20% 80%, rgba(227, 214, 180, 0.1) 0%, transparent 40%);
        pointer-events: none;
    }

    .visual-content {
        position: relative;
        z-index: 2;
        color: var(--color-hueso);
        max-width: 520px;
    }

    .visual-tagline {
        font-family: var(--font-serif);
        font-size: 3rem;
        font-weight: 300;
        line-height: 1.15;
        letter-spacing: 0.02em;
        margin-bottom: 1.25rem;
        color: #FDFCF9;
    }

    .visual-tagline span {
        color: var(--color-dorado);
        font-style: italic;
    }

    .visual-desc {
        color: #B3B0A6;
        font-size: 1rem;
        line-height: 1.6;
        font-weight: 300;
        letter-spacing: 0.03em;
    }

    .visual-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.85rem;
        border: 1px solid rgba(184, 154, 94, 0.4);
        border-radius: 4px;
        font-size: 0.75rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: var(--color-dorado);
        margin-bottom: 2rem;
    }

    /* Columna Derecha: Tarjeta de Login */
    .login-form-area {
        flex: 0.9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 2rem;
        background-color: var(--color-hueso);
    }

    .login-card {
        width: 100%;
        max-width: 440px;
        background-color: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        padding: 3rem 2.5rem;
        box-shadow: 0 4px 20px rgba(18, 22, 31, 0.03);
    }

    .brand-header {
        text-align: center;
        margin-bottom: 2.25rem;
    }

    .brand-title {
        font-size: 2.25rem;
        font-weight: 400;
        letter-spacing: 0.04em;
        color: var(--color-noche);
        margin-bottom: 0.25rem;
    }

    .brand-subtitle {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        color: var(--color-piedra);
        font-weight: 500;
    }

    .gold-divider {
        width: 36px;
        height: 1px;
        background: var(--color-dorado);
        margin: 1rem auto 0;
    }

    .form-group {
        margin-bottom: 1.35rem;
    }

    .form-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--color-noche);
        margin-bottom: 0.5rem;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .form-control {
        width: 100%;
        height: 44px;
        padding: 0 1rem;
        font-family: var(--font-sans);
        font-size: 0.95rem;
        color: var(--color-noche);
        background-color: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: var(--color-dorado);
        box-shadow: 0 0 0 3px rgba(184, 154, 94, 0.15);
    }

    .form-control.is-invalid {
        border-color: var(--color-error);
    }

    .toggle-password {
        position: absolute;
        right: 0.75rem;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0.25rem;
        color: var(--color-piedra);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-password:hover {
        color: var(--color-noche);
    }

    .form-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 1.25rem 0 1.75rem;
        font-size: 0.85rem;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        color: var(--color-piedra);
        user-select: none;
    }

    .remember-me input[type="checkbox"] {
        accent-color: var(--color-dorado);
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .forgot-link {
        color: var(--color-piedra);
        text-decoration: none;
        transition: color 0.2s;
    }

    .forgot-link:hover {
        color: var(--color-dorado);
        text-decoration: underline;
    }

    .btn-submit {
        width: 100%;
        height: 44px;
        background-color: var(--color-noche);
        color: var(--color-hueso);
        border: none;
        border-radius: 4px;
        font-family: var(--font-sans);
        font-size: 0.9rem;
        font-weight: 500;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
    }

    .btn-submit:hover {
        background-color: #1e2533;
    }

    .btn-submit:active {
        transform: scale(0.99);
    }

    .alert {
        padding: 0.85rem 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
        font-size: 0.85rem;
        line-height: 1.4;
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
    }

    .alert-error {
        background-color: #FDEDED;
        color: var(--color-error);
        border-left: 3px solid var(--color-error);
    }

    .alert-success {
        background-color: #EDF7EE;
        color: var(--color-exito);
        border-left: 3px solid var(--color-exito);
    }

    .error-msg {
        color: var(--color-error);
        font-size: 0.78rem;
        margin-top: 0.35rem;
        display: block;
    }

    @media (max-width: 900px) {
        .login-visual {
            display: none;
        }
        .login-form-area {
            flex: 1;
            padding: 2rem 1rem;
        }
    }
</style>

<div class="login-container">
    <!-- Panel Visual Izquierdo -->
    <div class="login-visual">
        <div class="visual-content">
            <div class="visual-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                Gestión Exclusiva
            </div>
            <h1 class="visual-tagline">
                Precisión, elegancia y control en cada <span>pieza</span>.
            </h1>
            <p class="visual-desc">
                Sistema centralizado de punto de venta, inventario inmutable y cotización en tiempo real de metales finos.
            </p>
        </div>
    </div>

    <!-- Panel de Formulario Derecho -->
    <div class="login-form-area">
        <div class="login-card">
            <div class="brand-header">
                <h2 class="brand-title">Joyería Web</h2>
                <p class="brand-subtitle">Panel de Control</p>
                <div class="gold-divider"></div>
            </div>

            {{-- Alerta de estado general --}}
            @if (session('status'))
                <div class="alert alert-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Alerta de errores de credenciales / bloqueo --}}
            @if ($errors->has('email') && !old('email'))
                <div class="alert alert-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first('email') }}</span>
                </div>
            @endif

            <form action="{{ route('login.attempt') }}" method="POST" autocomplete="on">
                @csrf

                <!-- Correo Electrónico -->
                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus 
                            placeholder="admin@joyeriaweb.com"
                        >
                    </div>
                    @error('email')
                        <span class="error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            required 
                            placeholder="••••••••"
                        >
                        <button type="button" class="toggle-password" id="togglePasswordBtn" title="Mostrar/Ocultar contraseña" aria-label="Mostrar u ocultar contraseña">
                            <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Opciones (Recordarme / Olvidé contraseña) -->
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Recordarme</span>
                    </label>

                    <a href="javascript:void(0)" onclick="alert('Por seguridad, solicita el restablecimiento de tu contraseña al Dueño o Administrador de la tienda.')" class="forgot-link">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Botón de Envío -->
                <button type="submit" class="btn-submit" id="submitBtn">
                    Ingresar al Panel
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Alternar visibilidad de contraseña
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            // Icono dinámico
            if (isPassword) {
                eyeIcon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        });
    }

    // Prevenir doble submit y mostrar feedback
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            submitBtn.innerText = 'Validando credenciales...';
        });
    }
</script>
@endsection
