<x-layouts.app :titulo="$estudio->nombre" codigo="E-02 — Estudio contratante">
    @if ($estudio->razon_social)
        <x-slot:bajada>{{ $estudio->razon_social }}</x-slot:bajada>
    @endif
    <x-slot:acciones>
        <a href="{{ route('obras.create', ['estudio_id' => $estudio->id]) }}" class="btn">Nueva obra</a>
        <a href="{{ route('estudios.edit', $estudio) }}" class="btn btn-linea">Editar</a>
    </x-slot:acciones>

    <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <section>
            <div class="flex items-baseline justify-between border-b border-linea pb-3">
                <h2 class="rotulo-texto">Obras</h2>
                <span class="font-mono text-[0.6875rem] text-gris">{{ $estudio->obras->count() }}</span>
            </div>
            @forelse ($estudio->obras as $obra)
                @include('obras._fila', ['obra' => $obra, 'mostrarEstudio' => false])
            @empty
                <p class="py-10 text-gris">Este estudio todavía no tiene obras cargadas.</p>
            @endforelse
        </section>

        <aside class="space-y-8">
            <dl class="panel space-y-5">
                <x-dato label="CUIT">{{ $estudio->cuit }}</x-dato>
                <x-dato label="Email">@if ($estudio->email)<a class="enlace" href="mailto:{{ $estudio->email }}">{{ $estudio->email }}</a>@endif</x-dato>
                <x-dato label="Teléfono">{{ $estudio->telefono }}</x-dato>
                <x-dato label="Dirección">{{ $estudio->direccion }}</x-dato>
            </dl>
            <dl class="panel space-y-5">
                <p class="rotulo-texto">Persona de contacto</p>
                <x-dato label="Nombre">{{ $estudio->contacto_nombre }}</x-dato>
                <x-dato label="Teléfono">{{ $estudio->contacto_telefono }}</x-dato>
                <x-dato label="Email">@if ($estudio->contacto_email)<a class="enlace" href="mailto:{{ $estudio->contacto_email }}">{{ $estudio->contacto_email }}</a>@endif</x-dato>
            </dl>
            @if ($estudio->notas)
                <div class="panel">
                    <p class="rotulo-texto">Notas</p>
                    <p class="mt-3 whitespace-pre-line text-sm">{{ $estudio->notas }}</p>
                </div>
            @endif
            <x-eliminar :action="route('estudios.destroy', $estudio)" pregunta="¿Eliminar este estudio?" texto="Eliminar estudio" />
        </aside>
    </div>
</x-layouts.app>
