<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Reportes;

use App\Actions\Compras\Analisis\GenerarReporteAnalisisPrecioPdfAction;
use App\Actions\Compras\Analisis\GenerarReporteResumenDepartamentosPdfAction;
use App\Actions\Compras\Analisis\GenerarReporteRotacionPdfAction;
use App\Actions\Compras\Analisis\GenerarReporteTiemposEntregaPdfAction;
use App\Actions\Compras\Analisis\GenerarReporteTrazabilidadCompletaPdfAction;
use App\Actions\Compras\Analisis\GenerarReporteValorizacionPdfAction;
use App\Actions\Compras\Cotizaciones\GenerarReporteComparativaPdfAction;
use App\Actions\Compras\Cotizaciones\GenerarReporteCotizacionPdfAction;
use App\Actions\Compras\Devoluciones\GenerarReporteDevolucionesPdfAction;
use App\Actions\Compras\Devoluciones\GenerarReporteDevolucionPdfAction;
use App\Actions\Compras\OrdenesCompra\GenerarReporteOrdenCompraPdfAction;
use App\Actions\Compras\OrdenesCompra\GenerarReporteSeguimientoOcPdfAction;
use App\Actions\Compras\Proveedores\GenerarReporteRankingProveedoresPdfAction;
use App\Actions\Compras\Recepciones\GenerarReporteRecepcionesPorProveedorPdfAction;
use App\Actions\Compras\Recepciones\GenerarReporteRecepcionPdfAction;
use App\Actions\Compras\Solicitudes\GenerarReporteSolicitudesEstadoPdfAction;
use App\Actions\Compras\Solicitudes\GenerarReporteSolicitudPdfAction;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Reportes\ObtenerAnalisisPrecioHistoricoQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerDevolucionesPorProveedorQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerRankingProveedoresQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerRecepcionesPorProveedorQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerRotacionComprasQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerSeguimientoOrdenesCompraQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerSolicitudesPorEstadoQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerTiemposEntregaQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerTrazabilidadCompletaQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerValorizacionPorCategoriaQuery;
use Barryvdh\DomPDF\PDF;
use InvalidArgumentException;

