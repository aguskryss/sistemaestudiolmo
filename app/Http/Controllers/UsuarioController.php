<?php

namespace App\Http\Controllers;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        return view('usuarios.index', ['usuarios' => User::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('usuarios.form', ['usuario' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);
        $password = Str::password(14, symbols: false);

        $usuario = new User(['name' => $datos['name'], 'email' => $datos['email'], 'password' => $password]);
        $usuario->rol = Rol::from($datos['rol']);
        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('status', "Usuario creado. Contraseña temporal para {$usuario->email}: {$password} — se muestra una sola vez; pedile que la cambie al entrar.");
    }

    public function edit(User $usuario): View
    {
        return view('usuarios.form', ['usuario' => $usuario]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $datos = $this->validar($request, $usuario);

        // Que nunca quede el sistema sin administradores.
        if ($usuario->esAdmin() && $datos['rol'] !== Rol::Admin->value && $this->adminsActivos() <= 1) {
            return back()->withErrors(['rol' => 'Tiene que quedar al menos un administrador.']);
        }

        $usuario->fill(['name' => $datos['name'], 'email' => $datos['email']]);
        $usuario->rol = Rol::from($datos['rol']);
        $usuario->save();

        return redirect()->route('usuarios.index')->with('status', 'Usuario actualizado.');
    }

    public function alternarActivo(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->withErrors(['usuario' => 'No podés desactivarte a vos mismo.']);
        }

        $usuario->activo = ! $usuario->activo;
        $usuario->save();

        return back()->with('status', $usuario->activo ? 'Usuario reactivado.' : 'Usuario desactivado. Ya no puede ingresar.');
    }

    public function restablecer(User $usuario): RedirectResponse
    {
        $password = Str::password(14, symbols: false);
        $usuario->forceFill([
            'password' => $password,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', "Acceso restablecido. Contraseña temporal para {$usuario->email}: {$password} — la verificación en dos pasos quedó desactivada.");
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        $request->merge(['email' => Str::lower((string) $request->input('email'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario)],
            'rol' => ['required', Rule::enum(Rol::class)],
        ]);
    }

    private function adminsActivos(): int
    {
        return User::where('rol', Rol::Admin)->where('activo', true)->count();
    }
}
