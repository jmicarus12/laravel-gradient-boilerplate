{{-- <x-ui::select name="status" label="Status" :options="['all' => 'All', 'active' => 'Active']" :value="$status" /> --}}
@props(['name', 'label' => null, 'options' => [], 'value' => null, 'bag' => 'default'])
@php
    $id = $attributes->get('id', $name);
    $error = $errors->getBag($bag)->first($name);
    $selected = (string) old($name, $value);
@endphp
<div {{ $attributes->only('class')->class(['mb-3']) }}>
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" id="{{ $id }}" {{ $attributes->except(['class', 'id'])->class(['form-select', 'is-invalid' => $error]) }}>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif
</div>
