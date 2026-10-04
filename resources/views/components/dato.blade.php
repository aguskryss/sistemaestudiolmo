@props(['label'])

<div {{ $attributes }}>
    <dt class="rotulo-texto text-gris">{{ $label }}</dt>
    <dd class="mt-1">{{ $slot->isEmpty() ? '—' : $slot }}</dd>
</div>
