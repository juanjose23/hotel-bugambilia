<?php

declare(strict_types=1);

namespace App\Http\Controllers\Servicios;

use App\Http\Controllers\Controller;
use App\UseCases\Servicios\Reportes\Queries\GenerarHistoricoPreciosExcel;
use App\UseCases\Servicios\Reportes\Queries\GenerarHistoricoPreciosPdf;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServicioReportController extends Controller
{
    public function historicoPreciosPdf(): PdfBuilder
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return app(GenerarHistoricoPreciosPdf::class)->ejecutar(request()->all());
    }

    public function historicoPreciosExcel(): BinaryFileResponse
    {
        $this->authorize('Servicios:ReporteHistoricoPrecios');

        return app(GenerarHistoricoPreciosExcel::class)->ejecutar(request()->all());
    }
}
