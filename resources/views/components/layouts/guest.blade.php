@props(['titulo' => 'Acceso', 'lamina' => 'A-00'])

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
    <div class="min-h-screen lg:grid lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)]">

        <aside class="grilla relative hidden flex-col justify-between bg-tinta p-12 text-papel lg:flex">
            <div class="flex items-start justify-between">
                <x-logo invertido />
                <span class="rotulo-texto text-papel/60">Gestión de obras</span>
            </div>

            {{-- Planta esquemática --}}
            <svg viewBox="0 0 480 380" class="mx-auto w-full max-w-lg text-papel" fill="none" stroke="currentColor" aria-hidden="true">
                <g stroke-width="1" opacity=".55">
                    <path d="M40 24 H440 M36 28 L44 20 M436 28 L444 20 M40 18 V32 M440 18 V32" />
                    <path d="M16 60 V320 M12 64 L20 56 M12 324 L20 316 M10 60 H24 M10 320 H24" />
                </g>
                <g font-family="IBM Plex Mono, monospace" font-size="10" fill="currentColor" stroke="none" opacity=".7">
                    <text x="240" y="16" text-anchor="middle">10.00</text>
                    <text x="8" y="190" text-anchor="middle" transform="rotate(-90 8 190)">6.50</text>
                </g>

                <g stroke-width="5" stroke-linecap="square">
                    <path d="M300 320 H40 V60 H440 V320 H340" />
                    <path d="M200 60 V200 M200 236 V320" />
                    <path d="M200 180 H330 M366 180 H440" />
                </g>
                <rect x="90" y="57" width="80" height="6" fill="var(--color-tinta)" stroke-width="1" />
                <rect x="280" y="57" width="100" height="6" fill="var(--color-tinta)" stroke-width="1" />

                <g stroke-width="1" opacity=".7">
                    <path d="M340 320 V280 A40 40 0 0 0 300 320" />
                    <path d="M200 200 H236 A36 36 0 0 1 200 236" />
                    <path d="M366 180 V144 A36 36 0 0 0 330 180" />
                </g>

                <g font-family="IBM Plex Mono, monospace" font-size="9" letter-spacing="1.5" fill="currentColor" stroke="none" opacity=".6">
                    <text x="120" y="194" text-anchor="middle">ESTAR</text>
                    <text x="320" y="124" text-anchor="middle">DORMITORIO</text>
                    <text x="290" y="260" text-anchor="middle">COCINA</text>
                </g>

                <g stroke-width="1" opacity=".7">
                    <circle cx="452" cy="352" r="11" />
                    <path d="M452 341 V363 M452 341 L447 352 M452 341 L457 352" />
                </g>
            </svg>

            {{-- Rótulo de lámina --}}
            <div class="grid grid-cols-3 border border-papel/30 text-papel/80">
                <div class="col-span-2 border-r border-b border-papel/30 p-3">
                    <p class="rotulo-texto text-papel/50">Proyecto</p>
                    <p class="mt-1 text-sm">Sistema de gestión del estudio</p>
                </div>
                <div class="border-b border-papel/30 p-3">
                    <p class="rotulo-texto text-papel/50">Lámina</p>
                    <p class="mt-1 font-mono text-sm">{{ $lamina }}</p>
                </div>
                <div class="border-r border-papel/30 p-3">
                    <p class="rotulo-texto text-papel/50">Escala</p>
                    <p class="mt-1 font-mono text-sm">1:1</p>
                </div>
                <div class="border-r border-papel/30 p-3">
                    <p class="rotulo-texto text-papel/50">Fecha</p>
                    <p class="mt-1 font-mono text-sm">{{ now()->format('d.m.Y') }}</p>
                </div>
                <div class="p-3">
                    <p class="rotulo-texto text-papel/50">Hoja</p>
                    <p class="mt-1 font-mono text-sm">01 / 01</p>
                </div>
            </div>
        </aside>

        <main class="flex min-h-screen items-center justify-center px-6 py-16 sm:px-12">
            <div class="w-full max-w-sm">
                <x-logo class="mb-16 lg:hidden" />
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
