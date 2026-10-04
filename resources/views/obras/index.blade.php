<x-layouts.app titulo="Obras" codigo="O-00 — Obras">
    <x-slot:acciones>
        <a href="{{ route('obras.create') }}" class="btn">Nueva obra</a>
    </x-slot:acciones>

    <x-buscador :valor="$busqueda" placeholder="Nombre, N°, dirección, cliente" class="mb-10">
        <div class="w-44">
            <x-select name="estado" label="Estado" :value="$estado"
                :options="['activas' => 'Activas', 'todas' => 'Todas'] + \App\Enums\EstadoObra::opciones()" />
        </div>
        <div class="w-56">
            <x-select name="estudio" label="Estudio" :value="$estudioId" placeholder="Todos"
                :options="['directas' => 'Obras directas'] + $estudios->all()" />
        </div>
    </x-buscador>

    @if ($obras->isEmpty())
        <x-vacio>
            No hay obras para mostrar.
            <x-slot:accion><a href="{{ route('obras.create') }}" class="btn btn-linea btn-chico">Cargar una obra</a></x-slot:accion>
        </x-vacio>
    @else
        <div class="border-t border-tinta">
            @foreach ($obras as $obra)
                @include('obras._fila')
            @endforeach
        </div>
        <div class="mt-8">{{ $obras->links() }}</div>
    @endif
</x-layouts.app>
