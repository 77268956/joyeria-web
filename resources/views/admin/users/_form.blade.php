@php
    $user = $user ?? null;
    $isEdit = $user !== null;
    $selectedRole = old('role', $isEdit ? $user->roles->first()?->name : 'Vendedor');
    $selectedStore = old('store_id', $isEdit ? $user->store_id : auth()->user()?->store_id);
    $isActive = (bool) old('is_active', $isEdit ? $user->is_active : true);
    $isSelf = $isEdit && $user->id === auth()->id();

    $roleDescriptions = [
        'Dueño' => 'Acceso total: configuración, usuarios, reportes, inventario y autorizaciones.',
        'Gerente' => 'Supervisa ventas, inventario, clientes y reportes. No administra usuarios ni configuración.',
        'Vendedor' => 'Operación de mostrador: ventas, apartados, clientes, consulta de inventario y caja.',
    ];
@endphp

<style>
    .usr-form-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(290px, 1fr);
        gap: 0.8rem;
        align-items: start;
    }

    .usr-form-main,
    .usr-form-side {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        min-width: 0;
    }

    .usr-form-card {
        overflow: hidden;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    .usr-form-card-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.65rem 0.9rem;
        border-bottom: 1px solid var(--color-linea);
        background: #FCFBF8;
    }

    .usr-form-card-icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .usr-form-card-header h2 {
        margin: 0;
        color: var(--color-noche);
        font-family: var(--font-serif);
        font-size: 1rem;
        font-weight: 500;
    }

    .usr-form-card-header p {
        margin: 0.05rem 0 0;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .usr-form-card-body {
        padding: 0.9rem;
    }

    .usr-form-field {
        margin-bottom: 0.75rem;
    }

    .usr-form-field:last-child {
        margin-bottom: 0;
    }

    .usr-form-field label {
        display: block;
        margin-bottom: 0.22rem;
        color: var(--color-piedra);
        font-size: 0.67rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .usr-form-field label .req {
        margin-left: 0.15rem;
        color: var(--color-error);
    }

    .usr-form-field .form-control,
    .usr-form-field .form-select {
        width: 100%;
        height: 34px;
        padding: 0 0.75rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: #FCFBF8;
        font-size: 0.82rem;
        transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
    }

    .usr-form-field .form-control:focus,
    .usr-form-field .form-select:focus {
        border-color: var(--color-dorado);
        background: var(--color-blanco);
        box-shadow: 0 0 0 2px rgba(184, 154, 94, 0.12);
        outline: none;
    }

    .usr-form-field .form-control.is-invalid,
    .usr-form-field .form-select.is-invalid {
        border-color: var(--color-error);
        background-color: #FFF5F5;
    }

    .usr-form-field .form-control:disabled,
    .usr-form-field .form-select:disabled {
        color: var(--color-piedra);
        background: #F1F0EB;
        cursor: not-allowed;
    }

    .usr-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.7rem;
    }

    .usr-form-hint {
        margin: 0.22rem 0 0;
        color: var(--color-piedra);
        font-size: 0.7rem;
        line-height: 1.4;
    }

    .usr-form-error {
        display: block;
        margin-top: 0.2rem;
        color: var(--color-error);
        font-size: 0.7rem;
        font-weight: 500;
    }

    .usr-form-check {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.55rem 0.65rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: #FCFBF8;
        cursor: pointer;
    }

    .usr-form-check input {
        margin-top: 0.15rem;
        accent-color: var(--color-dorado);
    }

    .usr-form-check strong {
        display: block;
        color: var(--color-noche);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .usr-form-check span {
        display: block;
        color: var(--color-piedra);
        font-size: 0.7rem;
    }

    .usr-role-card {
        display: flex;
        gap: 0.55rem;
        padding: 0.6rem 0.65rem;
        border: 1px solid var(--color-linea);
        border-radius: 5px;
        background: #FCFBF8;
        transition: border-color 0.15s, background-color 0.15s;
    }

    .usr-role-card + .usr-role-card {
        margin-top: 0.5rem;
    }

    .usr-role-card.is-selected {
        border-color: rgba(184, 154, 94, 0.55);
        background: #FBF6EA;
    }

    .usr-role-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--color-noche);
        color: var(--color-hueso);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .usr-role-card.is-manager .usr-role-badge {
        background: #205493;
    }

    .usr-role-card.is-seller .usr-role-badge {
        background: var(--color-piedra);
    }

    .usr-role-card strong {
        display: block;
        color: var(--color-noche);
        font-size: 0.78rem;
    }

    .usr-role-card p {
        margin: 0.1rem 0 0;
        color: var(--color-piedra);
        font-size: 0.68rem;
        line-height: 1.35;
    }

    .usr-form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: 0.8rem;
        padding: 0.7rem 0.9rem;
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
    }

    @media (max-width: 1024px) {
        .usr-form-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .usr-form-grid {
            grid-template-columns: 1fr;
        }

        .usr-form-footer {
            flex-direction: column-reverse;
        }

        .usr-form-footer .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="usr-form-layout">
    <div class="usr-form-main">
        <section class="usr-form-card">
            <header class="usr-form-card-header">
                <span class="usr-form-card-icon" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </span>
                <div>
                    <h2>Datos del colaborador</h2>
                    <p>Identidad y ubicación dentro del equipo</p>
                </div>
            </header>

            <div class="usr-form-card-body">
                <div class="usr-form-field">
                    <label for="user-name">Nombre completo <span class="req">*</span></label>
                    <input
                        type="text"
                        id="user-name"
                        name="name"
                        value="{{ old('name', $isEdit ? $user->name : '') }}"
                        required
                        autocomplete="name"
                        placeholder="Ej. Roberto Morales"
                        class="form-control @error('name') is-invalid @enderror"
                    >
                    @error('name')
                        <span class="usr-form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="usr-form-field">
                    <label for="user-email">Correo electrónico <span class="req">*</span></label>
                    <input
                        type="email"
                        id="user-email"
                        name="email"
                        value="{{ old('email', $isEdit ? $user->email : '') }}"
                        required
                        autocomplete="email"
                        placeholder="colaborador@joyeriaweb.com"
                        class="form-control @error('email') is-invalid @enderror"
                    >
                    @error('email')
                        <span class="usr-form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="usr-form-grid">
                    <div class="usr-form-field">
                        <label for="user-role">Rol de acceso <span class="req">*</span></label>
                        <select id="user-role" name="role" required class="form-select @error('role') is-invalid @enderror">
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <span class="usr-form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="usr-form-field">
                        <label for="user-store">Sucursal</label>
                        <select id="user-store" name="store_id" class="form-select @error('store_id') is-invalid @enderror">
                            <option value="">Sin sucursal</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" @selected((string) $selectedStore === (string) $store->id)>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <span class="usr-form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <p class="usr-form-hint" id="role-hint">
                    {{ $roleDescriptions[$selectedRole] ?? 'Selecciona el nivel de acceso del colaborador.' }}
                </p>
            </div>
        </section>

        <section class="usr-form-card">
            <header class="usr-form-card-header">
                <span class="usr-form-card-icon" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                    </svg>
                </span>
                <div>
                    <h2>Seguridad de la cuenta</h2>
                    <p>Credenciales de acceso y autorizaciones rápidas</p>
                </div>
            </header>

            <div class="usr-form-card-body">
                <div class="usr-form-grid">
                    <div class="usr-form-field">
                        <label for="user-password">
                            {{ $isEdit ? 'Nueva contraseña' : 'Contraseña' }}
                            @unless ($isEdit)
                                <span class="req">*</span>
                            @endunless
                        </label>
                        <input
                            type="password"
                            id="user-password"
                            name="password"
                            @required(! $isEdit)
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="{{ $isEdit ? 'Dejar en blanco para conservar' : 'Mínimo 8 caracteres' }}"
                            class="form-control @error('password') is-invalid @enderror"
                        >
                        @error('password')
                            <span class="usr-form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="usr-form-field">
                        <label for="user-password-confirmation">Confirmar contraseña @unless ($isEdit)<span class="req">*</span>@endunless</label>
                        <input
                            type="password"
                            id="user-password-confirmation"
                            name="password_confirmation"
                            @required(! $isEdit)
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="{{ $isEdit ? 'Confirmar nueva contraseña' : 'Repite la contraseña' }}"
                            class="form-control"
                        >
                    </div>
                </div>

                <div class="usr-form-field">
                    <label for="user-supervisor-pin">PIN de supervisor</label>
                    <input
                        type="password"
                        id="user-supervisor-pin"
                        name="supervisor_pin"
                        maxlength="4"
                        inputmode="numeric"
                        pattern="[0-9]{4}"
                        autocomplete="off"
                        placeholder="{{ $isEdit ? 'Dejar en blanco para conservar el actual' : '4 dígitos (opcional)' }}"
                        class="form-control @error('supervisor_pin') is-invalid @enderror"
                        style="letter-spacing: 0.2em;"
                    >
                    @error('supervisor_pin')
                        <span class="usr-form-error">{{ $message }}</span>
                    @enderror
                    <p class="usr-form-hint">Permite autorizar descuentos y acciones sensibles en mostrador sin ingresar la contraseña completa.</p>
                </div>

                <div class="usr-form-field">
                    <label class="usr-form-check" style="text-transform: none; letter-spacing: normal;">
                        @if ($isSelf)
                            <input type="hidden" name="is_active" value="1">
                        @endif
                        <input type="checkbox" name="is_active" value="1" @checked($isActive) @disabled($isSelf)>
                        <span>
                            <strong>Cuenta activa</strong>
                            <span>
                                {{ $isSelf ? 'No puedes desactivar tu propia cuenta mientras estás en sesión.' : 'Si se desactiva, el colaborador no podrá iniciar sesión hasta que se reactive.' }}
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </section>
    </div>

    <aside class="usr-form-side">
        <section class="usr-form-card">
            <header class="usr-form-card-header">
                <span class="usr-form-card-icon" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                    </svg>
                </span>
                <div>
                    <h2>Nivel de acceso</h2>
                    <p>El rol define las acciones disponibles</p>
                </div>
            </header>

            <div class="usr-form-card-body">
                @foreach ($roleDescriptions as $roleName => $description)
                    @php
                        $roleClass = match ($roleName) {
                            'Gerente' => 'is-manager',
                            'Vendedor' => 'is-seller',
                            default => 'is-owner',
                        };
                        $roleInitials = match ($roleName) {
                            'Dueño' => 'DU',
                            'Gerente' => 'GE',
                            default => 'VE',
                        };
                    @endphp
                    <div class="usr-role-card {{ $roleClass }} {{ $selectedRole === $roleName ? 'is-selected' : '' }}" data-role-card="{{ $roleName }}">
                        <span class="usr-role-badge">{{ $roleInitials }}</span>
                        <div>
                            <strong>{{ $roleName }}</strong>
                            <p>{{ $description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="usr-form-card">
            <header class="usr-form-card-header">
                <span class="usr-form-card-icon" aria-hidden="true">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 8v4l2.5 2.5"></path>
                    </svg>
                </span>
                <div>
                    <h2>Buenas prácticas</h2>
                    <p>Seguridad del equipo</p>
                </div>
            </header>

            <div class="usr-form-card-body">
                <p class="usr-form-hint" style="margin-top: 0;">
                    Usa una contraseña temporal y pide al colaborador cambiarla en su primer ingreso.
                    Asigna el PIN de supervisor solo a quienes autorizan descuentos y acciones sensibles.
                </p>
            </div>
        </section>
    </aside>
</div>

<div class="usr-form-footer">
    <a href="{{ $isEdit ? route('admin.users.show', $user) : route('admin.users.index') }}" class="btn btn-outline">
        Cancelar
    </a>
    <button type="submit" class="btn btn-gold" id="user-submit-btn">
        {{ $isEdit ? 'Guardar cambios' : 'Crear usuario' }}
    </button>
</div>

<script>
    (function () {
        const roleSelect = document.getElementById('user-role');
        const roleHint = document.getElementById('role-hint');
        const cards = document.querySelectorAll('[data-role-card]');
        const descriptions = @json($roleDescriptions);

        if (!roleSelect || !roleHint) {
            return;
        }

        function syncRole() {
            const selected = roleSelect.value;

            roleHint.textContent = descriptions[selected] || 'Selecciona el nivel de acceso del colaborador.';

            cards.forEach(function (card) {
                card.classList.toggle('is-selected', card.dataset.roleCard === selected);
            });
        }

        roleSelect.addEventListener('change', syncRole);
        syncRole();
    })();

    (function () {
        const submitButton = document.getElementById('user-submit-btn');

        if (!submitButton) {
            return;
        }

        const form = submitButton.closest('form');

        if (!form) {
            return;
        }

        form.addEventListener('submit', function () {
            if (submitButton.disabled) {
                return;
            }

            submitButton.disabled = true;
            submitButton.dataset.originalHtml = submitButton.innerHTML;
            submitButton.innerHTML = 'Guardando...';

            window.setTimeout(function () {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButton.dataset.originalHtml;
            }, 8000);
        });
    })();
</script>
