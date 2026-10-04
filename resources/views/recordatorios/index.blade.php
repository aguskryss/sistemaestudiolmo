<x-layouts.app titulo="Recordatorios" codigo="R-00 — Recordatorios">
    <x-slot:bajada>Te llegan por email a la hora indicada. Los vencimientos de seguros y permisos se agregan solos.</x-slot:bajada>

    <div class="grid gap-14 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="min-w-0">
            <nav class="mb-6 flex gap-2">
                @foreach (['pendientes' => 'Pendientes', 'completados' => 'Completados'] as $valor => $nombre)
                    <a href="{{ route('recordatorios.index', ['ver' => $valor]) }}"
                       @class(['border px-3 py-1.5 text-sm', 'border-tinta bg-tinta text-papel' => $ver === $valor, 'border-linea hover:border-tinta' => $ver !== $valor])>{{ $nombre }}</a>
                @endforeach
            </nav>

            @forelse ($recordatorios as $r)
                @php
                    $vencido = ! $r->completado_en && $r->fecha_hora->isPast();
                    $enlace = match ($r->recordable_type) {
                        'obra' => $r->recordable ? route('obras.show', $r->recordable_id) : null,
                        'seguro' => $r->recordable ? route('contactos.show', $r->recordable->contacto_id) : null,
                        'permiso' => $r->recordable ? route('obras.permisos', $r->recordable->obra_id) : null,
                        default => null,
                    };
                @endphp
                <article class="group flex items-start gap-4 border-b border-linea py-4">
                    <form method="POST" action="{{ route('recordatorios.completar', $r) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="mt-1 grid size-5 cursor-pointer place-items-center border border-tinta {{ $r->completado_en ? 'bg-tinta text-papel' : 'hover:bg-hueso' }}" aria-label="{{ $r->completado_en ? 'Marcar pendiente' : 'Marcar hecho' }}">
                            @if ($r->completado_en)
                                <svg viewBox="0 0 12 12" class="size-3" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 6.5l2.5 2.5L10 3" /></svg>
                            @endif
                        </button>
                    </form>
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-center gap-2">
                            <span @class(['line-through text-gris' => $r->completado_en])>{{ $r->titulo }}</span>
                            @if ($r->automatico) <span class="etiqueta etiqueta-tenue">Automático</span> @endif
                            @if ($r->repeticion) <span class="etiqueta etiqueta-tenue">{{ $r->repeticion->label() }}</span> @endif
                        </p>
                        @if ($r->descripcion)
                            <p class="mt-1 text-sm text-gris">{{ $r->descripcion }}</p>
                        @endif
                        <p class="mt-1 font-mono text-xs text-gris">
                            <span @class(['text-tinta underline decoration-dashed' => $vencido])>{{ $r->fecha_hora->format('d.m.Y H:i') }}</span>
                            @if ($r->enviado_en) · enviado @endif
                            @if ($enlace) · <a href="{{ $enlace }}" class="enlace">ver</a> @endif
                        </p>
                    </div>
                    <x-eliminar :action="route('recordatorios.destroy', $r)" pregunta="¿Eliminar este recordatorio?" texto="Eliminar" class="opacity-0 group-hover:opacity-100 focus-within:opacity-100" />
                </article>
            @empty
                <p class="py-10 text-gris">{{ $ver === 'pendientes' ? 'No tenés recordatorios pendientes.' : 'Nada completado todavía.' }}</p>
            @endforelse
            <div class="mt-8">{{ $recordatorios->links() }}</div>
        </section>

        <aside>
            <form method="POST" action="{{ route('recordatorios.store') }}" class="panel space-y-6">
                @csrf
                <p class="rotulo-texto">Nuevo recordatorio</p>
                <x-campo name="titulo" label="Qué" required placeholder="Ej: Llamar al electricista" />
                <div class="grid grid-cols-2 gap-4">
                    <x-campo name="fecha" label="Día" type="date" :value="today()->addDay()->format('Y-m-d')" required />
                    <x-campo name="hora" label="Hora" type="time" value="09:00" required />
                </div>
                <x-select name="repeticion" label="Repetir" placeholder="No se repite" :options="\App\Enums\Repeticion::opciones()" />
                <x-select name="obra_id" label="Obra (opcional)" placeholder="—" :options="$obras" />
                <x-area name="descripcion" label="Detalle (opcional)" rows="2" />
                <fieldset>
                    <legend class="rotulo-texto text-gris">Para</legend>
                    <div class="mt-2 space-y-1">
                        @foreach ($usuarios as $id => $nombre)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="para[]" value="{{ $id }}" class="size-4 accent-tinta" @checked(in_array($id, old('para', [auth()->id()])))> {{ $nombre }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <button type="submit" class="btn w-full">Crear</button>
            </form>
        </aside>
    </div>
</x-layouts.app>
