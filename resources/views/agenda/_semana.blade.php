{{-- Calendario semanal de la agenda. Espera: $dias, $tareas, $desde, $anterior, $siguiente, $usuario y opcionalmente $parametros (para los links). --}}
@php
    $parametros ??= [];
    $editable = fn ($t) => auth()->user()->esAdmin() || $t->user_id === auth()->id() || $t->creado_por === auth()->id();
@endphp

<div>
    <div class="flex flex-wrap items-baseline justify-between gap-4 border-b border-linea pb-3">
        <h2 class="rotulo-texto">
            {{ $titulo ?? 'Agenda' }} · {{ $desde->format('d.m') }} – {{ $desde->endOfWeek()->format('d.m.Y') }}
        </h2>
        <nav class="flex items-center gap-1 text-sm" aria-label="Semanas">
            <a href="{{ request()->fullUrlWithQuery(['semana' => $anterior] + $parametros) }}" class="px-2 py-1 hover:bg-hueso" aria-label="Semana anterior">←</a>
            @unless ($desde->isSameDay(today()->startOfWeek()))
                <a href="{{ request()->fullUrlWithQuery(['semana' => null] + $parametros) }}" class="rotulo-texto px-2 py-1 hover:bg-hueso">Hoy</a>
            @endunless
            <a href="{{ request()->fullUrlWithQuery(['semana' => $siguiente] + $parametros) }}" class="px-2 py-1 hover:bg-hueso" aria-label="Semana siguiente">→</a>
        </nav>
    </div>

    <div class="grid border-l border-linea md:grid-cols-7">
        @foreach ($dias as $dia)
            @php
                $delDia = $tareas->filter(fn ($t) => $t->ocupaDia($dia));
                $esHoy = $dia->isToday();
            @endphp
            <div @class(['min-h-32 border-r border-b border-linea p-2', 'bg-hueso' => $esHoy, 'max-md:min-h-0' => $delDia->isEmpty()])>
                <p @class(['mb-2 flex items-baseline justify-between font-mono text-[0.6875rem] uppercase', 'text-tinta' => $esHoy, 'text-gris' => ! $esHoy])>
                    <span>{{ $dia->locale('es')->isoFormat('ddd') }}</span>
                    <span @class(['px-1', 'bg-tinta text-papel' => $esHoy])>{{ $dia->format('d') }}</span>
                </p>
                <ul class="space-y-1.5">
                    @foreach ($delDia as $t)
                        <li x-data="{ abierto: false }" @class(['border-l-2 px-2 py-1 text-[0.8125rem] leading-snug', 'border-linea text-gris line-through' => $t->completada_en, 'border-tinta bg-papel' => ! $t->completada_en])>
                            <button type="button" class="w-full cursor-pointer text-left" x-on:click="abierto = ! abierto">
                                @if ($t->hora) <span class="font-mono text-[0.6875rem] text-gris">{{ substr($t->hora, 0, 5) }}</span> @endif
                                {{ $t->titulo }}
                                @if ($t->obra) <span class="block font-mono text-[0.625rem] text-gris no-underline">{{ $t->obra->codigo_corto }}</span> @endif
                            </button>
                            <div x-show="abierto" x-cloak class="mt-2 space-y-2 border-t border-linea pt-2 text-xs no-underline">
                                @if ($t->descripcion) <p class="whitespace-pre-line text-tinta">{{ $t->descripcion }}</p> @endif
                                @if ($t->fecha_fin) <p class="font-mono text-gris">{{ $t->fecha->format('d.m') }} → {{ $t->fecha_fin->format('d.m') }}</p> @endif
                                @if ($t->creado_por && $t->creado_por !== $t->user_id) <p class="text-gris">Asignada por {{ $t->creadoPor?->name }}</p> @endif
                                @if ($editable($t))
                                    <div class="flex flex-wrap gap-3">
                                        <form method="POST" action="{{ route('agenda.completar', $t) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="enlace cursor-pointer">{{ $t->completada_en ? 'Reabrir' : 'Hecha' }}</button>
                                        </form>
                                        <button type="button" class="enlace cursor-pointer" x-on:click="$dispatch('editar-agenda', @js([
                                            'id' => $t->id, 'titulo' => $t->titulo, 'descripcion' => $t->descripcion,
                                            'fecha' => $t->fecha->format('Y-m-d'), 'fecha_fin' => $t->fecha_fin?->format('Y-m-d'),
                                            'hora' => $t->hora ? substr($t->hora, 0, 5) : null, 'obra_id' => $t->obra_id, 'user_id' => $t->user_id,
                                        ]))">Editar</button>
                                        <x-eliminar :action="route('agenda.destroy', $t)" pregunta="¿Eliminar esta tarea?" class="inline" />
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>
