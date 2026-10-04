<x-layouts.app titulo="Estudios" codigo="E-00 — Estudios contratantes">
    <x-slot:bajada>Estudios que nos subcontratan obras.</x-slot:bajada>
    <x-slot:acciones>
        <a href="{{ route('estudios.create') }}" class="btn">Nuevo estudio</a>
    </x-slot:acciones>

    <x-buscador :valor="$busqueda" placeholder="Nombre, razón social o contacto" class="mb-10" />

    @if ($estudios->isEmpty())
        <x-vacio>
            {{ $busqueda ? 'No hay estudios que coincidan con la búsqueda.' : 'Todavía no cargaste ningún estudio.' }}
            <x-slot:accion><a href="{{ route('estudios.create') }}" class="btn btn-linea btn-chico">Cargar el primero</a></x-slot:accion>
        </x-vacio>
    @else
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Estudio</th>
                        <th>Contactos</th>
                        <th>Teléfono</th>
                        <th class="num">Obras activas</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estudios as $estudio)
                        <tr>
                            <td>
                                <a href="{{ route('estudios.show', $estudio) }}" class="enlace">{{ $estudio->nombre }}</a>
                                @if ($estudio->razon_social)
                                    <div class="text-sm text-gris">{{ $estudio->razon_social }}</div>
                                @endif
                            </td>
                            <td>{{ $estudio->contactos->pluck('nombre')->join(', ') ?: '—' }}</td>
                            <td class="font-mono text-sm">{{ $estudio->telefono ?? $estudio->contactos->first()?->telefono ?? '—' }}</td>
                            <td class="num">{{ $estudio->obras_activas_count }}</td>
                            <td class="num">{{ $estudio->obras_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-8">{{ $estudios->links() }}</div>
    @endif
</x-layouts.app>
