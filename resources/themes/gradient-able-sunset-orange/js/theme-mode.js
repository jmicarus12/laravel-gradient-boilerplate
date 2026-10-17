// Light / dark / auto toggle.
// The initial mode is applied by an inline script in partials/head.blade.php
// (before the page paints) so there is no white flash in dark mode.

const STORAGE_KEY = 'theme-mode';
const ORDER = ['auto', 'light', 'dark'];

export function initThemeToggle() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const current = document.documentElement.dataset.themePref || 'auto';
            apply(ORDER[(ORDER.indexOf(current) + 1) % ORDER.length]);
        });
    });

    // Follow the OS while in auto mode
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if ((document.documentElement.dataset.themePref || 'auto') === 'auto') {
            apply('auto');
        }
    });
}

function apply(pref) {
    try {
        localStorage.setItem(STORAGE_KEY, pref);
    } catch {
        // storage blocked (private mode) — still switch for this page
    }
    const dark = pref === 'dark' || (pref === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.dataset.themePref = pref;
    document.documentElement.dataset.bsTheme = dark ? 'dark' : 'light';
}
