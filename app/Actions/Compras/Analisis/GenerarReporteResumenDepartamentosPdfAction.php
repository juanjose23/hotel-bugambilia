<?php

declare(strict_types=1);

namespace App\Actions\Compras\Analisis;

use App\Repository\Queries\Compras\Reportes\ObtenerResumenDepartamentosCompras;
use App\Support\HotelInfo;
use App\Support\Pdf\Concerns\GuardaReporte;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;

final class GenerarReporteResumenDepartamentosPdfAction
{
    use GuardaReporte;

    public function __construct(
        private readonly ObtenerResumenDepartamentosCompras $query,
    ) {}

    public function ejecutar(?string $fechaInicio = null, ?string $fechaFin = null): \Barryvdh\DomPDF\PDF
    {
        $codigoReporte = 'HTB-COM-017';
        $nombreReporte = 'Resumen de Compras por Departamento';
        $datosHotel = HotelInfo::getBaseData();

        $layout = new LayoutPdf(
            margenSuperiorMm: 8,
            margenInferiorMm: 10,
            altoPieMm: 0,
        );

        $paginador = new ReportePaginador($layout);
        $resumenData = $this->query->ejecutar($fechaInicio, $fechaFin);
        $items = collect($resumenData->data);

        $paginas = $paginador->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
        );

        $pdf = Pdf::loadView('reports.compras.analisis.resumen-departamentos', [
            'paginas' => $paginas,
            'codigoReporte' => $codigoReporte,
            'nombreReporte' => $nombreReporte,
            'datosHotel' => $datosHotel,
            'fechaInicio' => $fechaInicio ?? 'Histórico',
            'fechaFin' => $fechaFin ?? 'Hoy',
            'totalGeneral' => $items->sum('total_oc'),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenSuperiorMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenSuperiorMm,
        ])->setPaper('letter', 'portrait');

        $this->guardarAuditoria(
            tipoReporte: $codigoReporte,
            parametros: [
                'id' => 0,
                'codigo_referencia' => 'GENERAL',
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
            pdf: $pdf,
        );

        return $pdf;
    }
}
