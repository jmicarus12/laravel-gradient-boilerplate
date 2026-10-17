<x-ui::layouts.user title="Dashboard">
    <x-ui::page-header title="Welcome, {{ auth('client')->user()->name }}" />

    <x-ui::card title="Your view">
        Hello World
    </x-ui::card>
</x-ui::layouts.user>