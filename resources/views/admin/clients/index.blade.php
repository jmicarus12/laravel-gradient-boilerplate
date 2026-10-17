<x-ui::layouts.admin title="Clients" :breadcrumbs="['Home' => route('admin.dashboard'), 'Clients' => null]">

    {{-- 1. Plain HTML + Bootstrap classes. No components: write it like you would in the CMS. --}}
    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
        <input type="search" id="client-filter" class="form-control" style="max-width: 320px" placeholder="Quick filter this page…">
        <button class="btn btn-primary" onclick="window.location.href='{{ route('admin.clients.create') }}'">Add Client</button>
    </div>

    <div class="table-responsive bg-body rounded shadow-sm">
        <table class="table table-hover align-middle mb-0" id="clients-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>View code</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td class="fw-semibold">{{ $client->name }}</td>
                        <td>{{ $client->email }}</td>
                        <td><code>{{ $client->view_code }}</code></td>
                        <td>
                            @if ($client->active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-warning">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @can('update', $client)
                                <x-ui::button href="{{ route('admin.clients.edit', $client) }}" size="sm" outline icon="pencil-simple">Edit</x-ui::button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No clients yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Page links (same as {{ $clients->links() }}, styled by the theme) --}}
    <x-ui::pagination :paginator="$clients" class="mt-3" />

    {{-- @push must be INSIDE the layout tag: the layout prints its @stack spots at the closing tag. --}}

    {{-- 2. Page-only CSS. Lands in <head> through @stack('styles') in the layout. --}}
    @push('styles')
        <style>
            #clients-table tr.is-hidden { display: none; }
        </style>
    @endpush

    {{-- 3. Page-only JS. Lands before </body> through @stack('scripts') in the layout. --}}
    @push('scripts')
        <script>
            // Filter the rows on this page as you type.
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('client-filter');
                const rows = document.querySelectorAll('#clients-table tbody tr');
                const count = document.getElementById('client-count');

                input.addEventListener('input', () => {
                    const term = input.value.toLowerCase();
                    let shown = 0;

                    rows.forEach((row) => {
                        const match = row.textContent.toLowerCase().includes(term);
                        row.classList.toggle('is-hidden', !match);
                        if (match) shown++;
                    });

                    count.textContent = `${shown} shown`;
                });
            });
        </script>
    @endpush

</x-ui::layouts.admin>
