<x-layouts.app titulo="Clientes" codigo="C-00 — Clientes">
    <x-slot:bajada>Comitentes finales de cada obra.</x-slot:bajada>
    <x-slot:acciones>
        <a href="{{ route('clientes.create') }}" class="btn">Nuevo cliente</a>
    </x-slot:acciones>

    <x-buscador :valor="$busqueda" placeholder="Nombre, CUIT/DNI o email" class="mb-10" />

    @if ($clientes->isEmpty())
        <x-vacio>
            {{ $busqueda ? 'No hay clientes que coincidan con la búsqueda.' : 'Todavía no cargaste ningún cliente.' }}
            <x-slot:accion><a href="{{ route('clientes.create') }}" class="btn btn-linea btn-chico">Cargar el primero</a></x-slot:accion>
        </x-vacio>
    @else
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>CUIT / DNI</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th class="num">Obras</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientes as $cliente)
                        <tr>
                            <td><a href="{{ route('clientes.show', $cliente) }}" class="enlace">{{ $cliente->nombre }}</a></td>
                            <td class="font-mono text-sm">{{ $cliente->cuit_dni ?? '—' }}</td>
                            <td class="font-mono text-sm">{{ $cliente->telefono ?? '—' }}</td>
                            <td>{{ $cliente->email ?? '—' }}</td>
                            <td class="num">{{ $cliente->obras_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-8">{{ $clientes->links() }}</div>
    @endif
</x-layouts.app>
