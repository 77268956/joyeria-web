@extends('layouts.public')

@section('title', 'Vender mi Oro — Joyería Web')

@section('content')
    <section class="section">
        <div class="container">
            <div class="section-head" data-animate>
                <span class="eyebrow">Compra de Metales</span>
                <h2 class="section-title">Vender mi <em>oro</em> y plata</h2>
                <p class="section-lead">
                    Obtén una estimación inmediata según el peso y la ley de tu pieza. La valuación final se confirma en tienda con pesaje certificado.
                </p>
            </div>

            <div class="quoter">
                <div class="form-card" data-animate>
                    <form data-quote-form>
                        <div class="form-group">
                            <label class="form-label" for="quote-metal">Metal</label>
                            <select class="form-select" id="quote-metal" data-quote-metal>
                                @foreach ($metals as $metal)
                                    <option value="{{ $metal->id }}">{{ $metal->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="quote-purity">Ley / Quilataje</label>
                            <select class="form-select" id="quote-purity" data-quote-purity></select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="quote-weight">Peso (g)</label>
                            <input type="number" step="0.001" min="0.001" class="form-control" id="quote-weight" data-quote-weight placeholder="Ej: 12.500">
                            <p class="form-hint">Ingresa el peso aproximado de tus piezas.</p>
                        </div>

                        <p class="alert alert-error" data-quote-error style="display: none;"></p>
                    </form>

                    <p class="form-hint" style="margin-top: 1rem;">
                        La estimación es informativa y no aplica descuentos internos. El monto definitivo puede variar tras la verificación de peso y pureza.
                    </p>
                </div>

                <div class="quote-result" data-animate>
                    <span class="quote-caption">Estimación</span>
                    <div class="quote-amount" data-quote-amount>$ 0.00</div>
                    <p class="quote-detail" data-quote-detail>Completa los datos para ver tu estimación.</p>

                    @if ($goldPricePerGram)
                        <p class="quote-note">Precio del oro del día: $ {{ number_format((float) $goldPricePerGram, 4) }} por gramo (24k).</p>
                    @endif
                    <p class="quote-note">Cotización referencial, sujeta a confirmación en tienda.</p>

                    <div class="mt-3">
                        <a href="{{ route('public.store') }}" class="btn-gold btn-block">Visitar tienda para vender</a>
                    </div>
                </div>
            </div>

            <ul class="steps" data-animate style="max-width: 620px; margin-left: auto; margin-right: auto;">
                <li>
                    <span class="step-number">1</span>
                    <div><strong>Cotiza en línea</strong><p class="muted">Usa el estimador para conocer el valor aproximado de tu oro.</p></div>
                </li>
                <li>
                    <span class="step-number">2</span>
                    <div><strong>Visítanos</strong><p class="muted">Pesamos y verificamos la ley de tus piezas con equipo certificado.</p></div>
                </li>
                <li>
                    <span class="step-number">3</span>
                    <div><strong>Recibe tu pago</strong><p class="muted">Te pagamos de inmediato, con total transparencia.</p></div>
                </li>
            </ul>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.publicQuote = {
            url: @json(route('public.sell_gold.quote')),
            metals: @json($metals->map(fn ($metal) => [
                'id' => $metal->id,
                'name' => $metal->name,
                'purities' => $metal->purities->map(fn ($purity) => [
                    'id' => $purity->id,
                    'label' => $purity->label,
                ])->values(),
            ])->values()),
        };
    </script>
@endpush
