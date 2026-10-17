{{-- <x-ui::card title="Users"> <x-slot:actions>...</x-slot:actions> body <x-slot:footer>...</x-slot:footer> </x-ui::card> --}}
@props(['title' => null, 'flush' => false])
<div {{ $attributes->class(['card', 'table-card' => $flush]) }}>
    @if ($title || isset($actions))
        <div class="card-header d-flex align-items-center justify-content-between gap-2">
            @if ($title)<h5 class="mb-0">{{ $title }}</h5>@endif
            @isset($actions)<div class="d-flex gap-2">{{ $actions }}</div>@endisset
        </div>
    @endif
    <div @class(['card-body', 'p-0' => $flush])>
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="card-footer">{{ $footer }}</div>
    @endisset
</div>
