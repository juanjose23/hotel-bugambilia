<?php

declare(strict_types=1);

namespace App\Actions\Limpieza\Horarios;

final readonly class NormalizarDestinosHorarioPlanificado
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function ejecutar(array $data): array
    {
        $tipo = is_string($data['seleccion_masiva_tipo'] ?? null) ? $data['seleccion_masiva_tipo'] : null;
        $destinos = is_array($data['seleccion_masiva_destinos'] ?? null) ? $data['seleccion_masiva_destinos'] : [];

        unset($data['seleccion_masiva_tipo'], $data['seleccion_masiva_ubicacion_id'], $data['seleccion_masiva_destinos']);

        if ($tipo === null || $destinos === []) {
            return $data;
        }

        $detalles = is_array($data['detalles'] ?? null) ? $data['detalles'] : [];
        $existentes = [];

        foreach ($detalles as $detalle) {
            if (! is_array($detalle)) {
                continue;
            }

            $detalleTipo = is_string($detalle['limpiable_type'] ?? null) ? $detalle['limpiable_type'] : null;
            $detalleId = is_numeric($detalle['limpiable_id'] ?? null) ? (int) $detalle['limpiable_id'] : null;

            if ($detalleTipo !== null && $detalleId !== null) {
                $existentes[$detalleTipo.'|'.$detalleId] = true;
            }
        }

        foreach ($destinos as $destinoId) {
            if (! is_numeric($destinoId)) {
                continue;
            }

            $id = (int) $destinoId;
            $key = $tipo.'|'.$id;

            if (isset($existentes[$key])) {
                continue;
            }

            $detalles[] = [
                'limpiable_type' => $tipo,
                'limpiable_id' => $id,
            ];

            $existentes[$key] = true;
        }

        $data['detalles'] = $detalles;

        return $data;
    }
}
