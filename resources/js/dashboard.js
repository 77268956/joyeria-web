import Chart from 'chart.js/auto';

const palette = {
    night: '#12161F',
    gold: '#B89A5E',
    lightGold: '#E3D6B4',
    stone: '#6B6B70',
    line: '#E4DFD3',
};

const data = window.dashboardData;
const salesCanvas = document.getElementById('sales-chart');
const metalsCanvas = document.getElementById('metals-chart');

if (salesCanvas && data) {
    new Chart(salesCanvas, {
        type: 'line',
        data: {
            labels: data.sales.labels,
            datasets: [
                {
                    label: 'Ventas',
                    data: data.sales.totals,
                    borderColor: palette.gold,
                    backgroundColor: 'rgba(184, 154, 94, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: palette.night,
                    pointBorderColor: palette.night,
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (item) => ` ${Number(item.parsed.y).toFixed(2)}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: palette.stone, font: { family: 'Jost' } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: palette.line },
                    ticks: { color: palette.stone, font: { family: 'Jost' } },
                },
            },
        },
    });
}

if (metalsCanvas && data) {
    new Chart(metalsCanvas, {
        type: 'doughnut',
        data: {
            labels: data.metals.labels,
            datasets: [
                {
                    data: data.metals.grams,
                    backgroundColor: [
                        palette.night,
                        palette.gold,
                        palette.lightGold,
                        palette.stone,
                        '#3F5F7A',
                        '#3E7C59',
                    ],
                    borderColor: '#FFFFFF',
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: palette.stone, font: { family: 'Jost' }, boxWidth: 12 },
                },
                tooltip: {
                    callbacks: {
                        label: (item) => ` ${Number(item.parsed).toFixed(3)} g`,
                    },
                },
            },
        },
    });
}
