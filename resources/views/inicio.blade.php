@php
    $fecha = now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY');
    $indicadores = [
        ['Obras en curso', $obrasEnCurso],
        ['En proyecto', $obrasEnProyecto],
        ['Seguros que vencen en 15 días', $segurosPorVencer],
        ['Permisos que vencen en 30 días', $permisosPorVencer],
    ];
@endphp

<x-layouts.app titulo="Inicio" :codigo="ucfirst($fecha)">

    @unless (auth()->user()->two_factor_confirmed_at)
        <div class="mb-10 flex flex-col gap-4 border border-tinta p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="rotulo-texto">Seguridad</p>
                <p class="mt-1 text-sm text-gris">Activá la verificación en dos pasos: además de la contraseña, se pide un código del celular.</p>
            </div>
            <a href="{{ route('perfil') }}#dos-pasos" class="btn btn-linea shrink-0">Activar</a>
        </div>
    @endunless

    <section aria-label="Resumen">
        <dl class="grid grid-cols-2 border-t border-l border-linea lg:grid-cols-4">
            @foreach ($indicadores as [$etiqueta, $valor])
                <div class="border-r border-b border-linea p-5">
                    <dt class="rotulo-texto text-gris">{{ $etiqueta }}</dt>
                    <dd class="mt-6 font-serif text-5xl leading-none">{{ str_pad($valor, 2, '0', STR_PAD_LEFT) }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <section class="mt-14">
        <div class="flex items-baseline justify-between border-b border-linea pb-3">
            <h2 class="rotulo-texto">Próximos 7 días</h2>
            <span class="font-mono text-[0.6875rem] text-gris">{{ $recordatorios->count() }} recordatorios</span>
        </div>

        @forelse ($recordatorios as $recordatorio)
            <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-4 border-b border-linea py-4">
                <span class="font-mono text-sm text-gris">{{ $recordatorio->fecha_hora->format('d.m H:i') }}</span>
                <div>
                    <p>{{ $recordatorio->titulo }}</p>
                    @if ($recordatorio->descripcion)
                        <p class="mt-1 text-sm text-gris">{{ $recordatorio->descripcion }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="py-10 text-gris">Nada pendiente para esta semana.</p>
        @endforelse
    </section>
</x-layouts.app>
