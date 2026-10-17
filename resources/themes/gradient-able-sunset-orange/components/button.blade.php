{{-- <x-ui::button variant="primary" icon="plus" href="...">Create</x-ui::button>  (no href → <button>) --}}
@props(['variant' => 'primary', 'size' => null, 'icon' => null, 'href' => null, 'type' => 'submit', 'outline' => false])
@php
    $classes = ['btn', ($outline ? 'btn-outline-' : 'btn-').$variant, $size ? "btn-{$size}" : null, 'd-inline-flex align-items-center gap-1'];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<i class="ph ph-{{ $icon }}"></i>@endif{{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)<i class="ph ph-{{ $icon }}"></i>@endif{{ $slot }}
    </button>
@endif
