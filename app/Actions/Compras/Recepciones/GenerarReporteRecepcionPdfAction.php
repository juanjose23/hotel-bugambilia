<?php

declare(strict_types=1);

namespace App\Actions\Compras\Recepciones;

use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Queries\Compras\Recepciones\ObtenerRecepcionReporteQuery;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteRecepcionPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly ObtenerRecepcionReporteQuery $query,
    ) {}

    public function ejecutar(RecepcionCompra $recepcion): PdfDocumento
    {
        $recepcionConRelaciones = $this->query->ejecutar($recepcion->id) ?? $recepcion;

        $codigoReporte = 'HTB-COM-004';
        $nombreReporte = 'Recepción de Mercancía';
        $datosHotel = HotelInfo::getBaseData();
        $barcodeBase64 = $this->barcodeGenerator->base64(
            code: (string) $recepcion->id,
            height: 100,
            widthFactor: 4,
        );

        $layout = new LayoutPdf;

        $pdf = Pdf::loadView('reports.compras.recepciones.recepcion', [
            'record' => $recepcionConRelaciones,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'barcodeBase64' => $barcodeBase64,
            'datosHotel' => $datosHotel,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
        ])->setPaper('letter');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'recepcion_id' => $recepcion->id,
                'orden_compra_id' => $recepcion->orden_compra_id,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
