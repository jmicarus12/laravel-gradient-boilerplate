{{-- <x-ui::input name="email" type="email" label="Email" required />  Shows old() value and validation error. --}}
{{-- bag: named error bag, for pages with several forms (->validateWithBag('deleteAccount', ...)) --}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'help' => null, 'bag' => 'default'])
@php
    $id = $attributes->get('id', $name);
    $error = $errors->getBag($bag)->first($name);
@endphp
<div class="mb-3">
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        @unless ($type === 'password') value="{{ old($name, $value) }}" @endunless
        {{ $attributes->class(['form-control', 'is-invalid' => $error]) }}
    >
    @if ($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @elseif ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
