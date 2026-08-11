<?php

declare(strict_types=1);

namespace App\Actions\Compras\Solicitudes;

use App\BusinessLogic\Compras\Data\Solicitudes\SolicitudReporteData;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Solicitudes\ObtenerSolicitudReporteQuery;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteSolicitudPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly ObtenerSolicitudReporteQuery $query,
    ) {}

    public function ejecutar(Solicitud $solicitud): PdfDocumento
    {
        $solicitudConRelaciones = $this->obtenerDatos($solicitud);

        $codigoReporte = 'HTB-COM-001';
        $nombreReporte = 'Solicitud de Compra';
        $barcodeBase64 = $this->obtenerBarcode((string) $solicitud->id);
        $layout = $this->construirLayout();

        $pdf = Pdf::loadView('reports.compras.solicitudes.solicitud-compra', $this->parametrosVista(
            codigoReporte: $codigoReporte,
            nombreReporte: $nombreReporte,
            layout: $layout,
            extra: [
                'solicitud' => $solicitudConRelaciones,
                'estadoLabel' => $solicitudConRelaciones->estado?->label() ?? '',
                'barcodeBase64' => $barcodeBase64,
            ],
        ))->setPaper('letter');

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

    private function obtenerDatos(Solicitud $solicitud): SolicitudReporteData|Solicitud
    {
        return $this->query->ejecutar($solicitud->id) ?? $solicitud;
    }

    private function obtenerBarcode(string $code): string
    {
        return $this->barcodeGenerator->base64(
            code: $code,
            height: 100,
            widthFactor: 4,
        );
    }

    private function construirLayout(): LayoutPdf
    {
        return new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function parametrosVista(string $codigoReporte, string $nombreReporte, LayoutPdf $layout, array $extra = []): array
    {
        return array_merge([
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => HotelInfo::getBaseData(),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ], $extra);
    }
}
