<x-layouts.guest titulo="Verificación" lamina="A-03">
    <div x-data="{ recuperacion: {{ $errors->has('recovery_code') ? 'true' : 'false' }} }">
        <p class="rotulo-texto text-gris">A-03 — Acceso</p>
        <h1 class="mt-3 font-titulo text-5xl leading-none">Verificación</h1>

        <p class="mt-4 text-gris" x-show="! recuperacion">Ingresá el código de 6 dígitos de tu app de autenticación.</p>
        <p class="mt-4 text-gris" x-show="recuperacion" x-cloak>Ingresá uno de tus códigos de recuperación.</p>

        <form method="POST" action="{{ route('two-factor.login') }}" class="mt-12 space-y-8">
            @csrf

            <div x-show="! recuperacion">
                <x-campo name="code" label="Código" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]*" maxlength="6" class="font-mono text-2xl tracking-[0.4em]" autofocus x-bind:disabled="recuperacion" />
            </div>
            <div x-show="recuperacion" x-cloak>
                <x-campo name="recovery_code" label="Código de recuperación" autocomplete="off" class="font-mono" x-bind:disabled="! recuperacion" />
            </div>

            <button type="submit" class="btn w-full">Verificar</button>
        </form>

        <button type="button" class="enlace mt-8 text-sm text-gris hover:text-tinta" x-on:click="recuperacion = ! recuperacion">
            <span x-show="! recuperacion">Usar un código de recuperación</span>
            <span x-show="recuperacion" x-cloak>Usar el código de la app</span>
        </button>
    </div>
</x-layouts.guest>
