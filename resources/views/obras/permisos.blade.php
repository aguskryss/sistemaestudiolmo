@php
    use App\Enums\EstadoPermiso;
    $claseEstado = fn (EstadoPermiso $e) => match ($e) {
        EstadoPermiso::Aprobado => 'etiqueta etiqueta-llena',
        EstadoPermiso::Observado, EstadoPermiso::Vencido => 'etiqueta etiqueta-alerta',
        EstadoPermiso::APresentar => 'etiqueta etiqueta-tenue',
        default => 'etiqueta',
    };
@endphp

<x-layouts.obra :obra="$obra" seccion="Permisos">
    <x-slot:acciones>
        <button type="button" class="btn" x-data x-on:click="$dispatch('editar-permiso', null)">Cargar permiso</button>
    </x-slot:acciones>

    <section x-data="{ p: null, abierto: {{ $errors->any() ? 'true' : 'false' }} }"
             x-on:editar-permiso.window="p = $event.detail; abierto = true"
             x-show="abierto" x-cloak class="panel mb-12">
        <p class="rotulo-texto" x-text="p ? 'Editar permiso' : 'Nuevo permiso'"></p>
        <form method="POST" :action="p ? @js(route('permisos.update', ':id')).replace(':id', p.id) : @js(route('obras.permisos.store', $obra))" class="mt-6">
            @csrf
            <template x-if="p"><input type="hidden" name="_method" value="PUT"></template>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <x-campo name="tipo" label="Permiso" list="tipos-permiso" required x-bind:value="p?.tipo ?? @js(old('tipo', ''))" />
                    <datalist id="tipos-permiso">
                        @foreach (['Permiso de obra', 'Registro de planos', 'Permiso de demolición', 'Aviso de obra', 'Conexión de agua', 'Conexión de cloacas', 'Conexión eléctrica', 'Conexión de gas', 'Final de obra', 'Habilitación'] as $t)
                            <option value="{{ $t }}">
                        @endforeach
                    </datalist>
                </div>
                <x-campo name="organismo" label="Organismo" placeholder="Municipio, empresa de servicios…" x-bind:value="p?.organismo ?? @js(old('organismo', ''))" />
                <x-campo name="numero_expediente" label="N° expediente" x-bind:value="p?.numero_expediente ?? @js(old('numero_expediente', ''))" />
                <x-select name="estado" label="Estado" :options="EstadoPermiso::opciones()" x-effect="$el.value = p?.estado ?? @js(old('estado', 'a_presentar'))" />
                <x-campo name="fecha_presentacion" label="Presentado" type="date" x-bind:value="p?.fecha_presentacion ?? ''" />
                <x-campo name="fecha_aprobacion" label="Aprobado" type="date" x-bind:value="p?.fecha_aprobacion ?? ''" />
                <x-campo name="fecha_vencimiento" label="Vence" type="date" x-bind:value="p?.fecha_vencimiento ?? ''" />
                <div class="sm:col-span-2"><x-area name="observaciones" label="Observaciones" rows="2" x-effect="$el.value = p?.observaciones ?? ''" /></div>
            </div>
            <div class="mt-6 flex gap-4">
                <button type="submit" class="btn btn-chico">Guardar</button>
                <button type="button" class="enlace cursor-pointer text-sm text-gris" x-on:click="abierto = false">Cancelar</button>
            </div>
        </form>
    </section>

    @forelse ($obra->permisos as $permiso)
        <article class="grid gap-6 border-b border-linea py-6 lg:grid-cols-[minmax(0,1fr)_16rem_16rem]">
            <div>
                <p class="flex flex-wrap items-center gap-3">
                    <span class="{{ $claseEstado($permiso->estado) }}">{{ $permiso->estado->label() }}</span>
                    <span class="font-serif text-xl">{{ $permiso->tipo }}</span>
                </p>
                <p class="mt-2 text-sm text-gris">{{ collect([$permiso->organismo, $permiso->numero_expediente ? 'Expte. '.$permiso->numero_expediente : null])->filter()->join(' · ') }}</p>
                @if ($permiso->observaciones)
                    <p class="mt-3 whitespace-pre-line text-sm">{{ $permiso->observaciones }}</p>
                @endif
            </div>
            <dl class="grid grid-cols-3 gap-3 font-mono text-xs lg:grid-cols-1">
                <div><dt class="text-gris">Presentado</dt><dd>{{ $permiso->fecha_presentacion?->format('d.m.Y') ?? '—' }}</dd></div>
                <div><dt class="text-gris">Aprobado</dt><dd>{{ $permiso->fecha_aprobacion?->format('d.m.Y') ?? '—' }}</dd></div>
                <div>
                    <dt class="text-gris">Vence</dt>
                    <dd @class(['underline decoration-dashed' => $permiso->fecha_vencimiento?->lte(today()->addDays(30))])>{{ $permiso->fecha_vencimiento?->format('d.m.Y') ?? '—' }}</dd>
                </div>
            </dl>
            <div>
                <x-adjuntos :modelo="$permiso" />
                <div class="mt-4 flex gap-4 text-sm">
                    <button type="button" class="enlace cursor-pointer text-gris hover:text-tinta" x-data
                        x-on:click="$dispatch('editar-permiso', @js([
                            'id' => $permiso->id, 'tipo' => $permiso->tipo, 'organismo' => $permiso->organismo,
                            'numero_expediente' => $permiso->numero_expediente, 'estado' => $permiso->estado->value,
                            'fecha_presentacion' => $permiso->fecha_presentacion?->format('Y-m-d'),
                            'fecha_aprobacion' => $permiso->fecha_aprobacion?->format('Y-m-d'),
                            'fecha_vencimiento' => $permiso->fecha_vencimiento?->format('Y-m-d'),
                            'observaciones' => $permiso->observaciones,
                        ])); window.scrollTo({ top: 0, behavior: 'smooth' })">Editar</button>
                    <x-eliminar :action="route('permisos.destroy', $permiso)" pregunta="¿Eliminar este permiso?" />
                </div>
            </div>
        </article>
    @empty
        <x-vacio>No hay permisos cargados para esta obra.</x-vacio>
    @endforelse
</x-layouts.obra>
