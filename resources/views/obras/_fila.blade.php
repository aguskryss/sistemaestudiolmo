@php
    $mostrarEstudio ??= true;
    $mostrarCliente ??= true;
@endphp

<a href="{{ route('obras.show', $obra) }}" class="group grid grid-cols-[5.5rem_minmax(0,1fr)_auto] items-baseline gap-4 border-b border-linea py-4 hover:bg-hueso">
    <span class="font-mono text-sm text-gris">N° {{ str_pad($obra->numero, 3, '0', STR_PAD_LEFT) }}</span>
    <span class="min-w-0">
        <span class="block truncate group-hover:underline">{{ $obra->nombre }}</span>
        <span class="mt-0.5 block truncate text-sm text-gris">
            @if ($mostrarEstudio)
                {{ $obra->estudio?->nombre ?? 'Obra directa' }}@if ($obra->codigo_estudio) <span class="font-mono">({{ $obra->codigo_estudio }})</span>@endif
            @endif
            @if ($mostrarEstudio && $mostrarCliente) · @endif
            @if ($mostrarCliente)
                Cliente: {{ $obra->cliente?->nombre }}
            @endif
            @if ($obra->direccion) · {{ $obra->direccion }} @endif
        </span>
    </span>
    <span class="{{ $obra->estado->clase() }}">{{ $obra->estado->label() }}</span>
</a>
