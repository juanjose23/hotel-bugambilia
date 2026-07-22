<?php

declare(strict_types=1);

namespace App\Console\Commands\Limpieza;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Notifications\Limpieza\NotificadorLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarRecordatoriosLimpieza extends Command
{
    protected $signature = 'limpieza:enviar-recordatorios';

    protected $description = 'Envía recordatorios para las ejecuciones de limpieza pendientes de hoy cuya hora estimada ya ha pasado.';

    public function __construct(
        private readonly NotificadorLimpieza $notificador,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $ahora = Carbon::now();
        $horaActual = $ahora->toTimeString();

        $this->info("Buscando ejecuciones pendientes al día de hoy para la hora actual: {$horaActual}");

        $ejecuciones = LimpiezaEjecucion::query()
            ->whereDate('fecha', $ahora->toDateString())
            ->where('estado', EstadoLimpieza::Pendiente)
            ->whereNull('recordatorio_enviado_at')
            ->whereHas('horario', function ($query) use ($horaActual) {
                $query->where('hora_estimada', '<=', $horaActual);
            })
            ->with([
                'turno.lider.persona',
                'turno.apoyo.persona',
                'colaborador.persona',
                'horario',
                'limpiable',
            ])
            ->get();

        $enviados = 0;

        foreach ($ejecuciones as $ejecucion) {
            $destinatarios = collect();

            $userColaborador = $ejecucion->colaborador?->persona
                ? User::where('persona_id', $ejecucion->colaborador->persona->id)->first()
                : null;
            if ($userColaborador) {
                $destinatarios->push($userColaborador);
            }

            $turno = $ejecucion->turno;
            if ($turno) {
                $userLider = $turno->lider?->persona
                    ? User::where('persona_id', $turno->lider->persona->id)->first()
                    : null;
                if ($userLider) {
                    $destinatarios->push($userLider);
                }

                $userApoyo = $turno->apoyo?->persona
                    ? User::where('persona_id', $turno->apoyo->persona->id)->first()
                    : null;
                if ($userApoyo) {
                    $destinatarios->push($userApoyo);
                }
            }

            $destinatarios = $destinatarios->filter()->unique('id');

            /** @var mixed $limpiable */
            $limpiable = $ejecucion->limpiable;
            $nombreLimpiable = is_object($limpiable) ? ($limpiable->nombre ?? 'Área sin nombre') : 'Área sin nombre';

            if ($destinatarios->isEmpty()) {
                $this->warn("La ejecución #{$ejecucion->id} (Ubicación: {$nombreLimpiable}) no tiene usuarios destinatarios con cuentas vinculadas.");

                continue;
            }

            $this->notificador->recordatorioPendiente($ejecucion, $destinatarios);

            $ejecucion->update([
                'recordatorio_enviado_at' => $ahora,
            ]);

            $emails = $destinatarios->pluck('email')->join(', ');
            $emailsStr = is_string($emails) ? $emails : '';
            Log::info("Recordatorio de limpieza enviado para la ejecución #{$ejecucion->id} (Ubicación: {$nombreLimpiable}) a las {$ahora->toDateTimeString()}. Destinatarios notificados: {$emailsStr}");
            $enviados++;
        }

        $this->info("Se procesaron y enviaron recordatorios para {$enviados} ejecuciones de limpieza.");

        return Command::SUCCESS;
    }
}
