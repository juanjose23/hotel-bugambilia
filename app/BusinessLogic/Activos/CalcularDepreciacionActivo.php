<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use Carbon\CarbonInterface;

final readonly class CalcularDepreciacionActivo
{
    /**
     * @return array{valor_libros: float|null, depreciacion_acumulada: float|null}
     */
    public function ejecutar(
        ?float $costoAdquisicion,
        ?int $vidaUtilMeses,
        ?CarbonInterface $fechaAdquisicion,
    ): array {
        if ($costoAdquisicion === null || $vidaUtilMeses === null || $fechaAdquisicion === null) {
            return ['valor_libros' => null, 'depreciacion_acumulada' => null];
        }

        $meses = now()->diffInMonths($fechaAdquisicion);

        if ($meses >= $vidaUtilMeses) {
            return ['valor_libros' => 0.0, 'depreciacion_acumulada' => $costoAdquisicion];
        }

        $depreciacion = ($costoAdquisicion / $vidaUtilMeses) * $meses;

        return [
            'valor_libros' => max(0.0, $costoAdquisicion - $depreciacion),
            'depreciacion_acumulada' => $depreciacion,
        ];
    }
}
