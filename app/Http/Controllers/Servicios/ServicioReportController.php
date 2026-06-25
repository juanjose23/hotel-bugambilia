<?php

declare(strict_types=1);

namespace App\Http\Controllers\Servicios;

use App\Http\Controllers\Controller;
use App\UseCases\Servicios\Reportes\Queries\GenerarHistoricoPreciosExcel;
use App\UseCases\Servicios\Reportes\Queries\GenerarHistoricoPreciosPdf;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServicioReportController extends Controller
{
    public function historicoPreciosPdf(Request $request): PdfBuilder
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return app(GenerarHistoricoPreciosPdf::class)->ejecutar(
            $request->only(['producto_id', 'fecha_desde', 'fecha_hasta'])
        );
    }

    public function historicoPreciosExcel(Request $request): BinaryFileResponse
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return app(GenerarHistoricoPreciosExcel::class)->ejecutar(
            $request->only(['producto_id', 'fecha_desde', 'fecha_hasta'])
        );
    }
}
