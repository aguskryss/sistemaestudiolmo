@php
    use App\Enums\EstadoMaterial;
    $cantidad = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
    $claseEstado = fn (EstadoMaterial $e) => match ($e) {
        EstadoMaterial::Necesito => 'etiqueta etiqueta-alerta',
        EstadoMaterial::Pedido => 'etiqueta',
        EstadoMaterial::EntregadoParcial => 'etiqueta',
        EstadoMaterial::Entregado => 'etiqueta etiqueta-llena',
    };
    $filtros = ['' => 'Todos', 'pendientes' => 'Pendientes'] + EstadoMaterial::opciones();
@endphp

<x-layouts.obra :obra="$obra" seccion="Materiales">
    <x-slot:acciones>
        <button type="button" class="btn" x-data x-on:click="$dispatch('nuevo-material')">Agregar material</button>
    </x-slot:acciones>

    {{-- Resumen por estado / filtros --}}
    <nav class="mb-8 flex flex-wrap gap-2" aria-label="Filtrar por estado">
        @foreach ($filtros as $valor => $nombre)
            @php
                $n = match ($valor) {
                    '' => $conteo->sum(),
                    'pendientes' => $conteo->sum() - ($conteo['entregado'] ?? 0),
                    default => $conteo[$valor] ?? 0,
                };
                $activo = (string) $estado === (string) $valor;
            @endphp
            <a href="{{ route('obras.materiales', [$obra, 'estado' => $valor ?: null]) }}"
               @class(['border px-3 py-1.5 text-sm', 'border-tinta bg-tinta text-papel' => $activo, 'border-linea hover:border-tinta' => ! $activo])>
                {{ $nombre }} <span class="ml-1 font-mono text-xs opacity-60">{{ $n }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Alta --}}
    <form method="POST" action="{{ route('obras.materiales.store', $obra) }}"
          x-data="{ abierto: {{ $errors->any() && old('cantidad_necesaria') !== null ? 'true' : 'false' }} }"
          x-on:nuevo-material.window="abierto = true; $nextTick(() => $refs.nombre.focus())"
          x-show="abierto" x-cloak class="panel mb-10">
        @csrf
        <p class="rotulo-texto">Necesito</p>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-3" x-data="{ nuevo: @js(old('nombre') !== null && old('material_id') === null) }">
                <div x-show="! nuevo">
                    <x-select name="material_id" label="Material" placeholder="Elegí del catálogo" :options="$catalogo" x-bind:disabled="nuevo" x-ref="nombre" />
                    <button type="button" class="enlace mt-2 cursor-pointer text-xs text-gris hover:text-tinta" x-on:click="nuevo = true">+ Uno que no está en el catálogo</button>
                </div>
                <div x-show="nuevo" x-cloak class="grid grid-cols-[1fr_7rem] gap-4">
                    <x-campo name="nombre" label="Material nuevo" x-bind:disabled="! nuevo" placeholder="Ej: Cemento Portland" />
                    <x-select name="unidad" label="Unidad" :options="$unidades->mapWithKeys(fn ($u) => [$u => $u])" x-bind:disabled="! nuevo" />
                    <button type="button" class="enlace col-span-2 cursor-pointer justify-self-start text-xs text-gris hover:text-tinta" x-on:click="nuevo = false">← Elegir del catálogo</button>
                </div>
            </div>
            <x-campo name="cantidad_necesaria" label="Cantidad" type="number" step="0.01" min="0.01" required />
            <x-campo name="fecha_necesaria" label="Para cuándo" type="date" />
            <div class="hidden lg:block"></div>
            <div class="lg:col-span-2"><x-select name="proveedor_id" label="Proveedor (opcional)" placeholder="Sin proveedor" :options="$proveedores" /></div>
            <div class="sm:col-span-2 lg:col-span-6"><x-campo name="observaciones" label="Observaciones" /></div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="btn btn-chico">Agregar</button>
            <button type="button" class="enlace cursor-pointer text-sm text-gris" x-on:click="abierto = false">Cancelar</button>
        </div>
    </form>

    @if ($materiales->isEmpty())
        <x-vacio>
            {{ $estado ? 'No hay materiales en este estado.' : 'Todavía no hay materiales en la lista de esta obra.' }}
        </x-vacio>
    @else
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th class="num">Necesito</th>
                        <th class="num">Pedido</th>
                        <th class="num">Entregado</th>
                        <th>Estado</th>
                        <th>Proveedor</th>
                        <th>Fechas</th>
                        <th></th>
                    </tr>
                </thead>
                @foreach ($materiales as $item)
                    <tbody x-data="{ panel: null }">
                        <tr>
                            <td>
                                {{ $item->material->nombre }}
                                @if ($item->observaciones)
                                    <div class="mt-1 text-sm text-gris">{{ $item->observaciones }}</div>
                                @endif
                            </td>
                            <td class="num">{{ $cantidad($item->cantidad_necesaria) }} <span class="text-gris">{{ $item->material->unidad }}</span></td>
                            <td class="num">{{ $cantidad($item->cantidad_pedida) }}</td>
                            <td class="num">{{ $cantidad($item->cantidad_entregada) }}</td>
                            <td><span class="{{ $claseEstado($item->estado) }}">{{ $item->estado->label() }}</span></td>
                            <td class="text-sm">{!! $item->proveedor ? e($item->proveedor->nombre) : '<span class="text-gris">Sin proveedor</span>' !!}</td>
                            <td class="font-mono text-xs whitespace-nowrap text-gris">
                                @if ($item->fecha_necesaria)
                                    <div @class(['text-tinta' => $item->estado !== EstadoMaterial::Entregado && $item->fecha_necesaria->isPast()])>Necesario {{ $item->fecha_necesaria->format('d.m') }}</div>
                                @endif
                                @if ($item->fecha_entrega_estimada && $item->estado !== EstadoMaterial::Entregado)
                                    <div>Llega {{ $item->fecha_entrega_estimada->format('d.m') }}</div>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-3 text-sm whitespace-nowrap">
                                    @if ($item->estado !== EstadoMaterial::Entregado)
                                        <button type="button" class="enlace cursor-pointer text-gris hover:text-tinta" x-on:click="panel = panel === 'pedido' ? null : 'pedido'">Pedí</button>
                                        <button type="button" class="enlace cursor-pointer text-gris hover:text-tinta" x-on:click="panel = panel === 'entrega' ? null : 'entrega'">Entregaron</button>
                                    @endif
                                    @if ($item->movimientos->isNotEmpty())
                                        <button type="button" class="enlace cursor-pointer text-gris hover:text-tinta" x-on:click="panel = panel === 'historial' ? null : 'historial'">Historial</button>
                                    @endif
                                    <x-eliminar :action="route('materiales.destroy', $item)" pregunta="¿Quitar este material de la lista?" texto="Quitar" />
                                </div>
                            </td>
                        </tr>

                        @foreach (['pedido' => 'Registrar pedido', 'entrega' => 'Registrar entrega'] as $tipo => $titulo)
                            @php
                                $sugerida = $tipo === 'pedido'
                                    ? max(0, $item->cantidad_necesaria - $item->cantidad_pedida)
                                    : max(0, $item->cantidad_pedida - $item->cantidad_entregada);
                            @endphp
                            <tr x-show="panel === '{{ $tipo }}'" x-cloak>
                                <td colspan="8" class="bg-hueso">
                                    <form method="POST" action="{{ route('materiales.movimiento', $item) }}" class="flex flex-wrap items-end gap-5 px-4 py-2">
                                        @csrf
                                        <input type="hidden" name="tipo" value="{{ $tipo }}">
                                        <p class="rotulo-texto w-full">{{ $titulo }}</p>
                                        <div class="w-28"><x-campo :id="$tipo.'-cant-'.$item->id" name="cantidad" label="Cantidad" type="number" step="0.01" min="0.01" :value="$sugerida ?: null" required /></div>
                                        <div class="w-40"><x-campo :id="$tipo.'-fecha-'.$item->id" name="fecha" label="Fecha" type="date" :value="today()->format('Y-m-d')" required /></div>
                                        @if ($tipo === 'pedido')
                                            <div class="w-56"><x-select :id="'prov-'.$item->id" name="proveedor_id" label="Proveedor (opcional)" :value="$item->proveedor_id" placeholder="Sin proveedor" :options="$proveedores" /></div>
                                            <div class="w-40"><x-campo :id="'llega-'.$item->id" name="fecha_entrega_estimada" label="Llega aprox." type="date" :value="$item->fecha_entrega_estimada?->format('Y-m-d')" /></div>
                                        @else
                                            <div class="w-36"><x-campo :id="'remito-'.$item->id" name="remito" label="N° remito" /></div>
                                        @endif
                                        <div class="min-w-48 flex-1"><x-campo :id="$tipo.'-obs-'.$item->id" name="observaciones" label="Observaciones" /></div>
                                        <button type="submit" class="btn btn-chico">Guardar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        <tr x-show="panel === 'historial'" x-cloak>
                            <td colspan="8" class="bg-hueso">
                                <ul class="px-4 py-2 text-sm">
                                    @foreach ($item->movimientos as $mov)
                                        <li class="flex flex-wrap gap-x-4 border-b border-linea py-2 last:border-0">
                                            <span class="w-20 font-mono text-gris">{{ $mov->fecha->format('d.m.Y') }}</span>
                                            <span class="w-24">{{ $mov->tipo->label() }}</span>
                                            <span class="w-24 font-mono">{{ $cantidad($mov->cantidad) }} {{ $item->material->unidad }}</span>
                                            <span class="text-gris">{{ collect([$mov->remito ? 'Remito '.$mov->remito : null, $mov->observaciones, $mov->usuario?->name])->filter()->join(' · ') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>
        </div>
    @endif
</x-layouts.obra>
