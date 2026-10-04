<div {{ $attributes->class('border border-dashed border-linea px-6 py-12 text-center') }}>
    <p class="text-gris">{{ $slot }}</p>
    @isset($accion)
        <div class="mt-5">{{ $accion }}</div>
    @endisset
</div>
