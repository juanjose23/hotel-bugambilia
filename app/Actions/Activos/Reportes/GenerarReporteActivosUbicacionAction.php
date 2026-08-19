<?php

declare(strict_types=1);

namespace App\Actions\Activos\Reportes;

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Repository\Queries\Activos\ObtenerActivosPorUbicacionUseCase;
use App\Repository\Queries\Activos\ObtenerHojaHabitacionEspacioUseCase;
use App\Support\HotelInfo;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;

final readonly class GenerarReporteActivosUbicacionAction
{
    public function __construct(
        private ObtenerActivosPorUbicacionUseCase $activosPorUbicacion,
        private ObtenerHojaHabitacionEspacioUseCase $hojaHabitacionEspacio,
        private ConvertirMoneda $convertirMoneda,
    ) {}

    /** @param array<string, mixed> $params */
    public function porUbicacion(array $params): DomPdfInstance
    {
        $tipoFiltro = is_string($params['ubicacion_tipo'] ?? null) ? $params['ubicacion_tipo'] : null;
        $agrupados = $this->activosPorUbicacion->ejecutar($tipoFiltro);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: collect($agrupados),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        return Pdf::loadView('reports.activos.por-ubicacion', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Activos por Ubicación',
            'codigoReporte' => 'HTB-ACT-005',
            'paginas' => $paginas,
            'ubicaciones' => $agrupados,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'totalUbicaciones' => count($agrupados),
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

    /** @param array<string, mixed> $params */
    public function hojaHabitacion(array $params): DomPdfInstance
    {
        $tipo = is_string($params['tipo'] ?? null) ? $params['tipo'] : 'habitacion';
        $id = is_numeric($params['id'] ?? null) ? (int) $params['id'] : 0;
        $resultado = $this->hojaHabitacionEspacio->ejecutar($tipo, $id);

        $tamano = TamanoPapel::fromRequest(is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter');
        $orientacion = Orientacion::fromRequest(is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait');
        $layout = new LayoutPdf(tamano: $tamano, orientacion: $orientacion);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: $resultado['activos'],
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 20,
        );

        $totalCosto = 0.0;
        foreach ($resultado['activos'] as $asignacion) {
            if ($asignacion->activo) {
                $totalCosto += $this->convertirMoneda->aBase(
                    (float) ($asignacion->activo->costo_adquisicion ?? 0),
                    $asignacion->activo->moneda_id
                );
            }
        }

        return Pdf::loadView('reports.activos.hoja-habitacion', array_merge(HotelInfo::getBaseData(), [
            'nombreReporte' => 'Hoja de Habitación o Espacio',
            'codigoReporte' => 'HTB-ACT-013',
            'paginas' => $paginas,
            'activos' => $resultado['activos'],
            'entidad' => $resultado['entidad'],
            'tipo' => $tipo,
            'totalCosto' => $totalCosto,
            'formato_pagina' => $params['formato_pagina'] ?? null,
            'totalActivos' => $resultado['activos']->count(),
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
