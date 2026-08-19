<?php

declare(strict_types=1);

namespace App\Http\Controllers\Servicios;

use App\Actions\Servicios\Reportes\GenerarHistoricoPreciosExcelAction;
use App\Actions\Servicios\Reportes\GenerarHistoricoPreciosPdfAction;
use App\Http\Controllers\ReporteController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServicioReportController extends ReporteController
{
    public function __construct(
        private readonly GenerarHistoricoPreciosPdfAction $historicoPreciosPdf,
        private readonly GenerarHistoricoPreciosExcelAction $historicoPreciosExcel,
    ) {}

    public function historicoPreciosPdf(Request $request): Response
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return $this->historicoPreciosPdf->ejecutar(
            $request->only(['categoria_id', 'servicio_id', 'moneda_id', 'estado'])
        );
    }

    public function historicoPreciosExcel(Request $request): StreamedResponse
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return $this->historicoPreciosExcel->ejecutar(
            $request->only(['categoria_id', 'servicio_id', 'moneda_id', 'estado'])
        );
    }
}
