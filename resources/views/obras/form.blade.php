@php $nuevo = ! $obra->exists; @endphp

<x-layouts.app :titulo="$nuevo ? 'Nueva obra' : 'Editar '.$obra->codigo" codigo="O-01 — Obras">
    <form method="POST" action="{{ $nuevo ? route('obras.store') : route('obras.update', $obra) }}" class="space-y-12">
        @csrf
        @unless ($nuevo) @method('PUT') @endunless

        <section class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <div>
                <h2 class="rotulo-texto">01 · Quién</h2>
                <p class="mt-2 text-sm text-gris">El estudio que nos pasa la obra y el cliente final. Si la obra es directa, dejá el estudio vacío.</p>
            </div>
            <div class="grid max-w-2xl gap-8 sm:grid-cols-2">
                <div>
                    <x-select name="estudio_id" label="Estudio contratante" :value="$obra->estudio_id" placeholder="Obra directa (sin estudio)" :options="$estudios" />
                    <a href="{{ route('estudios.create') }}" class="enlace mt-2 inline-block text-xs text-gris hover:text-tinta">+ Nuevo estudio</a>
                </div>
                <x-campo name="codigo_estudio" label="N° / código del estudio" :value="$obra->codigo_estudio" placeholder="Cómo la identifican ellos" />
                <div class="sm:col-span-2">
                    <x-select name="cliente_id" label="Cliente final" :value="$obra->cliente_id" placeholder="Elegí un cliente" :options="$clientes" required />
                    <a href="{{ route('clientes.create', ['volver_a_obra' => 1]) }}" class="enlace mt-2 inline-block text-xs text-gris hover:text-tinta">+ Nuevo cliente</a>
                </div>
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">02 · Obra</h2>
            <div class="grid max-w-2xl gap-8 sm:grid-cols-2">
                <div class="sm:col-span-2"><x-campo name="nombre" label="Nombre de la obra" :value="$obra->nombre" required placeholder="Ej: Vivienda Pérez — Nordelta" /></div>
                <x-campo name="direccion" label="Dirección" :value="$obra->direccion" />
                <x-campo name="localidad" label="Localidad" :value="$obra->localidad" />
                <x-campo name="tipo" label="Tipo" :value="$obra->tipo" list="tipos-obra" placeholder="Vivienda, reforma, local…" />
                <datalist id="tipos-obra">
                    <option value="Vivienda unifamiliar"><option value="Reforma"><option value="Ampliación"><option value="Local comercial"><option value="Oficinas"><option value="Edificio">
                </datalist>
                <x-campo name="superficie_m2" label="Superficie (m²)" type="number" step="0.01" min="0" :value="$obra->superficie_m2" />
                <x-select name="estado" label="Estado" :value="$obra->estado ?? 'proyecto'" :options="\App\Enums\EstadoObra::opciones()" />
                <x-select name="responsable_id" label="Responsable" :value="$obra->responsable_id" placeholder="Sin asignar" :options="$usuarios" />
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">03 · Fechas</h2>
            <div class="grid max-w-2xl gap-8 sm:grid-cols-2">
                <x-campo name="fecha_inicio_prevista" label="Inicio previsto" type="date" :value="$obra->fecha_inicio_prevista?->format('Y-m-d')" />
                <x-campo name="fecha_fin_prevista" label="Fin previsto" type="date" :value="$obra->fecha_fin_prevista?->format('Y-m-d')" />
                <x-campo name="fecha_inicio_real" label="Inicio real" type="date" :value="$obra->fecha_inicio_real?->format('Y-m-d')" />
                <x-campo name="fecha_fin_real" label="Fin real" type="date" :value="$obra->fecha_fin_real?->format('Y-m-d')" />
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">04 · Descripción</h2>
            <div class="max-w-2xl"><x-area name="descripcion" label="Alcance, observaciones" :value="$obra->descripcion" rows="5" /></div>
        </section>

        <div class="flex items-center gap-6 border-t border-tinta pt-8">
            <button type="submit" class="btn">{{ $nuevo ? 'Crear obra' : 'Guardar' }}</button>
            <a href="{{ $nuevo ? route('obras.index') : route('obras.show', $obra) }}" class="enlace text-sm text-gris hover:text-tinta">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
