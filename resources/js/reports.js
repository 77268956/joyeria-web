import Chart from 'chart.js/auto';
import { animate, stagger } from 'animejs';
import { prefersReducedMotion } from './motion.js';

const palette = {
    night: '#12161F',
    gold: '#B89A5E',
    lightGold: '#E3D6B4',
    stone: '#6B6B70',
    line: '#E4DFD3',
    success: '#3E7C59',
    danger: '#A63D40',
    blue: '#3F5F7A',
    warning: '#B7791F',
};

const charts = Array.isArray(window.reportData) ? window.reportData : [];

const moneyFormatter = new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const weightFormatter = new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 3,
    maximumFractionDigits: 3,
});

const compactFormatter = new Intl.NumberFormat('es-MX', {
    notation: 'compact',
    maximumFractionDigits: 1,
});

const axisTicks = {
    color: palette.stone,
    font: { family: 'Jost', size: 11 },
};

Chart.defaults.font.family = 'Jost';
Chart.defaults.color = palette.stone;

function withAlpha(hex, alpha) {
    if (typeof hex !== 'string' || !hex.startsWith('#') || hex.length !== 7) {
        return hex;
    }

    const red = parseInt(hex.slice(1, 3), 16);
    const green = parseInt(hex.slice(3, 5), 16);
    const blue = parseInt(hex.slice(5, 7), 16);

    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

function resolveColor(name) {
    return palette[name] || name || palette.gold;
}

function formatValue(value, type) {
    const number = Number(value) || 0;

    if (type === 'money') {
        return `$${moneyFormatter.format(number)}`;
    }

    if (type === 'weight') {
        return `${weightFormatter.format(number)} g`;
    }

    if (type === 'percent') {
        return `${moneyFormatter.format(number)} %`;
    }

    return number.toLocaleString('es-MX', { maximumFractionDigits: 0 });
}

function formatAxis(value, type) {
    const number = Number(value) || 0;

    if (type === 'money') {
        return `$${compactFormatter.format(number)}`;
    }

    if (type === 'weight') {
        return `${compactFormatter.format(number)} g`;
    }

    return compactFormatter.format(number);
}

function parsedValue(item) {
    if (typeof item.parsed === 'number') {
        return item.parsed;
    }

    if (item.parsed && typeof item.parsed === 'object') {
        return item.parsed.y ?? item.parsed.x ?? 0;
    }

    return Number(item.parsed) || 0;
}

function buildDataset(dataset, chart) {
    const baseColor = resolveColor(dataset.color);
    const colors = Array.isArray(dataset.colors)
        ? dataset.colors.map((color) => resolveColor(color))
        : null;
    const isDoughnut = chart.type === 'doughnut';

    return {
        label: dataset.label,
        data: dataset.data,
        borderColor: isDoughnut ? '#FFFFFF' : baseColor,
        backgroundColor: isDoughnut
            ? colors || [baseColor]
            : chart.type === 'line' && dataset.fill
                ? withAlpha(baseColor, 0.12)
                : withAlpha(baseColor, 0.65),
        borderWidth: isDoughnut ? 2 : 2,
        fill: Boolean(dataset.fill),
        tension: chart.type === 'line' ? 0.35 : 0,
        pointBackgroundColor: baseColor,
        pointBorderColor: '#FFFFFF',
        pointBorderWidth: 2,
        pointRadius: chart.type === 'line' ? 3 : 0,
        pointHoverRadius: chart.type === 'line' ? 5 : 0,
        borderRadius: chart.type === 'bar' ? 4 : 0,
        maxBarThickness: 30,
        categoryPercentage: 0.65,
        barPercentage: 0.85,
    };
}

function buildOptions(chart) {
    const valueAxis = {
        beginAtZero: true,
        grid: { color: palette.line },
        ticks: {
            ...axisTicks,
            callback: (value) => formatAxis(value, chart.valueType),
        },
    };

    const categoryAxis = {
        grid: { display: false },
        ticks: {
            ...axisTicks,
            autoSkip: true,
            maxRotation: 0,
            maxTicksLimit: 8,
        },
    };

    if (chart.type === 'doughnut') {
        return {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            spacing: 2,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        color: palette.stone,
                        font: { family: 'Jost', size: 11 },
                        boxWidth: 12,
                        padding: 14,
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (item) => ` ${item.label}: ${formatValue(parsedValue(item), chart.valueType)}`,
                    },
                },
            },
        };
    }

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        indexAxis: chart.horizontal ? 'y' : 'x',
        plugins: {
            legend: {
                display: chart.datasets.length > 1,
                position: 'bottom',
                labels: {
                    color: palette.stone,
                    font: { family: 'Jost', size: 11 },
                    boxWidth: 12,
                    padding: 14,
                    usePointStyle: true,
                },
            },
            tooltip: {
                callbacks: {
                    label: (item) => ` ${item.dataset.label}: ${formatValue(parsedValue(item), chart.valueType)}`,
                },
            },
        },
        scales: chart.horizontal
            ? { x: valueAxis, y: categoryAxis }
            : { x: categoryAxis, y: valueAxis },
    };
}

function mountChart(chart) {
    const canvas = document.getElementById(chart.id);

    if (!canvas) {
        return;
    }

    if (!chart.labels || chart.labels.length === 0) {
        canvas.style.display = 'none';

        return;
    }

    new Chart(canvas, {
        type: chart.type,
        data: {
            labels: chart.labels,
            datasets: chart.datasets.map((dataset) => buildDataset(dataset, chart)),
        },
        options: buildOptions(chart),
    });
}

charts.forEach(mountChart);

function animateCharts() {
    if (prefersReducedMotion) {
        return;
    }

    if (!document.querySelector('.report-chart-wrap canvas:not([style*="display: none"])')) {
        return;
    }

    animate('.report-chart-wrap canvas:not([style*="display: none"])', {
        opacity: [0, 1],
        scale: [0.97, 1],
        duration: 650,
        delay: stagger(90),
        ease: 'outCubic',
    });
}

animateCharts();
