<?php

declare(strict_types=1);

namespace App\Console\Commands\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\User;
use App\Notifications\Limpieza\RecordatorioLimpiezaPendiente;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarRecordatoriosLimpieza extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limpieza:enviar-recordatorios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios para las ejecuciones de limpieza pendientes de hoy cuya hora estimada ya ha pasado.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ahora = Carbon::now();
        $horaActual = $ahora->toTimeString();

        $this->info("Buscando ejecuciones pendientes al día de hoy para la hora actual: {$horaActual}");

        // Obtener las ejecuciones de hoy que estén pendientes, no tengan recordatorio enviado
        // y cuya hora estimada ya haya pasado
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

            $userLider = $ejecucion->turno->lider?->persona
                ? User::where('persona_id', $ejecucion->turno->lider->persona->id)->first()
                : null;
            if ($userLider) {
                $destinatarios->push($userLider);
            }

            $userApoyo = $ejecucion->turno->apoyo?->persona
                ? User::where('persona_id', $ejecucion->turno->apoyo->persona->id)->first()
                : null;
            if ($userApoyo) {
                $destinatarios->push($userApoyo);
            }

            $destinatarios = $destinatarios->filter()->unique('id');

            /** @var mixed $limpiable */
            $limpiable = $ejecucion->limpiable;
            $nombreLimpiable = is_object($limpiable) ? ($limpiable->nombre ?? 'Área sin nombre') : 'Área sin nombre';

            if ($destinatarios->isEmpty()) {
                $this->warn("La ejecución #{$ejecucion->id} (Ubicación: {$nombreLimpiable}) no tiene usuarios destinatarios con cuentas vinculadas.");

                continue;
            }

            foreach ($destinatarios as $usuario) {
                $usuario->notify(new RecordatorioLimpiezaPendiente($ejecucion));
            }

            // Marcar como enviado y guardar la fecha/hora actual
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
