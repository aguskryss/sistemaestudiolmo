<x-layouts.guest titulo="Confirmar contraseña" lamina="A-04">
    <p class="rotulo-texto text-gris">A-04 — Seguridad</p>
    <h1 class="mt-3 font-titulo text-5xl leading-none">Confirmá que sos vos</h1>
    <p class="mt-4 text-gris">Para continuar, ingresá tu contraseña.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-12 space-y-8">
        @csrf
        <x-campo name="password" label="Contraseña" type="password" autocomplete="current-password" required autofocus />
        <button type="submit" class="btn w-full">Confirmar</button>
    </form>

    <a href="{{ url()->previous() === url()->current() ? route('inicio') : url()->previous() }}" class="enlace mt-8 inline-block text-sm text-gris hover:text-tinta">← Volver</a>
</x-layouts.guest>
