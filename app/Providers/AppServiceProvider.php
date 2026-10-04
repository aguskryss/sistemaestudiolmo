<?php

namespace App\Providers;

use App\Models\Actividad;
use App\Models\Cliente;
use App\Models\Contacto;
use App\Models\Cotizacion;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Estudio;
use App\Models\Nota;
use App\Models\Obra;
use App\Models\ObraMaterial;
use App\Models\Permiso;
use App\Models\Seguro;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // En la base se guarda "obra", "seguro", etc. en lugar del nombre de la clase PHP.
        Relation::enforceMorphMap([
            'user' => User::class,
            'cliente' => Cliente::class,
            'estudio' => Estudio::class,
            'obra' => Obra::class,
            'contacto' => Contacto::class,
            'documento' => Documento::class,
            'documento_version' => DocumentoVersion::class,
            'obra_material' => ObraMaterial::class,
            'tarea' => Tarea::class,
            'cotizacion' => Cotizacion::class,
            'permiso' => Permiso::class,
            'seguro' => Seguro::class,
            'nota' => Nota::class,
        ]);

        // En desarrollo: error si se accede a una relación sin cargar (consultas N+1) o a un atributo inexistente.
        Model::shouldBeStrict(! $this->app->isProduction());

        // En producción además se rechazan contraseñas que aparecen en filtraciones conocidas.
        Password::defaults(function () {
            $rule = Password::min(10)->letters()->numbers();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        Gate::define('admin', fn (User $user) => $user->esAdmin());

        Paginator::defaultView('pagination.estudio');

        $this->registrarActividadDeAcceso();
    }

    private function registrarActividadDeAcceso(): void
    {
        Event::listen(Login::class, fn (Login $e) => Actividad::create([
            'user_id' => $e->user->getAuthIdentifier(),
            'accion' => 'login',
            'ip' => request()->ip(),
        ]));

        Event::listen(Logout::class, fn (Logout $e) => $e->user && Actividad::create([
            'user_id' => $e->user->getAuthIdentifier(),
            'accion' => 'logout',
            'ip' => request()->ip(),
        ]));

        Event::listen(Failed::class, fn (Failed $e) => Actividad::create([
            'user_id' => $e->user?->getAuthIdentifier(),
            'accion' => 'login_fallido',
            'cambios' => ['email' => $e->credentials['email'] ?? null],
            'ip' => request()->ip(),
        ]));
    }
}
