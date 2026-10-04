@php $esPropia = $usuario->is(auth()->user()); @endphp

<x-layouts.app titulo="Agenda" codigo="A-00 — Tareas del equipo">
    <x-slot:bajada>
        {{ $esPropia ? 'Tu semana.' : 'Semana de '.$usuario->name.'.' }}
        {{ auth()->user()->esAdmin() ? 'Como admin podés asignar tareas a cualquiera.' : '' }}
    </x-slot:bajada>
    <x-slot:acciones>
        <form method="GET" class="flex items-end gap-3">
            @if (request('semana')) <input type="hidden" name="semana" value="{{ request('semana') }}"> @endif
            <div class="w-48">
                <x-select name="usuario" label="Ver agenda de" :value="$usuario->id" :options="$usuarios->pluck('name', 'id')" x-data x-on:change="$el.form.submit()" />
            </div>
        </form>
    </x-slot:acciones>

    <div class="grid gap-12 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="min-w-0 space-y-12">
            @include('agenda._semana', ['titulo' => 'Semana', 'parametros' => ['usuario' => $esPropia ? null : $usuario->id]])

            @if ($pendientes->isNotEmpty())
                <section>
                    <p class="rotulo-texto border-b border-linea pb-3">Quedaron sin hacer</p>
                    @foreach ($pendientes as $t)
                        <div class="flex items-baseline justify-between gap-4 border-b border-linea py-3">
                            <span class="min-w-0 truncate">
                                <span class="font-mono text-sm underline decoration-dashed">{{ $t->fecha->format('d.m') }}</span>
                                {{ $t->titulo }}
                                @if ($t->obra) <span class="text-gris">· {{ $t->obra->codigo_corto }}</span> @endif
                            </span>
                            <form method="POST" action="{{ route('agenda.completar', $t) }}">
                                @csrf
                                @method('PATCH')
                                <button class="enlace cursor-pointer text-sm text-gris hover:text-tinta">Marcar hecha</button>
                            </form>
                        </div>
                    @endforeach
                </section>
            @endif
        </div>

        <aside>
            @include('agenda._form', ['abierto' => true, 'paraUsuario' => $usuario])
        </aside>
    </div>
</x-layouts.app>
