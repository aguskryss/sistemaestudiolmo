@php $nuevo = ! $usuario->exists; @endphp

<x-layouts.app :titulo="$nuevo ? 'Nuevo usuario' : $usuario->name" codigo="U-01 — Usuarios">
    <form method="POST" action="{{ $nuevo ? route('usuarios.store') : route('usuarios.update', $usuario) }}" class="max-w-md space-y-8">
        @csrf
        @unless ($nuevo) @method('PUT') @endunless

        <x-campo name="name" label="Nombre" :value="$usuario->name" required />
        <x-campo name="email" label="Email" type="email" :value="$usuario->email" required />
        <x-select name="rol" label="Rol" :value="$usuario->rol" :options="collect(\App\Enums\Rol::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()])" />

        @if ($nuevo)
            <p class="text-sm text-gris">Se genera una contraseña temporal que vas a ver una sola vez al guardar.</p>
        @endif

        <div class="flex items-center gap-6">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ route('usuarios.index') }}" class="enlace text-sm text-gris hover:text-tinta">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
