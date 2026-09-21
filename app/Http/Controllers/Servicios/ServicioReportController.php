<?php

declare(strict_types=1);

namespace App\Http\Controllers\Servicios;

use App\Actions\Servicios\Reportes\GenerarHistoricoPreciosExcelAction;
use App\Actions\Servicios\Reportes\GenerarHistoricoPreciosPdfAction;
use App\Http\Controllers\ReporteController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServicioReportController extends ReporteController
{
    public function __construct(
        private readonly GenerarHistoricoPreciosPdfAction $historicoPreciosPdf,
        private readonly GenerarHistoricoPreciosExcelAction $historicoPreciosExcel,
    ) {}

    public function historicoPreciosPdf(Request $request): Response|StreamedResponse|JsonResponse
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        $params = $this->filtrosReporte($request);

        return $this->manejarReporte(
            $request,
            'HTB-SER-001',
            $params,
            fn () => $this->historicoPreciosPdf->ejecutar($params),
        );
    }

    public function historicoPreciosExcel(Request $request): StreamedResponse
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return $this->historicoPreciosExcel->ejecutar(
            $this->filtrosReporte($request)
        );
    }

    /** @return array{servicio_id: int|null, moneda_id: int|null, estado: int|null, categoria_id: int|null} */
    private function filtrosReporte(Request $request): array
    {
        return [
            'categoria_id' => $request->integer('categoria_id', 0) ?: null,
            'servicio_id' => $request->integer('servicio_id', 0) ?: null,
            'moneda_id' => $request->integer('moneda_id', 0) ?: null,
            'estado' => $request->integer('estado', 0) ?: null,
        ];
    }
}
