<x-layouts.app titulo="Cotizaciones" codigo="Q-00 — Cotizaciones">
    <x-slot:acciones>
        <a href="{{ route('cotizaciones.create', ['tipo' => 'recibida']) }}" class="btn btn-linea">Cargar recibida</a>
        <a href="{{ route('cotizaciones.create', ['tipo' => 'emitida']) }}" class="btn">Nueva emitida</a>
    </x-slot:acciones>

    <x-buscador :valor="$filtros['q'] ?? ''" placeholder="Título, número, gremio u obra" class="mb-10">
        <div class="w-40"><x-select name="tipo" label="Tipo" :value="$filtros['tipo'] ?? null" placeholder="Todas" :options="\App\Enums\TipoCotizacion::opciones()" /></div>
        <div class="w-40"><x-select name="estado" label="Estado" :value="$filtros['estado'] ?? null" placeholder="Todos" :options="\App\Enums\EstadoCotizacion::opciones()" /></div>
        <div class="w-48"><x-select name="rubro" label="Rubro" :value="$filtros['rubro'] ?? null" placeholder="Todos" :options="$rubros" /></div>
    </x-buscador>

    @if ($cotizaciones->isEmpty())
        <x-vacio>No hay cotizaciones para mostrar.</x-vacio>
    @else
        @include('cotizaciones._tabla')
        <div class="mt-8">{{ $cotizaciones->links() }}</div>
    @endif
</x-layouts.app>
