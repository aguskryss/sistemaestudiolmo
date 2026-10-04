@php
    $nueva = ! $cotizacion->exists;
    $items = old('items', $cotizacion->exists
        ? $cotizacion->items->map(fn ($i) => $i->only(['descripcion', 'unidad', 'cantidad', 'precio_unitario']))->all()
        : []);
    $tipoInicial = old('tipo', $cotizacion->tipo?->value ?? $cotizacion->tipo ?? 'recibida');
@endphp

<x-layouts.app :titulo="$nueva ? 'Nueva cotización' : $cotizacion->titulo" codigo="Q-01 — Cotizaciones">
    <form method="POST" action="{{ $nueva ? route('cotizaciones.store') : route('cotizaciones.update', $cotizacion) }}"
          x-data="{
              tipo: @js($tipoInicial),
              items: @js(array_values($items)),
              totalManual: @js((float) old('total', $cotizacion->total ?? 0)),
              moneda: @js(old('moneda', $cotizacion->moneda?->value ?? 'ARS')),
              agregar() { this.items.push({ descripcion: '', unidad: '', cantidad: 1, precio_unitario: '' }) },
              subtotal(i) { return (parseFloat(i.cantidad) || 0) * (parseFloat(i.precio_unitario) || 0) },
              get total() { return this.items.length ? this.items.reduce((s, i) => s + this.subtotal(i), 0) : (parseFloat(this.totalManual) || 0) },
              formato(n) { return (this.moneda === 'USD' ? 'US$ ' : '$ ') + n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
          }"
          class="space-y-12">
        @csrf
        @unless ($nueva) @method('PUT') @endunless

        <section class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <div>
                <h2 class="rotulo-texto">01 · Tipo</h2>
                <p class="mt-2 text-sm text-gris">Recibida: la que nos manda un gremio o proveedor. Emitida: la que mandamos nosotros a un estudio o cliente.</p>
            </div>
            <div class="flex gap-0">
                @foreach (\App\Enums\TipoCotizacion::cases() as $t)
                    <label class="cursor-pointer border border-tinta px-5 py-2.5 text-sm first:border-r-0" :class="tipo === '{{ $t->value }}' ? 'bg-tinta text-papel' : 'hover:bg-hueso'">
                        <input type="radio" name="tipo" value="{{ $t->value }}" x-model="tipo" class="sr-only"> {{ $t->label() }}
                    </label>
                @endforeach
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">02 · Datos</h2>
            <div class="grid max-w-3xl gap-8 sm:grid-cols-2">
                <div class="sm:col-span-2"><x-campo name="titulo" label="Título" :value="$cotizacion->titulo" required placeholder="Ej: Instalación eléctrica completa" /></div>

                <div x-show="tipo === 'recibida'">
                    <x-select name="contacto_id" label="Gremio / proveedor" :value="$cotizacion->contacto_id" placeholder="Elegí un contacto" :options="$contactos" />
                    <a href="{{ route('contactos.create') }}" class="enlace mt-2 inline-block text-xs text-gris hover:text-tinta">+ Nuevo contacto</a>
                </div>
                <div x-show="tipo === 'emitida'" x-cloak><x-select name="estudio_id" label="Para el estudio" :value="$cotizacion->estudio_id" placeholder="—" :options="$estudios" /></div>
                <div x-show="tipo === 'emitida'" x-cloak><x-select name="cliente_id" label="Para el cliente" :value="$cotizacion->cliente_id" placeholder="—" :options="$clientes" /></div>

                <x-select name="obra_id" label="Obra" :value="$cotizacion->obra_id" placeholder="Sin obra (todavía)" :options="$obras" />
                <x-select name="rubro_id" label="Rubro" :value="$cotizacion->rubro_id" placeholder="—" :options="$rubros" />
                <x-campo name="numero" label="N° de cotización" :value="$cotizacion->numero" />
                <x-select name="estado" label="Estado" :value="$cotizacion->estado" :options="\App\Enums\EstadoCotizacion::opciones()" />
                <x-campo name="fecha" label="Fecha" type="date" :value="$cotizacion->fecha?->format('Y-m-d')" required />
                <x-campo name="valida_hasta" label="Válida hasta" type="date" :value="$cotizacion->valida_hasta?->format('Y-m-d')" />
                <x-select name="moneda" label="Moneda" :value="$cotizacion->moneda" :options="\App\Enums\Moneda::opciones()" x-model="moneda" />
                <div x-show="moneda === 'USD'" x-cloak><x-campo name="tipo_cambio" label="Tipo de cambio (opcional)" type="number" step="0.0001" min="0" :value="$cotizacion->tipo_cambio" /></div>
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <div>
                <h2 class="rotulo-texto">03 · Detalle</h2>
                <p class="mt-2 text-sm text-gris">Opcional. Si no cargás ítems, ingresá el total a mano.</p>
            </div>
            <div class="min-w-0 max-w-4xl">
                <template x-if="items.length">
                    <div class="overflow-x-auto">
                        <table class="tabla">
                            <thead>
                                <tr><th>Descripción</th><th class="w-24">Unidad</th><th class="w-24">Cant.</th><th class="w-36">Precio unit.</th><th class="num">Subtotal</th><th></th></tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, i) in items" :key="i">
                                    <tr>
                                        <td><input class="campo" :name="`items[${i}][descripcion]`" x-model="item.descripcion" required maxlength="255"></td>
                                        <td><input class="campo" :name="`items[${i}][unidad]`" x-model="item.unidad" maxlength="20"></td>
                                        <td><input class="campo font-mono" type="number" step="0.01" min="0" :name="`items[${i}][cantidad]`" x-model="item.cantidad"></td>
                                        <td><input class="campo font-mono" type="number" step="0.01" min="0" :name="`items[${i}][precio_unitario]`" x-model="item.precio_unitario"></td>
                                        <td class="num pt-5" x-text="formato(subtotal(item))"></td>
                                        <td class="pt-5"><button type="button" class="enlace cursor-pointer text-sm text-gris hover:text-tinta" x-on:click="items.splice(i, 1)">Quitar</button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
                <button type="button" class="btn btn-linea btn-chico mt-4" x-on:click="agregar()">+ Agregar ítem</button>

                <div class="mt-8 flex items-end justify-between gap-6 border-t border-tinta pt-4">
                    <div x-show="! items.length" class="w-56">
                        <label for="total" class="rotulo-texto text-gris">Total</label>
                        <input id="total" name="total" type="number" step="0.01" min="0" class="campo mt-1 font-mono" x-model="totalManual">
                    </div>
                    <p class="ml-auto text-right">
                        <span class="rotulo-texto text-gris">Total</span>
                        <span class="block font-titulo text-4xl" x-text="formato(total)"></span>
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-8 border-t border-linea pt-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <h2 class="rotulo-texto">04 · Observaciones</h2>
            <div class="max-w-3xl"><x-area name="observaciones" label="Condiciones, forma de pago, plazos…" :value="$cotizacion->observaciones" rows="4" /></div>
        </section>

        <div class="flex items-center gap-6 border-t border-tinta pt-8">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ $nueva ? url()->previous() : route('cotizaciones.show', $cotizacion) }}" class="enlace text-sm text-gris hover:text-tinta">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
