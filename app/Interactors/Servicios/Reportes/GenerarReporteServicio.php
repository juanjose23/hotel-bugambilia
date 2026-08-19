<?php

declare(strict_types=1);

namespace App\Interactors\Servicios\Reportes;

use App\Actions\Servicios\Reportes\GenerarHistoricoPreciosExcelAction;
use App\Actions\Servicios\Reportes\GenerarHistoricoPreciosPdfAction;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class GenerarReporteServicio
{
    public function __construct(
        private GenerarHistoricoPreciosPdfAction $historicoPreciosPdf,
        private GenerarHistoricoPreciosExcelAction $historicoPreciosExcel,
    ) {}

    /** @param array<string, mixed> $params */
    public function ejecutar(string $reportName, array $params = []): Response|StreamedResponse
    {
        $filtros = array_filter($params, fn (string $key) => in_array($key, ['servicio_id', 'moneda_id', 'estado', 'categoria_id'], true), ARRAY_FILTER_USE_KEY);

        /** @var array{servicio_id?: int|null, moneda_id?: int|null, estado?: int|null, categoria_id?: int|null} $filtros */
        return match ($reportName) {
            'historicoPreciosPdf' => $this->historicoPreciosPdf->ejecutar($filtros),
            'historicoPreciosExcel' => $this->historicoPreciosExcel->ejecutar($filtros),
            default => throw new InvalidArgumentException("Reporte Servicios '$reportName' no soportado."),
        };
    }

    /**
     * Alias for backward compatibility
     *
     * @param  array<string, mixed>  $params
     */
    public function execute(string $reportName, array $params = []): Response|StreamedResponse
    {
        return $this->ejecutar($reportName, $params);
    }
}
