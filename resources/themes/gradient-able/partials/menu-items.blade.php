@foreach ($items as $item)
    @if (isset($item['caption']))
        <li class="pc-item pc-caption"><label>{{ $item['caption'] }}</label></li>
    @elseif (! empty($item['children']))
        <li @class(['pc-item', 'pc-hasmenu', 'active pc-trigger' => $item['is_active']])>
            <a href="#" class="pc-link">
                @isset($item['icon'])<span class="pc-micon"><i class="ph ph-{{ $item['icon'] }}"></i></span>@endisset
                <span class="pc-mtext">{{ $item['label'] }}</span>
                <span class="pc-arrow"><i class="ph ph-caret-right"></i></span>
            </a>
            <ul class="pc-submenu">
                @include('theme::partials.menu-items', ['items' => $item['children']])
            </ul>
        </li>
    @else
        <li @class(['pc-item', 'active' => $item['is_active']])>
            <a href="{{ $item['url'] }}" class="pc-link">
                @isset($item['icon'])<span class="pc-micon"><i class="ph ph-{{ $item['icon'] }}"></i></span>@endisset
                <span class="pc-mtext">{{ $item['label'] }}</span>
            </a>
        </li>
    @endif
@endforeach
