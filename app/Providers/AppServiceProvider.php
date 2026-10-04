<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\Contacto;
use App\Models\Cotizacion;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Nota;
use App\Models\Obra;
use App\Models\ObraMaterial;
use App\Models\Permiso;
use App\Models\Seguro;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

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
    }
}
