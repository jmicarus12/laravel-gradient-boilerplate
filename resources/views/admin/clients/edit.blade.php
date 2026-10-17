<x-ui::layouts.admin title="Clients" :breadcrumbs="['Home' => route('admin.dashboard'), 'Clients' => route('admin.clients.index')]">
    Hello Edit Client {{ $client->name }}
</x-ui::layouts.admin>
