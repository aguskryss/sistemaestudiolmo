@props(['titulo', 'codigo' => null])

@php
    // Las secciones aparecen como link a medida que existen sus rutas.
    $secciones = [
        'inicio' => 'Inicio',
        'obras.index' => 'Obras',
        'clientes.index' => 'Clientes',
        'contactos.index' => 'Contactos',
        'cotizaciones.index' => 'Cotizaciones',
        'calendario' => 'Calendario',
        'recordatorios.index' => 'Recordatorios',
    ];
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="min-h-screen lg:grid lg:grid-cols-[15rem_minmax(0,1fr)]">

        <aside x-data="{ abierto: false }" class="border-b border-linea lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:border-r lg:border-b-0">
            <div class="flex items-center justify-between px-6 py-5 lg:py-7">
                <a href="{{ route('inicio') }}"><x-logo /></a>
                <button type="button" class="rotulo-texto lg:hidden" x-on:click="abierto = ! abierto" x-bind:aria-expanded="abierto">
                    <span x-show="! abierto">Menú</span><span x-show="abierto" x-cloak>Cerrar</span>
                </button>
            </div>

            <div class="hidden lg:flex lg:flex-1 lg:flex-col" x-bind:class="{ 'max-lg:block': abierto }">
                <nav class="border-t border-linea px-3 py-4 lg:flex-1">
                    <ul class="space-y-px">
                        @foreach ($secciones as $ruta => $nombre)
                            @php $n = str_pad($loop->iteration, 2, '0', STR_PAD_LEFT); @endphp
                            <li>
                                @if (Route::has($ruta))
                                    @php $activa = request()->routeIs(str_replace('.index', '.*', $ruta)); @endphp
                                    <a href="{{ route($ruta) }}"
                                       @class([
                                           'flex items-baseline gap-3 px-3 py-2 transition-colors',
                                           'bg-tinta text-papel' => $activa,
                                           'hover:bg-hueso' => ! $activa,
                                       ])
                                       @if ($activa) aria-current="page" @endif>
                                        <span @class(['font-mono text-[0.6875rem]', 'text-papel/60' => $activa, 'text-gris' => ! $activa])>{{ $n }}</span>
                                        {{ $nombre }}
                                    </a>
                                @else
                                    <span class="flex items-baseline gap-3 px-3 py-2 text-gris/50" title="Próximamente">
                                        <span class="font-mono text-[0.6875rem]">{{ $n }}</span>
                                        {{ $nombre }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="border-t border-linea px-6 py-5">
                    <p class="truncate text-sm">{{ auth()->user()->name }}</p>
                    <p class="rotulo-texto mt-0.5 text-gris">{{ auth()->user()->rol->label() }}</p>
                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <a href="{{ route('perfil') }}" class="enlace text-gris hover:text-tinta">Mi perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="enlace cursor-pointer text-gris hover:text-tinta">Salir</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <main class="px-6 py-10 sm:px-10 lg:px-16 lg:py-14">
            <header class="border-b border-tinta pb-6">
                @if ($codigo)
                    <p class="rotulo-texto text-gris">{{ $codigo }}</p>
                @endif
                <h1 class="mt-2 font-serif text-4xl leading-none sm:text-5xl">{{ $titulo }}</h1>
            </header>

            <div class="mt-10">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
