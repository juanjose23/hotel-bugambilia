<?php

declare(strict_types=1);

namespace App\Interactors\Reportes\Reservas;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Queries\Reportes\HuespedesQuery;
use App\Repository\Queries\Reportes\RendimientoHabitacionesQuery;
use App\Repository\Queries\Reportes\ReservasOcupacionQuery;
use App\Support\HotelInfo;
use App\Support\Pdf\FormatoPagina;
use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\TiposReporte;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use InvalidArgumentException;

final readonly class GenerarReporteReserva
{
    public function __construct(
        private ReservasOcupacionQuery $reservasOcupacion,
        private HuespedesQuery $huespedes,
        private RendimientoHabitacionesQuery $rendimientoHabitaciones,
        private RegistrarAuditoriaReporte $auditoria,
    ) {}

    /** @param array<string, mixed> $params */
    public function ejecutar(string $reportName, array $params = []): Response
    {
        $fechaInicio = is_string($params['fecha_inicio'] ?? null) ? $params['fecha_inicio'] : now()->startOfMonth()->format('Y-m-d');
        $fechaFin = is_string($params['fecha_fin'] ?? null) ? $params['fecha_fin'] : now()->format('Y-m-d');
        $formatoPagina = is_string($params['formato_pagina'] ?? null) ? $params['formato_pagina'] : null;

        $estado = is_string($params['estado'] ?? null) ? $params['estado'] : null;
        $tipoPago = is_string($params['tipo_pago'] ?? null) ? $params['tipo_pago'] : null;

        return match ($reportName) {
            'ocupacionPdf' => $this->ocupacionPdf($fechaInicio, $fechaFin, $estado, $formatoPagina),
            'ventasIngresosPdf' => $this->ventasIngresosPdf($fechaInicio, $fechaFin, $tipoPago, $formatoPagina),
            'reservasEstadoPdf' => $this->reservasEstadoPdf($fechaInicio, $fechaFin, $estado, $formatoPagina),
            'huespedesPdf' => $this->huespedesPdf($formatoPagina),
            'rendimientoHabitacionesPdf' => $this->rendimientoHabitacionesPdf($formatoPagina),
            default => throw new InvalidArgumentException("Reporte Reservas '$reportName' no soportado."),
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

    public function ocupacionPdf(string $fechaInicio, string $fechaFin, ?string $estado, ?string $formatoPagina = null): Response
    {
        $layout = $this->layoutPdf($formatoPagina);
        $reservas = $this->reservasOcupacion->paraOcupacion($fechaInicio, $fechaFin, $estado);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: collect($reservas->all()),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 10,
        );

        $pdf = Pdf::loadView('reports.reservas.reporte-ocupacion', [
            'titulo' => 'Reporte de Ocupación y Estadías',
            'codigo' => 'HTB-RES-001',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'paginas' => $paginas,
            'totalNoches' => $reservas->sum('noches'),
            'totalIngresos' => $reservas->sum('total'),
            'totalRegistros' => $reservas->count(),
            ...$this->parametrosLayout($layout),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->auditoria->ejecutar('HTB-RES-001', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'estado' => $estado,
        ]);

        return $pdf->stream("Reporte_Ocupacion_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function ventasIngresosPdf(string $fechaInicio, string $fechaFin, ?string $tipoPago, ?string $formatoPagina = null): Response
    {
        $layout = $this->layoutPdf($formatoPagina);
        $reservas = $this->reservasOcupacion->paraVentas($fechaInicio, $fechaFin, $tipoPago);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: collect($reservas->all()),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 10,
        );

        $pdf = Pdf::loadView('reports.reservas.reporte-ventas', [
            'titulo' => 'Ventas e Ingresos por Canal de Pago',
            'codigo' => 'HTB-RES-002',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'paginas' => $paginas,
            'totalVentas' => $reservas->sum('total'),
            'totalPagado' => $reservas->sum('total_pagado'),
            'totalSaldo' => $reservas->sum('saldo'),
            'totalRegistros' => $reservas->count(),
            ...$this->parametrosLayout($layout),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->auditoria->ejecutar('HTB-RES-002', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'tipo_pago' => $tipoPago,
        ]);

        return $pdf->stream("Reporte_Ventas_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function reservasEstadoPdf(string $fechaInicio, string $fechaFin, ?string $estado, ?string $formatoPagina = null): Response
    {
        $layout = $this->layoutPdf($formatoPagina);
        $reservas = $this->reservasOcupacion->paraEstados($fechaInicio, $fechaFin, $estado);

        $paginas = (new ReportePaginador($layout))->paginar(
            items: collect($reservas->all()),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 10,
        );

        $pdf = Pdf::loadView('reports.reservas.reporte-estados', [
            'titulo' => 'Reservas Agrupadas por Estado',
            'codigo' => 'HTB-RES-003',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'paginas' => $paginas,
            'totalRegistros' => $reservas->count(),
            ...$this->parametrosLayout($layout),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->auditoria->ejecutar('HTB-RES-003', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'estado' => $estado,
        ]);

        return $pdf->stream("Reporte_Reservas_Estado_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function huespedesPdf(?string $formatoPagina = null): Response
    {
        $layout = $this->layoutPdf($formatoPagina);
        $clientes = $this->huespedes->todosConReservas();

        $paginas = (new ReportePaginador($layout))->paginar(
            items: collect($clientes->all()),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 10,
        );

        $pdf = Pdf::loadView('reports.reservas.reporte-huespedes', [
            'titulo' => 'Listado y Fichas de Huéspedes',
            'codigo' => 'HTB-RES-004',
            'datosHotel' => $this->datosHotel(),
            'paginas' => $paginas,
            'totalRegistros' => $clientes->count(),
            ...$this->parametrosLayout($layout),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->auditoria->ejecutar('HTB-RES-004');

        return $pdf->stream('Reporte_Huespedes.pdf');
    }

    public function rendimientoHabitacionesPdf(?string $formatoPagina = null): Response
    {
        $layout = $this->layoutPdf($formatoPagina);
        $categorias = $this->rendimientoHabitaciones->categorias();

        $paginas = (new ReportePaginador($layout))->paginar(
            items: collect($categorias),
            tipo: TiposReporte::TABLA_SIMPLE,
            altoExtraPrimeraPaginaMm: 10,
        );

        $pdf = Pdf::loadView('reports.reservas.reporte-rendimiento-habitaciones', [
            'titulo' => 'Rendimiento por Categoría de Habitación',
            'codigo' => 'HTB-RES-005',
            'datosHotel' => $this->datosHotel(),
            'paginas' => $paginas,
            'totalRegistros' => count($categorias),
            ...$this->parametrosLayout($layout),
        ])->setPaper(
            $layout->tamano->dompdfName(),
            $layout->orientacion->dompdfName(),
        );

        $this->auditoria->ejecutar('HTB-RES-005');

        return $pdf->stream('Reporte_Rendimiento_Habitaciones.pdf');
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
