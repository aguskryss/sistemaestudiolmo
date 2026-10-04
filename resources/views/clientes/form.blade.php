@php $nuevo = ! $cliente->exists; @endphp

<x-layouts.app :titulo="$nuevo ? 'Nuevo cliente' : $cliente->nombre" codigo="C-01 — Clientes">
    <form method="POST" action="{{ $nuevo ? route('clientes.store') : route('clientes.update', $cliente) }}" class="max-w-2xl space-y-8">
        @csrf
        @unless ($nuevo) @method('PUT') @endunless
        @if (request('volver_a_obra'))
            <input type="hidden" name="volver_a_obra" value="1">
        @endif

        <div class="grid gap-8 sm:grid-cols-2">
            <x-select name="tipo" label="Tipo" :value="$cliente->tipo ?? 'persona'" :options="collect(\App\Enums\TipoCliente::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])" />
            <x-campo name="cuit_dni" label="CUIT / DNI" :value="$cliente->cuit_dni" />
            <div class="sm:col-span-2"><x-campo name="nombre" label="Nombre o razón social" :value="$cliente->nombre" required /></div>
            <x-campo name="telefono" label="Teléfono" :value="$cliente->telefono" />
            <x-campo name="email" label="Email" type="email" :value="$cliente->email" />
            <div class="sm:col-span-2"><x-campo name="direccion" label="Dirección" :value="$cliente->direccion" /></div>
        </div>

        <div class="flex items-center gap-6 border-t border-tinta pt-8">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ $nuevo ? (request('volver_a_obra') ? route('obras.create') : route('clientes.index')) : route('clientes.show', $cliente) }}" class="enlace text-sm text-gris hover:text-tinta">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
