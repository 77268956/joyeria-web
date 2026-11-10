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
    danger: '#B05A3A',
    blue: '#3F5F7A',
};

const data = window.dashboardData;

const axisTicks = {
    color: palette.stone,
    font: { family: 'Jost' },
};

const currencyLabel = (item) => ` $${Number(item.parsed.y ?? item.parsed).toFixed(2)}`;

function mountChart(id, config) {
    const canvas = document.getElementById(id);

    if (!canvas || !data) {
        return;
    }

    new Chart(canvas, config);
}

if (data && data.sales) {
    mountChart('sales-chart', {
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
                tooltip: { callbacks: { label: currencyLabel } },
            },
            scales: {
                x: { grid: { display: false }, ticks: axisTicks },
                y: { beginAtZero: true, grid: { color: palette.line }, ticks: axisTicks },
            },
        },
    });
}

if (data && data.paymentMethods) {
    mountChart('payment-methods-chart', {
        type: 'doughnut',
        data: {
            labels: data.paymentMethods.labels,
            datasets: [
                {
                    data: data.paymentMethods.totals,
                    backgroundColor: [
                        palette.night,
                        palette.gold,
                        palette.blue,
                        palette.lightGold,
                        palette.stone,
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
                    labels: { color: palette.stone, font: { family: 'Jost' }, boxWidth: 12, padding: 14 },
                },
                tooltip: {
                    callbacks: {
                        label: (item) => ` ${item.label}: $${Number(item.parsed).toFixed(2)}`,
                    },
                },
            },
        },
    });
}

if (data && data.metals) {
    mountChart('metals-chart', {
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
                        palette.blue,
                        palette.success,
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
                    labels: { color: palette.stone, font: { family: 'Jost' }, boxWidth: 12, padding: 14 },
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

if (data && data.inventoryStatus) {
    mountChart('inventory-status-chart', {
        type: 'bar',
        data: {
            labels: data.inventoryStatus.labels,
            datasets: [
                {
                    label: 'Piezas',
                    data: data.inventoryStatus.totals,
                    backgroundColor: [
                        palette.gold,
                        palette.blue,
                        palette.night,
                        palette.danger,
                        palette.stone,
                    ],
                    borderRadius: 4,
                    barThickness: 18,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (item) => ` ${Number(item.parsed.x)} piezas`,
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: palette.line },
                    ticks: { ...axisTicks, precision: 0 },
                },
                y: { grid: { display: false }, ticks: axisTicks },
            },
        },
    });
}

if (data && data.purchases) {
    mountChart('purchases-chart', {
        type: 'bar',
        data: {
            labels: data.purchases.labels,
            datasets: [
                {
                    label: 'Compras',
                    data: data.purchases.totals,
                    backgroundColor: 'rgba(184, 154, 94, 0.55)',
                    borderColor: palette.gold,
                    borderWidth: 1,
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: currencyLabel } },
            },
            scales: {
                x: { grid: { display: false }, ticks: axisTicks },
                y: { beginAtZero: true, grid: { color: palette.line }, ticks: axisTicks },
            },
        },
    });
}

if (data && data.cashFlow) {
    mountChart('cash-flow-chart', {
        type: 'bar',
        data: {
            labels: data.cashFlow.labels,
            datasets: [
                {
                    label: 'Ingresos',
                    data: data.cashFlow.incoming,
                    backgroundColor: 'rgba(62, 124, 89, 0.65)',
                    borderColor: palette.success,
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Retiros',
                    data: data.cashFlow.outgoing,
                    backgroundColor: 'rgba(176, 90, 58, 0.65)',
                    borderColor: palette.danger,
                    borderWidth: 1,
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: palette.stone, font: { family: 'Jost' }, boxWidth: 12, padding: 14 },
                },
                tooltip: { callbacks: { label: currencyLabel } },
            },
            scales: {
                x: { grid: { display: false }, ticks: axisTicks },
                y: { beginAtZero: true, grid: { color: palette.line }, ticks: axisTicks },
            },
        },
    });
}

const carousel = document.querySelector('[data-chart-carousel]');

if (carousel) {
    const track = carousel.querySelector('[data-carousel-track]');
    const slides = Array.from(carousel.querySelectorAll('[data-chart-slide]'));
    const counter = carousel.querySelector('[data-carousel-counter]');
    const dotsContainer = carousel.querySelector('[data-carousel-dots]');
    let index = 0;
    let startX = null;

    const dots = slides.map((slide, position) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'carousel-dot';
        dot.setAttribute('aria-label', `Ir a la gráfica ${position + 1}`);

        if (dotsContainer) {
            dotsContainer.appendChild(dot);
        }

        dot.addEventListener('click', () => goTo(position));

        return dot;
    });

    function goTo(next) {
        index = (next + slides.length) % slides.length;
        track.style.transform = `translateX(-${index * 100}%)`;

        if (counter) {
            counter.textContent = `${index + 1} / ${slides.length}`;
        }

        dots.forEach((dot, position) => {
            dot.classList.toggle('active', position === index);
            dot.setAttribute('aria-current', position === index ? 'true' : 'false');
        });

        slides.forEach((slide, position) => {
            slide.setAttribute('aria-hidden', position === index ? 'false' : 'true');
        });
    }

    carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => goTo(index - 1));
    carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => goTo(index + 1));

    carousel.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goTo(index - 1);
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            goTo(index + 1);
        }
    });

    carousel.addEventListener('pointerdown', (event) => {
        startX = event.clientX;
    });

    carousel.addEventListener('pointerup', (event) => {
        if (startX === null) {
            return;
        }

        const delta = event.clientX - startX;
        startX = null;

        if (Math.abs(delta) < 40) {
            return;
        }

        goTo(delta < 0 ? index + 1 : index - 1);
    });

    goTo(0);
}

function animateMetrics() {
    if (prefersReducedMotion) {
        return;
    }

    document.querySelectorAll('.metric-value').forEach((element) => {
        const text = element.textContent.trim();
        const match = text.match(/^(\$)?\s*([\d,]+(?:\.\d+)?)$/);

        if (!match) {
            return;
        }

        const prefix = match[1] || '';
        const target = Number(match[2].replace(/,/g, ''));
        const decimals = (match[2].split('.')[1] || '').length;
        const state = { value: 0 };

        animate(state, {
            value: target,
            duration: 1100,
            ease: 'outCubic',
            onUpdate: () => {
                element.textContent = prefix + state.value.toLocaleString('es-MX', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });
            },
        });
    });
}

function animateCharts() {
    if (prefersReducedMotion) {
        return;
    }

    animate('.chart-wrap canvas', {
        opacity: [0, 1],
        scale: [0.97, 1],
        duration: 700,
        delay: stagger(110),
        ease: 'outCubic',
    });
}

animateMetrics();
animateCharts();
