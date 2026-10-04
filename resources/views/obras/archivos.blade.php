@php
    use App\Support\Archivos;
    $raices = $carpetas->whereNull('parent_id');
    $hijas = $carpetas->groupBy('parent_id');
@endphp

<x-layouts.obra :obra="$obra" seccion="Archivos">
    <div class="grid gap-12 lg:grid-cols-[15rem_minmax(0,1fr)]">

        {{-- Carpetas --}}
        <aside>
            <p class="rotulo-texto border-b border-linea pb-3">Carpetas</p>
            <ul class="mt-2 space-y-px text-sm">
                @foreach ($raices as $carpeta)
                    @include('obras._carpeta', ['carpeta' => $carpeta, 'nivel' => 0])
                @endforeach
            </ul>

            <form method="POST" action="{{ route('obras.carpetas.store', $obra) }}" class="mt-6 space-y-3" x-data="{ abierto: false }">
                @csrf
                <button type="button" class="enlace cursor-pointer text-sm text-gris hover:text-tinta" x-show="! abierto" x-on:click="abierto = true; $nextTick(() => $refs.nombre.focus())">+ Nueva carpeta</button>
                <div x-show="abierto" x-cloak class="space-y-3">
                    <input x-ref="nombre" name="nombre" class="campo text-sm" placeholder="Nombre de la carpeta" required maxlength="100">
                    @if ($actual)
                        <label class="flex items-center gap-2 text-xs text-gris">
                            <input type="checkbox" name="parent_id" value="{{ $actual->id }}" class="accent-tinta"> Dentro de «{{ $actual->nombre }}»
                        </label>
                    @endif
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-chico">Crear</button>
                        <button type="button" class="enlace cursor-pointer text-sm text-gris" x-on:click="abierto = false">Cancelar</button>
                    </div>
                </div>
            </form>
        </aside>

        {{-- Contenido de la carpeta --}}
        <section class="min-w-0">
            @if (! $actual)
                <x-vacio>Creá una carpeta para empezar a subir archivos.</x-vacio>
            @else
                <div class="flex flex-wrap items-baseline justify-between gap-4 border-b border-tinta pb-3">
                    <h2 class="font-serif text-2xl">{{ $actual->nombre }}</h2>
                    @if ($documentos->isEmpty() && $actual->subcarpetas()->doesntExist())
                        <x-eliminar :action="route('carpetas.destroy', $actual)" pregunta="¿Eliminar esta carpeta vacía?" texto="Eliminar carpeta" />
                    @endif
                </div>

                @error('carpeta') <p class="mt-4 text-sm">— {{ $message }}</p> @enderror

                {{-- Subida --}}
                <form method="POST" action="{{ route('obras.archivos.subir', $obra) }}" enctype="multipart/form-data"
                      x-data="{ archivos: [], arrastrando: false }"
                      class="mt-6">
                    @csrf
                    <input type="hidden" name="carpeta_id" value="{{ $actual->id }}">
                    <label x-on:dragover.prevent="arrastrando = true" x-on:dragleave.prevent="arrastrando = false"
                           x-on:drop.prevent="arrastrando = false; $refs.input.files = $event.dataTransfer.files; archivos = [...$refs.input.files].map(f => f.name)"
                           :class="arrastrando ? 'border-tinta bg-hueso' : 'border-linea'"
                           class="block cursor-pointer border border-dashed px-6 py-8 text-center transition-colors">
                        <input x-ref="input" type="file" name="archivos[]" multiple class="sr-only"
                               accept="{{ collect(Archivos::EXTENSIONES)->map(fn ($e) => '.'.$e)->join(',') }}"
                               x-on:change="archivos = [...$event.target.files].map(f => f.name)">
                        <span class="rotulo-texto">Arrastrá archivos acá o hacé clic</span>
                        <span class="mt-2 block text-sm text-gris">Si el nombre coincide con uno existente, se guarda como versión nueva. Máx. 100 MB por archivo.</span>
                        <template x-if="archivos.length">
                            <span class="mt-4 block font-mono text-xs" x-text="archivos.join(' · ')"></span>
                        </template>
                    </label>
                    @error('archivos') <p class="mt-2 text-sm">— {{ $message }}</p> @enderror
                    @error('archivos.*') <p class="mt-2 text-sm">— {{ $message }}</p> @enderror
                    <div class="mt-4 flex flex-wrap items-end gap-4" x-show="archivos.length" x-cloak>
                        <div class="min-w-64 flex-1"><input name="comentario" class="campo text-sm" placeholder="Comentario de la versión (opcional)" maxlength="255"></div>
                        <button type="submit" class="btn btn-chico">Subir</button>
                    </div>
                </form>

                {{-- Documentos --}}
                @if ($documentos->isEmpty())
                    <p class="py-10 text-gris">Esta carpeta está vacía.</p>
                @else
                    <div class="mt-8 overflow-x-auto">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Versión</th>
                                    <th>Subido</th>
                                    <th class="num">Tamaño</th>
                                    <th></th>
                                </tr>
                            </thead>
                            @foreach ($documentos as $documento)
                                @php $vigente = $documento->versionActual; @endphp
                                <tbody x-data="{ historial: false, subir: false }">
                                    <tr>
                                        <td class="max-w-sm">
                                            <a href="{{ route('versiones.descargar', [$vigente, 'ver' => 1]) }}" target="_blank" rel="noopener" class="enlace break-words">{{ $documento->nombre }}</a>
                                            @if ($vigente?->comentario)
                                                <div class="mt-1 text-sm text-gris">{{ $vigente->comentario }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="etiqueta etiqueta-llena">v{{ $vigente?->numero }}</span>
                                            @if ($documento->versiones->count() > 1)
                                                <button type="button" class="enlace ml-2 cursor-pointer text-xs text-gris hover:text-tinta" x-on:click="historial = ! historial">
                                                    <span x-text="historial ? 'Ocultar' : '{{ $documento->versiones->count() - 1 }} anterior(es)'"></span>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="text-sm whitespace-nowrap">
                                            <span class="font-mono">{{ $vigente?->created_at->format('d.m.Y H:i') }}</span>
                                            <div class="text-gris">{{ $vigente?->subidoPor?->name }}</div>
                                        </td>
                                        <td class="num">{{ $vigente ? Archivos::tamanoLegible($vigente->tamano) : '' }}</td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-4 text-sm whitespace-nowrap">
                                                <a href="{{ route('versiones.descargar', $vigente) }}" class="enlace text-gris hover:text-tinta">Descargar</a>
                                                <button type="button" class="enlace cursor-pointer text-gris hover:text-tinta" x-on:click="subir = ! subir">Nueva versión</button>
                                                <x-eliminar :action="route('documentos.destroy', $documento)" :pregunta="'¿Eliminar «'.$documento->nombre.'» y todas sus versiones?'" />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr x-show="subir" x-cloak>
                                        <td colspan="5">
                                            <form method="POST" action="{{ route('documentos.version', $documento) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
                                                @csrf
                                                <input type="file" name="archivo" required class="text-sm file:mr-3 file:cursor-pointer file:border file:border-tinta file:bg-papel file:px-3 file:py-1.5 file:font-mono file:text-xs file:uppercase">
                                                <input name="comentario" class="campo w-64 text-sm" placeholder="Qué cambió (opcional)" maxlength="255">
                                                <button type="submit" class="btn btn-chico">Subir v{{ ($vigente?->numero ?? 0) + 1 }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @foreach ($documento->versiones->skip(1) as $anterior)
                                        <tr x-show="historial" x-cloak class="text-gris">
                                            <td class="pl-4 text-sm">{{ $anterior->comentario ?? $anterior->nombre_original }}</td>
                                            <td><span class="etiqueta etiqueta-tenue">v{{ $anterior->numero }}</span></td>
                                            <td class="text-sm"><span class="font-mono">{{ $anterior->created_at->format('d.m.Y H:i') }}</span> · {{ $anterior->subidoPor?->name }}</td>
                                            <td class="num">{{ Archivos::tamanoLegible($anterior->tamano) }}</td>
                                            <td class="text-right text-sm"><a href="{{ route('versiones.descargar', $anterior) }}" class="enlace hover:text-tinta">Descargar</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            @endforeach
                        </table>
                    </div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.obra>
