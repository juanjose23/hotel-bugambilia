<?php

declare(strict_types=1);

namespace App\Actions\Limpieza\Horarios;

use App\Repository\Models\Limpieza\Turno;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final readonly class ValidarCargaTurnoHorarioPlanificado
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function ejecutar(array $data): void
    {
        $turnoId = is_numeric($data['turno_id'] ?? null) ? (int) $data['turno_id'] : null;
        $minutosPorDestino = is_numeric($data['duracion_estimada_minutos'] ?? null)
            ? (int) $data['duracion_estimada_minutos']
            : 0;
        $detalles = is_array($data['detalles'] ?? null) ? $data['detalles'] : [];
        $cantidadDestinos = count($detalles);

        if ($turnoId === null || $minutosPorDestino <= 0 || $cantidadDestinos === 0) {
            return;
        }

        $turno = Turno::query()->find($turnoId);

        if (! $turno) {
            return;
        }

        $minutosTurno = self::minutosTurno($turno);
        $minutosPlanificados = $cantidadDestinos * $minutosPorDestino;

        if ($minutosPlanificados <= $minutosTurno) {
            return;
        }

        throw ValidationException::withMessages([
            'duracion_estimada_minutos' => "El turno {$turno->nombre} cubre {$minutosTurno} minutos, pero la planificación requiere {$minutosPlanificados} minutos.",
        ]);
    }

    public static function minutosTurno(Turno $turno): int
    {
        $inicio = CarbonImmutable::parse('2000-01-01 '.$turno->hora_inicio);
        $fin = CarbonImmutable::parse('2000-01-01 '.$turno->hora_fin);

        if ($fin->lessThanOrEqualTo($inicio)) {
            $fin = $fin->addDay();
        }

        return (int) $inicio->diffInMinutes($fin);
    }
}
