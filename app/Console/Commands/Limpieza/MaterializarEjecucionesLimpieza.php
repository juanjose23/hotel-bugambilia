<?php

declare(strict_types=1);

namespace App\Console\Commands\Limpieza;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Notifications\Limpieza\NotificadorLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MaterializarEjecucionesLimpieza extends Command
{
    protected $signature = 'limpieza:materializar-ejecuciones {fecha?}';

    protected $description = 'Materializa los horarios de limpieza activos en ejecuciones para un día específico (por defecto hoy).';

    public function __construct(
        private readonly NotificadorLimpieza $notificador,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $fechaInput = $this->argument('fecha');
        $fecha = $fechaInput ? Carbon::parse($fechaInput) : Carbon::today();

        $diasMap = [
            'Monday' => 'lunes',
            'Tuesday' => 'martes',
            'Wednesday' => 'miercoles',
            'Thursday' => 'jueves',
            'Friday' => 'viernes',
            'Saturday' => 'sabado',
            'Sunday' => 'domingo',
        ];

        $diaSemanaActual = $diasMap[$fecha->format('l')];
        $this->info("Procesando horarios de limpieza para la fecha: {$fecha->toDateString()} ({$diaSemanaActual})");

        $horarios = LimpiezaHorario::query()
            ->where('activo', true)
            ->whereNotNull('turno_id')
            ->where(function ($query) use ($diaSemanaActual) {
                $query->where('frecuencia', 'diaria')
                    ->orWhere(function ($q) use ($diaSemanaActual) {
                        $q->where('frecuencia', 'semanal')
                            ->where('dia_semana', $diaSemanaActual);
                    });
            })
            ->with(['turno', 'detalles'])
            ->get();

        $creados = 0;
        $creadosPorTurno = [];

        foreach ($horarios as $horario) {
            // Preparar checklist predeterminado si hay plantilla
            $checklistData = [];
            if (! empty($horario->checklist)) {
                foreach ($horario->checklist as $task) {
                    $checklistData[$task] = false;
                }
            }

            foreach ($horario->detalles as $detalle) {
                $exists = LimpiezaEjecucion::where('limpiable_type', $detalle->limpiable_type)
                    ->where('limpiable_id', $detalle->limpiable_id)
                    ->whereDate('fecha', $fecha->toDateString())
                    ->where('turno_id', $horario->turno_id)
                    ->exists();

                if (! $exists) {
                    LimpiezaEjecucion::create([
                        'horario_id' => $horario->id,
                        'limpiable_type' => $detalle->limpiable_type,
                        'limpiable_id' => $detalle->limpiable_id,
                        'turno_id' => $horario->turno_id,
                        'colaborador_id' => null, // starts unassigned
                        'fecha' => $fecha->toDateString(),
                        'estado' => EstadoLimpieza::Pendiente,
                        'detalles_checklist' => ! empty($checklistData) ? $checklistData : null,
                    ]);
                    $creados++;
                    $creadosPorTurno[$horario->turno_id] = ($creadosPorTurno[$horario->turno_id] ?? 0) + 1;
                }
            }
        }

        // Enviar notificaciones de tareas sin asignar
        foreach ($creadosPorTurno as $turnoId => $cantidad) {
            $turno = Turno::with(['lider.persona', 'apoyo.persona'])->find($turnoId);
            if (! $turno) {
                continue;
            }

            $destinatarios = collect();

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

            $destinatarios = $destinatarios->filter()->unique('id');

            if ($destinatarios->isNotEmpty()) {
                $this->notificador->nuevasAsignaciones($turno, $cantidad, $destinatarios);
            }
        }

        $this->info("Ejecución finalizada. Se crearon {$creados} ejecuciones de limpieza.");

        return Command::SUCCESS;
    }
}
