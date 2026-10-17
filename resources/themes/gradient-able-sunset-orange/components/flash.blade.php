{{-- Session messages: ->with('success', '...') / 'error' / 'status' --}}
@foreach (['success' => 'success', 'status' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $type)
    @if (session()->has($key))
        <x-ui::alert :type="$type">{{ session($key) }}</x-ui::alert>
    @endif
@endforeach
