<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\ResolverDestinatarios;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Notifications\Limpieza\NotificadorLimpieza;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use App\Repository\Queries\Limpieza\ObtenerUsuariosPorPersonaIds;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final readonly class MaterializarEjecuciones
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
        private NotificadorLimpieza $notificador,
        private ResolverDestinatarios $resolverDestinatarios,
        private ObtenerUsuariosPorPersonaIds $obtenerUsuariosPorPersonaIds,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    /**
     * @return array{fecha: string, dia_semana: string, creados: int}
     */
    public function execute(?string $fechaInput = null): array
    {
        return $this->ejecutar($fechaInput);
    }

    /**
     * @return array{fecha: string, dia_semana: string, creados: int}
     */
    public function ejecutar(?string $fechaInput = null): array
    {
        $fecha = $fechaInput ? Carbon::parse($fechaInput) : Carbon::today();
        $diaSemanaActual = self::DIAS_SEMANA[$fecha->format('l')];

        $horarios = $this->limpiezaRepositorio->obtenerHorariosActivosParaMaterializar($diaSemanaActual);

        return $this->materializarHorarios($horarios, $fecha, $diaSemanaActual);
    }

    /**
     * @return array{fecha: string, dia_semana: string, creados: int}
     */
    public function ejecutarHorario(int $horarioId, ?string $fechaInput = null): array
    {
        $fecha = $fechaInput ? Carbon::parse($fechaInput) : Carbon::today();
        $diaSemanaActual = self::DIAS_SEMANA[$fecha->format('l')];

        $horarios = $this->limpiezaRepositorio->obtenerHorariosActivosParaMaterializar($diaSemanaActual)
            ->filter(fn (LimpiezaHorario $h): bool => (int) $h->id === $horarioId);

        return $this->materializarHorarios($horarios, $fecha, $diaSemanaActual);
    }

    /**
     * @param  Collection<int, LimpiezaHorario>  $horarios
     * @return array{fecha: string, dia_semana: string, creados: int}
     */
    private function materializarHorarios(Collection $horarios, Carbon $fecha, string $diaSemanaActual): array
    {
        $fechaStr = $fecha->toDateString();
        $ejecucionesExistentes = $this->limpiezaRepositorio->obtenerEjecucionesExistentesKeys($fechaStr);

        $creados = 0;
        $creadosPorTurno = [];
        /** @var list<array<string, mixed>> $nuevasEjecuciones */
        $nuevasEjecuciones = [];
        $ahora = Carbon::now()->toDateTimeString();

        foreach ($horarios as $horario) {
            $checklistData = $this->prepararChecklist($horario->checklist);

            foreach ($horario->detalles as $detalle) {
                $key = sprintf('%s-%s-%s', $detalle->limpiable_type, $detalle->limpiable_id, $horario->turno_id);
                if ($ejecucionesExistentes->has($key)) {
                    continue;
                }

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

        if ($nuevasEjecuciones !== []) {
            foreach (array_chunk($nuevasEjecuciones, 500) as $lote) {
                $this->limpiezaRepositorio->insertarEjecucionesMasivas($lote);
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

        $turnos = $this->limpiezaRepositorio->obtenerTurnosConPersonasPorIds($turnoIds);

        $personaIds = $turnos->pluck('lider.persona.id')
            ->merge($turnos->pluck('apoyo.persona.id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $usuarios = $this->obtenerUsuariosPorPersonaIds->ejecutar($personaIds);

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
