<?php

declare(strict_types=1);

namespace App\Interactors\Reportes\Financiero;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Queries\Reportes\CuentasCobrarQuery;
use App\Repository\Queries\Reportes\FacturacionVentasQuery;
use App\Repository\Queries\Reportes\ResumenEjecutivoQuery;
use App\Support\HotelInfo;
use App\Support\Pdf\FormatoPagina;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use InvalidArgumentException;

final readonly class GenerarReporteFinanciero
{
    public function __construct(
        private readonly CuentasCobrarQuery $cuentasCobrar,
        private readonly FacturacionVentasQuery $facturacionVentas,
        private readonly ResumenEjecutivoQuery $resumenEjecutivo,
        private readonly RegistrarAuditoriaReporte $auditoria,
    ) {}

    /** @param array<string, mixed> $params */
    public function ejecutar(string $reportName, array $params = []): Response
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : now()->startOfMonth()->format('Y-m-d');
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : now()->format('Y-m-d');
        $formatoPagina = is_string($params['formato_pagina'] ?? null) ? $params['formato_pagina'] : null;

        return match ($reportName) {
            'cuentasCobrarPdf' => $this->cuentasCobrarPdf($fechaInicio, $fechaFin, $formatoPagina),
            'facturacionVentasPdf' => $this->facturacionVentasPdf($fechaInicio, $fechaFin, $formatoPagina),
            'resumenEjecutivoPdf' => $this->resumenEjecutivoPdf($fechaInicio, $fechaFin, $formatoPagina),
            default => throw new InvalidArgumentException("Reporte Financiero '$reportName' no soportado."),
        };
    }

    /**
     * Alias for backward compatibility
     *
     * @param  array<string, mixed>  $params
     */
    public function execute(string $reportName, array $params = []): Response
    {
        return $this->ejecutar($reportName, $params);
    }

    /**
     * @return array<string, mixed>
     */
    private function datosHotel(): array
    {
        return HotelInfo::getBaseData();
    }

    public function cuentasCobrarPdf(string $fechaInicio, string $fechaFin, ?string $formatoPagina = null): Response
    {
        $layout = $this->layoutPdf($formatoPagina);
        $reservasConSaldo = $this->cuentasCobrar->reservasConSaldo($fechaInicio, $fechaFin);
        $cuentasPendientes = $this->cuentasCobrar->cuentasPendientes();

        $paginasReservas = (new ReportePaginador($layout))->paginar(
            items: collect($reservasConSaldo->all()),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 15,
        );

        $paginasCuentas = (new ReportePaginador($layout))->paginar(
            items: collect($cuentasPendientes->all()),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 15,
        );

        $pdf = Pdf::loadView('reports.financiero.reporte-cuentas-cobrar', [
            'titulo' => 'Reporte de Cuentas por Cobrar y Antigüedad de Saldos',
            'codigo' => 'HTB-FIN-001',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'paginasReservas' => $paginasReservas,
            'paginasCuentas' => $paginasCuentas,
            'totalPendiente' => $this->monto($reservasConSaldo->sum('saldo')) + $this->monto($cuentasPendientes->sum('saldo')),
            'totalRegistros' => $reservasConSaldo->count() + $cuentasPendientes->count(),
            ...$this->parametrosLayout($layout),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->auditoria->ejecutar('HTB-FIN-001', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);

        return $pdf->stream("Reporte_Cuentas_Por_Cobrar_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function facturacionVentasPdf(string $fechaInicio, string $fechaFin, ?string $formatoPagina = null): Response
    {
        $layout = $this->layoutPdf($formatoPagina);
        $facturas = $this->facturacionVentas->porRango($fechaInicio, $fechaFin);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: collect($facturas->all()),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 10,
        );

        $pdf = Pdf::loadView('reports.financiero.reporte-facturacion-ventas', [
            'titulo' => 'Reporte de Facturación Fiscal y Ventas Totalizadas',
            'codigo' => 'HTB-FIN-002',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'paginas' => $paginas,
            'totalSubtotal' => $facturas->sum('subtotal'),
            'totalImpuestos' => $facturas->sum('iva_total'),
            'totalGeneral' => $facturas->sum('total'),
            'totalRegistros' => $facturas->count(),
            ...$this->parametrosLayout($layout),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->auditoria->ejecutar('HTB-FIN-002', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);

        return $pdf->stream("Reporte_Facturacion_Ventas_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function resumenEjecutivoPdf(string $fechaInicio, string $fechaFin, ?string $formatoPagina = null): Response
    {
        $layout = $this->layoutPdf($formatoPagina);
        $resumen = $this->resumenEjecutivo->paraRango($fechaInicio, $fechaFin);

        $pdf = Pdf::loadView('reports.financiero.reporte-resumen-ejecutivo', [
            'titulo' => 'Resumen Ejecutivo de Rendimiento y Tomador de Decisiones',
            'codigo' => 'HTB-FIN-003',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            ...$resumen,
            ...$this->parametrosLayout($layout),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->auditoria->ejecutar('HTB-FIN-003', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);

        return $pdf->stream("Reporte_Resumen_Ejecutivo_{$fechaInicio}_{$fechaFin}.pdf");
    }

    private function monto(mixed $valor): float
    {
        return is_numeric($valor) ? (float) $valor : 0.0;
    }

    private function layoutPdf(?string $formatoPagina = null): LayoutPdf
    {
        $formato = is_string($formatoPagina) ? FormatoPagina::tryFrom($formatoPagina) : null;
        [$tamano, $orientacion] = ($formato ?? FormatoPagina::Carta_Vertical)->resolver();

        return new LayoutPdf(tamano: $tamano, orientacion: $orientacion);
    }

    /**
     * @return array<string, mixed>
     */
    private function parametrosLayout(LayoutPdf $layout): array
    {
        return [
            'pageSize' => $layout->tamano->cssName(),
            'orientation' => $layout->orientacion->cssName(),
            'pageMarginTop' => $layout->margenSuperiorMm,
            'pageMarginRight' => $layout->margenLateralMm,
            'pageMarginBottom' => $layout->margenInferiorMm,
            'pageMarginLeft' => $layout->margenLateralMm,
            'pageContentHeight' => $layout->altoContenidoMm(),
            'pageContentWidth' => $layout->anchoContenidoMm(),
        ];
    }
}
