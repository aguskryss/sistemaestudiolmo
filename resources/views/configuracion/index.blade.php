@php
    use App\Http\Controllers\ConfiguracionController;
    $titulo = ConfiguracionController::SECCIONES[$seccion];
    $campo = $seccion === 'checklist' ? 'descripcion' : 'nombre';
    $ayuda = [
        'tipo_obra' => 'Aparecen al cargar o editar una obra.',
        'rubros' => 'Para clasificar contactos, tareas del calendario, cotizaciones y materiales.',
        'materiales' => 'El catálogo que se elige al agregar materiales a una obra.',
        'unidad' => 'Unidades de medida de los materiales.',
        'tipo_permiso' => 'Aparecen al cargar un permiso en una obra.',
        'checklist' => 'Se copia a cada obra nueva. Cambiarlo no modifica las obras ya creadas.',
    ][$seccion];
@endphp

<x-layouts.app titulo="Configuración" :codigo="'S-00 — '.$titulo">
    <x-slot:bajada>Las listas que se usan en los formularios. Lo que está en uso no se borra: se desactiva.</x-slot:bajada>

    <div class="grid gap-12 lg:grid-cols-[14rem_minmax(0,1fr)]">
        <nav aria-label="Listas">
            <ul class="space-y-px text-sm">
                @foreach (ConfiguracionController::SECCIONES as $clave => $nombre)
                    <li>
                        <a href="{{ route('configuracion', $clave) }}"
                           @class(['block px-3 py-2', 'bg-tinta text-papel' => $clave === $seccion, 'hover:bg-hueso' => $clave !== $seccion])>{{ $nombre }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <section class="min-w-0">
            <div class="border-b border-tinta pb-3">
                <h2 class="font-serif text-2xl">{{ $titulo }}</h2>
                <p class="mt-1 text-sm text-gris">{{ $ayuda }}</p>
            </div>

            {{-- Alta --}}
            <form method="POST" action="{{ route('configuracion.store', $seccion) }}" class="mt-6 flex flex-wrap items-end gap-4">
                @csrf
                <div class="min-w-56 flex-1">
                    <x-campo :name="$campo" :label="$seccion === 'checklist' ? 'Nuevo ítem' : 'Nuevo'" required id="nuevo" />
                </div>
                @if ($seccion === 'materiales')
                    <div class="w-32"><x-select name="unidad" label="Unidad" :options="$unidades->mapWithKeys(fn ($u) => [$u => $u])" required id="nueva-unidad" /></div>
                    <div class="w-52"><x-select name="rubro_id" label="Rubro" placeholder="—" :options="$rubros" id="nuevo-rubro" /></div>
                @endif
                <button type="submit" class="btn btn-chico">Agregar</button>
            </form>

            {{-- Listado editable --}}
            @if ($items->isEmpty())
                <p class="py-10 text-gris">La lista está vacía.</p>
            @else
                <div class="mt-8 overflow-x-auto">
                    <table class="tabla">
                        <thead>
                            <tr>
                                @if ($seccion === 'checklist') <th class="w-20">Orden</th> @endif
                                <th>{{ $seccion === 'checklist' ? 'Ítem' : 'Nombre' }}</th>
                                @if ($seccion === 'materiales') <th class="w-28">Unidad</th><th class="w-48">Rubro</th> @endif
                                @if ($seccion !== 'checklist') <th class="num">En uso</th> @endif
                                <th class="w-20">Activo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                @php $f = 'fila-'.$item->id; @endphp
                                <tr @class(['text-gris' => ! $item->activo])>
                                    @if ($seccion === 'checklist')
                                        <td><input form="{{ $f }}" name="orden" type="number" min="0" max="999" value="{{ $item->orden }}" class="campo font-mono" aria-label="Orden"></td>
                                    @endif
                                    <td><input form="{{ $f }}" name="{{ $campo }}" value="{{ $item->{$campo} }}" required maxlength="255" class="campo" aria-label="Nombre"></td>
                                    @if ($seccion === 'materiales')
                                        <td>
                                            <select form="{{ $f }}" name="unidad" class="campo" aria-label="Unidad">
                                                @foreach ($unidades->values()->push($item->unidad)->unique() as $u)
                                                    <option value="{{ $u }}" @selected($u === $item->unidad)>{{ $u }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select form="{{ $f }}" name="rubro_id" class="campo" aria-label="Rubro">
                                                <option value="">—</option>
                                                @foreach ($rubros as $id => $nombre)
                                                    <option value="{{ $id }}" @selected($id === $item->rubro_id)>{{ $nombre }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endif
                                    @if ($seccion !== 'checklist')
                                        <td class="num">{{ $item->usos }}</td>
                                    @endif
                                    <td><input form="{{ $f }}" type="checkbox" name="activo" value="1" class="mt-2 size-4 accent-tinta" @checked($item->activo) aria-label="Activo"></td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-4 pt-1.5 text-sm whitespace-nowrap">
                                            <form id="{{ $f }}" method="POST" action="{{ route('configuracion.update', [$seccion, $item->id]) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="enlace cursor-pointer text-gris hover:text-tinta">Guardar</button>
                                            </form>
                                            <x-eliminar :action="route('configuracion.destroy', [$seccion, $item->id])"
                                                :pregunta="($item->usos ?? 0) > 0 ? 'Está en uso: se va a desactivar. ¿Continuar?' : '¿Eliminar?'"
                                                :texto="($item->usos ?? 0) > 0 ? 'Desactivar' : 'Eliminar'" />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
