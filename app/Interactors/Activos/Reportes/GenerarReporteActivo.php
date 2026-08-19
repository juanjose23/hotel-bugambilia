<?php

declare(strict_types=1);

namespace App\Interactors\Activos\Reportes;

use App\Actions\Activos\GenerarEtiquetasCodigosBarrasActivosAction;
use App\Actions\Activos\Reportes\GenerarFichasActivosAction;
use App\Actions\Activos\Reportes\GenerarReporteActivosUbicacionAction;
use App\Actions\Activos\Reportes\GenerarReporteHistorialYMantenimientosAction;
use App\Actions\Activos\Reportes\GenerarReporteInventarioGeneralActivosAction;
use App\BusinessLogic\Activos\Data\ActivoFiltrosData;
use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class GenerarReporteActivo
{
    public function __construct(
        private RegistrarAuditoriaReporte $auditoriaUseCase,
        private GenerarReporteInventarioGeneralActivosAction $inventarioGeneralAction,
        private GenerarFichasActivosAction $fichasAction,
        private GenerarReporteActivosUbicacionAction $ubicacionAction,
        private GenerarReporteHistorialYMantenimientosAction $historialAction,
        private GenerarEtiquetasCodigosBarrasActivosAction $etiquetasAction,
    ) {}

    /** @param array<string, mixed> $params */
    public function ejecutar(string $reportName, array $params = []): DomPdfInstance|StreamedResponse
    {
        return match ($reportName) {
            'inventarioGeneralPdf' => $this->ejecutarConAuditoria('HTB-ACT-001', fn () => $this->inventarioGeneralAction->pdf($params)),
            'inventarioGeneralExcel' => $this->ejecutarConAuditoria('HTB-ACT-001', fn () => $this->inventarioGeneralAction->excel()),
            'fichaActivoPdf' => $this->ejecutarConAuditoria('HTB-ACT-002', fn () => $this->fichasAction->fichaActivo($params), $params['activo'] ?? null),
            'fichaMantenimientoPdf' => $this->ejecutarConAuditoria('HTB-ACT-003', fn () => $this->fichasAction->fichaMantenimiento($params), $params['mantenimiento'] ?? null),
            'etiquetasPdf' => $this->ejecutarConAuditoria('HTB-ACT-004', fn () => $this->etiquetasAction->ejecutar(ActivoFiltrosData::fromArray($params))),
            'porUbicacionPdf' => $this->ejecutarConAuditoria('HTB-ACT-005', fn () => $this->ubicacionAction->porUbicacion($params)),
            'historialMovimientosPdf' => $this->ejecutarConAuditoria('HTB-ACT-006', fn () => $this->historialAction->historial($params)),
            'enMantenimientoPdf' => $this->ejecutarConAuditoria('HTB-ACT-007', fn () => $this->historialAction->enMantenimiento($params)),
            'garantiasProximasPdf' => $this->ejecutarConAuditoria('HTB-ACT-008', fn () => $this->historialAction->garantiasProximas($params)),
            'dadosDeBajaPdf' => $this->ejecutarConAuditoria('HTB-ACT-009', fn () => $this->historialAction->dadosDeBaja($params)),
            'extraviadosPdf' => $this->ejecutarConAuditoria('HTB-ACT-010', fn () => $this->historialAction->extraviados($params)),
            'sinAsignacionPdf' => $this->ejecutarConAuditoria('HTB-ACT-011', fn () => $this->historialAction->sinAsignacion($params)),
            'mantenimientosVencidosPdf' => $this->ejecutarConAuditoria('HTB-ACT-012', fn () => $this->historialAction->mantenimientosVencidos($params)),
            'hojaHabitacionPdf' => $this->ejecutarConAuditoria('HTB-ACT-013', fn () => $this->ubicacionAction->hojaHabitacion($params)),
            default => throw new InvalidArgumentException("Reporte '$reportName' no soportado."),
        };
    }

    /**
     * Alias for backward compatibility
     *
     * @param  array<string, mixed>  $params
     */
    public function execute(string $reportName, array $params = []): DomPdfInstance|StreamedResponse
    {
        return $this->ejecutar($reportName, $params);
    }

    /**
     * @template T of DomPdfInstance|StreamedResponse
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function ejecutarConAuditoria(string $codigo, callable $callback, mixed $record = null): mixed
    {
        $this->auditoriaUseCase->ejecutar($codigo, [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
            'referencia_id' => (is_object($record) && method_exists($record, 'getKey')) ? $record->getKey() : null,
        ]);

        return $callback();
    }
}
