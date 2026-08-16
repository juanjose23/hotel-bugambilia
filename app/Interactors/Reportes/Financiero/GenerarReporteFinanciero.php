<?php

declare(strict_types=1);

namespace App\Interactors\Reportes\Financiero;

use App\Repository\Queries\Reportes\CuentasCobrarQuery;
use App\Repository\Queries\Reportes\FacturacionVentasQuery;
use App\Repository\Queries\Reportes\ResumenEjecutivoQuery;
use App\Support\HotelInfo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final readonly class GenerarReporteFinanciero
{
    public function __construct(
        private readonly CuentasCobrarQuery $cuentasCobrar,
        private readonly FacturacionVentasQuery $facturacionVentas,
        private readonly ResumenEjecutivoQuery $resumenEjecutivo,
    ) {}

    /**
     * @return array<string, mixed>
     */
    private function datosHotel(): array
    {
        return HotelInfo::getBaseData();
    }

    public function cuentasCobrarPdf(string $fechaInicio, string $fechaFin): Response
    {
        $reservasConSaldo = $this->cuentasCobrar->reservasConSaldo($fechaInicio, $fechaFin);
        $cuentasPendientes = $this->cuentasCobrar->cuentasPendientes();

        $pdf = Pdf::loadView('pdf.financiero.reporte-cuentas-cobrar', [
            'titulo' => 'Reporte de Cuentas por Cobrar y Antigüedad de Saldos',
            'codigo' => 'HTB-FIN-001',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'reservas' => $reservasConSaldo,
            'cuentas' => $cuentasPendientes,
            'totalPendiente' => $this->monto($reservasConSaldo->sum('saldo')) + $this->monto($cuentasPendientes->sum('saldo')),
        ]);

        return $pdf->stream("Reporte_Cuentas_Por_Cobrar_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function facturacionVentasPdf(string $fechaInicio, string $fechaFin): Response
    {
        $facturas = $this->facturacionVentas->porRango($fechaInicio, $fechaFin);

        $pdf = Pdf::loadView('pdf.financiero.reporte-facturacion-ventas', [
            'titulo' => 'Reporte de Facturación Fiscal y Ventas Totalizadas',
            'codigo' => 'HTB-FIN-002',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'facturas' => $facturas,
            'totalSubtotal' => $facturas->sum('subtotal'),
            'totalImpuestos' => $facturas->sum('iva_total'),
            'totalGeneral' => $facturas->sum('total'),
        ]);

        return $pdf->stream("Reporte_Facturacion_Ventas_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function resumenEjecutivoPdf(string $fechaInicio, string $fechaFin): Response
    {
        $resumen = $this->resumenEjecutivo->paraRango($fechaInicio, $fechaFin);

        $pdf = Pdf::loadView('pdf.financiero.reporte-resumen-ejecutivo', [
            'titulo' => 'Resumen Ejecutivo de Rendimiento y Tomador de Decisiones',
            'codigo' => 'HTB-FIN-003',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            ...$resumen,
        ]);

        return $pdf->stream("Reporte_Resumen_Ejecutivo_{$fechaInicio}_{$fechaFin}.pdf");
    }

    private function monto(mixed $valor): float
    {
        return is_numeric($valor) ? (float) $valor : 0.0;
    }
}
