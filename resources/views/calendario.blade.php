<x-layouts.app titulo="Calendario" codigo="G-00 — Calendario de obras">
    <x-slot:bajada>Todas las obras activas. Hacé clic en una barra para ver el detalle de esa obra.</x-slot:bajada>

    <section x-data="gantt({ tareas: @js($barras), soloLectura: true, urlMover: '', urlClick: @js(route('obras.gantt', ':id')) })" class="mb-14">
        @if ($barras->isEmpty())
            <x-vacio>No hay obras activas con fechas o tareas cargadas.</x-vacio>
        @else
            <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm text-gris">
                    {{ $barras->count() }} obra(s).
                    @if ($sinFechas) {{ $sinFechas }} sin fechas ni tareas, no aparecen. @endif
                </p>
                <div class="flex border border-tinta">
                    <template x-for="m in modos" :key="m">
                        <button type="button" class="rotulo-texto cursor-pointer px-3 py-1.5" :class="modo === m ? 'bg-tinta text-papel' : 'hover:bg-hueso'" x-on:click="cambiarModo(m)" x-text="m"></button>
                    </template>
                </div>
            </div>
            <div x-ref="lienzo" class="min-h-48"></div>
        @endif
    </section>

    <section>
        <div class="flex items-baseline justify-between border-b border-linea pb-3">
            <h2 class="rotulo-texto">Esta semana en obra</h2>
            <span class="font-mono text-[0.6875rem] text-gris">{{ today()->startOfWeek()->format('d.m') }} – {{ today()->endOfWeek()->format('d.m') }}</span>
        </div>
        @forelse ($semana as $t)
            <a href="{{ route('obras.gantt', $t->obra) }}" class="grid grid-cols-[6rem_minmax(0,1fr)_auto] items-baseline gap-4 border-b border-linea py-3 hover:bg-hueso">
                <span class="font-mono text-sm text-gris">{{ $t->obra->codigo_corto }}</span>
                <span class="min-w-0 truncate">{{ $t->nombre }}@if ($t->contacto) <span class="text-gris">· {{ $t->contacto->nombre }}</span>@endif</span>
                <span class="font-mono text-sm text-gris">{{ $t->fecha_inicio->format('d.m') }}–{{ $t->fecha_fin->format('d.m') }} · {{ $t->avance }}%</span>
            </a>
        @empty
            <p class="py-8 text-gris">No hay tareas en curso esta semana.</p>
        @endforelse
    </section>
</x-layouts.app>
