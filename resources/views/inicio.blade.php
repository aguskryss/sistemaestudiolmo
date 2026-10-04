@php
    $fecha = now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY');
    $indicadores = [
        ['Obras en curso', $obrasEnCurso, route('obras.index', ['estado' => 'en_obra'])],
        ['En proyecto', $obrasEnProyecto, route('obras.index', ['estado' => 'proyecto'])],
        ['En cotización', $obrasEnCotizacion, route('obras.index', ['estado' => 'en_cotizacion'])],
        ['Vencimientos', $segurosPorVencer->count() + $permisosPorVencer->count(), '#vencimientos'],
    ];
@endphp

<x-layouts.app titulo="Inicio" :codigo="ucfirst($fecha)">
    <x-slot:acciones>
        <a href="{{ route('obras.create') }}" class="btn">Nueva obra</a>
    </x-slot:acciones>

    @unless (auth()->user()->two_factor_confirmed_at)
        <div class="mb-10 flex flex-col gap-4 border border-tinta p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="rotulo-texto">Seguridad</p>
                <p class="mt-1 text-sm text-gris">Activá la verificación en dos pasos: además de la contraseña, se pide un código del celular.</p>
            </div>
            <a href="{{ route('perfil') }}#dos-pasos" class="btn btn-linea shrink-0">Activar</a>
        </div>
    @endunless

    <dl class="grid grid-cols-2 border-t border-l border-linea lg:grid-cols-4">
        @foreach ($indicadores as [$etiqueta, $valor, $link])
            <a href="{{ $link }}" class="border-r border-b border-linea p-5 hover:bg-hueso">
                <dt class="rotulo-texto text-gris">{{ $etiqueta }}</dt>
                <dd class="mt-6 font-titulo text-5xl leading-none">{{ str_pad($valor, 2, '0', STR_PAD_LEFT) }}</dd>
            </a>
        @endforeach
    </dl>

    {{-- Agenda de la semana --}}
    <section class="mt-14">
        @include('agenda._semana', ['titulo' => 'Mi semana'])
        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div class="w-full max-w-md">@include('agenda._form')</div>
            <a href="{{ route('agenda.index') }}" class="enlace text-sm text-gris hover:text-tinta">Agenda del equipo →</a>
        </div>
    </section>

    <div class="mt-14 grid gap-14 xl:grid-cols-2">
        {{-- Esta semana --}}
        <section>
            <div class="flex items-baseline justify-between border-b border-linea pb-3">
                <h2 class="rotulo-texto">Esta semana en obra</h2>
                <a href="{{ route('calendario') }}" class="enlace text-xs text-gris hover:text-tinta">Calendario</a>
            </div>
            @forelse ($tareasSemana as $t)
                <a href="{{ route('obras.gantt', $t->obra) }}" class="grid grid-cols-[5rem_minmax(0,1fr)_auto] items-baseline gap-4 border-b border-linea py-3 hover:bg-hueso">
                    <span class="font-mono text-sm text-gris">{{ $t->obra->codigo_corto }}</span>
                    <span class="min-w-0 truncate">{{ $t->nombre }}@if ($t->contacto) <span class="text-gris">· {{ $t->contacto->nombre }}</span>@endif</span>
                    <span class="font-mono text-xs text-gris">{{ $t->avance }}%</span>
                </a>
            @empty
                <p class="py-8 text-gris">No hay tareas en curso esta semana.</p>
            @endforelse
        </section>

        {{-- Recordatorios --}}
        <section>
            <div class="flex items-baseline justify-between border-b border-linea pb-3">
                <h2 class="rotulo-texto">Mis recordatorios · 7 días</h2>
                <a href="{{ route('recordatorios.index') }}" class="enlace text-xs text-gris hover:text-tinta">Todos</a>
            </div>
            @forelse ($recordatorios as $r)
                <div class="grid grid-cols-[6.5rem_minmax(0,1fr)] gap-4 border-b border-linea py-3">
                    <span @class(['font-mono text-sm', 'text-gris' => ! $r->fecha_hora->isPast(), 'underline decoration-dashed' => $r->fecha_hora->isPast()])>{{ $r->fecha_hora->format('d.m H:i') }}</span>
                    <span class="min-w-0 truncate">{{ $r->titulo }}</span>
                </div>
            @empty
                <p class="py-8 text-gris">Nada pendiente para esta semana.</p>
            @endforelse
        </section>

        {{-- Materiales --}}
        <section>
            <div class="flex items-baseline justify-between border-b border-linea pb-3">
                <h2 class="rotulo-texto">Materiales que se necesitan ya</h2>
            </div>
            @forelse ($materialesUrgentes as $m)
                <a href="{{ route('obras.materiales', [$m->obra, 'estado' => 'pendientes']) }}" class="grid grid-cols-[5rem_minmax(0,1fr)_auto] items-baseline gap-4 border-b border-linea py-3 hover:bg-hueso">
                    <span class="font-mono text-sm text-gris">{{ $m->obra->codigo_corto }}</span>
                    <span class="min-w-0 truncate">{{ $m->material->nombre }} <span class="text-gris">· {{ $m->estado->label() }}</span></span>
                    <span @class(['font-mono text-xs', 'underline decoration-dashed' => $m->fecha_necesaria->isPast(), 'text-gris' => ! $m->fecha_necesaria->isPast()])>{{ $m->fecha_necesaria->format('d.m') }}</span>
                </a>
            @empty
                <p class="py-8 text-gris">Nada urgente.</p>
            @endforelse
        </section>

        {{-- Vencimientos --}}
        <section id="vencimientos" class="scroll-mt-10">
            <div class="flex items-baseline justify-between border-b border-linea pb-3">
                <h2 class="rotulo-texto">Vencimientos</h2>
            </div>
            @foreach ($segurosPorVencer as $s)
                @php [$texto, $clase] = $s->situacion(); @endphp
                <a href="{{ route('contactos.show', $s->contacto) }}" class="flex items-baseline justify-between gap-4 border-b border-linea py-3 hover:bg-hueso">
                    <span class="min-w-0 truncate">{{ $s->contacto->nombre }} <span class="text-gris">· {{ $s->tipo->label() }}</span></span>
                    <span class="{{ $clase }}">{{ $texto }}</span>
                </a>
            @endforeach
            @foreach ($permisosPorVencer as $p)
                <a href="{{ route('obras.permisos', $p->obra) }}" class="flex items-baseline justify-between gap-4 border-b border-linea py-3 hover:bg-hueso">
                    <span class="min-w-0 truncate"><span class="font-mono text-sm text-gris">{{ $p->obra->codigo_corto }}</span> {{ $p->nombre() }}</span>
                    <span class="etiqueta">Vence {{ $p->fecha_vencimiento->format('d.m') }}</span>
                </a>
            @endforeach
            @if ($segurosPorVencer->isEmpty() && $permisosPorVencer->isEmpty())
                <p class="py-8 text-gris">Sin vencimientos próximos.</p>
            @endif
        </section>
    </div>
</x-layouts.app>
