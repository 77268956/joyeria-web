@php($customer = $customer ?? null)

<div class="grid-2">
    <div class="form-group">
        <label class="form-label" for="full_name">Nombre completo</label>
        <input type="text" name="full_name" id="full_name" class="form-control" maxlength="150" value="{{ old('full_name', $customer?->full_name) }}" required>
    </div>
    <div class="grid-2">
        <div class="form-group">
            <label class="form-label" for="document_type">Tipo de documento</label>
            <select name="document_type" id="document_type" class="form-select" required>
                @foreach (['DNI', 'Cédula', 'Pasaporte', 'RUC', 'Otro'] as $type)
                    <option value="{{ $type }}" {{ old('document_type', $customer?->document_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="document_number">Número de documento</label>
            <input type="text" name="document_number" id="document_number" class="form-control" maxlength="40" value="{{ old('document_number', $customer?->document_number) }}" required>
        </div>
    </div>
</div>

<div class="grid-3">
    <div class="form-group">
        <label class="form-label" for="phone">Teléfono</label>
        <input type="text" name="phone" id="phone" class="form-control" maxlength="50" value="{{ old('phone', $customer?->phone) }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="email">Correo</label>
        <input type="email" name="email" id="email" class="form-control" maxlength="150" value="{{ old('email', $customer?->email) }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="birth_date">Fecha de nacimiento</label>
        <input type="date" name="birth_date" id="birth_date" class="form-control" value="{{ old('birth_date', $customer?->birth_date?->format('Y-m-d')) }}">
    </div>
</div>

<div class="form-group">
    <label class="form-label" for="address">Dirección</label>
    <input type="text" name="address" id="address" class="form-control" maxlength="255" value="{{ old('address', $customer?->address) }}">
</div>

<div class="grid-2">
    <div class="form-group">
        <label class="form-label" for="id_photo">Foto de identificación (privada)</label>
        <input type="file" name="id_photo" id="id_photo" class="form-control" accept="image/*">
        @if ($customer?->id_photo_path)
            <p class="form-hint">Ya existe una foto cargada. <a href="{{ route('admin.customers.photo', $customer) }}" target="_blank">Ver actual</a>. Sube una nueva para reemplazarla.</p>
        @else
            <p class="form-hint">Se guarda en almacenamiento privado, no accesible públicamente.</p>
        @endif
    </div>
    <div class="form-group">
        <label class="form-label" for="notes">Notas</label>
        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $customer?->notes) }}</textarea>
    </div>
</div>
