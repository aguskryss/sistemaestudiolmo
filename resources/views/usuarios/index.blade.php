<x-layouts.app titulo="Usuarios" codigo="U-00 — Administración">
    <x-slot:acciones>
        <a href="{{ route('usuarios.create') }}" class="btn">Nuevo usuario</a>
    </x-slot:acciones>

    <div class="overflow-x-auto">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Dos pasos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr @class(['text-gris' => ! $usuario->activo])>
                        <td>{{ $usuario->name }} @if ($usuario->is(auth()->user())) <span class="text-gris">(vos)</span> @endif</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->rol->label() }}</td>
                        <td>
                            <span @class(['etiqueta', 'etiqueta-llena' => $usuario->two_factor_confirmed_at, 'etiqueta-tenue' => ! $usuario->two_factor_confirmed_at])>
                                {{ $usuario->two_factor_confirmed_at ? 'Activa' : 'No' }}
                            </span>
                        </td>
                        <td>{{ $usuario->activo ? 'Activo' : 'Desactivado' }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-4 text-sm">
                                <a href="{{ route('usuarios.edit', $usuario) }}" class="enlace text-gris hover:text-tinta">Editar</a>
                                @unless ($usuario->is(auth()->user()))
                                    <form method="POST" action="{{ route('usuarios.restablecer', $usuario) }}" x-data x-on:submit="if (! confirm('Se genera una contraseña nueva y se desactiva su verificación en dos pasos. ¿Continuar?')) $event.preventDefault()">
                                        @csrf
                                        <button class="enlace cursor-pointer text-gris hover:text-tinta">Restablecer acceso</button>
                                    </form>
                                    <form method="POST" action="{{ route('usuarios.activo', $usuario) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="enlace cursor-pointer text-gris hover:text-tinta">{{ $usuario->activo ? 'Desactivar' : 'Reactivar' }}</button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
