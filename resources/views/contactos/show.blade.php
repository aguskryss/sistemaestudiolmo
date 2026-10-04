@php use App\Support\Formato; @endphp

<x-layouts.app :titulo="$contacto->nombre" codigo="K-02 — Contacto">
    <x-slot:bajada>{{ collect([$contacto->empresa, $contacto->tipo->label(), $contacto->rubros->pluck('nombre')->join(', ')])->filter()->join(' · ') }}</x-slot:bajada>
    <x-slot:acciones>
        <a href="{{ route('contactos.edit', $contacto) }}" class="btn btn-linea">Editar</a>
    </x-slot:acciones>

    <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="min-w-0 space-y-14">

            {{-- Seguros --}}
            <section x-data="{ abierto: {{ $errors->hasAny(['aseguradora', 'vigencia_desde', 'vigencia_hasta']) ? 'true' : 'false' }} }">
                <div class="flex items-baseline justify-between border-b border-linea pb-3">
                    <h2 class="rotulo-texto">Seguros</h2>
                    <button type="button" class="enlace cursor-pointer text-sm text-gris hover:text-tinta" x-on:click="abierto = ! abierto">+ Cargar seguro</button>
                </div>

                <form method="POST" action="{{ route('contactos.seguros.store', $contacto) }}" x-show="abierto" x-cloak class="panel mt-6">
                    @csrf
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <x-select name="tipo" label="Tipo" :options="\App\Enums\TipoSeguro::opciones()" />
                        <x-campo name="aseguradora" label="Aseguradora" required />
                        <x-campo name="numero_poliza" label="N° póliza" />
                        <x-campo name="vigencia_desde" label="Vigente desde" type="date" required />
                        <x-campo name="vigencia_hasta" label="Vigente hasta" type="date" required />
                        <x-campo name="suma_asegurada" label="Suma asegurada (opcional)" type="number" step="0.01" min="0" />
                        @if ($obras->isNotEmpty())
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="rotulo-texto text-gris" for="obras">Cubre las obras</label>
                                <select id="obras" name="obras[]" multiple size="4" class="campo mt-1">
                                    @foreach ($obras as $id => $nombre)
                                        <option value="{{ $id }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gris">Ctrl + clic para elegir varias.</p>
                            </div>
                        @endif
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="btn btn-chico">Guardar</button>
                        <button type="button" class="enlace cursor-pointer text-sm text-gris" x-on:click="abierto = false">Cancelar</button>
                    </div>
                </form>

                @forelse ($contacto->seguros as $seguro)
                    @php [$texto, $clase] = $seguro->situacion(); @endphp
                    <article class="grid gap-4 border-b border-linea py-5 sm:grid-cols-[minmax(0,1fr)_14rem]">
                        <div>
                            <p class="flex flex-wrap items-center gap-3">
                                <span class="{{ $clase }}">{{ $texto }}</span>
                                <span>{{ $seguro->tipo->label() }} · {{ $seguro->aseguradora }}</span>
                            </p>
                            <p class="mt-2 font-mono text-sm text-gris">
                                {{ $seguro->vigencia_desde->format('d.m.Y') }} → {{ $seguro->vigencia_hasta->format('d.m.Y') }}
                                @if ($seguro->numero_poliza) · Póliza {{ $seguro->numero_poliza }} @endif
                            </p>
                            @if ($seguro->obras->isNotEmpty())
                                <p class="mt-2 text-sm">Obras: {{ $seguro->obras->map(fn ($o) => $o->codigo)->join(', ') }}</p>
                            @endif
                        </div>
                        <div>
                            <x-adjuntos :modelo="$seguro" />
                            <x-eliminar :action="route('seguros.destroy', $seguro)" pregunta="¿Eliminar este seguro?" texto="Eliminar seguro" class="mt-3" />
                        </div>
                    </article>
                @empty
                    <p class="py-6 text-gris">{{ $contacto->tipo->value === 'gremio' ? 'Sin seguros cargados. Un gremio sin ART vigente no debería entrar a obra.' : 'Sin seguros cargados.' }}</p>
                @endforelse
            </section>

            {{-- Trabajos --}}
            <section>
                <p class="rotulo-texto border-b border-linea pb-3">Tareas asignadas</p>
                @forelse ($contacto->tareas as $tarea)
                    <a href="{{ route('obras.gantt', $tarea->obra) }}" class="flex items-baseline justify-between gap-4 border-b border-linea py-3 hover:bg-hueso">
                        <span><span class="font-mono text-sm text-gris">{{ $tarea->obra->codigo }}</span> · {{ $tarea->nombre }}</span>
                        <span class="font-mono text-sm text-gris">{{ $tarea->fecha_inicio->format('d.m') }}–{{ $tarea->fecha_fin->format('d.m.Y') }}</span>
                    </a>
                @empty
                    <p class="py-6 text-gris">Sin tareas asignadas.</p>
                @endforelse
            </section>

            <section>
                <p class="rotulo-texto border-b border-linea pb-3">Cotizaciones</p>
                @if ($contacto->cotizaciones->isEmpty())
                    <p class="py-6 text-gris">Sin cotizaciones.</p>
                @else
                    <div class="mt-4">@include('cotizaciones._tabla', ['cotizaciones' => $contacto->cotizaciones])</div>
                @endif
            </section>
        </div>

        <aside class="space-y-8">
            <dl class="panel space-y-5">
                <x-dato label="Teléfono">@if ($contacto->telefono)<a class="enlace" href="tel:{{ preg_replace('/[^0-9+]/', '', $contacto->telefono) }}">{{ $contacto->telefono }}</a>@endif</x-dato>
                <x-dato label="Email">@if ($contacto->email)<a class="enlace" href="mailto:{{ $contacto->email }}">{{ $contacto->email }}</a>@endif</x-dato>
                <x-dato label="CUIT">{{ $contacto->cuit }}</x-dato>
                <x-dato label="Dirección">{{ $contacto->direccion }}</x-dato>
                <x-dato label="Calificación">{{ $contacto->calificacion ? str_repeat('●', $contacto->calificacion).str_repeat('○', 5 - $contacto->calificacion) : '' }}</x-dato>
            </dl>
            @if ($contacto->notas)
                <div class="panel">
                    <p class="rotulo-texto">Notas</p>
                    <p class="mt-3 whitespace-pre-line text-sm">{{ $contacto->notas }}</p>
                </div>
            @endif
            <x-eliminar :action="route('contactos.destroy', $contacto)" pregunta="¿Eliminar este contacto?" texto="Eliminar contacto" />
        </aside>
    </div>
</x-layouts.app>
