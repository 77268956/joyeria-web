@extends('layouts.admin')

@section('title', 'Centro de reportes')

@section('content')
@include('admin.reports._styles')

@php
    $quickRanges = [
        ['label' => 'Hoy', 'from' => today()->toDateString(), 'to' => today()->toDateString()],
        ['label' => '7 días', 'from' => today()->subDays(6)->toDateString(), 'to' => today()->toDateString()],
        ['label' => 'Este mes', 'from' => now()->startOfMonth()->toDateString(), 'to' => today()->toDateString()],
        ['label' => 'Año', 'from' => now()->startOfYear()->toDateString(), 'to' => today()->toDateString()],
    ];
@endphp

<div class="reports-page">
    <div class="reports-hero">
        <div>
            <span class="reports-eyebrow">Control y supervisión</span>
            <h1 class="page-title font-serif">Centro de reportes</h1>
            <p class="reports-subtitle">
                Del {{ \Illuminate\Support\Carbon::parse($overview['from'])->format('d/m/Y') }}
                al {{ \Illuminate\Support\Carbon::parse($overview['to'])->format('d/m/Y') }}
                · {{ $overview['reportCount'] }} reportes disponibles
            </p>
        </div>

        <form action="{{ route('admin.reports.index') }}" method="GET" class="reports-filter">
            <div class="form-group">
                <label class="form-label" for="from">Desde</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ $overview['from'] }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="to">Hasta</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ $overview['to'] }}" required>
            </div>
            <div class="reports-range-links">
                @foreach ($quickRanges as $range)
                    <a
                        href="{{ route('admin.reports.index', ['from' => $range['from'], 'to' => $range['to']]) }}"
                        class="{{ $overview['from'] === $range['from'] && $overview['to'] === $range['to'] ? 'active' : '' }}"
                    >{{ $range['label'] }}</a>
                @endforeach
            </div>
            <button type="submit" class="btn btn-gold">Aplicar</button>
        </form>
    </div>

    <div class="reports-metrics">
        @foreach ($overview['metrics'] as $metric)
            <article class="report-metric">
                <span class="report-metric-icon tone-{{ $metric['tone'] }}">
                    @include('admin.reports._icon', ['name' => $metric['icon'], 'size' => 16])
                </span>
                <div class="report-metric-body">
                    <span class="report-metric-label">{{ $metric['label'] }}</span>
                    <strong class="report-metric-value">{{ $metric['value'] }}</strong>
                    <span class="report-metric-hint">{{ $metric['hint'] }}</span>
                </div>
            </article>
        @endforeach
    </div>

    <div class="reports-chart-grid">
        @foreach ($overview['charts'] as $chart)
            @include('admin.reports._chart', ['chart' => $chart])
        @endforeach
    </div>

    <div class="reports-two-col">
        <section class="card report-panel">
            <div class="report-section-head">
                <h2>Piezas más vendidas</h2>
                <span>Top 6 del período</span>
            </div>

            @if (count($overview['topProducts']) > 0)
                <div class="report-list">
                    @foreach ($overview['topProducts'] as $product)
                        <div class="report-list-row">
                            <div>
                                <strong>{{ $product['name'] }}</strong>
                                <span>{{ $product['pieces'] }} {{ $product['pieces'] === 1 ? 'pieza' : 'piezas' }}</span>
                            </div>
                            <span class="report-list-amount">$ {{ $product['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="reports-empty">Sin ventas en el período seleccionado.</p>
            @endif
        </section>

        <section class="card report-panel">
            <div class="report-section-head">
                <h2>Clientes con mayor compra</h2>
                <span>Top 6 del período</span>
            </div>

            @if (count($overview['topCustomers']) > 0)
                <div class="report-list">
                    @foreach ($overview['topCustomers'] as $customer)
                        <div class="report-list-row">
                            <div>
                                <strong>{{ $customer['name'] }}</strong>
                                <span>{{ $customer['sales'] }} {{ $customer['sales'] === 1 ? 'venta' : 'ventas' }}</span>
                            </div>
                            <span class="report-list-amount">$ {{ $customer['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="reports-empty">Sin clientes con compras en el período seleccionado.</p>
            @endif
        </section>
    </div>

    <section class="card report-panel">
        <div class="report-section-head">
            <h2>Reportes disponibles</h2>
            <span>{{ count($reports) }} módulos exportables</span>
        </div>

        <div class="report-catalogue">
            @foreach ($reports as $slug => $report)
                <a href="{{ route('admin.reports.show', $slug) }}" class="report-link-card">
                    <div class="report-link-head">
                        <span class="report-link-icon">
                            @include('admin.reports._icon', ['name' => $slug, 'size' => 15])
                        </span>
                        <span class="badge {{ $report['dated'] ? 'badge-gold' : 'badge-info' }}">
                            {{ $report['dated'] ? 'Por período' : 'Instantáneo' }}
                        </span>
                    </div>
                    <h3>{{ $report['title'] }}</h3>
                    <p>{{ $report['description'] }}</p>
                    <span class="report-link-cta">
                        Abrir reporte
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script>
        window.reportData = @json($overview['charts']);
    </script>
    @vite('resources/js/reports.js')
@endpush
