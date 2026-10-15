@extends('layouts.public')

@section('title', 'Contacto — Joyería Web')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-head" data-animate>
                <span class="eyebrow">Contacto</span>
                <h2 class="section-title">Escríbenos</h2>
                <p class="section-lead">Cuéntanos qué necesitas y te responderemos a la brevedad.</p>
            </div>

            <div class="store-grid">
                <div data-animate>
                    <div class="form-card">
                        @if (session('contact_status'))
                            <div class="alert alert-success">{{ session('contact_status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-error">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('public.contact.store') }}" method="POST">
                            @csrf

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="name">Nombre <span style="color: var(--color-error);">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" maxlength="120" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="phone">Teléfono</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="email">Correo electrónico <span style="color: var(--color-error);">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" maxlength="150" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="message">Mensaje <span style="color: var(--color-error);">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="5" maxlength="2000" required>{{ old('message') }}</textarea>
                            </div>

                            <!-- Honeypot anti-spam: oculto para personas, tentador para bots -->
                            <div class="hp-field" aria-hidden="true">
                                <label for="website">No completar este campo</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <button type="submit" class="btn-gold btn-block">Enviar mensaje</button>
                        </form>
                    </div>
                </div>

                <div data-animate>
                    <ul class="info-list">
                        @if ($publicStore?->address)
                            <li>
                                <span class="info-icon">&#9737;</span>
                                <div>
                                    <strong>Dirección</strong>
                                    <p class="muted">{{ $publicStore->address }}</p>
                                </div>
                            </li>
                        @endif
                        @if ($publicStore?->phone)
                            <li>
                                <span class="info-icon">&#9742;</span>
                                <div>
                                    <strong>Teléfono</strong>
                                    <p class="muted"><a href="tel:{{ $publicStore->phone }}" style="text-decoration: none;">{{ $publicStore->phone }}</a></p>
                                </div>
                            </li>
                        @endif
                        @if ($publicSettings?->whatsapp_number)
                            <li>
                                <span class="info-icon">&#9990;</span>
                                <div>
                                    <strong>WhatsApp</strong>
                                    <p class="muted">
                                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $publicSettings->whatsapp_number) }}" target="_blank" rel="noopener" style="text-decoration: none;">Escríbenos ahora</a>
                                    </p>
                                </div>
                            </li>
                        @endif
                    </ul>

                    <p class="form-hint mt-3">
                        Tus datos se usan únicamente para responder tu consulta. No compartimos tu información con terceros.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
