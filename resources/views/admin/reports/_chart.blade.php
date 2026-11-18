<article class="report-chart-card">
    <div class="report-chart-head">
        <h2 class="report-chart-title">{{ $chart['title'] }}</h2>
        <span class="report-chart-hint">{{ $chart['hint'] }}</span>
    </div>
    <div class="report-chart-wrap">
        <canvas id="{{ $chart['id'] }}" data-report-chart aria-label="{{ $chart['title'] }}"></canvas>
        @if (empty($chart['labels']))
            <span class="report-chart-empty">Sin datos en el período</span>
        @endif
    </div>
</article>
