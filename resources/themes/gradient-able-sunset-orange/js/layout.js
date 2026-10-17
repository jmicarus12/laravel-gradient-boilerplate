// Sidebar behaviour, trimmed down from the template's script.js.

export function initLayout(SimpleBar) {
    const sidebar = document.querySelector('.pc-sidebar');
    if (!sidebar) {
        return;
    }

    // Scrollbar for long menus
    const content = sidebar.querySelector('.navbar-content');
    if (content) {
        new SimpleBar(content);
    }

    // Desktop: collapse / expand sidebar
    document.querySelector('#sidebar-hide')?.addEventListener('click', (e) => {
        e.preventDefault();
        sidebar.classList.toggle('pc-sidebar-hide');
    });

    // Mobile: slide sidebar in with an overlay
    document.querySelector('#mobile-collapse')?.addEventListener('click', (e) => {
        e.preventDefault();
        if (sidebar.classList.contains('mob-sidebar-active')) {
            closeMobile(sidebar);
            return;
        }
        sidebar.classList.add('mob-sidebar-active');
        const overlay = document.createElement('div');
        overlay.className = 'pc-menu-overlay';
        overlay.addEventListener('click', () => closeMobile(sidebar));
        sidebar.appendChild(overlay);
    });

    // Submenus: click a parent item to open it (and close its siblings)
    sidebar.querySelectorAll('.pc-hasmenu > .pc-link').forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const item = link.parentElement;
            const open = item.classList.contains('pc-trigger');
            item.parentElement.querySelectorAll(':scope > .pc-trigger').forEach((sibling) => sibling.classList.remove('pc-trigger'));
            item.classList.toggle('pc-trigger', !open);
        });
    });
}

function closeMobile(sidebar) {
    sidebar.classList.remove('mob-sidebar-active');
    sidebar.querySelector('.pc-menu-overlay')?.remove();
}
