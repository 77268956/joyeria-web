@php
    $customer = $customer ?? null;
    $isEdit = $customer !== null;
@endphp

<style>
    .cust-form-wrapper {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
    }

    .cust-form-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(310px, 1fr);
        gap: 0.75rem;
        align-items: start;
    }

    .cust-form-col {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .cust-section-card {
        background: var(--color-blanco);
        border: 1px solid var(--color-linea);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(18, 22, 31, 0.02);
        overflow: hidden;
    }

    .cust-section-header {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.55rem 0.85rem;
        border-bottom: 1px solid var(--color-linea);
        background: #FCFBF8;
    }

    .cust-section-icon {
        display: grid;
        place-items: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
        flex-shrink: 0;
    }

    .cust-section-header h2 {
        margin: 0;
        font-family: var(--font-serif);
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--color-noche);
    }

    .cust-section-header p {
        margin: 0.05rem 0 0;
        font-size: 0.68rem;
        color: var(--color-piedra);
    }

    .cust-section-body {
        padding: 0.75rem 0.85rem;
    }

    .cust-field-group {
        margin-bottom: 0.65rem;
    }

    .cust-field-group:last-child {
        margin-bottom: 0;
    }

    .cust-field-group label {
        display: block;
        margin-bottom: 0.2rem;
        font-size: 0.67rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--color-piedra);
    }

    .cust-field-group label .req {
        color: var(--color-error);
        margin-left: 0.15rem;
    }

    .cust-field-group .form-control,
    .cust-field-group .form-select {
        width: 100%;
        height: 33px;
        padding: 0 0.75rem;
        font-size: 0.82rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: #FCFBF8;
        transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
    }

    .cust-field-group textarea.form-control {
        height: auto;
        padding: 0.45rem 0.75rem;
        font-family: inherit;
        line-height: 1.4;
        resize: vertical;
        font-size: 0.82rem;
    }

    .cust-field-group .form-control:focus,
    .cust-field-group .form-select:focus {
        background: var(--color-blanco);
        border-color: var(--color-dorado);
        box-shadow: 0 0 0 2px rgba(184, 154, 94, 0.12);
        outline: none;
    }

    .cust-field-group .form-control.is-invalid,
    .cust-field-group .form-select.is-invalid {
        border-color: var(--color-error);
        background-color: #FFF5F5;
    }

    .cust-error-msg {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.68rem;
        color: var(--color-error);
        font-weight: 500;
    }

    .cust-form-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .cust-upload-area {
        position: relative;
        border: 2px dashed var(--color-linea);
        border-radius: 5px;
        padding: 0.85rem 0.75rem;
        text-align: center;
        background: #FCFBF8;
        transition: border-color 0.15s, background-color 0.15s;
        cursor: pointer;
    }

    .cust-upload-area:hover {
        border-color: var(--color-dorado);
        background: #FBF9F3;
    }

    .cust-upload-icon {
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        margin: 0 auto 0.35rem;
        border-radius: 50%;
        background: var(--color-crema);
        color: var(--color-dorado);
    }

    .cust-upload-title {
        display: block;
        font-weight: 600;
        font-size: 0.78rem;
        color: var(--color-noche);
    }

    .cust-upload-hint {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.68rem;
        color: var(--color-piedra);
    }

    .cust-upload-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }

    .cust-current-photo {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.5rem 0.65rem;
        margin-bottom: 0.65rem;
        border: 1px solid var(--color-linea);
        border-radius: 4px;
        background: #FCFBF8;
    }

    .cust-current-photo-thumb {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
        border: 1px solid var(--color-linea);
    }

    .cust-current-photo-info {
        flex: 1;
        min-width: 0;
    }

    .cust-current-photo-info strong {
        display: block;
        font-size: 0.78rem;
        color: var(--color-noche);
    }

    .cust-current-photo-info small {
        display: block;
        font-size: 0.68rem;
        color: var(--color-piedra);
    }

    .cust-photo-preview {
        display: none;
        align-items: center;
        gap: 0.65rem;
        padding: 0.45rem 0.65rem;
        margin-top: 0.55rem;
        border: 1px solid rgba(184, 154, 94, 0.4);
        border-radius: 4px;
        background: #FBF6EA;
    }

    .cust-photo-preview img {
        width: 36px;
        height: 36px;
        border-radius: 4px;
        object-fit: cover;
    }

    .cust-photo-preview-text {
        flex: 1;
        min-width: 0;
        font-size: 0.72rem;
        color: #8A6516;
    }

    .cust-photo-preview-text strong {
        display: block;
        color: var(--color-noche);
    }

    .cust-badge-secure {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.18rem 0.55rem;
        border-radius: 999px;
        background: #EDF7EE;
        color: var(--color-exito);
        font-size: 0.65rem;
        font-weight: 600;
        margin-top: 0.45rem;
    }

    .cust-form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        padding: 0.65rem 0.85rem;
        border-top: 1px solid var(--color-linea);
        background: #FCFBF8;
        border-radius: 0 0 6px 6px;
    }

    .cust-form-footer .btn {
        height: 34px;
        padding: 0 0.95rem;
        font-size: 0.8rem;
    }

    @media (max-width: 950px) {
        .cust-form-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 600px) {
        .cust-form-row {
            grid-template-columns: minmax(0, 1fr);
        }
        .cust-form-footer {
            flex-direction: column;
            align-items: stretch;
        }
        .cust-form-footer .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="cust-form-wrapper">
    <div class="cust-form-grid">
        {{-- Columna izquierda: Identidad y Contacto --}}
        <div class="cust-form-col">
            {{-- Tarjeta 1: Datos de identidad --}}
            <div class="cust-section-card">
                <div class="cust-section-header">
                    <span class="cust-section-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <div>
                        <h2>Identidad del Cliente</h2>
                        <p>Nombre oficial y documento para emisión de comprobantes</p>
                    </div>
                </div>

                <div class="cust-section-body">
                    <div class="cust-field-group">
                        <label for="full_name">Nombre completo <span class="req">*</span></label>
                        <input
                            type="text"
                            name="full_name"
                            id="full_name"
                            class="form-control @error('full_name') is-invalid @enderror"
                            maxlength="150"
                            value="{{ old('full_name', $customer?->full_name) }}"
                            placeholder="Ej. María Fernanda Morales Rosas"
                            required
                            autofocus
                        >
                        @error('full_name')
                            <span class="cust-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="cust-form-row">
                        <div class="cust-field-group">
                            <label for="document_type">Tipo de documento <span class="req">*</span></label>
                            <select name="document_type" id="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
                                @php
                                    $currentDocType = old('document_type', $customer?->document_type ?? 'DUI');
                                @endphp
                                @foreach (['DUI', 'Cédula', 'Pasaporte', 'DNI', 'RUC', 'Otro'] as $type)
                                    <option value="{{ $type }}" {{ $currentDocType === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('document_type')
                                <span class="cust-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="cust-field-group">
                            <label for="document_number">Número de documento <span class="req">*</span></label>
                            <input
                                type="text"
                                name="document_number"
                                id="document_number"
                                class="form-control num @error('document_number') is-invalid @enderror"
                                maxlength="40"
                                value="{{ old('document_number', $customer?->document_number) }}"
                                placeholder="Número o folio oficial"
                                required
                            >
                            @error('document_number')
                                <span class="cust-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="cust-field-group" style="margin-bottom: 0;">
                        <label for="birth_date">Fecha de nacimiento</label>
                        <input
                            type="date"
                            name="birth_date"
                            id="birth_date"
                            class="form-control @error('birth_date') is-invalid @enderror"
                            value="{{ old('birth_date', $customer?->birth_date?->format('Y-m-d')) }}"
                        >
                        @error('birth_date')
                            <span class="cust-error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Tarjeta 2: Contacto y ubicación --}}
            <div class="cust-section-card">
                <div class="cust-section-header">
                    <span class="cust-section-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </span>
                    <div>
                        <h2>Contacto y Domicilio</h2>
                        <p>Teléfono, correo y dirección para apartados o avisos</p>
                    </div>
                </div>

                <div class="cust-section-body">
                    <div class="cust-form-row">
                        <div class="cust-field-group">
                            <label for="phone">Teléfono celular / WhatsApp</label>
                            <input
                                type="tel"
                                name="phone"
                                id="phone"
                                class="form-control num @error('phone') is-invalid @enderror"
                                maxlength="50"
                                value="{{ old('phone', $customer?->phone) }}"
                                placeholder="Ej. 7000-0000"
                            >
                            @error('phone')
                                <span class="cust-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="cust-field-group">
                            <label for="email">Correo electrónico</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                maxlength="150"
                                value="{{ old('email', $customer?->email) }}"
                                placeholder="ejemplo@correo.com"
                            >
                            @error('email')
                                <span class="cust-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="cust-field-group" style="margin-bottom: 0;">
                        <label for="address">Dirección completa</label>
                        <input
                            type="text"
                            name="address"
                            id="address"
                            class="form-control @error('address') is-invalid @enderror"
                            maxlength="255"
                            value="{{ old('address', $customer?->address) }}"
                            placeholder="Calle, número, colonia o ciudad"
                        >
                        @error('address')
                            <span class="cust-error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna derecha: Foto de ID, notas y acciones --}}
        <div class="cust-form-col">
            {{-- Tarjeta 3: Foto de identificación --}}
            <div class="cust-section-card">
                <div class="cust-section-header">
                    <span class="cust-section-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                            <circle cx="9" cy="9" r="2"></circle>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                        </svg>
                    </span>
                    <div>
                        <h2>Foto de Identificación</h2>
                        <p>Copia digital de DUI o documento oficial</p>
                    </div>
                </div>

                <div class="cust-section-body">
                    @if ($customer?->id_photo_path)
                        <div class="cust-current-photo">
                            <img
                                src="{{ route('admin.customers.photo', $customer) }}"
                                alt="Identificación de {{ $customer->full_name }}"
                                class="cust-current-photo-thumb"
                            >
                            <div class="cust-current-photo-info">
                                <strong>Identificación registrada</strong>
                                <small>Guardada de forma segura</small>
                                <a href="{{ route('admin.customers.photo', $customer) }}" target="_blank" class="muted" style="font-size: 0.68rem; color: var(--color-dorado); font-weight: 600; text-decoration: underline; margin-top: 0.15rem; display: inline-block;">Ver archivo original</a>
                            </div>
                        </div>
                    @endif

                    <div class="cust-upload-area" id="cust-upload-dropzone">
                        <input
                            type="file"
                            name="id_photo"
                            id="id_photo"
                            class="cust-upload-input"
                            accept="image/jpeg,image/png,image/webp"
                        >
                        <span class="cust-upload-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                        </span>
                        <strong class="cust-upload-title">{{ $customer?->id_photo_path ? 'Subir nueva fotografía' : 'Arrastra o selecciona el documento' }}</strong>
                        <span class="cust-upload-hint">JPG, PNG o WebP (máx. 4 MB)</span>
                    </div>

                    <div class="cust-photo-preview" id="cust-photo-preview">
                        <img id="cust-preview-img" src="" alt="Vista previa">
                        <div class="cust-photo-preview-text">
                            <strong id="cust-preview-name">Archivo seleccionado</strong>
                            <span id="cust-preview-size">—</span>
                        </div>
                    </div>

                    @error('id_photo')
                        <span class="cust-error-msg" style="margin-top: 0.35rem;">{{ $message }}</span>
                    @enderror

                    <div style="margin-top: 0.5rem; text-align: center;">
                        <span class="cust-badge-secure">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <span>Almacenamiento privado y confidencial</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tarjeta 4: Notas y preferencias --}}
            <div class="cust-section-card">
                <div class="cust-section-header">
                    <span class="cust-section-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                    </span>
                    <div>
                        <h2>Notas y Preferencias</h2>
                        <p>Gustos de joyería, tallas o notas de atención</p>
                    </div>
                </div>

                <div class="cust-section-body">
                    <div class="cust-field-group" style="margin-bottom: 0;">
                        <textarea
                            name="notes"
                            id="notes"
                            class="form-control @error('notes') is-invalid @enderror"
                            rows="2"
                            maxlength="2000"
                            placeholder="Ej. Talla de anillo 6.5. Prefiere oro amarillo 14k."
                        >{{ old('notes', $customer?->notes) }}</textarea>
                        @error('notes')
                            <span class="cust-error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Tarjeta 5: Barra de acciones del formulario --}}
            <div class="cust-section-card">
                <div class="cust-form-footer">
                    <a href="{{ $isEdit ? route('admin.customers.show', $customer) : route('admin.customers.index') }}" class="btn btn-outline">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-gold" id="customer-submit-btn" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                            <path d="M17 21v-8H7v8M7 3v5h8"></path>
                        </svg>
                        <span>{{ $isEdit ? 'Guardar cambios' : 'Guardar cliente' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const fileInput = document.getElementById('id_photo');
        const previewWrap = document.getElementById('cust-photo-preview');
        const previewImg = document.getElementById('cust-preview-img');
        const previewName = document.getElementById('cust-preview-name');
        const previewSize = document.getElementById('cust-preview-size');

        if (fileInput && previewWrap && previewImg) {
            fileInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewImg.src = e.target.result;
                            previewName.textContent = file.name;
                            previewSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                            previewWrap.style.display = 'flex';
                        };
                        reader.readAsDataURL(file);
                    }
                } else {
                    previewWrap.style.display = 'none';
                }
            });
        }
    })();
</script>
