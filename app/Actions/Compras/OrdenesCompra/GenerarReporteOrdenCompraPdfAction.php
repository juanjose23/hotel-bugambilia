<?php

declare(strict_types=1);

namespace App\Actions\Compras\OrdenesCompra;

use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Queries\Compras\OrdenesCompra\ObtenerOrdenCompraReporteQuery;
use App\Support\Barcode\BarcodeGenerator;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteOrdenCompraPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly BarcodeGenerator $barcodeGenerator,
        private readonly ObtenerOrdenCompraReporteQuery $query,
    ) {}

    public function ejecutar(OrdenCompra $ordenCompra): PdfDocumento
    {
        /** @var int $ordenId */
        $ordenId = $ordenCompra->getKey();
        $ordenCompraConRelaciones = $this->query->ejecutar($ordenId) ?? $ordenCompra;

        $datosHotel = HotelInfo::getBaseData();
        $barcodeBase64 = $this->barcodeGenerator->base64(
            code: (string) $ordenId,
            height: 100,
            widthFactor: 4,
        );

        $moneda = $ordenCompraConRelaciones->cotizacion?->moneda;
        $simboloMoneda = $moneda->simbolo ?? '$';

        $codigoReporte = 'HTB-COM-003';
        $nombreReporte = 'Orden de Compra';

        $layout = new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $pdf = Pdf::loadView('reports.compras.ordenes_compra.orden-compra', [
            'ordenCompra' => $ordenCompraConRelaciones,
            'estadoLabel' => $ordenCompraConRelaciones->estado?->label() ?? '',
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'barcodeBase64' => $barcodeBase64,
            'simboloMoneda' => $simboloMoneda,
            'datosHotel' => $datosHotel,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ])->setPaper('letter', 'portrait');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'orden_compra_id' => $ordenCompra->id,
                'codigo' => $ordenCompra->codigo,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
