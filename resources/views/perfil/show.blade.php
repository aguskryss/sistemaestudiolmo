@php
    $user = auth()->user();
    $confirmada = time() - (int) session('auth.password_confirmed_at', 0) < config('auth.password_timeout');
    $mostrarCodigos = in_array(session('status'), ['two-factor-authentication-confirmed', 'recovery-codes-generated']);
@endphp

<x-layouts.app titulo="Mi perfil" codigo="P-01 — Cuenta">
    <div class="divide-y divide-linea">

        {{-- Datos --}}
        <section class="grid gap-8 pb-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <div>
                <h2 class="rotulo-texto">01 · Datos</h2>
                <p class="mt-2 text-sm text-gris">Nombre y email con el que ingresás.</p>
            </div>
            <form method="POST" action="{{ route('user-profile-information.update') }}" class="max-w-md space-y-8">
                @csrf
                @method('PUT')
                <x-campo name="name" label="Nombre" :value="$user->name" bag="updateProfileInformation" required autocomplete="name" />
                <x-campo name="email" label="Email" type="email" :value="$user->email" bag="updateProfileInformation" required autocomplete="username" />
                <button type="submit" class="btn">Guardar</button>
            </form>
        </section>

        {{-- Contraseña --}}
        <section class="grid gap-8 py-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <div>
                <h2 class="rotulo-texto">02 · Contraseña</h2>
                <p class="mt-2 text-sm text-gris">Mínimo 10 caracteres, con letras y números.</p>
            </div>
            <form method="POST" action="{{ route('user-password.update') }}" class="max-w-md space-y-8">
                @csrf
                @method('PUT')
                <x-campo name="current_password" label="Contraseña actual" type="password" bag="updatePassword" required autocomplete="current-password" />
                <x-campo name="password" label="Contraseña nueva" type="password" bag="updatePassword" required autocomplete="new-password" />
                <x-campo name="password_confirmation" label="Repetir contraseña nueva" type="password" bag="updatePassword" required autocomplete="new-password" />
                <button type="submit" class="btn">Cambiar contraseña</button>
            </form>
        </section>

        {{-- Verificación en dos pasos --}}
        <section id="dos-pasos" class="grid scroll-mt-10 gap-8 py-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <div>
                <h2 class="rotulo-texto">03 · Verificación en dos pasos</h2>
                <p class="mt-2 text-sm text-gris">Al ingresar se pide, además de la contraseña, un código de una app como Google Authenticator.</p>
            </div>

            <div class="max-w-md">
                <p class="flex items-center gap-3">
                    <span @class(['inline-block size-2.5 border border-tinta', 'bg-tinta' => $user->two_factor_confirmed_at])></span>
                    {{ $user->two_factor_confirmed_at ? 'Activada' : ($user->two_factor_secret ? 'Pendiente de confirmar' : 'Desactivada') }}
                </p>

                @if (! $confirmada)
                    <p class="mt-6 text-sm text-gris">Para modificarla tenés que confirmar tu contraseña.</p>
                    <a href="{{ route('perfil.seguridad') }}" class="btn btn-linea mt-6">Confirmar contraseña</a>

                @elseif (! $user->two_factor_secret)
                    <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-8">
                        @csrf
                        <button type="submit" class="btn">Activar</button>
                    </form>

                @elseif (! $user->two_factor_confirmed_at)
                    <ol class="mt-8 space-y-8 text-sm">
                        <li>
                            <p><span class="font-mono text-gris">1.</span> Escaneá este código con la app.</p>
                            <div class="mt-4 inline-block border border-linea bg-papel p-4">{!! $user->twoFactorQrCodeSvg() !!}</div>
                            <p class="mt-3 text-gris">O ingresá la clave a mano: <span class="font-mono text-tinta break-all">{{ decrypt($user->two_factor_secret) }}</span></p>
                        </li>
                        <li>
                            <p><span class="font-mono text-gris">2.</span> Ingresá el código que muestra la app.</p>
                            <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-4 space-y-6">
                                @csrf
                                <x-campo name="code" label="Código" bag="confirmTwoFactorAuthentication" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="font-mono text-2xl tracking-[0.4em]" required />
                                <div class="flex gap-3">
                                    <button type="submit" class="btn">Confirmar</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="enlace cursor-pointer text-sm text-gris hover:text-tinta">Cancelar</button>
                            </form>
                        </li>
                    </ol>

                @else
                    @if ($mostrarCodigos)
                        <div class="mt-8 border border-tinta p-5">
                            <p class="rotulo-texto">Códigos de recuperación</p>
                            <p class="mt-2 text-sm text-gris">Guardalos en un lugar seguro. Cada uno sirve una sola vez para entrar si perdés el celular. No se vuelven a mostrar.</p>
                            <ul class="mt-4 grid grid-cols-2 gap-x-6 gap-y-1 font-mono text-sm">
                                @foreach ($user->recoveryCodes() as $codigo)
                                    <li>{{ $codigo }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-8 flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                            @csrf
                            <button type="submit" class="btn btn-linea">Generar códigos nuevos</button>
                        </form>
                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-linea">Desactivar</button>
                        </form>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-layouts.app>
