<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\BusinessLogic\Reservas\Data\RegistrarHuespedData;
use App\Enums\Reservas\TipoHuesped;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaHuesped;

final readonly class ReservaHuespedRepositorio
{
    /** @param array<int, mixed> $huespedes */
    public function crearHuespedes(ReservaDetalle $detalle, array $huespedes): void
    {
        $registros = [];
        $now = now();

        foreach ($huespedes as $huesped) {
            if ($huesped instanceof RegistrarHuespedData) {
                if (trim($huesped->nombre) === '') {
                    continue;
                }
                $registros[] = [
                    'reserva_detalle_id' => $detalle->id,
                    'nombre' => $huesped->nombre,
                    'identificacion' => $huesped->numeroDocumento,
                    'tipo_huesped' => $huesped->tipoHuesped->value,
                    'es_titular' => $huesped->esTitular,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                continue;
            }

            if (! is_array($huesped) || empty($huesped['nombre'])) {
                continue;
            }

            $tipoRaw = strtolower($this->aString($huesped['tipo'] ?? 'adulto', 'adulto'));
            $tipo = match ($tipoRaw) {
                'nino', 'niño', 'child' => TipoHuesped::NINO,
                default => TipoHuesped::ADULTO,
            };

            $registros[] = [
                'reserva_detalle_id' => $detalle->id,
                'nombre' => $huesped['nombre'],
                'identificacion' => is_string($huesped['identificacion'] ?? null) ? $huesped['identificacion'] : null,
                'tipo_huesped' => $tipo->value,
                'es_titular' => (bool) ($huesped['es_titular'] ?? false),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($registros !== []) {
            ReservaHuesped::insert($registros);
        }
    }

    /** @param array<string, mixed> $datos */
    public function crearHuesped(ReservaDetalle $detalle, array $datos): ReservaHuesped
    {
        /** @var ReservaHuesped $huesped */
        $huesped = $detalle->huespedes()->create($datos);

        return $huesped;
    }

    /** @param array<string, mixed> $datos */
    public function actualizarHuesped(ReservaHuesped $huesped, array $datos): ReservaHuesped
    {
        $huesped->update($datos);

        /** @var ReservaHuesped $actualizado */
        $actualizado = $huesped->fresh() ?? $huesped;

        return $actualizado;
    }

    public function eliminarHuesped(ReservaHuesped $huesped): void
    {
        $huesped->delete();
    }

    private function aString(mixed $valor, string $default = ''): string
    {
        if (is_string($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (string) $valor;
        }

        return $default;
    }
}
