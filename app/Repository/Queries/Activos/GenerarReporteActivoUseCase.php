<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Actions\Activos\GenerarEtiquetasCodigosBarrasActivosAction;
use App\BusinessLogic\Activos\Data\ActivoFiltrosData;
use App\Exports\Activos\ActivosExport;
use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Support\HotelInfo;
use App\Support\ReportePaginador;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

readonly class GenerarReporteActivoUseCase
{
    public function __construct(
        private RegistrarAuditoriaReporte $auditoriaUseCase,
        private ObtenerReportesActivosVariosUseCase $reportesActivosVarios,
        private ObtenerFichasReportesUseCase $fichasReportes,
        private ObtenerActivosPorUbicacionUseCase $activosPorUbicacion,
        private ObtenerHistorialMovimientosUseCase $historialMovimientos,
        private ObtenerMantenimientosReportesUseCase $mantenimientosReportes,
        private ObtenerHojaHabitacionEspacioUseCase $hojaHabitacionEspacio,
        private ReportePaginador $reportePaginador,
    ) {}

    /** @param array<string, mixed> $params */
    public function execute(string $reportName, array $params = []): DomPdfInstance|BinaryFileResponse
    {
        return match ($reportName) {
            'inventarioGeneralPdf' => $this->inventarioGeneralPdf($params),
            'inventarioGeneralExcel' => $this->inventarioGeneralExcel(),
            'fichaActivoPdf' => $this->fichaActivoPdf($params),
            'fichaMantenimientoPdf' => $this->fichaMantenimientoPdf($params),
            'etiquetasPdf' => $this->etiquetasPdf($params),
            'porUbicacionPdf' => $this->porUbicacionPdf($params),
            'historialMovimientosPdf' => $this->historialMovimientosPdf($params),
            'enMantenimientoPdf' => $this->enMantenimientoPdf(),
            'garantiasProximasPdf' => $this->garantiasProximasPdf($params),
            'dadosDeBajaPdf' => $this->dadosDeBajaPdf(),
            'extraviadosPdf' => $this->extraviadosPdf(),
            'sinAsignacionPdf' => $this->sinAsignacionPdf(),
            'mantenimientosVencidosPdf' => $this->mantenimientosVencidosPdf(),
            'hojaHabitacionPdf' => $this->hojaHabitacionPdf($params),
            default => throw new InvalidArgumentException("Reporte '$reportName' no soportado."),
        };
    }

    /** @param array<string, mixed> $params */
    private function inventarioGeneralPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-001');
        $filtros = [
            'estado' => $params['estado'] ?? null,
            'producto_id' => $params['producto_id'] ?? null,
            'ubicacion_tipo' => $params['ubicacion_tipo'] ?? null,
        ];
        $activos = $this->reportesActivosVarios->inventarioGeneral($filtros);
        $paginas = $this->reportePaginador->chunkParaPdf($activos);

        return $this->vistaPdf('reports.activos.inventario-general', [
            'nombreReporte' => 'Inventario General de Activos',
            'codigoReporte' => 'HTB-ACT-001',
            'paginas' => $paginas,
        ]);
    }

    private function inventarioGeneralExcel(): BinaryFileResponse
    {
        $this->auditoria('HTB-ACT-001');

        return Excel::download(new ActivosExport, 'HTB-ACT-001-Inventario-General.xlsx');
    }

    /** @param array<string, mixed> $params */
    private function fichaActivoPdf(array $params): DomPdfInstance
    {
        /** @var Activo $activo */
        $activo = $params['activo'];
        $this->auditoria('HTB-ACT-002', $activo);

        return $this->vistaPdf('reports.activos.activo', [
            'nombreReporte' => 'Ficha de Activo',
            'codigoReporte' => 'HTB-ACT-002',
            'record' => $this->fichasReportes->fichaActivo($activo),
        ]);
    }

    /** @param array<string, mixed> $params */
    private function fichaMantenimientoPdf(array $params): DomPdfInstance
    {
        /** @var ActivoMantenimiento $mantenimiento */
        $mantenimiento = $params['mantenimiento'];
        $this->auditoria('HTB-ACT-003', $mantenimiento);

        return $this->vistaPdf('reports.activos.mantenimiento', [
            'nombreReporte' => 'Ficha de Mantenimiento',
            'codigoReporte' => 'HTB-ACT-003',
            'record' => $this->fichasReportes->fichaMantenimiento($mantenimiento),
        ]);
    }

    /** @param array<string, mixed> $params */
    private function etiquetasPdf(array $params): DomPdfInstance
    {
        $dto = ActivoFiltrosData::fromArray($params);

        return app(GenerarEtiquetasCodigosBarrasActivosAction::class)
            ->ejecutar($dto);
    }

    /** @param array<string, mixed> $params */
    private function porUbicacionPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-005');
        $tipoFiltro = $params['ubicacion_tipo'] ?? null;

        return $this->vistaPdf('reports.activos.por-ubicacion', [
            'nombreReporte' => 'Activos por Ubicación',
            'codigoReporte' => 'HTB-ACT-005',
            'ubicaciones' => $this->activosPorUbicacion->ejecutar(is_string($tipoFiltro) ? $tipoFiltro : null),
        ]);
    }

    /** @param array<string, mixed> $params */
    private function historialMovimientosPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-006');
        $activoId = $this->enteroDesdeParams($params['activo_id'] ?? null);
        $data = $this->historialMovimientos->ejecutar($activoId);

        return $this->vistaPdf('reports.activos.historial-movimientos', [
            'nombreReporte' => 'Historial de Movimientos',
            'codigoReporte' => 'HTB-ACT-006',
            'activo' => $data['activo'],
            'lineaTiempo' => $data['lineaTiempo'],
            'filtroActivo' => $activoId > 0 ? $data['activo'] : null,
        ]);
    }

    private function enMantenimientoPdf(): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-007');
        $activos = $this->mantenimientosReportes->enMantenimiento();
        $paginas = $this->reportePaginador->chunkParaPdf($activos);

        return $this->vistaPdf('reports.activos.en-mantenimiento', [
            'nombreReporte' => 'Activos en Mantenimiento',
            'codigoReporte' => 'HTB-ACT-007',
            'paginas' => $paginas,
            'totalRegistros' => $activos->count(),
        ]);
    }

    /** @param array<string, mixed> $params */
    private function garantiasProximasPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-008');
        $dias = $this->enteroDesdeParams($params['dias'] ?? null, 90);
        $activos = $this->reportesActivosVarios->garantiasProximas($dias);
        $paginas = $this->reportePaginador->chunkParaPdf($activos);

        return $this->vistaPdf('reports.activos.garantias-proximas', [
            'nombreReporte' => 'Garantías Próximas a Vencer',
            'codigoReporte' => 'HTB-ACT-008',
            'paginas' => $paginas,
            'totalRegistros' => $activos->count(),
            'dias' => $dias,
        ]);
    }

    private function dadosDeBajaPdf(): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-009');
        $bajas = $this->reportesActivosVarios->dadosDeBaja();
        $paginas = $this->reportePaginador->chunkParaPdf($bajas);

        return $this->vistaPdf('reports.activos.dados-de-baja', [
            'nombreReporte' => 'Activos Dados de Baja',
            'codigoReporte' => 'HTB-ACT-009',
            'paginas' => $paginas,
            'totalRegistros' => $bajas->count(),
            'totalValorResidual' => $bajas->sum('valor_residual'),
        ]);
    }

    private function extraviadosPdf(): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-010');
        $activos = $this->reportesActivosVarios->extraviados();
        $paginas = $this->reportePaginador->chunkParaPdf($activos);

        return $this->vistaPdf('reports.activos.extraviados', [
            'nombreReporte' => 'Activos Extraviados',
            'codigoReporte' => 'HTB-ACT-010',
            'paginas' => $paginas,
            'totalRegistros' => $activos->count(),
            'totalCosto' => $activos->sum('costo_adquisicion'),
        ]);
    }

    private function sinAsignacionPdf(): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-011');
        $activos = $this->reportesActivosVarios->sinAsignacion();
        $paginas = $this->reportePaginador->chunkParaPdf($activos);

        return $this->vistaPdf('reports.activos.sin-asignacion', [
            'nombreReporte' => 'Activos Sin Asignación',
            'codigoReporte' => 'HTB-ACT-011',
            'paginas' => $paginas,
            'totalRegistros' => $activos->count(),
        ]);
    }

    private function mantenimientosVencidosPdf(): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-012');
        $mantenimientos = $this->mantenimientosReportes->mantenimientosVencidos();
        $paginas = $this->reportePaginador->chunkParaPdf($mantenimientos);

        return $this->vistaPdf('reports.activos.mantenimientos-vencidos', [
            'nombreReporte' => 'Mantenimientos Vencidos',
            'codigoReporte' => 'HTB-ACT-012',
            'paginas' => $paginas,
            'totalRegistros' => $mantenimientos->count(),
        ]);
    }

    /** @param array<string, mixed> $params */
    private function hojaHabitacionPdf(array $params): DomPdfInstance
    {
        $this->auditoria('HTB-ACT-013');
        $tipo = is_string($params['tipo'] ?? null) ? (string) $params['tipo'] : '';
        $id = $this->enteroDesdeParams($params['id'] ?? null);
        $data = $this->hojaHabitacionEspacio->ejecutar($tipo, $id);

        return $this->vistaPdf('reports.activos.hoja-habitacion', [
            'nombreReporte' => 'Hoja de '.($tipo === 'habitacion' ? 'Habitación' : 'Espacio'),
            'codigoReporte' => 'HTB-ACT-013',
            'entidad' => $data['entidad'],
            'activos' => $data['activos'],
            'tipo' => $tipo,
        ]);
    }

    private function enteroDesdeParams(mixed $valor, int $predeterminado = 0): int
    {
        return match (true) {
            is_int($valor) => $valor,
            is_string($valor) => (int) $valor,
            default => $predeterminado,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function vistaPdf(string $vista, array $data): DomPdfInstance
    {
        return Pdf::loadView($vista, array_merge(HotelInfo::getBaseData(), $data));
    }

    private function auditoria(string $codigo, mixed $record = null): void
    {
        $this->auditoriaUseCase->ejecutar($codigo, [
            'usuario' => auth()->id(),
            'ip' => request()->ip(),
            'referencia_id' => (is_object($record) && method_exists($record, 'getKey')) ? $record->getKey() : null,
        ]);
    }
}
