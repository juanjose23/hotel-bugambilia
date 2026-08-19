<?php

declare(strict_types=1);

namespace App\Actions\Compras\Cotizaciones;

use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Cotizaciones\ObtenerComparativaReporteQuery;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteComparativaPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly ObtenerComparativaReporteQuery $query,
    ) {}

    public function ejecutar(Solicitud $solicitud): PdfDocumento
    {
        $solicitudConRelaciones = $this->query->ejecutar($solicitud->id) ?? $solicitud;

        $codigoReporte = 'HTB-COM-006';
        $nombreReporte = 'Cuadro Comparativo de Cotizaciones';
        $datosHotel = HotelInfo::getBaseData();

        $layout = new LayoutPdf;

        $pdf = Pdf::loadView('reports.compras.cotizaciones.comparativa', [
            'record' => $solicitudConRelaciones,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
        ])->setPaper('letter');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'solicitud_id' => $solicitud->id,
                'codigo' => $solicitud->codigo,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
