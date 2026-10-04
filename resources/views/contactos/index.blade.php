<x-layouts.app titulo="Contactos" codigo="K-00 — Contactos de rubro">
    <x-slot:bajada>Gremios, proveedores y profesionales.</x-slot:bajada>
    <x-slot:acciones>
        <a href="{{ route('contactos.create') }}" class="btn">Nuevo contacto</a>
    </x-slot:acciones>

    <x-buscador :valor="$busqueda" placeholder="Nombre, empresa o teléfono" class="mb-10">
        <div class="w-52"><x-select name="rubro" label="Rubro" :value="$rubro" placeholder="Todos" :options="$rubros" /></div>
        <div class="w-40"><x-select name="tipo" label="Tipo" :value="$tipo" placeholder="Todos" :options="\App\Enums\TipoContacto::opciones()" /></div>
    </x-buscador>

    @if ($contactos->isEmpty())
        <x-vacio>
            No hay contactos para mostrar.
            <x-slot:accion><a href="{{ route('contactos.create') }}" class="btn btn-linea btn-chico">Cargar un contacto</a></x-slot:accion>
        </x-vacio>
    @else
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Contacto</th>
                        <th>Rubros</th>
                        <th>Teléfono</th>
                        <th>Seguro</th>
                        <th class="num">Calif.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contactos as $contacto)
                        <tr>
                            <td>
                                <a href="{{ route('contactos.show', $contacto) }}" class="enlace">{{ $contacto->nombre }}</a>
                                <div class="text-sm text-gris">{{ collect([$contacto->empresa, $contacto->tipo->label()])->filter()->join(' · ') }}</div>
                            </td>
                            <td class="text-sm">{{ $contacto->rubros->pluck('nombre')->join(', ') ?: '—' }}</td>
                            <td class="font-mono text-sm whitespace-nowrap">
                                @if ($contacto->telefono)
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contacto->telefono) }}" class="hover:underline">{{ $contacto->telefono }}</a>
                                @else — @endif
                            </td>
                            <td>
                                @if ($contacto->tipo->value === 'gremio')
                                    @if ($contacto->seguros->isNotEmpty())
                                        <span class="etiqueta etiqueta-llena">Al día</span>
                                    @else
                                        <span class="etiqueta etiqueta-alerta">Sin seguro</span>
                                    @endif
                                @endif
                            </td>
                            <td class="num">{{ $contacto->calificacion ? str_repeat('●', $contacto->calificacion).str_repeat('○', 5 - $contacto->calificacion) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-8">{{ $contactos->links() }}</div>
    @endif
</x-layouts.app>
