@php $nuevo = ! $estudio->exists; @endphp

<x-layouts.app :titulo="$nuevo ? 'Nuevo estudio' : $estudio->nombre" codigo="E-01 — Estudios contratantes">
    <form method="POST" action="{{ $nuevo ? route('estudios.store') : route('estudios.update', $estudio) }}" class="space-y-12">
        @csrf
        @unless ($nuevo) @method('PUT') @endunless

        <section class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">01 · Estudio</h2>
            <div class="grid max-w-2xl gap-8 sm:grid-cols-2 lg:col-start-2">
                <div class="sm:col-span-2"><x-campo name="nombre" label="Nombre" :value="$estudio->nombre" required /></div>
                <x-campo name="razon_social" label="Razón social" :value="$estudio->razon_social" />
                <x-campo name="cuit" label="CUIT" :value="$estudio->cuit" />
                <x-campo name="email" label="Email" type="email" :value="$estudio->email" />
                <x-campo name="telefono" label="Teléfono" :value="$estudio->telefono" />
                <div class="sm:col-span-2"><x-campo name="direccion" label="Dirección" :value="$estudio->direccion" /></div>
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">02 · Persona de contacto</h2>
            <div class="grid max-w-2xl gap-8 sm:grid-cols-2 lg:col-start-2">
                <div class="sm:col-span-2"><x-campo name="contacto_nombre" label="Nombre" :value="$estudio->contacto_nombre" /></div>
                <x-campo name="contacto_telefono" label="Teléfono" :value="$estudio->contacto_telefono" />
                <x-campo name="contacto_email" label="Email" type="email" :value="$estudio->contacto_email" />
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">03 · Notas</h2>
            <div class="max-w-2xl lg:col-start-2">
                <x-area name="notas" label="Notas internas" :value="$estudio->notas" />
            </div>
        </section>

        <div class="flex items-center gap-6 border-t border-tinta pt-8">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ $nuevo ? route('estudios.index') : route('estudios.show', $estudio) }}" class="enlace text-sm text-gris hover:text-tinta">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
