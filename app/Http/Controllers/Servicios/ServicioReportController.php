<?php

declare(strict_types=1);

namespace App\Http\Controllers\Servicios;

use App\Http\Controllers\Controller;
use App\Repository\Queries\Servicios\Reportes\GenerarHistoricoPreciosExcel;
use App\Repository\Queries\Servicios\Reportes\GenerarHistoricoPreciosPdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ServicioReportController extends Controller
{
    public function __construct(
        private readonly GenerarHistoricoPreciosPdf $historicoPreciosPdf,
        private readonly GenerarHistoricoPreciosExcel $historicoPreciosExcel,
    ) {}

    public function historicoPreciosPdf(Request $request): Response
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return $this->historicoPreciosPdf->ejecutar(
            $request->only(['producto_id', 'fecha_desde', 'fecha_hasta'])
        );
    }

    public function historicoPreciosExcel(Request $request): BinaryFileResponse
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return $this->historicoPreciosExcel->ejecutar(
            $request->only(['producto_id', 'fecha_desde', 'fecha_hasta'])
        );
    }
}
