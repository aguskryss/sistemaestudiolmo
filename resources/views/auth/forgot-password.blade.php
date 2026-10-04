<x-layouts.guest titulo="Recuperar contraseña" lamina="A-01">
    <p class="rotulo-texto text-gris">A-01 — Acceso</p>
    <h1 class="mt-3 font-serif text-5xl leading-none">Recuperar contraseña</h1>
    <p class="mt-4 text-gris">Te enviamos un link por email para elegir una contraseña nueva.</p>

    <x-estado class="mt-8" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-12 space-y-8">
        @csrf
        <x-campo name="email" label="Email" type="email" autocomplete="username" required autofocus />
        <button type="submit" class="btn w-full">Enviar link</button>
    </form>

    <a href="{{ route('login') }}" class="enlace mt-8 inline-block text-sm text-gris hover:text-tinta">← Volver a ingresar</a>
</x-layouts.guest>
