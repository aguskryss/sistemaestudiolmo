<x-layouts.guest titulo="Nueva contraseña" lamina="A-02">
    <p class="rotulo-texto text-gris">A-02 — Acceso</p>
    <h1 class="mt-3 font-serif text-5xl leading-none">Nueva contraseña</h1>
    <p class="mt-4 text-gris">Mínimo 10 caracteres, con letras y números.</p>

    <form method="POST" action="{{ route('password.update') }}" class="mt-12 space-y-8">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-campo name="email" label="Email" type="email" :value="$request->email" autocomplete="username" required />
        <x-campo name="password" label="Contraseña nueva" type="password" autocomplete="new-password" required autofocus />
        <x-campo name="password_confirmation" label="Repetir contraseña" type="password" autocomplete="new-password" required />

        <button type="submit" class="btn w-full">Guardar contraseña</button>
    </form>
</x-layouts.guest>
