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

final readonly class GenerarReporteCompra
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
        return match ($reportName) {
            'solicitud' => $this->solicitud($params),
            'cotizacion' => $this->cotizacion($params),
            'orden_compra' => $this->ordenCompra($params),
            'recepcion' => $this->recepcion($params),
            'devolucion' => $this->devolucion($params),
            'comparativa' => $this->comparativa($params),
            'resumen_departamentos' => $this->resumenDepartamentos($params),
            'rotacion_compras' => $this->rotacionCompras($params),
            'tiempos_entrega' => $this->tiemposEntrega($params),
            'solicitudes_estado' => $this->solicitudesEstado($params),
            'seguimiento_oc' => $this->seguimientoOc($params),
            'recepciones_proveedor' => $this->recepcionesProveedor($params),
            'analisis_precio' => $this->analisisPrecio($params),
            'valorizacion_categoria' => $this->valorizacionCategoria($params),
            'ranking_proveedores' => $this->rankingProveedores($params),
            'devoluciones_proveedor' => $this->devolucionesProveedor($params),
            'trazabilidad_completa' => $this->trazabilidadCompleta($params),
            default => throw new InvalidArgumentException("Reporte '$reportName' no soportado."),
        };
    }

    /** @param array<string, mixed> $params */
    private function solicitud(array $params): PDF
    {
        /** @var Solicitud $solicitud */
        $solicitud = $params['solicitud'];

        return $this->generarSolicitudPdf->ejecutar($solicitud);
    }

    /** @param array<string, mixed> $params */
    private function cotizacion(array $params): PDF
    {
        /** @var Cotizacion $cotizacion */
        $cotizacion = $params['cotizacion'];

        return $this->generarCotizacionPdf->ejecutar($cotizacion);
    }

    /** @param array<string, mixed> $params */
    private function ordenCompra(array $params): PDF
    {
        /** @var OrdenCompra $orden */
        $orden = $params['orden'];

        return $this->generarOrdenCompraPdf->ejecutar($orden);
    }

    /** @param array<string, mixed> $params */
    private function recepcion(array $params): PDF
    {
        /** @var RecepcionCompra $recepcion */
        $recepcion = $params['recepcion'];

        return $this->generarRecepcionPdf->ejecutar($recepcion);
    }

    /** @param array<string, mixed> $params */
    private function devolucion(array $params): PDF
    {
        /** @var DevolucionCompra $devolucion */
        $devolucion = $params['devolucion'];

        return $this->generarDevolucionPdf->ejecutar($devolucion);
    }

    /** @param array<string, mixed> $params */
    private function comparativa(array $params): PDF
    {
        /** @var Solicitud $solicitud */
        $solicitud = $params['solicitud'];

        return $this->generarComparativaPdf->ejecutar($solicitud);
    }

    /** @param array<string, mixed> $params */
    private function resumenDepartamentos(array $params): PDF
    {
        return $this->generarResumenDepartamentosPdf
            ->ejecutar(
                $this->fechaInicio($params),
                $this->fechaFin($params),
            );
    }

    /** @param array<string, mixed> $params */
    private function rotacionCompras(array $params): PDF
    {
        $reportData = $this->obtenerRotacionCompras->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
        );

        return $this->generarRotacionPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function tiemposEntrega(array $params): PDF
    {
        $reportData = $this->obtenerTiemposEntrega->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
        );

        return $this->generarTiemposEntregaPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function solicitudesEstado(array $params): PDF
    {
        $reportData = $this->obtenerSolicitudesPorEstado->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
            $this->estado($params),
        );

        return $this->generarSolicitudesEstadoPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function seguimientoOc(array $params): PDF
    {
        $reportData = $this->obtenerSeguimientoOrdenes->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
        );

        return $this->generarSeguimientoOcPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function recepcionesProveedor(array $params): PDF
    {
        $reportData = $this->obtenerRecepcionesPorProveedor->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
        );

        return $this->generarRecepcionesPorProveedorPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function analisisPrecio(array $params): PDF
    {
        $reportData = $this->obtenerAnalisisPrecio->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
            $this->meses($params),
        );

        return $this->generarAnalisisPrecioPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function valorizacionCategoria(array $params): PDF
    {
        $reportData = $this->obtenerValorizacion->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
        );

        return $this->generarValorizacionPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function rankingProveedores(array $params): PDF
    {
        $reportData = $this->obtenerRankingProveedores->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
        );

        return $this->generarRankingProveedoresPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function devolucionesProveedor(array $params): PDF
    {
        $reportData = $this->obtenerDevoluciones->ejecutar(
            $this->fechaInicio($params),
            $this->fechaFin($params),
        );

        return $this->generarDevolucionesPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function trazabilidadCompleta(array $params): PDF
    {
        /** @var Solicitud $solicitud */
        $solicitud = $params['solicitud'];
        $reportData = $this->obtenerTrazabilidadCompleta->ejecutar($solicitud);

        return $this->generarTrazabilidadCompletaPdf->ejecutar($reportData);
    }

    /** @param array<string, mixed> $params */
    private function fechaInicio(array $params): ?string
    {
        $valor = $params['fecha_inicio'] ?? null;

        return is_string($valor) ? $valor : null;
    }

    /** @param array<string, mixed> $params */
    private function fechaFin(array $params): ?string
    {
        $valor = $params['fecha_fin'] ?? null;

        return is_string($valor) ? $valor : null;
    }

    /** @param array<string, mixed> $params */
    private function estado(array $params): ?string
    {
        $valor = $params['estado'] ?? null;

        return is_string($valor) ? $valor : null;
    }

    /** @param array<string, mixed> $params */
    private function meses(array $params): int
    {
        $valor = $params['meses'] ?? null;

        return is_numeric($valor) ? (int) $valor : 6;
    }
}
