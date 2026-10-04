<?php

namespace App\Console\Commands;

use App\Enums\Repeticion;
use App\Mail\RecordatorioMail;
use App\Models\Recordatorio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnviarRecordatorios extends Command
{
    protected $signature = 'recordatorios:enviar';

    protected $description = 'Envía por email los recordatorios cuya fecha ya llegó';

    public function handle(): int
    {
        $enviados = 0;

        Recordatorio::pendientesDeEnvio()->with('usuario')->chunkById(50, function ($recordatorios) use (&$enviados) {
            foreach ($recordatorios as $recordatorio) {
                if (! $recordatorio->usuario?->activo) {
                    continue;
                }

                try {
                    Mail::to($recordatorio->usuario)->send(new RecordatorioMail($recordatorio));
                } catch (Throwable $e) {
                    report($e);

                    continue;
                }

                // Los que se repiten pasan a la próxima fecha; el resto queda marcado como enviado.
                $recordatorio->forceFill($recordatorio->repeticion
                    ? ['fecha_hora' => $this->proxima($recordatorio)]
                    : ['enviado_en' => now()])->save();

                $enviados++;
            }
        });

        $this->info("Recordatorios enviados: {$enviados}");

        return self::SUCCESS;
    }

    private function proxima(Recordatorio $r): \Carbon\CarbonInterface
    {
        $fecha = $r->fecha_hora->copy();

        do {
            $fecha = match ($r->repeticion) {
                Repeticion::Diaria => $fecha->addDay(),
                Repeticion::Semanal => $fecha->addWeek(),
                Repeticion::Mensual => $fecha->addMonthNoOverflow(),
                Repeticion::Anual => $fecha->addYearNoOverflow(),
            };
        } while ($fecha->isPast());

        return $fecha;
    }
}
