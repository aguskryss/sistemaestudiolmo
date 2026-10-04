@props(['obra', 'seccion' => 'Resumen'])

@php
    $pestanas = [
        'obras.show' => 'Resumen',
        'obras.archivos' => 'Archivos',
        'obras.materiales' => 'Materiales',
        'obras.gantt' => 'Calendario',
        'obras.cotizaciones' => 'Cotizaciones',
        'obras.permisos' => 'Permisos',
        'obras.seguros' => 'Seguros',
    ];
@endphp

<x-layouts.app :titulo="$obra->nombre" :codigo="$obra->codigo.' — '.$seccion">
    <x-slot:bajada>
        <span class="inline-flex flex-wrap items-center gap-x-3 gap-y-1">
            <span class="{{ $obra->estado->clase() }}">{{ $obra->estado->label() }}</span>
            <span>
                @if ($obra->estudio)
                    <a href="{{ route('estudios.show', $obra->estudio) }}" class="enlace">{{ $obra->estudio->nombre }}</a>@if ($obra->codigo_estudio) <span class="font-mono text-sm">({{ $obra->codigo_estudio }})</span>@endif
                @else
                    Obra directa
                @endif
                · Cliente: <a href="{{ route('clientes.show', $obra->cliente) }}" class="enlace">{{ $obra->cliente->nombre }}</a>
            </span>
        </span>
    </x-slot:bajada>

    @isset($acciones)
        <x-slot:acciones>{{ $acciones }}</x-slot:acciones>
    @endisset

    <x-slot:pestanas>
        @foreach ($pestanas as $ruta => $nombre)
            @if (Route::has($ruta))
                <a href="{{ route($ruta, $obra) }}"
                   @class(['-mb-px shrink-0 border-b py-3 text-sm transition-colors', 'border-tinta text-tinta' => request()->routeIs($ruta), 'border-transparent text-gris hover:text-tinta' => ! request()->routeIs($ruta)])>
                    {{ $nombre }}
                </a>
            @endif
        @endforeach
    </x-slot:pestanas>

    {{ $slot }}
</x-layouts.app>
