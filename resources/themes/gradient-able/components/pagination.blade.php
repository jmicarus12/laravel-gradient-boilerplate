{{-- <x-ui::pagination :paginator="$admins" /> — page links in this theme's style --}}
@props(['paginator'])
@if ($paginator->hasPages())
    <div {{ $attributes->class(['ui-pagination']) }}>
        {{ $paginator->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
@endif
