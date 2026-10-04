@php use App\Support\Colores; @endphp

<x-layouts.obra :obra="$obra" seccion="Calendario">
    <x-slot:acciones>
        <button type="button" class="btn" x-data x-on:click="$dispatch('editar-tarea', null)">Agregar tarea</button>
    </x-slot:acciones>

    {{-- Diagrama --}}
    <section x-data="gantt({ tareas: @js($gantt), urlMover: @js(route('tareas.mover', ':id')) })" class="mb-14">
        @if ($tareas->isEmpty())
            <x-vacio>
                Todavía no hay tareas. Cargá la primera para ver el diagrama.
            </x-vacio>
        @else
            <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm text-gris">Arrastrá una barra para mover fechas, sus extremos para cambiar la duración, o el borde del avance para actualizar el %.</p>
                <div class="flex border border-tinta">
                    <template x-for="m in modos" :key="m">
                        <button type="button" class="rotulo-texto cursor-pointer px-3 py-1.5" :class="modo === m ? 'bg-tinta text-papel' : 'hover:bg-hueso'" x-on:click="cambiarModo(m)" x-text="m"></button>
                    </template>
                </div>
            </div>
            <p x-show="error" x-cloak x-text="error" class="mb-4 border-l-2 border-tinta bg-hueso px-4 py-3 text-sm"></p>
            <ul class="mb-4 flex flex-wrap gap-x-6 gap-y-2 text-sm" aria-label="Referencias">
                @foreach ($leyenda as $ref)
                    <li class="flex items-center gap-2"><span class="inline-block h-3 w-6 muestra-{{ $ref['color'] }}"></span>{{ $ref['nombre'] }}</li>
                @endforeach
                @if ($tareas->contains('es_hito', true))
                    <li class="flex items-center gap-2"><span class="inline-block size-3 bg-tinta"></span>Hito</li>
                @endif
            </ul>
            <div x-ref="lienzo" class="min-h-48"></div>
        @endif
    </section>

    {{-- Formulario (alta / edición) --}}
    <section x-data="{ tarea: null, abierto: {{ $errors->any() ? 'true' : 'false' }} }"
             x-on:editar-tarea.window="tarea = $event.detail; abierto = true; $nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
             x-show="abierto" x-cloak class="panel mb-14 scroll-mt-10">
        <p class="rotulo-texto" x-text="tarea ? 'Editar tarea' : 'Nueva tarea'"></p>
        <form method="POST" :action="tarea ? @js(route('tareas.update', ':id')).replace(':id', tarea.id) : @js(route('obras.tareas.store', $obra))" class="mt-6">
            @csrf
            <template x-if="tarea"><input type="hidden" name="_method" value="PUT"></template>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2"><x-campo name="nombre" label="Tarea" required x-bind:value="tarea?.nombre ?? {{ \Illuminate\Support\Js::from(old('nombre', '')) }}" /></div>
                <x-campo name="fecha_inicio" label="Inicio" type="date" required x-bind:value="tarea?.fecha_inicio ?? {{ \Illuminate\Support\Js::from(old('fecha_inicio', today()->format('Y-m-d'))) }}" />
                <x-campo name="fecha_fin" label="Fin" type="date" required x-bind:value="tarea?.fecha_fin ?? {{ \Illuminate\Support\Js::from(old('fecha_fin', today()->addWeek()->format('Y-m-d'))) }}" />
                <x-select name="rubro_id" label="Rubro" placeholder="—" :options="$rubros->pluck('nombre', 'id')" x-effect="$el.value = tarea?.rubro_id ?? ''" />
                <x-select name="contacto_id" label="Gremio / contacto" placeholder="—" :options="$contactos" x-effect="$el.value = tarea?.contacto_id ?? ''" />
                <x-campo name="avance" label="Avance %" type="number" min="0" max="100" x-bind:value="tarea?.avance ?? 0" />
                <label class="flex items-end gap-2 pb-2 text-sm">
                    <input type="checkbox" name="es_hito" value="1" class="size-4 accent-tinta" x-bind:checked="tarea?.es_hito"> Es un hito
                </label>
                <div class="sm:col-span-2"><x-area name="notas" label="Notas" rows="2" x-effect="$el.value = tarea?.notas ?? ''" /></div>
                <div class="sm:col-span-2" x-data="{ color: '' }" x-effect="color = tarea?.color ?? ''">
                    <p class="rotulo-texto text-gris">Color</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" class="cursor-pointer border px-3 py-1.5 text-sm" :class="color === '' ? 'border-tinta bg-tinta text-papel' : 'border-linea hover:border-tinta'" x-on:click="color = ''">Según rubro</button>
                        @foreach (Colores::PALETA as $clave => [$nombreColor])
                            <button type="button" title="{{ $nombreColor }}" aria-label="{{ $nombreColor }}"
                                    class="size-8 cursor-pointer border border-tinta/20 muestra-{{ $clave }}"
                                    :class="color === '{{ $clave }}' && 'outline-2 outline-offset-2 outline-tinta'"
                                    x-on:click="color = '{{ $clave }}'"></button>
                        @endforeach
                    </div>
                    <input type="hidden" name="color" :value="color">
                </div>
            </div>
            <div class="mt-6 flex gap-4">
                <button type="submit" class="btn btn-chico">Guardar</button>
                <button type="button" class="enlace cursor-pointer text-sm text-gris" x-on:click="abierto = false">Cancelar</button>
            </div>
        </form>
    </section>

    {{-- Listado --}}
    @if ($tareas->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Tarea</th>
                        <th>Rubro</th>
                        <th>Gremio</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th class="num">Días</th>
                        <th class="num">Avance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tareas as $t)
                        <tr>
                            <td>
                                <span class="mr-1.5 inline-block size-3 align-[-1px] {{ $t->es_hito ? 'bg-tinta' : 'muestra-'.$t->colorEfectivo() }}"></span>{{ $t->nombre }} @if ($t->es_hito) <span class="etiqueta etiqueta-llena ml-1">Hito</span> @endif
                            </td>
                            <td class="text-sm">{{ $t->rubro?->nombre ?? '—' }}</td>
                            <td class="text-sm">{{ $t->contacto?->nombre ?? '—' }}</td>
                            <td class="font-mono text-sm">{{ $t->fecha_inicio->format('d.m.Y') }}</td>
                            <td class="font-mono text-sm">{{ $t->fecha_fin->format('d.m.Y') }}</td>
                            <td class="num">{{ $t->fecha_inicio->diffInDays($t->fecha_fin) + 1 }}</td>
                            <td class="num">
                                <span class="inline-flex items-center gap-2">
                                    <span class="relative inline-block h-1.5 w-14 bg-linea"><span class="absolute inset-y-0 left-0 bg-tinta" style="width: {{ $t->avance }}%"></span></span>
                                    {{ $t->avance }}%
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-4 text-sm">
                                    <button type="button" class="enlace cursor-pointer text-gris hover:text-tinta" x-data
                                        x-on:click="$dispatch('editar-tarea', @js([
                                            'id' => $t->id, 'nombre' => $t->nombre, 'fecha_inicio' => $t->fecha_inicio->format('Y-m-d'),
                                            'fecha_fin' => $t->fecha_fin->format('Y-m-d'), 'rubro_id' => $t->rubro_id, 'contacto_id' => $t->contacto_id,
                                            'avance' => $t->avance, 'es_hito' => $t->es_hito, 'notas' => $t->notas, 'color' => $t->color,
                                        ]))">Editar</button>
                                    <x-eliminar :action="route('tareas.destroy', $t)" pregunta="¿Eliminar esta tarea?" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.obra>
