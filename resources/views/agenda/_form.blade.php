{{-- Alta / edición de tareas de agenda. Escucha el evento "editar-agenda". Espera $obras, $usuarios (opcional) y $paraUsuario (opcional). --}}
@php
    $esAdmin = auth()->user()->esAdmin();
    $paraUsuario ??= auth()->user();
@endphp

<section x-data="{ t: null, abierto: {{ $abierto ?? false ? 'true' : 'false' }} || {{ $errors->has('titulo') || $errors->has('fecha') ? 'true' : 'false' }} }"
         x-on:editar-agenda.window="t = $event.detail; abierto = true; $nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }))">
    <button type="button" class="btn btn-linea btn-chico" x-show="! abierto" x-on:click="t = null; abierto = true">+ Agendar tarea</button>

    <form method="POST" x-show="abierto" x-cloak class="panel space-y-5"
          :action="t ? @js(route('agenda.update', ':id')).replace(':id', t.id) : @js(route('agenda.store'))">
        @csrf
        <template x-if="t"><input type="hidden" name="_method" value="PUT"></template>
        <p class="rotulo-texto" x-text="t ? 'Editar tarea' : 'Nueva tarea'"></p>

        <x-campo name="titulo" label="Qué" required placeholder="Ej: Relevamiento en obra" x-bind:value="t?.titulo ?? {{ \Illuminate\Support\Js::from(old('titulo', '')) }}" id="agenda-titulo" />
        <div class="grid grid-cols-2 gap-4">
            <x-campo name="fecha" label="Día" type="date" required x-bind:value="t?.fecha ?? {{ \Illuminate\Support\Js::from(old('fecha', today()->format('Y-m-d'))) }}" id="agenda-fecha" />
            <x-campo name="hora" label="Hora (opcional)" type="time" x-bind:value="t?.hora ?? ''" id="agenda-hora" />
        </div>
        <x-campo name="fecha_fin" label="Hasta (si dura varios días)" type="date" x-bind:value="t?.fecha_fin ?? ''" id="agenda-fin" />
        <x-select name="obra_id" label="Obra (opcional)" placeholder="—" :options="$obras" x-effect="$el.value = t?.obra_id ?? ''" id="agenda-obra" />

        @if ($esAdmin && isset($usuarios))
            <x-select name="user_id" label="Para" :options="$usuarios->pluck('name', 'id')" x-effect="$el.value = t?.user_id ?? {{ $paraUsuario->id }}" id="agenda-usuario" />
        @endif

        <x-area name="descripcion" label="Detalle (opcional)" rows="2" x-effect="$el.value = t?.descripcion ?? ''" id="agenda-descripcion" />

        <div class="flex gap-4">
            <button type="submit" class="btn btn-chico">Guardar</button>
            <button type="button" class="enlace cursor-pointer text-sm text-gris" x-on:click="abierto = false; t = null">Cancelar</button>
        </div>
    </form>
</section>
