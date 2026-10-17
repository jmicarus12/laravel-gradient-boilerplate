// Gradient Able theme — entry point
import * as bootstrap from 'bootstrap';
import SimpleBar from 'simplebar';
import 'simplebar/dist/simplebar.css';
import '@phosphor-icons/web/regular';
import '@fontsource/poppins/400.css';
import '@fontsource/poppins/500.css';
import '@fontsource/poppins/600.css';

import { initLayout } from './layout';
import { initCharts } from './charts';
import { initThemeToggle } from './theme-mode';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    initLayout(SimpleBar);
    initThemeToggle();
    initCharts();

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
});
