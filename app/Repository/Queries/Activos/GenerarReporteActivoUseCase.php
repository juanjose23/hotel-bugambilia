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
        switch ($reportName) {
            case 'inventarioGeneralPdf':
                $this->auditoria('HTB-ACT-001');
                $filtros = [
                    'estado' => $params['estado'] ?? null,
                    'producto_id' => $params['producto_id'] ?? null,
                    'ubicacion_tipo' => $params['ubicacion_tipo'] ?? null,
                ];
                $activos = $this->reportesActivosVarios->inventarioGeneral($filtros);
                $paginas = $this->reportePaginador->chunkParaPdf($activos);

                return Pdf::loadView('reports.activos.inventario-general', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Inventario General de Activos',
                    'codigoReporte' => 'HTB-ACT-001',
                    'paginas' => $paginas,
                ]));

            case 'inventarioGeneralExcel':
                $this->auditoria('HTB-ACT-001');
                $filtros = [
                    'estado' => $params['estado'] ?? null,
                    'producto_id' => $params['producto_id'] ?? null,
                    'ubicacion_tipo' => $params['ubicacion_tipo'] ?? null,
                ];

                return Excel::download(new ActivosExport, 'HTB-ACT-001-Inventario-General.xlsx');
            case 'fichaActivoPdf':
                /** @var Activo $activo */
                $activo = $params['activo'];
                $this->auditoria('HTB-ACT-002', $activo);

                return Pdf::loadView('reports.activos.activo', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Ficha de Activo',
                    'codigoReporte' => 'HTB-ACT-002',
                    'record' => $this->fichasReportes->fichaActivo($activo),
                ]));

            case 'fichaMantenimientoPdf':
                /** @var ActivoMantenimiento $mantenimiento */
                $mantenimiento = $params['mantenimiento'];
                $this->auditoria('HTB-ACT-003', $mantenimiento);

                return Pdf::loadView('reports.activos.mantenimiento', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Ficha de Mantenimiento',
                    'codigoReporte' => 'HTB-ACT-003',
                    'record' => $this->fichasReportes->fichaMantenimiento($mantenimiento),
                ]));

            case 'etiquetasPdf':
                $dto = ActivoFiltrosData::fromArray($params);

                return app(GenerarEtiquetasCodigosBarrasActivosAction::class)
                    ->ejecutar($dto);

            case 'porUbicacionPdf':
                $this->auditoria('HTB-ACT-005');
                $tipoFiltro = $params['ubicacion_tipo'] ?? null;

                return Pdf::loadView('reports.activos.por-ubicacion', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Activos por Ubicación',
                    'codigoReporte' => 'HTB-ACT-005',
                    'ubicaciones' => $this->activosPorUbicacion->ejecutar(is_string($tipoFiltro) ? $tipoFiltro : null),
                ]));

            case 'historialMovimientosPdf':
                $this->auditoria('HTB-ACT-006');
                $activoIdRaw = $params['activo_id'] ?? null;
                $activoId = match (true) {
                    is_int($activoIdRaw) => $activoIdRaw,
                    is_string($activoIdRaw) => (int) $activoIdRaw,
                    default => 0,
                };
                $data = $this->historialMovimientos->ejecutar($activoId);

                return Pdf::loadView('reports.activos.historial-movimientos', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Historial de Movimientos',
                    'codigoReporte' => 'HTB-ACT-006',
                    'activo' => $data['activo'],
                    'lineaTiempo' => $data['lineaTiempo'],
                    'filtroActivo' => $activoId > 0 ? $data['activo'] : null,
                ]));

            case 'enMantenimientoPdf':
                $this->auditoria('HTB-ACT-007');
                $activos = $this->mantenimientosReportes->enMantenimiento();
                $paginas = $this->reportePaginador->chunkParaPdf($activos);

                return Pdf::loadView('reports.activos.en-mantenimiento', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Activos en Mantenimiento',
                    'codigoReporte' => 'HTB-ACT-007',
                    'paginas' => $paginas,
                    'totalRegistros' => $activos->count(),
                ]));

            case 'garantiasProximasPdf':
                $this->auditoria('HTB-ACT-008');
                $diasRaw = $params['dias'] ?? null;
                $dias = match (true) {
                    is_int($diasRaw) => $diasRaw,
                    is_string($diasRaw) => (int) $diasRaw,
                    default => 90,
                };
                $activos = $this->reportesActivosVarios->garantiasProximas($dias);
                $paginas = $this->reportePaginador->chunkParaPdf($activos);

                return Pdf::loadView('reports.activos.garantias-proximas', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Garantías Próximas a Vencer',
                    'codigoReporte' => 'HTB-ACT-008',
                    'paginas' => $paginas,
                    'totalRegistros' => $activos->count(),
                    'dias' => $dias,
                ]));

            case 'dadosDeBajaPdf':
                $this->auditoria('HTB-ACT-009');
                $bajas = $this->reportesActivosVarios->dadosDeBaja();
                $paginas = $this->reportePaginador->chunkParaPdf($bajas);

                return Pdf::loadView('reports.activos.dados-de-baja', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Activos Dados de Baja',
                    'codigoReporte' => 'HTB-ACT-009',
                    'paginas' => $paginas,
                    'totalRegistros' => $bajas->count(),
                    'totalValorResidual' => $bajas->sum('valor_residual'),
                ]));

            case 'extraviadosPdf':
                $this->auditoria('HTB-ACT-010');
                $activos = $this->reportesActivosVarios->extraviados();
                $paginas = $this->reportePaginador->chunkParaPdf($activos);

                return Pdf::loadView('reports.activos.extraviados', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Activos Extraviados',
                    'codigoReporte' => 'HTB-ACT-010',
                    'paginas' => $paginas,
                    'totalRegistros' => $activos->count(),
                    'totalCosto' => $activos->sum('costo_adquisicion'),
                ]));

            case 'sinAsignacionPdf':
                $this->auditoria('HTB-ACT-011');
                $activos = $this->reportesActivosVarios->sinAsignacion();
                $paginas = $this->reportePaginador->chunkParaPdf($activos);

                return Pdf::loadView('reports.activos.sin-asignacion', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Activos Sin Asignación',
                    'codigoReporte' => 'HTB-ACT-011',
                    'paginas' => $paginas,
                    'totalRegistros' => $activos->count(),
                ]));

            case 'mantenimientosVencidosPdf':
                $this->auditoria('HTB-ACT-012');
                $mantenimientos = $this->mantenimientosReportes->mantenimientosVencidos();
                $paginas = $this->reportePaginador->chunkParaPdf($mantenimientos);

                return Pdf::loadView('reports.activos.mantenimientos-vencidos', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Mantenimientos Vencidos',
                    'codigoReporte' => 'HTB-ACT-012',
                    'paginas' => $paginas,
                    'totalRegistros' => $mantenimientos->count(),
                ]));

            case 'hojaHabitacionPdf':
                $this->auditoria('HTB-ACT-013');
                $tipo = is_string($params['tipo'] ?? null) ? (string) $params['tipo'] : '';
                $idRaw = $params['id'] ?? null;
                $id = match (true) {
                    is_int($idRaw) => $idRaw,
                    is_string($idRaw) => (int) $idRaw,
                    default => 0,
                };
                $data = $this->hojaHabitacionEspacio->ejecutar($tipo, $id);

                return Pdf::loadView('reports.activos.hoja-habitacion', array_merge(HotelInfo::getBaseData(), [
                    'nombreReporte' => 'Hoja de '.($tipo === 'habitacion' ? 'Habitación' : 'Espacio'),
                    'codigoReporte' => 'HTB-ACT-013',
                    'entidad' => $data['entidad'],
                    'activos' => $data['activos'],
                    'tipo' => $tipo,
                ]));

            default:
                throw new \InvalidArgumentException("Reporte '$reportName' no soportado.");
        }
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
