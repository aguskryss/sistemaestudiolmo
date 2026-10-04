<?php

namespace App\Models\Concerns;

use App\Models\Actividad;
use Illuminate\Database\Eloquent\Model;

/**
 * Deja constancia en `actividad` de quién creó, modificó o eliminó cada registro.
 */
trait RegistraActividad
{
    protected static function bootRegistraActividad(): void
    {
        static::created(fn (Model $m) => static::registrarActividad($m, 'creado'));
        static::updated(fn (Model $m) => static::registrarActividad($m, 'actualizado', static::cambiosAuditables($m)));
        static::deleted(fn (Model $m) => static::registrarActividad($m, 'eliminado'));
    }

    private static function cambiosAuditables(Model $m): ?array
    {
        $cambios = collect($m->getChanges())->except(['updated_at', 'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes']);

        return $cambios->isEmpty() ? null : $cambios->map(fn ($nuevo, $campo) => [
            'antes' => $m->getRawOriginal($campo),
            'despues' => $nuevo,
        ])->all();
    }

    private static function registrarActividad(Model $m, string $accion, ?array $cambios = null): void
    {
        if ($accion === 'actualizado' && $cambios === null) {
            return;
        }

        Actividad::create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'sujeto_type' => $m->getMorphClass(),
            'sujeto_id' => $m->getKey(),
            'cambios' => $cambios,
            'ip' => request()?->ip(),
        ]);
    }
}
