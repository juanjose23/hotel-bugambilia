<?php

declare(strict_types=1);

namespace App\Actions\Compras\Recepciones;

use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Queries\Compras\Recepciones\ObtenerRecepcionReporteQuery;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use Barryvdh\DomPDF\Facade\Pdf;

final class GenerarReporteRecepcionPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly ObtenerRecepcionReporteQuery $query,
    ) {}

    public function ejecutar(RecepcionCompra $recepcion): \Barryvdh\DomPDF\PDF
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

        $pdf = Pdf::loadView('reports.compras.recepciones.recepcion', [
            'record' => $recepcionConRelaciones,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'barcodeBase64' => $barcodeBase64,
            'datosHotel' => $datosHotel,
            'pageMarginTop' => 8,
            'pageMarginRight' => 6,
            'pageMarginBottom' => 10,
            'pageMarginLeft' => 6,
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
