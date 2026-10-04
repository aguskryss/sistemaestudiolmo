@props(['name', 'label', 'value' => null, 'bag' => 'default'])

@php
    $error = $errors->getBag($bag)->first($name);
    $id = $attributes->get('id', $name);
@endphp

<div>
    <label for="{{ $id }}" class="rotulo-texto text-gris">{{ $label }}</label>
    <textarea id="{{ $id }}" name="{{ $name }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except('id')->class('campo mt-1') }}>{{ old($name, $value) }}</textarea>
    @if ($error)
        <p id="{{ $id }}-error" class="mt-2 text-sm">— {{ $error }}</p>
    @endif
</div>
