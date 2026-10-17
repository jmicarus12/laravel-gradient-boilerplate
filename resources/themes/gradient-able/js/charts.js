// Draws every <x-ui::chart> on the page with ApexCharts.
// ApexCharts is loaded only on pages that have a chart (separate file, not in app.js).

export async function initCharts() {
    const elements = document.querySelectorAll('[data-ui-chart]');
    if (elements.length === 0) {
        return;
    }

    const { default: ApexCharts } = await import('apexcharts');

    const charts = [...elements].map((element) => {
        const config = JSON.parse(element.dataset.uiChart);
        const chart = new ApexCharts(element, options(config));
        chart.render();
        return { chart, config };
    });

    // Light/dark toggle: redraw with the new colors
    new MutationObserver(() => {
        charts.forEach(({ chart, config }) => chart.updateOptions(options(config)));
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
}

function options({ type, series, labels, height }) {
    const styles = getComputedStyle(document.documentElement);
    const css = (name) => styles.getPropertyValue(`--bs-${name}`).trim();
    const mode = document.documentElement.dataset.bsTheme === 'dark' ? 'dark' : 'light';
    const isPie = type === 'donut' || type === 'pie';

    return {
        chart: { type, height, fontFamily: 'inherit', background: 'transparent', foreColor: css('secondary-color'), toolbar: { show: false } },
        theme: { mode },
        colors: ['primary', 'success', 'warning', 'danger', 'info'].map(css),
        series,
        ...(isPie
            ? { labels, legend: { position: 'bottom' }, stroke: { width: 0 } }
            : { xaxis: { categories: labels }, stroke: { curve: 'smooth', width: type === 'bar' ? 0 : 3 } }),
        grid: { borderColor: css('border-color') },
        dataLabels: { enabled: false },
        tooltip: { theme: mode },
    };
}
