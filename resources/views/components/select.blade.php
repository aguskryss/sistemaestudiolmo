@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'bag' => 'default'])

@php
    $error = $errors->getBag($bag)->first($name);
    $id = $attributes->get('id', $name);
    $actual = (string) old($name, $value instanceof \BackedEnum ? $value->value : $value);
@endphp

<div>
    <label for="{{ $id }}" class="rotulo-texto text-gris">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except('id')->class('campo mt-1') }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $valor => $texto)
            <option value="{{ $valor }}" @selected($actual === (string) $valor)>{{ $texto }}</option>
        @endforeach
    </select>
    @if ($error)
        <p id="{{ $id }}-error" class="mt-2 text-sm">— {{ $error }}</p>
    @endif
</div>
