@props(['invertido' => false])

{{-- Cuando subas el logo, guardalo como public/img/logo.svg y se usa automáticamente. --}}
@if (file_exists(public_path('img/logo.svg')))
    <img src="{{ asset('img/logo.svg') }}" alt="{{ config('app.name') }}" {{ $attributes->class(['h-8 w-auto', 'invert' => $invertido]) }}>
@else
    <span {{ $attributes->class('font-serif text-[1.75rem] leading-none tracking-tight') }}>{{ config('app.name') }}</span>
@endif
