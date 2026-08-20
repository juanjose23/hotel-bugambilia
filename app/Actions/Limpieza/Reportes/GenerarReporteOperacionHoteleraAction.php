<?php

declare(strict_types=1);

namespace App\Actions\Limpieza\Reportes;

use App\Repository\Queries\Limpieza\Reportes\ObtenerReporteOperacionHoteleraQuery;
use App\Support\HotelInfo;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;

final readonly class GenerarReporteOperacionHoteleraAction
{
    public function __construct(
        private ObtenerReporteOperacionHoteleraQuery $query,
    ) {}

    /** @param array<string, mixed> $params */
    public function pdf(array $params = []): DomPdfInstance
    {
        $reporte = is_string($params['reporte'] ?? null) ? $params['reporte'] : 'operacion_hotelera';
        $datos = $this->query->ejecutar($params);
        $pageSize = is_string($params['pageSize'] ?? null) ? $params['pageSize'] : 'letter';
        $orientation = is_string($params['orientation'] ?? null) ? $params['orientation'] : 'portrait';
        $config = $this->configuracionReporte($reporte);

        return Pdf::loadView('reports.limpieza.operacion-hotelera', array_merge(HotelInfo::getBaseData(), $datos, [
            'nombreReporte' => $config['titulo'],
            'codigoReporte' => $config['codigo'],
            'secciones' => $config['secciones'],
            'pageSize' => $pageSize,
            'orientation' => $orientation,
            'fechaInicio' => $this->filtros($datos)['fecha_desde'] ?? null,
            'fechaFin' => $this->filtros($datos)['fecha_hasta'] ?? null,
            'totalRegistros' => $this->totalRegistros($reporte, $datos),
        ]))->setPaper($pageSize, $orientation);
    }

    /**
     * @return array{codigo: string, titulo: string, secciones: list<string>}
     */
    private function configuracionReporte(string $reporte): array
    {
        return match ($reporte) {
            'tiempo_promedio_limpieza' => [
                'codigo' => 'HTB-LIM-002',
                'titulo' => 'Tiempo Promedio de Limpieza',
                'secciones' => ['tiempos'],
            ],
            'habitaciones_pendientes_bloqueadas' => [
                'codigo' => 'HTB-LIM-003',
                'titulo' => 'Habitaciones Pendientes y Bloqueadas',
                'secciones' => ['pendientes'],
            ],
            'consumo_amenities_habitacion' => [
                'codigo' => 'HTB-LIM-004',
                'titulo' => 'Consumo de Amenities por Habitación',
                'secciones' => ['amenities'],
            ],
            'productividad_colaborador_turno' => [
                'codigo' => 'HTB-LIM-005',
                'titulo' => 'Productividad por Colaborador y Turno',
                'secciones' => ['productividad'],
            ],
            default => [
                'codigo' => 'HTB-LIM-001',
                'titulo' => 'Reporte de Limpieza y Operación Hotelera',
                'secciones' => ['tiempos', 'pendientes', 'amenities', 'productividad'],
            ],
        };
    }

    /** @param array<string, mixed> $datos */
    private function totalRegistros(string $reporte, array $datos): int
    {
        return match ($reporte) {
            'tiempo_promedio_limpieza' => count($this->lista($datos['tiempos_por_habitacion'] ?? [])),
            'habitaciones_pendientes_bloqueadas' => count($this->lista($datos['pendientes_bloqueadas'] ?? [])),
            'consumo_amenities_habitacion' => count($this->lista($datos['amenities_por_habitacion'] ?? [])),
            'productividad_colaborador_turno' => count($this->lista($datos['productividad'] ?? [])),
            default => $this->entero($this->resumen($datos)['ejecuciones'] ?? 0),
        };
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function filtros(array $datos): array
    {
        return is_array($datos['filtros'] ?? null) ? $datos['filtros'] : [];
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function resumen(array $datos): array
    {
        return is_array($datos['resumen'] ?? null) ? $datos['resumen'] : [];
    }

    /** @return array<int|string, mixed> */
    private function lista(mixed $valor): array
    {
        return is_array($valor) ? $valor : [];
    }

    private function entero(mixed $valor): int
    {
        return is_numeric($valor) ? (int) $valor : 0;
    }
}
