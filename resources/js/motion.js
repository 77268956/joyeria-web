import { animate, stagger, utils } from 'animejs';

const motionPreference = new URLSearchParams(window.location.search).get('motion');
const forcedByMarkup = document.documentElement.classList.contains('motion-forced');
const offByMarkup = document.documentElement.classList.contains('motion-off');
const forceMotion = forcedByMarkup || motionPreference === '1';
const forceMotionOff = offByMarkup || motionPreference === '0';

export const prefersReducedMotion = forceMotion
    ? false
    : forceMotionOff || window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (forceMotion) {
    document.documentElement.classList.add('js-motion', 'motion-forced');

    try {
        window.sessionStorage.setItem('motion-preference', '1');
    } catch (error) {}
}

if (forceMotionOff) {
    document.documentElement.classList.add('motion-off');
    document.documentElement.classList.remove('js-motion');
}

const EASE = 'outCubic';

const VARIANTS = {
    up: { translateY: 28 },
    down: { translateY: -28 },
    left: { translateX: 32 },
    right: { translateX: -32 },
    zoom: { scale: 0.94 },
    fade: {},
};

const AUTO_SELECTORS = [
    '.content-area .metric-card',
    '.content-area .card',
    '.content-area .page-header',
    '.content-area .table tbody tr',
    '.sidebar-brand',
    '.sidebar-nav > li',
    '.login-card .brand-header',
    '.login-card .form-group',
    '.login-card .btn-submit',
    '.login-visual .visual-content',
];

const GROUP_SELECTORS = [
    '.section-head',
    '.cta-band',
    '.hero-inner',
    '.page-header .container',
    '.content-area .page-header',
].join(', ');

const TARGET_SELECTOR = ['[data-animate]', ...AUTO_SELECTORS].join(', ');

function resolveVariant(element) {
    return VARIANTS[element.dataset.animate] || VARIANTS.up;
}

function variantTargets(variant) {
    const targets = {};

    if (variant.translateY) {
        targets.translateY = [variant.translateY, 0];
    }

    if (variant.translateX) {
        targets.translateX = [variant.translateX, 0];
    }

    if (variant.scale) {
        targets.scale = [variant.scale, 1];
    }

    return targets;
}

function siblingIndex(element) {
    const parent = element.parentElement;

    if (!parent) {
        return 0;
    }

    const siblings = [...parent.children].filter((child) => child.matches(TARGET_SELECTOR));

    return Math.min(siblings.indexOf(element), 8);
}

function durationFor(element) {
    const value = Number(element.dataset.animateDuration);

    return Number.isFinite(value) && value > 0 ? value : 750;
}

/**
 * Reveal a single element (or its children when it is a grouped block).
 */
export function reveal(element, { delay = 0 } = {}) {
    if (!element || element.dataset.motionRevealed === '1') {
        return;
    }

    element.dataset.motionRevealed = '1';

    if (element.matches(GROUP_SELECTORS) && element.children.length > 0) {
        utils.set(element, { opacity: 1, translateX: 0, translateY: 0, scale: 1 });
        animate([...element.children], {
            opacity: [0, 1],
            translateY: [18, 0],
            duration: 700,
            delay: stagger(80, { start: delay }),
            ease: EASE,
        });

        return;
    }

    const variant = element.tagName === 'TR' ? VARIANTS.fade : resolveVariant(element);

    animate(element, {
        opacity: [0, 1],
        ...variantTargets(variant),
        duration: durationFor(element),
        delay,
        ease: EASE,
    });
}

function prepare(element) {
    if (element.dataset.motionReady === '1') {
        return false;
    }

    element.dataset.motionReady = '1';

    const isGroup = element.matches(GROUP_SELECTORS);

    if (!element.hasAttribute('data-animate') && element.tagName !== 'TR' && !isGroup) {
        utils.set(element, { opacity: 0, ...resolveVariant(element) });
    }

    return true;
}

/**
 * Animate every registered element once it enters the viewport.
 */
export function initMotion() {
    if (prefersReducedMotion) {
        document.documentElement.classList.remove('js-motion');
        document.querySelectorAll('[data-animate]').forEach((element) => {
            element.style.opacity = '';
            element.style.transform = '';
        });

        return;
    }

    const targets = [...document.querySelectorAll(TARGET_SELECTOR)].filter(prepare);

    if (targets.length === 0) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        targets.forEach((target) => reveal(target));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                observer.unobserve(entry.target);
                reveal(entry.target, { delay: siblingIndex(entry.target) * 70 });
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -6% 0px' },
    );

    targets.forEach((target) => observer.observe(target));
}

/**
 * Animate a numeric value inside an element.
 */
export function countTo(element, value, { duration = 800, format = (current) => Math.round(current).toLocaleString('es-MX') } = {}) {
    if (!element) {
        return;
    }

    if (prefersReducedMotion) {
        element.textContent = format(value);

        return;
    }

    const state = { value: Number(element.dataset.countFrom || 0) };

    animate(state, {
        value,
        duration,
        ease: EASE,
        onUpdate: () => {
            element.textContent = format(state.value);
        },
        onComplete: () => {
            element.dataset.countFrom = String(value);
        },
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMotion);
} else {
    initMotion();
}
