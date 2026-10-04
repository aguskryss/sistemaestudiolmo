@php
    $hechos = $obra->checklist->whereNotNull('completado_en')->count();
    $total = $obra->checklist->count();
@endphp

<x-layouts.obra :obra="$obra">
    <x-slot:acciones>
        <a href="{{ route('obras.edit', $obra) }}" class="btn btn-linea">Editar obra</a>
    </x-slot:acciones>

    <dl class="mb-14 grid grid-cols-2 border-t border-l border-linea lg:grid-cols-4">
        @foreach ([
            ['Avance', $resumen['avance'].'%', route('obras.gantt', $obra)],
            ['Inicio de obra', $hechos.' / '.$total, '#checklist'],
            ['Materiales pendientes', $resumen['materiales_pendientes'], route('obras.materiales', $obra)],
            ['Documentos', $resumen['documentos'], route('obras.archivos', $obra)],
        ] as [$etiqueta, $valor, $link])
            <a href="{{ $link }}" class="border-r border-b border-linea p-5 hover:bg-hueso">
                <dt class="rotulo-texto text-gris">{{ $etiqueta }}</dt>
                <dd class="mt-5 font-titulo text-4xl leading-none">{{ $valor }}</dd>
            </a>
        @endforeach
    </dl>

    <div class="grid gap-14 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-14">
            {{-- Checklist de inicio de obra --}}
            <section id="checklist" class="scroll-mt-10">
                <div class="flex items-baseline justify-between border-b border-linea pb-3">
                    <h2 class="rotulo-texto">Inicio de obra</h2>
                    <span class="font-mono text-xs text-gris">{{ $hechos }} de {{ $total }}</span>
                </div>
                <ul>
                    @foreach ($obra->checklist as $item)
                        <li class="group flex items-start gap-4 border-b border-linea py-3">
                            <form method="POST" action="{{ route('checklist.alternar', $item) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="mt-0.5 grid size-5 cursor-pointer place-items-center border border-tinta {{ $item->completado_en ? 'bg-tinta text-papel' : 'hover:bg-hueso' }}" aria-label="{{ $item->completado_en ? 'Marcar como pendiente' : 'Marcar como hecho' }}">
                                    @if ($item->completado_en)
                                        <svg viewBox="0 0 12 12" class="size-3" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 6.5l2.5 2.5L10 3" /></svg>
                                    @endif
                                </button>
                            </form>
                            <div class="min-w-0 flex-1">
                                <p @class(['text-gris line-through' => $item->completado_en])>{{ $item->descripcion }}</p>
                                @if ($item->completado_en)
                                    <p class="font-mono text-xs text-gris">{{ $item->completado_en->format('d.m.Y') }} · {{ $item->completadoPor?->name }}</p>
                                @endif
                            </div>
                            <x-eliminar :action="route('checklist.destroy', $item)" pregunta="¿Quitar este ítem del checklist?" texto="Quitar" class="opacity-0 group-hover:opacity-100 focus-within:opacity-100" />
                        </li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('obras.checklist.store', $obra) }}" class="mt-4 flex items-end gap-4">
                    @csrf
                    <div class="flex-1"><x-campo name="descripcion" label="Agregar ítem" placeholder="Ej: Obrador instalado" /></div>
                    <button type="submit" class="btn btn-linea btn-chico">Agregar</button>
                </form>
            </section>

            {{-- Notas --}}
            <section>
                <div class="flex items-baseline justify-between border-b border-linea pb-3">
                    <h2 class="rotulo-texto">Notas de la obra</h2>
                    @if ($obra->cliente)
                        <a href="{{ route('clientes.show', $obra->cliente) }}" class="enlace text-sm text-gris hover:text-tinta">Todas las notas del cliente</a>
                    @endif
                </div>
                <form method="POST" action="{{ route('obras.notas.store', $obra) }}" class="mt-6 space-y-4">
                    @csrf
                    <x-area name="contenido" label="Nueva nota" rows="3" required />
                    <button type="submit" class="btn btn-chico">Agregar nota</button>
                </form>
                @forelse ($obra->notas as $nota)
                    <article @class(['border-b border-linea py-5', 'border-l-2 border-l-tinta pl-4' => $nota->fijada])>
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="font-mono text-xs text-gris">{{ $nota->created_at->format('d.m.Y H:i') }} · {{ $nota->autor?->name ?? '—' }}</p>
                            <x-eliminar :action="route('notas.destroy', $nota)" pregunta="¿Eliminar esta nota?" />
                        </div>
                        <p class="mt-2 whitespace-pre-line">{{ $nota->contenido }}</p>
                    </article>
                @empty
                    <p class="py-6 text-gris">Sin notas.</p>
                @endforelse
            </section>
        </div>

        <aside class="space-y-8">
            <dl class="panel grid grid-cols-2 gap-5">
                <x-dato label="Dirección" class="col-span-2">{{ collect([$obra->direccion, $obra->localidad])->filter()->join(', ') }}</x-dato>
                <x-dato label="Tipo">{{ $obra->tipoObra?->nombre }}</x-dato>
                <x-dato label="Superficie">@if ($obra->superficie_m2){{ number_format($obra->superficie_m2, 2, ',', '.') }} m²@endif</x-dato>
                <x-dato label="Responsable" class="col-span-2">{{ $obra->responsable?->name }}</x-dato>
                <x-dato label="Inicio">{{ ($obra->fecha_inicio_real ?? $obra->fecha_inicio_prevista)?->format('d.m.Y') }}@if (! $obra->fecha_inicio_real && $obra->fecha_inicio_prevista) <span class="text-gris">(prev.)</span>@endif</x-dato>
                <x-dato label="Fin">{{ ($obra->fecha_fin_real ?? $obra->fecha_fin_prevista)?->format('d.m.Y') }}@if (! $obra->fecha_fin_real && $obra->fecha_fin_prevista) <span class="text-gris">(prev.)</span>@endif</x-dato>
            </dl>

            @if ($obra->estudio)
                <dl class="panel space-y-4">
                    <p class="rotulo-texto">Estudio contratante</p>
                    <x-dato label="Contacto">{{ $obra->estudioContacto?->nombre }}@if ($obra->estudioContacto?->cargo) <span class="text-gris">· {{ $obra->estudioContacto->cargo }}</span>@endif</x-dato>
                    <x-dato label="Teléfono">{{ $obra->estudioContacto?->telefono ?? $obra->estudio->telefono }}</x-dato>
                    <x-dato label="Email">{{ $obra->estudioContacto?->email ?? $obra->estudio->email }}</x-dato>
                </dl>
            @endif

            @if ($obra->cliente)
                <dl class="panel space-y-4">
                    <p class="rotulo-texto">Cliente</p>
                    <x-dato label="Teléfono">{{ $obra->cliente->telefono }}</x-dato>
                    <x-dato label="Email">{{ $obra->cliente->email }}</x-dato>
                </dl>
            @endif

            <div class="panel">
                <div class="flex items-baseline justify-between">
                    <p class="rotulo-texto">Permisos</p>
                    <a href="{{ route('obras.permisos', $obra) }}" class="enlace text-xs text-gris hover:text-tinta">Ver</a>
                </div>
                @forelse ($obra->permisos as $permiso)
                    <div class="mt-3 flex items-baseline justify-between gap-3 text-sm">
                        <span class="truncate">{{ $permiso->nombre() }}</span>
                        <span class="etiqueta {{ $permiso->estado === \App\Enums\EstadoPermiso::Aprobado ? 'etiqueta-llena' : '' }}">{{ $permiso->estado->label() }}</span>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-gris">Sin permisos cargados.</p>
                @endforelse
            </div>

            <div class="panel">
                <div class="flex items-baseline justify-between">
                    <p class="rotulo-texto">Seguros de gremios</p>
                    <a href="{{ route('obras.seguros', $obra) }}" class="enlace text-xs text-gris hover:text-tinta">Ver</a>
                </div>
                @forelse ($obra->seguros as $seguro)
                    <div class="mt-3 flex items-baseline justify-between gap-3 text-sm">
                        <span class="truncate">{{ $seguro->contacto->nombre }} · {{ $seguro->tipo->label() }}</span>
                        <span class="etiqueta {{ $seguro->estaVigente() ? '' : 'etiqueta-alerta' }}">{{ $seguro->estaVigente() ? 'Vigente' : 'Vencido' }}</span>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-gris">Ningún seguro asociado.</p>
                @endforelse
            </div>

            <x-eliminar :action="route('obras.destroy', $obra)" pregunta="¿Eliminar esta obra? Se puede recuperar desde la base de datos." texto="Eliminar obra" />
        </aside>
    </div>
</x-layouts.obra>
