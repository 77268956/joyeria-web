@extends('layouts.admin')

@section('title', $report['title'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title font-serif">{{ $report['title'] }}</h1>
        <p class="page-subtitle">{{ $report['description'] }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline">Volver</a>
        <a href="{{ route('admin.reports.export', ['report' => $report['key'], 'format' => 'csv'] + request()->only(['from', 'to'])) }}" class="btn btn-outline">Exportar CSV</a>
        <a href="{{ route('admin.reports.export', ['report' => $report['key'], 'format' => 'pdf'] + request()->only(['from', 'to'])) }}" class="btn btn-dark">Exportar PDF</a>
    </div>
</div>

@if ($report['dated'])
    <div class="card" style="margin-bottom: 1.5rem;">
        <form action="{{ route('admin.reports.show', $report['key']) }}" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="min-width: 180px; margin: 0;">
                <label class="form-label" for="from">Desde</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ $report['from'] }}" required>
            </div>
            <div class="form-group" style="min-width: 180px; margin: 0;">
                <label class="form-label" for="to">Hasta</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ $report['to'] }}" required>
            </div>
            <button type="submit" class="btn btn-gold">Aplicar</button>
        </form>
    </div>
@endif

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
    @foreach ($report['totals'] as $total)
        <div class="card">
            <p class="muted" style="margin: 0; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em;">{{ $total['label'] }}</p>
            <p class="font-serif num" style="font-size: 1.6rem; margin: 0.25rem 0 0;">{{ $total['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-wrap">
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
                    <tr><td colspan="{{ count($report['columns']) }}" class="muted" style="text-align: center; padding: 1.5rem;">Sin datos en el período seleccionado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
