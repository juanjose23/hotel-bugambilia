<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\ResolverDestinatarios;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Notifications\Limpieza\NotificadorLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Queries\Limpieza\ObtenerUsuariosPorPersonaIds;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class MaterializarEjecuciones
{
    private const DIAS_SEMANA = [
        'Monday' => 'lunes',
        'Tuesday' => 'martes',
        'Wednesday' => 'miercoles',
        'Thursday' => 'jueves',
        'Friday' => 'viernes',
        'Saturday' => 'sabado',
        'Sunday' => 'domingo',
    ];

    public function __construct(
        private readonly NotificadorLimpieza $notificador,
        private readonly ResolverDestinatarios $resolverDestinatarios,
        private readonly ObtenerUsuariosPorPersonaIds $obtenerUsuariosPorPersonaIds,
    ) {}

    /**
     * @return array{fecha: string, dia_semana: string, creados: int}
     */
    public function ejecutar(?string $fechaInput = null): array
    {
        $fecha = $fechaInput ? Carbon::parse($fechaInput) : Carbon::today();
        $diaSemanaActual = self::DIAS_SEMANA[$fecha->format('l')];

        $horarios = $this->obtenerHorariosActivos($diaSemanaActual);

        $fechaStr = $fecha->toDateString();
        $ejecucionesExistentes = LimpiezaEjecucion::query()
            ->whereDate('fecha', $fechaStr)
            ->get(['limpiable_type', 'limpiable_id', 'turno_id'])
            ->map(fn ($e) => "{$e->limpiable_type}:{$e->limpiable_id}:{$e->turno_id}")
            ->flip();

        $creados = 0;
        $creadosPorTurno = [];
        /** @var list<array<string, mixed>> $nuevasEjecuciones */
        $nuevasEjecuciones = [];
        $ahora = Carbon::now()->toDateTimeString();

        foreach ($horarios as $horario) {
            $checklistData = $this->prepararChecklist($horario->checklist);

            foreach ($horario->detalles as $detalle) {
                $key = "{$detalle->limpiable_type}:{$detalle->limpiable_id}:{$horario->turno_id}";
                if ($ejecucionesExistentes->has($key)) {
                    continue;
                }

                // Acumular en memoria para un único INSERT masivo al final
                $nuevasEjecuciones[] = [
                    'horario_id' => $horario->id,
                    'limpiable_type' => $detalle->limpiable_type,
                    'limpiable_id' => $detalle->limpiable_id,
                    'turno_id' => $horario->turno_id,
                    'colaborador_id' => null,
                    'fecha' => $fechaStr,
                    'estado' => EstadoLimpieza::Pendiente->value,
                    'detalles_checklist' => $checklistData !== null ? json_encode($checklistData) : null,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                $ejecucionesExistentes->put($key, 1);

                $creados++;
                $turnoId = (int) $horario->turno_id;
                $creadosPorTurno[$turnoId] = ($creadosPorTurno[$turnoId] ?? 0) + 1;
            }
        }

        // INSERT masivo: una sola query en lugar de N INSERTs individuales
        if ($nuevasEjecuciones !== []) {
            foreach (array_chunk($nuevasEjecuciones, 500) as $lote) {
                LimpiezaEjecucion::insert($lote);
            }
        }

        $this->notificarNuevasAsignaciones($creadosPorTurno);

        return [
            'fecha' => $fecha->toDateString(),
            'dia_semana' => $diaSemanaActual,
            'creados' => $creados,
        ];
    }

    /**
     * @return Collection<int, LimpiezaHorario>
     */
    private function obtenerHorariosActivos(string $diaSemanaActual): Collection
    {
        return LimpiezaHorario::query()
            ->where('activo', true)
            ->whereNotNull('turno_id')
            ->where(function (Builder $query) use ($diaSemanaActual) {
                $query->where('frecuencia', 'diaria')
                    ->orWhere(function (Builder $query) use ($diaSemanaActual) {
                        $query->where('frecuencia', 'semanal')
                            ->where('dia_semana', $diaSemanaActual);
                    });
            })
            ->with(['turno', 'detalles'])
            ->get();
    }

    /**
     * @param  array<array-key, mixed>|null  $checklist
     * @return array<string, bool>|null
     */
    private function prepararChecklist(?array $checklist): ?array
    {
        if (empty($checklist)) {
            return null;
        }

        $checklistData = array_fill_keys(
            array_map(
                static fn (mixed $task): string => is_string($task) ? $task : strval($task),
                array_filter($checklist, static fn (mixed $task): bool => is_string($task) || is_int($task)),
            ),
            false,
        );

        return $checklistData;
    }

    /**
     * @param  array<int, int>  $creadosPorTurno
     */
    private function notificarNuevasAsignaciones(array $creadosPorTurno): void
    {
        $turnoIds = array_keys($creadosPorTurno);
        if ($turnoIds === []) {
            return;
        }

        $turnos = Turno::with(['lider.persona', 'apoyo.persona'])
            ->whereIn('id', $turnoIds)
            ->get();

        $personaIds = $turnos->pluck('lider.persona.id')
            ->merge($turnos->pluck('apoyo.persona.id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $usuarios = $this->obtenerUsuariosPorPersonaIds->ejecutar($personaIds);

        // keyBy('id') para acceso O(1) en lugar de firstWhere O(n) por iteración
        $turnosPorId = $turnos->keyBy('id');

        foreach ($creadosPorTurno as $turnoId => $cantidad) {
            $turno = $turnosPorId->get($turnoId);
            if ($turno === null) {
                continue;
            }

            $destinatarios = $this->resolverDestinatarios->paraTurno($turno, $usuarios);

            if ($destinatarios->isNotEmpty()) {
                $this->notificador->nuevasAsignaciones($turno, $cantidad, $destinatarios);
            }
        }
    }
}
