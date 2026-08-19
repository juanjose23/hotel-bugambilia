<?php

declare(strict_types=1);

namespace App\Actions\Activos\Reportes;

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Repository\Queries\Activos\ObtenerHistorialMovimientosUseCase;
use App\Repository\Queries\Activos\ObtenerMantenimientosReportesUseCase;
use App\Repository\Queries\Activos\ObtenerReportesActivosVariosUseCase;
use App\Support\HotelInfo;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use Illuminate\Support\Collection;

final readonly class GenerarReporteHistorialYMantenimientosAction
{
    public function __construct(
        private ObtenerHistorialMovimientosUseCase $historialMovimientos,
        private ObtenerMantenimientosReportesUseCase $mantenimientosReportes,
        private ObtenerReportesActivosVariosUseCase $reportesActivosVarios,
        private ConvertirMoneda $convertirMoneda,
    ) {}

    /** @param array<string, mixed> $params */
    public function historial(array $params): DomPdfInstance
    {
        $activoId = is_numeric($params['activo_id'] ?? null) ? (int) $params['activo_id'] : 0;
        $resultado = $this->historialMovimientos->ejecutar($activoId);

        return $this->generarPdfSimple('reports.activos.historial-movimientos', [
            'nombreReporte' => 'Historial de Movimientos de Activo',
            'codigoReporte' => 'HTB-ACT-006',
            'activo' => $resultado['activo'],
            'lineaTiempo' => $resultado['lineaTiempo'],
            'filtroActivo' => $activoId > 0,
        ], collect($resultado['lineaTiempo']), $params);
    }

    /** @param array<string, mixed> $params */
    public function enMantenimiento(array $params): DomPdfInstance
    {
        $activos = $this->mantenimientosReportes->enMantenimiento();

        return $this->generarPdfSimple('reports.activos.en-mantenimiento', [
            'nombreReporte' => 'Activos en Mantenimiento',
            'codigoReporte' => 'HTB-ACT-007',
            'totalEnMantenimiento' => $activos->count(),
        ], collect($activos->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function garantiasProximas(array $params): DomPdfInstance
    {
        $dias = is_numeric($params['dias'] ?? null) ? (int) $params['dias'] : 90;
        $activos = $this->reportesActivosVarios->garantiasProximas($dias);

        return $this->generarPdfSimple('reports.activos.garantias-proximas', [
            'nombreReporte' => 'Garantías Próximas a Vencer',
            'codigoReporte' => 'HTB-ACT-008',
            'dias' => $dias,
            'totalPorVencer' => $activos->count(),
        ], collect($activos->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function dadosDeBaja(array $params): DomPdfInstance
    {
        $bajas = $this->reportesActivosVarios->dadosDeBaja();

        $totalValorResidual = 0.0;
        foreach ($bajas as $baja) {
            $totalValorResidual += $this->convertirMoneda->aBase(
                (float) ($baja->valor_residual ?? 0),
                $baja->activo?->moneda_id
            );
        }

        return $this->generarPdfSimple('reports.activos.dados-de-baja', [
            'nombreReporte' => 'Activos Dados de Baja',
            'codigoReporte' => 'HTB-ACT-009',
            'totalBajas' => $bajas->count(),
            'totalValorResidual' => $totalValorResidual,
        ], collect($bajas->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function extraviados(array $params): DomPdfInstance
    {
        $activos = $this->reportesActivosVarios->extraviados();

        $totalCosto = 0.0;
        foreach ($activos as $activo) {
            $totalCosto += $this->convertirMoneda->aBase(
                (float) ($activo->costo_adquisicion ?? 0),
                $activo->moneda_id
            );
        }

        return $this->generarPdfSimple('reports.activos.extraviados', [
            'nombreReporte' => 'Activos Extraviados',
            'codigoReporte' => 'HTB-ACT-010',
            'totalExtraviados' => $activos->count(),
            'totalCosto' => $totalCosto,
        ], collect($activos->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function sinAsignacion(array $params): DomPdfInstance
    {
        $activos = $this->reportesActivosVarios->sinAsignacion();

        return $this->generarPdfSimple('reports.activos.sin-asignacion', [
            'nombreReporte' => 'Activos Sin Asignación',
            'codigoReporte' => 'HTB-ACT-011',
            'totalSinAsignacion' => $activos->count(),
        ], collect($activos->all()), $params);
    }

    /** @param array<string, mixed> $params */
    public function mantenimientosVencidos(array $params): DomPdfInstance
    {
        $activos = $this->mantenimientosReportes->mantenimientosVencidos();

        return $this->generarPdfSimple('reports.activos.mantenimientos-vencidos', [
            'nombreReporte' => 'Mantenimientos Vencidos',
            'codigoReporte' => 'HTB-ACT-012',
            'totalVencidos' => $activos->count(),
        ], collect($activos->all()), $params);
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  array<string, mixed>  $viewData
     * @param  Collection<TKey, TValue>  $items
     * @param  array<string, mixed>  $params
     */
    private function generarPdfSimple(string $view, array $viewData, Collection $items, array $params): DomPdfInstance
    {
        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $items,
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView($view, array_merge(HotelInfo::getBaseData(), $viewData, [
            'paginas' => $paginas,
            'totalRegistros' => $items->count(),
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'pageSize' => $layout->tamano->cssName(),
            'orientation' => $layout->orientacion->cssName(),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
            'pageContentHeight' => $layout->altoContenidoMm(),
            'pageContentWidth' => $layout->anchoContenidoMm(),
        ]))->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );
    }
}
