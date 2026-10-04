<?php

namespace App\Console\Commands;

use App\Enums\EstadoObra;
use App\Models\Permiso;
use App\Models\Recordatorio;
use App\Models\Seguro;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Crea recordatorios automáticos para seguros que vencen en 15 días y permisos que vencen en 30.
 * Corre una vez por día; no duplica los que ya existen.
 */
class GenerarRecordatoriosVencimientos extends Command
{
    protected $signature = 'recordatorios:vencimientos';

    protected $description = 'Genera recordatorios automáticos de vencimientos de seguros y permisos';

    public function handle(): int
    {
        $usuarios = User::where('activo', true)->get();
        $creados = 0;

        Seguro::with('contacto')->vencenEn(15)->get()->each(function (Seguro $s) use ($usuarios, &$creados) {
            $creados += $this->crear(
                $s,
                $usuarios,
                "Vence el seguro de {$s->contacto->nombre}",
                "{$s->tipo->label()} · {$s->aseguradora}".($s->numero_poliza ? " · Póliza {$s->numero_poliza}" : '')." · Vence el {$s->vigencia_hasta->format('d.m.Y')}.",
            );
        });

        Permiso::with(['obra', 'tipoPermiso'])
            ->whereBetween('fecha_vencimiento', [today(), today()->addDays(30)])
            ->whereHas('obra', fn ($q) => $q->where('estado', '!=', EstadoObra::Terminada))
            ->get()
            ->each(function (Permiso $p) use ($usuarios, &$creados) {
                $creados += $this->crear(
                    $p,
                    $usuarios,
                    "Vence {$p->nombre()} — {$p->obra->codigo}",
                    "{$p->obra->nombre}".($p->numero_expediente ? " · Expte. {$p->numero_expediente}" : '')." · Vence el {$p->fecha_vencimiento->format('d.m.Y')}.",
                );
            });

        $this->info("Recordatorios automáticos creados: {$creados}");

        return self::SUCCESS;
    }

    private function crear(Model $sujeto, Collection $usuarios, string $titulo, string $descripcion): int
    {
        $creados = 0;

        foreach ($usuarios as $usuario) {
            $existe = Recordatorio::where('user_id', $usuario->id)
                ->where('recordable_type', $sujeto->getMorphClass())
                ->where('recordable_id', $sujeto->getKey())
                ->where('automatico', true)
                ->where('created_at', '>=', now()->subDays(45))
                ->exists();

            if ($existe) {
                continue;
            }

            $r = new Recordatorio([
                'user_id' => $usuario->id,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'fecha_hora' => today()->setTime(8, 0),
                'recordable_type' => $sujeto->getMorphClass(),
                'recordable_id' => $sujeto->getKey(),
            ]);
            $r->automatico = true;
            $r->save();
            $creados++;
        }

        return $creados;
    }
}
