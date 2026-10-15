@extends('layouts.public')

@section('title', 'Sucursal — Joyería Web')

@section('content')
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

                    <div class="mt-3">
                        <a href="{{ route('public.contact') }}" class="btn-gold">Enviar mensaje</a>
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
@endsection
