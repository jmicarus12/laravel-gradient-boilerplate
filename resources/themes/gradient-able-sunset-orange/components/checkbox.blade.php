{{-- <x-ui::checkbox name="active" label="Active" :checked="true" />                                        --}}
{{-- Lists: <x-ui::checkbox name="roles[]" :value="$role->name" :id="'role-'.$role->id" :checked="..." />  --}}
{{-- (for "name[]" lists the page works out "checked" itself, including old() input)                     --}}
@props(['name', 'label', 'checked' => false, 'value' => '1', 'id' => null, 'help' => null])
@php
    $id ??= $name;
    $isList = str_ends_with($name, '[]');
    $isChecked = $isList ? $checked : old($name, $checked);
@endphp
<div {{ $attributes->only('class')->class(['form-check mb-3']) }}>
    <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($isChecked) {{ $attributes->except('class')->class(['form-check-input', 'input-primary']) }}>
    <label class="form-check-label" for="{{ $id }}">
        {{ $label }}
        @if ($help)<small class="d-block text-muted">{{ $help }}</small>@endif
    </label>
</div>
