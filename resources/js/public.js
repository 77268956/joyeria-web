import '../css/public.css';

import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation, Pagination, Zoom } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import 'swiper/css/zoom';

import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Initialise Swiper carousels. Each carousel declares its behaviour through a
 * `.js-swiper` class plus a data attribute for the variant.
 */
function initCarousels() {
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
 * Reveal elements softly as they scroll into view.
 */
function initRevealAnimations() {
    const targets = document.querySelectorAll('[data-animate]');

    if (prefersReducedMotion || targets.length === 0) {
        targets.forEach((target) => {
            target.style.opacity = '1';
        });

        return;
    }

    targets.forEach((target) => {
        gsap.fromTo(
            target,
            { opacity: 0, y: 24 },
            {
                opacity: 1,
                y: 0,
                duration: 0.7,
                ease: 'power2.out',
                scrollTrigger: { trigger: target, start: 'top 88%' },
            },
        );
    });
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
            if (amountEl) amountEl.textContent = '$ 0.00';
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

            if (amountEl) amountEl.textContent = money(data.amount);
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
    initCarousels();
    initRevealAnimations();
    initMobileNav();
    initQuoter();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
