@php
    use App\Support\Formato;
    $c = $cotizacion;
    $num = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
@endphp

<x-layouts.app :titulo="$c->titulo" :codigo="'Q-02 — Cotización '.strtolower($c->tipo->label()).($c->numero ? ' N° '.$c->numero : '')">
    <x-slot:bajada>
        <span class="inline-flex flex-wrap items-center gap-x-3 gap-y-1">
            <span class="etiqueta {{ $c->estado->value === 'aceptada' ? 'etiqueta-llena' : '' }}">{{ $c->estado->label() }}</span>
            @if ($c->tipo->value === 'recibida')
                <span>De: {{ $c->contacto?->nombre ?? '—' }}</span>
            @else
                <span>Para: {{ collect([$c->estudio?->nombre, $c->cliente?->nombre])->filter()->join(' / ') ?: '—' }}</span>
            @endif
            @if ($c->obra) <span>· <a href="{{ route('obras.cotizaciones', $c->obra) }}" class="enlace">{{ $c->obra->codigo }} {{ $c->obra->nombre }}</a></span> @endif
        </span>
    </x-slot:bajada>
    <x-slot:acciones>
        <a href="{{ route('cotizaciones.edit', $c) }}" class="btn btn-linea">Editar</a>
    </x-slot:acciones>

    <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <section class="min-w-0">
            @if ($c->items->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="tabla">
                        <thead><tr><th>Descripción</th><th>Unidad</th><th class="num">Cant.</th><th class="num">Precio unit.</th><th class="num">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($c->items as $item)
                                <tr>
                                    <td>{{ $item->descripcion }}</td>
                                    <td class="text-sm text-gris">{{ $item->unidad }}</td>
                                    <td class="num">{{ $num($item->cantidad) }}</td>
                                    <td class="num">{{ Formato::dinero($item->precio_unitario, $c->moneda) }}</td>
                                    <td class="num">{{ Formato::dinero($item->subtotal, $c->moneda) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-6 flex items-baseline justify-between border-t border-tinta pt-4">
                <span class="rotulo-texto">Total</span>
                <span class="text-right">
                    <span class="font-serif text-4xl">{{ Formato::dinero($c->total, $c->moneda) }}</span>
                    @if ($c->moneda->value === 'USD' && $c->tipo_cambio)
                        <span class="block font-mono text-sm text-gris">≈ {{ Formato::dinero($c->total * $c->tipo_cambio, 'ARS') }} (TC {{ $num($c->tipo_cambio) }})</span>
                    @endif
                </span>
            </div>

            @if ($c->observaciones)
                <div class="mt-12">
                    <p class="rotulo-texto border-b border-linea pb-3">Observaciones</p>
                    <p class="mt-4 whitespace-pre-line">{{ $c->observaciones }}</p>
                </div>
            @endif
        </section>

        <aside class="space-y-8">
            <form method="POST" action="{{ route('cotizaciones.estado', $c) }}" class="panel">
                @csrf
                @method('PATCH')
                <p class="rotulo-texto">Cambiar estado</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach (\App\Enums\EstadoCotizacion::cases() as $estado)
                        <button name="estado" value="{{ $estado->value }}"
                            @class(['cursor-pointer border px-3 py-1.5 text-sm', 'border-tinta bg-tinta text-papel' => $c->estado === $estado, 'border-linea hover:border-tinta' => $c->estado !== $estado])>
                            {{ $estado->label() }}
                        </button>
                    @endforeach
                </div>
            </form>

            <dl class="panel grid grid-cols-2 gap-5">
                <x-dato label="Fecha">{{ $c->fecha->format('d.m.Y') }}</x-dato>
                <x-dato label="Válida hasta">
                    @if ($c->valida_hasta)
                        <span @class(['underline decoration-dashed' => $c->valida_hasta->isPast()])>{{ $c->valida_hasta->format('d.m.Y') }}</span>
                    @endif
                </x-dato>
                <x-dato label="Rubro">{{ $c->rubro?->nombre }}</x-dato>
                <x-dato label="Moneda">{{ $c->moneda->label() }}</x-dato>
                <x-dato label="Cargada por" class="col-span-2">{{ $c->creadoPor?->name }}</x-dato>
            </dl>

            <div class="panel">
                <p class="rotulo-texto">Archivos</p>
                <x-adjuntos :modelo="$c" class="mt-3" />
            </div>

            <x-eliminar :action="route('cotizaciones.destroy', $c)" pregunta="¿Eliminar esta cotización?" texto="Eliminar cotización" />
        </aside>
    </div>
</x-layouts.app>
