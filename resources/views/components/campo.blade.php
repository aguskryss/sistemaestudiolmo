@props(['name', 'label', 'type' => 'text', 'bag' => 'default', 'value' => null])

@php
    $error = $errors->getBag($bag)->first($name);
    $id = $attributes->get('id', $name);
@endphp

<div>
    <label for="{{ $id }}" class="rotulo-texto text-gris">{{ $label }}</label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except('id')->class('campo mt-1') }}
    >
    @if ($error)
        <p id="{{ $id }}-error" class="mt-2 text-sm">— {{ $error }}</p>
    @endif
</div>
