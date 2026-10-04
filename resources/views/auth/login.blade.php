<x-layouts.guest titulo="Ingresar">
    <p class="rotulo-texto text-gris">A-00 — Acceso</p>
    <h1 class="mt-3 font-serif text-5xl leading-none">Ingresar</h1>
    <p class="mt-4 text-gris">Sistema interno del estudio.</p>

    <x-estado class="mt-8" />

    <form method="POST" action="{{ route('login') }}" class="mt-12 space-y-8">
        @csrf

        <x-campo name="email" label="Email" type="email" autocomplete="username" required autofocus />
        <x-campo name="password" label="Contraseña" type="password" autocomplete="current-password" required />

        <div class="flex items-center justify-between text-sm">
            <label class="flex cursor-pointer items-center gap-2">
                <input type="checkbox" name="remember" class="size-4 accent-tinta" @checked(old('remember'))>
                Recordarme
            </label>
            <a href="{{ route('password.request') }}" class="enlace text-gris hover:text-tinta">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn w-full">Ingresar <span aria-hidden="true">→</span></button>
    </form>
</x-layouts.guest>
