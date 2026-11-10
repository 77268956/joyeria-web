import '../css/public.css';

import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation, Pagination, Zoom } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import 'swiper/css/zoom';

import { animate, stagger } from 'animejs';
import { prefersReducedMotion } from './motion.js';

/**
 * Initialise Swiper carousels. Each carousel declares its behaviour through a
 * `.js-swiper` class plus a data attribute for the variant.
 */
function initCarousels() {
    document.querySelectorAll('.js-hero-swiper').forEach((element) => {
        new Swiper(element, {
            modules: [Navigation, Pagination, Autoplay, EffectFade],
            effect: 'fade',
            fadeEffect: { crossFade: true },
            loop: element.querySelectorAll('.swiper-slide').length > 1,
            speed: 700,
            autoplay: prefersReducedMotion ? false : { delay: 5500, disableOnInteraction: false },
            pagination: { clickable: true },
            navigation: { nextEl: element.querySelector('.swiper-button-next'), prevEl: element.querySelector('.swiper-button-prev') },
            on: {
                init(instance) {
                    animateHeroSlide(instance.slides[instance.activeIndex]);
                },
                slideChangeTransitionStart(instance) {
                    animateHeroSlide(instance.slides[instance.activeIndex]);
                },
            },
        });
    });

    document.querySelectorAll('.js-featured-swiper').forEach((element) => {
        new Swiper(element, {
            modules: [Navigation, Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 24,
            loop: element.dataset.loop === 'true',
            autoplay: prefersReducedMotion ? false : { delay: 4500, disableOnInteraction: false },
            pagination: element.dataset.pagination === 'false' ? false : { clickable: true },
            navigation: { nextEl: element.querySelector('.swiper-button-next'), prevEl: element.querySelector('.swiper-button-prev') },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: element.dataset.perView ? Number(element.dataset.perView) : 3 },
            },
        });
    });

    document.querySelectorAll('.js-gallery-swiper').forEach((element) => {
        new Swiper(element, {
            modules: [Navigation, Pagination, Zoom],
            slidesPerView: 1,
            zoom: true,
            pagination: { clickable: true },
            navigation: { nextEl: element.querySelector('.swiper-button-next'), prevEl: element.querySelector('.swiper-button-prev') },
        });
    });
}

/**
 * Animate the content of the active hero slide.
 */
function animateHeroSlide(slide) {
    if (!slide || prefersReducedMotion) {
        return;
    }

    const content = slide.querySelector('.hero-slide-content');

    if (!content) {
        return;
    }

    animate(content.children, {
        opacity: [0, 1],
        translateY: [26, 0],
        duration: 850,
        delay: stagger(80, { start: 180 }),
        ease: 'outCubic',
    });
}

/**
 * Animate the top bar and navigation on first paint.
 */
function initChromeAnimations() {
    if (prefersReducedMotion) {
        return;
    }

    animate('.topbar', { opacity: [0, 1], translateY: [-14, 0], duration: 550, ease: 'outCubic' });
    animate('.navbar-main', { opacity: [0, 1], translateY: [-16, 0], duration: 650, delay: 80, ease: 'outCubic' });
    animate('.navbar-menu', { opacity: [0, 1], translateY: [-10, 0], duration: 650, delay: 160, ease: 'outCubic' });
}

/**
 * Toggle the mobile navigation drawer.
 */
function initMobileNav() {
    const toggle = document.getElementById('navbar-toggle');
    const menu = document.getElementById('navbar-menu');

    if (!toggle || !menu) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        if (isOpen && !prefersReducedMotion) {
            animate(menu.querySelectorAll('li'), {
                opacity: [0, 1],
                translateX: [-12, 0],
                duration: 400,
                delay: stagger(45),
                ease: 'outCubic',
            });
        }
    });
}

/**
 * Estimated gold buy-back calculator (no internal discount is applied).
 */
function initQuoter() {
    const form = document.querySelector('[data-quote-form]');

    if (!form || !window.publicQuote) {
        return;
    }

    const config = window.publicQuote;
    const metalSelect = form.querySelector('[data-quote-metal]');
    const puritySelect = form.querySelector('[data-quote-purity]');
    const weightInput = form.querySelector('[data-quote-weight]');
    const amountEl = document.querySelector('[data-quote-amount]');
    const detailEl = document.querySelector('[data-quote-detail]');
    const errorEl = document.querySelector('[data-quote-error]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const money = (value) => '$ ' + Number(value).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    let currentAmount = 0;
    let amountAnimation = null;

    function setAmount(value) {
        const target = Number(value) || 0;

        if (!amountEl) {
            return;
        }

        amountAnimation?.cancel();

        if (prefersReducedMotion) {
            amountEl.textContent = money(target);
            currentAmount = target;

            return;
        }

        const state = { value: currentAmount };

        amountAnimation = animate(state, {
            value: target,
            duration: 650,
            ease: 'outCubic',
            onUpdate: () => {
                currentAmount = state.value;
                amountEl.textContent = money(state.value);
            },
        });
    }

    function fillPurities() {
        const metal = (config.metals || []).find((item) => String(item.id) === String(metalSelect.value));
        const purities = metal ? metal.purities : [];

        puritySelect.innerHTML = purities
            .map((purity) => `<option value="${purity.id}">${purity.label}</option>`)
            .join('');
    }

    let timer = null;

    async function quote() {
        const weight = Number(weightInput.value);

        if (!metalSelect.value || !puritySelect.value || !weight || weight <= 0) {
            setAmount(0);
            if (detailEl) detailEl.textContent = 'Completa los datos para ver tu estimación.';
            if (errorEl) errorEl.style.display = 'none';

            return;
        }

        try {
            const response = await fetch(config.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    metal_id: metalSelect.value,
                    purity_id: puritySelect.value,
                    weight_g: weight,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'No se pudo calcular la estimación.');
            }

            setAmount(data.amount);
            if (detailEl) detailEl.textContent = `${data.purity} · ${money(data.price_per_gram)} por gramo`;
            if (errorEl) errorEl.style.display = 'none';
        } catch (error) {
            if (errorEl) {
                errorEl.textContent = error.message;
                errorEl.style.display = 'block';
            }
        }
    }

    function scheduleQuote() {
        clearTimeout(timer);
        timer = setTimeout(quote, 300);
    }

    metalSelect.addEventListener('change', () => {
        fillPurities();
        quote();
    });
    puritySelect.addEventListener('change', quote);
    weightInput.addEventListener('input', scheduleQuote);

    fillPurities();
    quote();
}

function boot() {
    initChromeAnimations();
    initCarousels();
    initMobileNav();
    initQuoter();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
