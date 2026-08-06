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
            ->whereDate('fecha', '<=', $ahora->toDateString())
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

        $personaIds = $ejecuciones->pluck('colaborador.persona.id')
            ->merge($ejecuciones->pluck('turno.lider.persona.id'))
            ->merge($ejecuciones->pluck('turno.apoyo.persona.id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $usersByPersonaId = User::whereIn('persona_id', $personaIds)
            ->get()
            ->keyBy('persona_id');

        foreach ($ejecuciones as $ejecucion) {
            $destinatarios = collect();

            $personaIdColaborador = $ejecucion->colaborador?->persona?->id;
            if ($personaIdColaborador) {
                $userColaborador = $usersByPersonaId->get($personaIdColaborador)
                    ?: $usersByPersonaId->get((string) $personaIdColaborador);
                if ($userColaborador) {
                    $destinatarios->push($userColaborador);
                }
            }

            $turno = $ejecucion->turno;
            if ($turno) {
                $personaIdLider = $turno->lider?->persona?->id;
                if ($personaIdLider) {
                    $userLider = $usersByPersonaId->get($personaIdLider)
                        ?: $usersByPersonaId->get((string) $personaIdLider);
                    if ($userLider) {
                        $destinatarios->push($userLider);
                    }
                }

                $personaIdApoyo = $turno->apoyo?->persona?->id;
                if ($personaIdApoyo) {
                    $userApoyo = $usersByPersonaId->get($personaIdApoyo)
                        ?: $usersByPersonaId->get((string) $personaIdApoyo);
                    if ($userApoyo) {
                        $destinatarios->push($userApoyo);
                    }
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
