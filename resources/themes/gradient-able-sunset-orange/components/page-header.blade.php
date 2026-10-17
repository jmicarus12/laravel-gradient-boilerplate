{{-- Breadcrumbs: ['Users' => route('admin.users.index'), 'Edit' => null] --}}
@props(['title', 'breadcrumbs' => []])
<div class="page-header">
    <div class="page-block card mb-0">
        <div class="card-body">
            <div class="page-header-title @if ($breadcrumbs) border-bottom pb-2 mb-2 @endif">
                <h4 class="mb-0">{{ $title }}</h4>
            </div>
            @if ($breadcrumbs)
                <ul class="breadcrumb mb-0">
                    @foreach ($breadcrumbs as $label => $url)
                        <li class="breadcrumb-item" @if (! $url) aria-current="page" @endif>
                            @if ($url)<a href="{{ $url }}">{{ $label }}</a>@else{{ $label }}@endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