final readonly class GenerarReporteCompraUseCase
{
    public function __construct(

        private GenerarReporteSolicitudPdfAction $generarSolicitudPdf,
        private GenerarReporteCotizacionPdfAction $generarCotizacionPdf,
        private GenerarReporteOrdenCompraPdfAction $generarOrdenCompraPdf,
        private GenerarReporteComparativaPdfAction $generarComparativaPdf,
        private GenerarReporteRecepcionPdfAction $generarRecepcionPdf,
        private GenerarReporteDevolucionPdfAction $generarDevolucionPdf,

        private GenerarReporteRotacionPdfAction $generarRotacionPdf,
        private GenerarReporteTiemposEntregaPdfAction $generarTiemposEntregaPdf,

        private GenerarReporteResumenDepartamentosPdfAction $generarResumenDepartamentosPdf,
        private GenerarReporteSolicitudesEstadoPdfAction $generarSolicitudesEstadoPdf,
        private GenerarReporteSeguimientoOcPdfAction $generarSeguimientoOcPdf,
        private GenerarReporteRecepcionesPorProveedorPdfAction $generarRecepcionesPorProveedorPdf,
        private GenerarReporteAnalisisPrecioPdfAction $generarAnalisisPrecioPdf,
        private GenerarReporteValorizacionPdfAction $generarValorizacionPdf,
        private GenerarReporteRankingProveedoresPdfAction $generarRankingProveedoresPdf,
        private GenerarReporteDevolucionesPdfAction $generarDevolucionesPdf,
        private GenerarReporteTrazabilidadCompletaPdfAction $generarTrazabilidadCompletaPdf,

        private ObtenerRotacionComprasQuery $obtenerRotacionCompras,
        private ObtenerTiemposEntregaQuery $obtenerTiemposEntrega,
        private ObtenerSolicitudesPorEstadoQuery $obtenerSolicitudesPorEstado,
        private ObtenerSeguimientoOrdenesCompraQuery $obtenerSeguimientoOrdenes,
        private ObtenerRecepcionesPorProveedorQuery $obtenerRecepcionesPorProveedor,
        private ObtenerAnalisisPrecioHistoricoQuery $obtenerAnalisisPrecio,
        private ObtenerValorizacionPorCategoriaQuery $obtenerValorizacion,
        private ObtenerRankingProveedoresQuery $obtenerRankingProveedores,
        private ObtenerDevolucionesPorProveedorQuery $obtenerDevoluciones,
        private ObtenerTrazabilidadCompletaQuery $obtenerTrazabilidadCompleta,
    ) {}

    /** @param array<string, mixed> $params */
    public function execute(string $reportName, array $params = []): PDF
    {
        $fechaInicio = isset($params['fecha_inicio']) && is_string($params['fecha_inicio']) ? $params['fecha_inicio'] : null;
        $fechaFin = isset($params['fecha_fin']) && is_string($params['fecha_fin']) ? $params['fecha_fin'] : null;
        $estado = isset($params['estado']) && is_string($params['estado']) ? $params['estado'] : null;
        $meses = isset($params['meses']) && is_numeric($params['meses']) ? (int) $params['meses'] : 6;

        switch ($reportName) {

            case 'solicitud':
                /** @var Solicitud $solicitud */
                $solicitud = $params['solicitud'];

                return $this->generarSolicitudPdf->ejecutar($solicitud);

            case 'cotizacion':
                /** @var Cotizacion $cotizacion */
                $cotizacion = $params['cotizacion'];

                return $this->generarCotizacionPdf->ejecutar($cotizacion);

            case 'orden_compra':
                /** @var OrdenCompra $orden */
                $orden = $params['orden'];

                return $this->generarOrdenCompraPdf->ejecutar($orden);

            case 'recepcion':
                /** @var RecepcionCompra $recepcion */
                $recepcion = $params['recepcion'];

                return $this->generarRecepcionPdf->ejecutar($recepcion);

            case 'devolucion':
                /** @var DevolucionCompra $devolucion */
                $devolucion = $params['devolucion'];

                return $this->generarDevolucionPdf->ejecutar($devolucion);

            case 'comparativa':
                /** @var Solicitud $solicitud */
                $solicitud = $params['solicitud'];

                return $this->generarComparativaPdf->ejecutar($solicitud);

            case 'resumen_departamentos':
                return $this->generarResumenDepartamentosPdf
                    ->ejecutar(
                        $fechaInicio,
                        $fechaFin,
                    );

            case 'rotacion_compras':
                $reportData = $this->obtenerRotacionCompras->ejecutar(
                    $fechaInicio,
                    $fechaFin
                );

                return $this->generarRotacionPdf->ejecutar($reportData);

            case 'tiempos_entrega':
                $reportData = $this->obtenerTiemposEntrega->ejecutar(
                    $fechaInicio,
                    $fechaFin
                );

                return $this->generarTiemposEntregaPdf->ejecutar($reportData);

            case 'solicitudes_estado':
                $reportData = $this->obtenerSolicitudesPorEstado->ejecutar(
                    $fechaInicio,
                    $fechaFin,
                    $estado,
                );

                return $this->generarSolicitudesEstadoPdf->ejecutar($reportData);

            case 'seguimiento_oc':
                $reportData = $this->obtenerSeguimientoOrdenes->ejecutar(
                    $fechaInicio,
                    $fechaFin
                );

                return $this->generarSeguimientoOcPdf->ejecutar($reportData);

            case 'recepciones_proveedor':
                $reportData = $this->obtenerRecepcionesPorProveedor->ejecutar(
                    $fechaInicio,
                    $fechaFin
                );

                return $this->generarRecepcionesPorProveedorPdf->ejecutar($reportData);

            case 'analisis_precio':
                $reportData = $this->obtenerAnalisisPrecio->ejecutar(
                    $fechaInicio,
                    $fechaFin,
                    $meses,
                );

                return $this->generarAnalisisPrecioPdf->ejecutar($reportData);

            case 'valorizacion_categoria':
                $reportData = $this->obtenerValorizacion->ejecutar(
                    $fechaInicio,
                    $fechaFin
                );

                return $this->generarValorizacionPdf->ejecutar($reportData);

            case 'ranking_proveedores':
                $reportData = $this->obtenerRankingProveedores->ejecutar(
                    $fechaInicio,
                    $fechaFin
                );

                return $this->generarRankingProveedoresPdf->ejecutar($reportData);

            case 'devoluciones_proveedor':
                $reportData = $this->obtenerDevoluciones->ejecutar(
                    $fechaInicio,
                    $fechaFin
                );

                return $this->generarDevolucionesPdf->ejecutar($reportData);

            case 'trazabilidad_completa':
                /** @var Solicitud $solicitud */
                $solicitud = $params['solicitud'];
                $reportData = $this->obtenerTrazabilidadCompleta->ejecutar($solicitud);

                return $this->generarTrazabilidadCompletaPdf->ejecutar($reportData);

            default:
                throw new InvalidArgumentException("Reporte '$reportName' no soportado.");
        }
    }
}
