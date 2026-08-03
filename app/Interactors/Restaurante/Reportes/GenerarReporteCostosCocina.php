<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Reportes;

use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Collection;

final class GenerarReporteCostosCocina
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @return array{
     *     procesos: Collection<int, ProcesoCocina>,
     *     total_procesos: int,
     *     total_platos: int,
     *     costo_total_acumulado: float,
     *     costo_promedio_por_plato: float
     * }
     */
    public function ejecutar(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $procesos = $this->repositorio->obtenerProcesosCocinaFiltrados($fechaInicio, $fechaFin);

        $totalProcesos = count($procesos);

        /** @var int|float $sumaCantidadPlatos */
        $sumaCantidadPlatos = $procesos->sum('cantidad_platos');
        $totalPlatos = (int) $sumaCantidadPlatos;

        /** @var int|float $sumaCostoTotal */
        $sumaCostoTotal = $procesos->sum('costo_total');
        $costoTotalAcumulado = round((float) $sumaCostoTotal, 2);
        $costoPromedioPorPlato = $totalPlatos > 0 ? round($costoTotalAcumulado / $totalPlatos, 2) : 0.0;

        return [
            'procesos' => $procesos,
            'total_procesos' => $totalProcesos,
            'total_platos' => $totalPlatos,
            'costo_total_acumulado' => $costoTotalAcumulado,
            'costo_promedio_por_plato' => $costoPromedioPorPlato,
        ];
    }
}
