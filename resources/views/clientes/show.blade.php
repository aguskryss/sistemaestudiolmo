<x-layouts.app :titulo="$cliente->nombre" codigo="C-02 — Cliente">
    <x-slot:acciones>
        <a href="{{ route('obras.create', ['cliente_id' => $cliente->id]) }}" class="btn">Nueva obra</a>
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-linea">Editar</a>
    </x-slot:acciones>

    <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-14">
            <section>
                <div class="flex items-baseline justify-between border-b border-linea pb-3">
                    <h2 class="rotulo-texto">Notas</h2>
                    <span class="font-mono text-xs text-gris">{{ $cliente->notas->count() }}</span>
                </div>

                <form method="POST" action="{{ route('clientes.notas.store', $cliente) }}" class="mt-6 space-y-4">
                    @csrf
                    <x-area name="contenido" label="Nueva nota" rows="3" placeholder="Lo que pidió, lo que se acordó, preferencias…" required />
                    <div class="flex flex-wrap items-end gap-6">
                        @if ($cliente->obras->isNotEmpty())
                            <div class="w-64">
                                <x-select name="obra_id" label="Obra (opcional)" placeholder="General" :options="$cliente->obras->mapWithKeys(fn ($o) => [$o->id => $o->codigo.' · '.$o->nombre])" />
                            </div>
                        @endif
                        <button type="submit" class="btn btn-chico">Agregar nota</button>
                    </div>
                </form>

                <div class="mt-8">
                    @forelse ($cliente->notas as $nota)
                        <article @class(['border-b border-linea py-5', 'border-l-2 border-l-tinta pl-4' => $nota->fijada])>
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="font-mono text-xs text-gris">
                                    {{ $nota->created_at->format('d.m.Y H:i') }} · {{ $nota->autor?->name ?? '—' }}
                                    @if ($nota->obra) · {{ $nota->obra->codigo }} @endif
                                    @if ($nota->fijada) · <span class="text-tinta">Fijada</span> @endif
                                </p>
                                <div class="flex gap-4">
                                    <form method="POST" action="{{ route('notas.fijar', $nota) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="enlace cursor-pointer text-sm text-gris hover:text-tinta">{{ $nota->fijada ? 'Desfijar' : 'Fijar' }}</button>
                                    </form>
                                    <x-eliminar :action="route('notas.destroy', $nota)" pregunta="¿Eliminar esta nota?" />
                                </div>
                            </div>
                            <p class="mt-2 whitespace-pre-line">{{ $nota->contenido }}</p>
                        </article>
                    @empty
                        <p class="py-6 text-gris">Sin notas todavía.</p>
                    @endforelse
                </div>
            </section>

            <section>
                <div class="flex items-baseline justify-between border-b border-linea pb-3">
                    <h2 class="rotulo-texto">Obras</h2>
                    <span class="font-mono text-xs text-gris">{{ $cliente->obras->count() }}</span>
                </div>
                @forelse ($cliente->obras as $obra)
                    @include('obras._fila', ['obra' => $obra, 'mostrarCliente' => false])
                @empty
                    <p class="py-6 text-gris">Sin obras.</p>
                @endforelse
            </section>
        </div>

        <aside class="space-y-8">
            <dl class="panel space-y-5">
                <x-dato label="Tipo">{{ $cliente->tipo->label() }}</x-dato>
                <x-dato label="CUIT / DNI">{{ $cliente->cuit_dni }}</x-dato>
                <x-dato label="Teléfono">{{ $cliente->telefono }}</x-dato>
                <x-dato label="Email">@if ($cliente->email)<a class="enlace" href="mailto:{{ $cliente->email }}">{{ $cliente->email }}</a>@endif</x-dato>
                <x-dato label="Dirección">{{ $cliente->direccion }}</x-dato>
            </dl>
            <x-eliminar :action="route('clientes.destroy', $cliente)" pregunta="¿Eliminar este cliente?" texto="Eliminar cliente" />
        </aside>
    </div>
</x-layouts.app>
