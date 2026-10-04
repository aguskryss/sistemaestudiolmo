@php use App\Support\Formato; @endphp

<x-layouts.obra :obra="$obra" seccion="Cotizaciones">
    <x-slot:acciones>
        <a href="{{ route('cotizaciones.create', ['tipo' => 'recibida', 'obra_id' => $obra->id]) }}" class="btn btn-linea">Cargar recibida</a>
        <a href="{{ route('cotizaciones.create', ['tipo' => 'emitida', 'obra_id' => $obra->id]) }}" class="btn">Nueva emitida</a>
    </x-slot:acciones>

    @if ($comparacion->isNotEmpty())
        <section class="mb-14">
            <p class="rotulo-texto border-b border-linea pb-3">Comparación por rubro</p>
            <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($comparacion as $grupo)
                    @php
                        // Solo se compara dentro de la misma moneda.
                        $porMoneda = $grupo->groupBy(fn ($c) => $c->moneda->value);
                    @endphp
                    <div class="panel">
                        <p class="font-serif text-xl">{{ $grupo->first()->rubro->nombre }}</p>
                        @foreach ($porMoneda as $moneda => $cotis)
                            @php $minimo = $cotis->min(fn ($c) => (float) $c->total); @endphp
                            <ul class="mt-4">
                                @foreach ($cotis->sortBy(fn ($c) => (float) $c->total) as $c)
                                    <li class="flex items-baseline justify-between gap-4 border-b border-linea py-2 text-sm">
                                        <a href="{{ route('cotizaciones.show', $c) }}" class="enlace truncate">{{ $c->contacto?->nombre ?? $c->titulo }}</a>
                                        <span class="flex shrink-0 items-baseline gap-2">
                                            @if ((float) $c->total === $minimo && $cotis->count() > 1)
                                                <span class="etiqueta etiqueta-llena">Menor</span>
                                            @endif
                                            @if ($c->estado->value === 'aceptada')
                                                <span class="etiqueta">Aceptada</span>
                                            @endif
                                            <span class="font-mono">{{ Formato::dinero($c->total, $c->moneda) }}</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($cotizaciones->isEmpty())
        <x-vacio>No hay cotizaciones para esta obra.</x-vacio>
    @else
        @include('cotizaciones._tabla', ['mostrarObra' => false])
    @endif
</x-layouts.obra>
