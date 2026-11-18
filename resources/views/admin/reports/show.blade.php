@extends('layouts.admin')

@section('title', $report['title'])

@section('content')
@include('admin.reports._styles')

@php
    $rangeFrom = $report['from'] ?? now()->startOfMonth()->toDateString();
    $rangeTo = $report['to'] ?? today()->toDateString();
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
            <a href="{{ route('admin.reports.index', request()->only(['from', 'to'])) }}" class="reports-back">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Centro de reportes
            </a>
            <span class="reports-eyebrow">{{ $report['dated'] ? 'Reporte por período' : 'Reporte instantáneo' }}</span>
            <h1 class="page-title font-serif">{{ $report['title'] }}</h1>
            <p class="reports-subtitle">
                {{ $report['description'] }}
                @if ($report['dated'])
                    · Del {{ \Illuminate\Support\Carbon::parse($report['from'])->format('d/m/Y') }}
                    al {{ \Illuminate\Support\Carbon::parse($report['to'])->format('d/m/Y') }}
                @endif
            </p>
        </div>

        <div class="page-actions">
            <a href="{{ route('admin.reports.export', ['report' => $report['key'], 'format' => 'csv'] + request()->only(['from', 'to'])) }}" class="btn btn-outline">
                Exportar CSV
            </a>
            <a href="{{ route('admin.reports.export', ['report' => $report['key'], 'format' => 'pdf'] + request()->only(['from', 'to'])) }}" class="btn btn-dark">
                Exportar PDF
            </a>
        </div>
    </div>

    @if ($report['dated'])
        <div class="card reports-filter-card">
            <form action="{{ route('admin.reports.show', $report['key']) }}" method="GET" class="reports-filter">
                <div class="form-group">
                    <label class="form-label" for="from">Desde</label>
                    <input type="date" name="from" id="from" class="form-control" value="{{ $report['from'] }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="to">Hasta</label>
                    <input type="date" name="to" id="to" class="form-control" value="{{ $report['to'] }}" required>
                </div>
                <div class="reports-range-links">
                    @foreach ($quickRanges as $range)
                        <a
                            href="{{ route('admin.reports.show', ['report' => $report['key'], 'from' => $range['from'], 'to' => $range['to']]) }}"
                            class="{{ $report['from'] === $range['from'] && $report['to'] === $range['to'] ? 'active' : '' }}"
                        >{{ $range['label'] }}</a>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-gold">Aplicar</button>
            </form>
        </div>
    @endif

    <div class="report-totals-grid">
        @foreach ($report['totals'] as $total)
            <div class="report-total-card">
                <span>{{ $total['label'] }}</span>
                <strong>{{ $total['value'] }}</strong>
            </div>
        @endforeach
    </div>

    <div class="reports-chart-grid">
        @foreach ($report['charts'] as $chart)
            @include('admin.reports._chart', ['chart' => $chart])
        @endforeach
    </div>

    <section class="card report-table-card">
        <div class="report-table-head">
            <div class="report-section-head">
                <h2>Detalle del reporte</h2>
                <span>{{ count($report['rows']) }} registros</span>
            </div>
        </div>

        <div class="report-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        @foreach ($report['columns'] as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td class="num">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['columns']) }}" class="muted" style="text-align: center; padding: 1.5rem;">
                                Sin datos en el período seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script>
        window.reportData = @json($report['charts']);
    </script>
    @vite('resources/js/reports.js')
@endpush
