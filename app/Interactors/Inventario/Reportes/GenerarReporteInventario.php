<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Reportes;

use App\Actions\Inventario\Reportes\GenerarReporteAlertasInventarioAction;
use App\Actions\Inventario\Reportes\GenerarReporteCostoVentasInventarioAction;
use App\Actions\Inventario\Reportes\GenerarReporteMermasYAjustesAction;
use App\Actions\Inventario\Reportes\GenerarReporteMovimientosInventarioAction;
use App\Actions\Inventario\Reportes\GenerarReporteStockInventarioAction;
use App\Actions\Inventario\Reportes\GenerarReporteTrazabilidadInventarioAction;
use App\Actions\Inventario\Reportes\GenerarReporteValorizacionInventarioAction;
use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class GenerarReporteInventario
{
    public function __construct(
        private RegistrarAuditoriaReporte $auditoria,
        private GenerarReporteStockInventarioAction $stockAction,
        private GenerarReporteAlertasInventarioAction $alertasAction,
        private GenerarReporteValorizacionInventarioAction $valorizacionAction,
        private GenerarReporteMermasYAjustesAction $mermasAction,
        private GenerarReporteMovimientosInventarioAction $movimientosAction,
        private GenerarReporteTrazabilidadInventarioAction $trazabilidadAction,
        private GenerarReporteCostoVentasInventarioAction $costoVentasAction,
    ) {}

    /** @param  array<string, mixed>  $params */
    public function ejecutar(string $reportName, array $params = []): DomPdfInstance
    {
        return match ($reportName) {
            'stockPorProductoPdf' => $this->ejecutarConAuditoria('HTB-INV-001', fn () => $this->stockAction->stockPdf($params)),
            'movimientosPdf' => $this->ejecutarConAuditoria('HTB-INV-002', fn () => $this->movimientosAction->movimientosPdf($params)),
            'cuarentenaPdf' => $this->ejecutarConAuditoria('HTB-INV-004', fn () => $this->alertasAction->cuarentenaPdf($params)),
            'proximosVencerPdf' => $this->ejecutarConAuditoria('HTB-INV-005', fn () => $this->alertasAction->proximosVencerPdf($params)),
            'mermasPdf' => $this->ejecutarConAuditoria('HTB-INV-006', fn () => $this->mermasAction->mermasPdf($params)),
            'valorizacionPdf' => $this->ejecutarConAuditoria('HTB-INV-007', fn () => $this->valorizacionAction->pdf($params)),
            'trazabilidadLotePdf' => $this->ejecutarConAuditoria('HTB-INV-011', fn () => $this->trazabilidadAction->pdf($params)),
            'vencidosPdf' => $this->ejecutarConAuditoria('HTB-INV-012', fn () => $this->alertasAction->vencidosPdf($params)),
            'stockMinimoPdf' => $this->ejecutarConAuditoria('HTB-INV-009', fn () => $this->stockAction->stockMinimoPdf($params)),
            'ajustesPdf' => $this->ejecutarConAuditoria('HTB-INV-010', fn () => $this->mermasAction->ajustesPdf($params)),
            'costoVentasPdf' => $this->ejecutarConAuditoria('HTB-INV-013', fn () => $this->costoVentasAction->pdf($params)),
            'rotacionPdf' => $this->ejecutarConAuditoria('HTB-INV-008', fn () => $this->movimientosAction->rotacionPdf($params)),
            default => throw new InvalidArgumentException("Reporte '{$reportName}' no soportado."),
        };
    }

    /**
     * Alias for backward compatibility
     *
     * @param  array<string, mixed>  $params
     */
    public function execute(string $reportName, array $params = []): DomPdfInstance
    {
        return $this->ejecutar($reportName, $params);
    }

    /**
     * Alias for backward compatibility
     *
     * @param  array<string, mixed>  $params
     */
    public function executeExcel(string $reportName, array $params = []): StreamedResponse
    {
        return $this->descargarExcel($reportName, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function descargarExcel(string $reportName, array $params = []): StreamedResponse
    {
        return match ($reportName) {
            'stockPorProductoExcel' => $this->stockPorProductoExcel($params),
            'movimientosExcel' => $this->movimientosExcel($params),
            'cuarentenaExcel' => $this->cuarentenaExcel($params),
            'proximosVencerExcel' => $this->proximosVencerExcel($params),
            'mermasExcel' => $this->mermasExcel($params),
            'valorizacionExcel' => $this->valorizacionExcel($params),
            'rotacionExcel' => $this->rotacionExcel($params),
            'vencidosExcel' => $this->vencidosExcel($params),
            'stockMinimoExcel' => $this->stockMinimoExcel($params),
            'ajustesExcel' => $this->ajustesExcel($params),
            'costoVentasExcel' => $this->costoVentasExcel($params),
            default => throw new InvalidArgumentException("Reporte Excel '{$reportName}' no soportado."),
        };
    }

    /** @param array<string, mixed> $params */
    public function stockPorProductoExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-001', fn () => $this->stockAction->stockExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function movimientosExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-002', fn () => $this->movimientosAction->movimientosExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function cuarentenaExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-004', fn () => $this->alertasAction->cuarentenaExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function proximosVencerExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-005', fn () => $this->alertasAction->proximosVencerExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function mermasExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-006', fn () => $this->mermasAction->mermasExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function valorizacionExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-007', fn () => $this->valorizacionAction->excel($params));
    }

    /** @param array<string, mixed> $params */
    public function rotacionExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-008', fn () => $this->movimientosAction->rotacionExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function vencidosExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-012', fn () => $this->alertasAction->vencidosExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function stockMinimoExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-009', fn () => $this->stockAction->stockMinimoExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function ajustesExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-010', fn () => $this->mermasAction->ajustesExcel($params));
    }

    /** @param array<string, mixed> $params */
    public function costoVentasExcel(array $params = []): StreamedResponse
    {
        return $this->ejecutarConAuditoria('HTB-INV-013', fn () => $this->costoVentasAction->excel($params));
    }

    /**
     * @template T of DomPdfInstance|StreamedResponse
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function ejecutarConAuditoria(string $codigo, callable $callback): mixed
    {
        $this->auditoria->ejecutar($codigo, [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
        ]);

        return $callback();
    }
}
