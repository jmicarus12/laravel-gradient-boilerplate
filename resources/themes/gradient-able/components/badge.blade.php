@props(['variant' => 'primary'])
<span {{ $attributes->class(['badge', "bg-{$variant}"]) }}>{{ $slot }}</span>
