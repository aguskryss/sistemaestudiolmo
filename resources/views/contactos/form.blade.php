@php
    $nuevo = ! $contacto->exists;
    $seleccionados = collect(old('rubros', $contacto->rubros?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
@endphp

<x-layouts.app :titulo="$nuevo ? 'Nuevo contacto' : $contacto->nombre" codigo="K-01 — Contactos">
    <form id="form-contacto" method="POST" action="{{ $nuevo ? route('contactos.store') : route('contactos.update', $contacto) }}" class="space-y-12">
        @csrf
        @unless ($nuevo) @method('PUT') @endunless

        <section class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">01 · Datos</h2>
            <div class="grid max-w-2xl gap-8 sm:grid-cols-2">
                <x-select name="tipo" label="Tipo" :value="$contacto->tipo" :options="\App\Enums\TipoContacto::opciones()" />
                <x-select name="calificacion" label="Calificación interna" :value="$contacto->calificacion" placeholder="—" :options="[5 => '●●●●● Excelente', 4 => '●●●●○ Muy bueno', 3 => '●●●○○ Bueno', 2 => '●●○○○ Regular', 1 => '●○○○○ Malo']" />
                <x-campo name="nombre" label="Nombre" :value="$contacto->nombre" required />
                <x-campo name="empresa" label="Empresa" :value="$contacto->empresa" />
                <x-campo name="telefono" label="Teléfono" :value="$contacto->telefono" />
                <x-campo name="email" label="Email" type="email" :value="$contacto->email" />
                <x-campo name="cuit" label="CUIT" :value="$contacto->cuit" />
                <x-campo name="direccion" label="Dirección" :value="$contacto->direccion" />
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <div>
                <h2 class="rotulo-texto">02 · Rubros</h2>
                <p class="mt-2 text-sm text-gris">Puede tener más de uno.</p>
            </div>
            <div class="max-w-3xl">
                <div class="flex flex-wrap gap-2">
                    @foreach ($rubros as $rubro)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="rubros[]" value="{{ $rubro->id }}" class="peer sr-only" @checked($seleccionados->contains($rubro->id))>
                            <span class="inline-block border border-linea px-3 py-1.5 text-sm peer-checked:border-tinta peer-checked:bg-tinta peer-checked:text-papel peer-focus-visible:outline peer-focus-visible:outline-1 hover:border-tinta">{{ $rubro->nombre }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">03 · Notas</h2>
            <div class="max-w-2xl"><x-area name="notas" label="Cómo trabaja, precios de referencia, recomendaciones…" :value="$contacto->notas" /></div>
        </section>

        <div class="flex items-center gap-6 border-t border-tinta pt-8">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ $nuevo ? route('contactos.index') : route('contactos.show', $contacto) }}" class="enlace text-sm text-gris hover:text-tinta">Cancelar</a>
        </div>
    </form>

    {{-- Rubro nuevo: formulario aparte (no se puede anidar dentro del otro) --}}
    <form method="POST" action="{{ route('rubros.store') }}" class="mt-10 flex max-w-md items-end gap-4 lg:ml-[18rem]" x-data="{ abierto: false }">
        @csrf
        <button type="button" class="enlace cursor-pointer text-sm text-gris hover:text-tinta" x-show="! abierto" x-on:click="abierto = true">+ Crear un rubro que no está en la lista</button>
        <template x-if="abierto">
            <div class="flex flex-1 items-end gap-4">
                <input name="nombre" class="campo flex-1" placeholder="Nombre del rubro" required maxlength="100">
                <button type="submit" class="btn btn-chico">Crear</button>
            </div>
        </template>
    </form>
</x-layouts.app>
