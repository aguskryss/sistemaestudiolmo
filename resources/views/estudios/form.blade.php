@php
    $nuevo = ! $estudio->exists;
    $contactos = old('contactos', $estudio->exists
        ? $estudio->contactos->map(fn ($c) => $c->only(['id', 'nombre', 'cargo', 'telefono', 'email']))->all()
        : [['id' => null, 'nombre' => '', 'cargo' => '', 'telefono' => '', 'email' => '']]);
@endphp

<x-layouts.app :titulo="$nuevo ? 'Nuevo estudio' : $estudio->nombre" codigo="E-01 — Estudios contratantes">
    <form method="POST" action="{{ $nuevo ? route('estudios.store') : route('estudios.update', $estudio) }}" class="space-y-12">
        @csrf
        @unless ($nuevo) @method('PUT') @endunless

        <section class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">01 · Estudio</h2>
            <div class="grid max-w-2xl gap-8 sm:grid-cols-2">
                <div class="sm:col-span-2"><x-campo name="nombre" label="Nombre" :value="$estudio->nombre" required /></div>
                <x-campo name="razon_social" label="Razón social" :value="$estudio->razon_social" />
                <x-campo name="cuit" label="CUIT" :value="$estudio->cuit" />
                <x-campo name="email" label="Email" type="email" :value="$estudio->email" />
                <x-campo name="telefono" label="Teléfono" :value="$estudio->telefono" />
                <div class="sm:col-span-2"><x-campo name="direccion" label="Dirección" :value="$estudio->direccion" /></div>
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]"
                 x-data="{ contactos: @js(array_values($contactos)) }">
            <div>
                <h2 class="rotulo-texto">02 · Contactos</h2>
                <p class="mt-2 text-sm text-gris">Las personas del estudio con las que tratan. En cada obra se elige cuál es el referente.</p>
            </div>
            <div class="max-w-3xl">
                <template x-for="(c, i) in contactos" :key="i">
                    <div class="grid gap-x-6 gap-y-4 border-b border-linea py-5 first:pt-0 sm:grid-cols-[1fr_1fr_auto]">
                        <input type="hidden" :name="`contactos[${i}][id]`" :value="c.id">
                        <div>
                            <label class="rotulo-texto text-gris" :for="`c-nombre-${i}`">Nombre</label>
                            <input :id="`c-nombre-${i}`" class="campo mt-1" :name="`contactos[${i}][nombre]`" x-model="c.nombre" maxlength="255">
                        </div>
                        <div>
                            <label class="rotulo-texto text-gris" :for="`c-cargo-${i}`">Cargo / rol</label>
                            <input :id="`c-cargo-${i}`" class="campo mt-1" :name="`contactos[${i}][cargo]`" x-model="c.cargo" maxlength="100" placeholder="Ej: jefa de proyecto">
                        </div>
                        <div class="flex items-start pt-6 sm:row-span-2 sm:justify-end">
                            <button type="button" class="enlace cursor-pointer text-sm text-gris hover:text-tinta" x-on:click="contactos.splice(i, 1)">Quitar</button>
                        </div>
                        <div>
                            <label class="rotulo-texto text-gris" :for="`c-tel-${i}`">Teléfono</label>
                            <input :id="`c-tel-${i}`" class="campo mt-1" :name="`contactos[${i}][telefono]`" x-model="c.telefono" maxlength="50">
                        </div>
                        <div>
                            <label class="rotulo-texto text-gris" :for="`c-email-${i}`">Email</label>
                            <input :id="`c-email-${i}`" type="email" class="campo mt-1" :name="`contactos[${i}][email]`" x-model="c.email" maxlength="255">
                        </div>
                    </div>
                </template>
                <button type="button" class="btn btn-linea btn-chico mt-5" x-on:click="contactos.push({ id: null, nombre: '', cargo: '', telefono: '', email: '' })">+ Agregar contacto</button>
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">03 · Notas</h2>
            <div class="max-w-2xl">
                <x-area name="notas" label="Notas internas" :value="$estudio->notas" />
            </div>
        </section>

        <div class="flex items-center gap-6 border-t border-tinta pt-8">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ $nuevo ? route('estudios.index') : route('estudios.show', $estudio) }}" class="enlace text-sm text-gris hover:text-tinta">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
