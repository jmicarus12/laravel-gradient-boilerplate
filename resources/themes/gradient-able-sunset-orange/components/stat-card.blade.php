{{-- Colored summary tile. variant: primary|success|warning|danger|info --}}
@props(['label', 'value', 'icon' => null, 'variant' => 'primary', 'note' => null, 'noteValue' => null])
<div {{ $attributes->class(['card', 'order-card', "bg-grd-{$variant}"]) }}>
    <div class="card-body">
        <h6 class="text-white">{{ $label }}</h6>
        <h2 class="text-end text-white">
            @if ($icon)<i class="ph ph-{{ $icon }} float-start"></i>@endif
            <span>{{ $value }}</span>
        </h2>
        @if ($note)
            <p class="mb-0 text-white">{{ $note }}<span class="float-end">{{ $noteValue }}</span></p>
        @endif
    </div>
</div>
