<x-ui::layouts.admin title="Icons" :breadcrumbs="['Home' => route('admin.dashboard'), 'Icons' => null]">
    <x-ui::alert type="info" :dismissible="false">
        Click an icon to copy its name. Use it as <code>'icon' =&gt; 'name'</code> in <code>config/menu.php</code>,
        <code>&lt;x-ui::button icon="name"&gt;</code>, or <code>&lt;i class="ph ph-name"&gt;&lt;/i&gt;</code>.
    </x-ui::alert>

    <x-ui::card title="Phosphor icons">
        <x-slot:actions>
            <span class="text-muted small" id="icon-count"></span>
        </x-slot:actions>

        <input type="search" id="icon-filter" class="form-control mb-3" placeholder="Search icons, e.g. user, file, arrow…" autofocus>

        <div id="icon-grid" class="icon-grid"></div>
        <p id="icon-empty" class="text-muted text-center my-4 d-none">No icons match your search.</p>
    </x-ui::card>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="icon-toast" class="toast align-items-center text-bg-primary border-0" role="status">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .icon-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: .5rem; }
            .icon-tile {
                display: flex; flex-direction: column; align-items: center; gap: .4rem;
                padding: .9rem .4rem; border: 1px solid var(--bs-border-color); border-radius: .5rem;
                background: none; color: inherit; font-size: .72rem; word-break: break-all; text-align: center;
            }
            .icon-tile i { font-size: 1.75rem; }
            .icon-tile:hover, .icon-tile:focus-visible { border-color: var(--bs-primary); color: var(--bs-primary); }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Read icon names from the loaded CSS (".ph.ph-gauge::before"), so this page
                // always lists exactly the icons the theme ships — no list to keep up to date.
                const names = new Set();
                for (const sheet of document.styleSheets) {
                    let rules;
                    try { rules = sheet.cssRules; } catch { continue; } // other-origin sheet
                    for (const rule of rules) {
                        const match = rule.selectorText?.match(/^\.ph\.ph-([a-z0-9-]+)::?before$/);
                        if (match) names.add(match[1]);
                    }
                }

                const grid = document.getElementById('icon-grid');
                const count = document.getElementById('icon-count');
                const empty = document.getElementById('icon-empty');
                const toastEl = document.getElementById('icon-toast');
                const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 1500 });

                const tiles = [...names].sort().map((name) => {
                    const tile = document.createElement('button');
                    tile.type = 'button';
                    tile.className = 'icon-tile';
                    tile.dataset.name = name;
                    tile.innerHTML = `<i class="ph ph-${name}"></i><span>${name}</span>`;
                    grid.appendChild(tile);
                    return tile;
                });

                const update = () => {
                    const term = document.getElementById('icon-filter').value.trim().toLowerCase();
                    let shown = 0;
                    tiles.forEach((tile) => {
                        const match = tile.dataset.name.includes(term);
                        tile.hidden = ! match;
                        if (match) shown++;
                    });
                    count.textContent = `${shown} of ${tiles.length}`;
                    empty.classList.toggle('d-none', shown > 0);
                };

                document.getElementById('icon-filter').addEventListener('input', update);
                update();

                grid.addEventListener('click', async (event) => {
                    const tile = event.target.closest('.icon-tile');
                    if (! tile) return;
                    try {
                        await navigator.clipboard.writeText(tile.dataset.name);
                        toastEl.querySelector('.toast-body').textContent = `Copied "${tile.dataset.name}"`;
                    } catch {
                        toastEl.querySelector('.toast-body').textContent = tile.dataset.name;
                    }
                    toast.show();
                });
            });
        </script>
    @endpush
</x-ui::layouts.admin>
