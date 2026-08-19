<?php

declare(strict_types=1);

namespace App\Actions\Compras\Proveedores;

use App\BusinessLogic\Compras\Data\Reportes\RankingProveedoresReporteData;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocumento;

final class GenerarReporteRankingProveedoresPdfAction
{
    use GuardaReporte;

    public function ejecutar(RankingProveedoresReporteData $reportData): PdfDocumento
    {
        $codigoReporte = 'HTB-COM-015';
        $nombreReporte = 'Ranking de Desempeño de Proveedores';
        $datosHotel = HotelInfo::getBaseData();

        $layout = new LayoutPdf;

        $paginador = new ReportePaginador($layout);
        $items = collect($reportData->data);

        $paginas = $paginador->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 10,
        );

        $pdf = Pdf::loadView('reports.compras.proveedores.ranking-proveedores', [
            'paginas' => $paginas,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'fechaInicio' => $reportData->fechaInicio,
            'fechaFin' => $reportData->fechaFin,
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
            'pageContentHeight' => $layout->altoContenidoMm(),
            'pageContentWidth' => $layout->anchoContenidoMm(),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'fecha_inicio' => $reportData->fechaInicio,
                'fecha_fin' => $reportData->fechaFin,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
