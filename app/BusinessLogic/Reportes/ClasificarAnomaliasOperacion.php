<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reportes;

use App\Enums\Reportes\SeveridadAnomalia;

/**
 * Clasifica las métricas operativas del hotel en anomalías con severidad,
 * usando umbrales configurables por indicador.
 *
 * Es una regla de negocio pura: no conoce Eloquent, Filament ni HTTP.
 */
final readonly class ClasificarAnomaliasOperacion
{
    /**
     * @param  array<string, int>  $umbrales  Sobrescriben los umbrales por defecto.
     */
    public function __construct(
        private array $umbrales = [],
    ) {}

    /** @return array<string, int> */
    public function umbrales(): array
    {
        return array_merge([
            'limpiezas_pendientes' => 5,
            'tiempo_promedio_minutos' => 90,
            'habitaciones_bloqueadas' => 2,
            'mantenimientos_vencidos' => 1,
            'mantenimientos_proximos' => 3,
            'activos_en_mantenimiento' => 3,
            'garantias_proximas' => 5,
        ], $this->umbrales);
    }

    /**
     * Resuelve las anomalías activas del período según los umbrales.
     *
     * @param  array<string, float|int>  $metricas
     * @return array<int, array{clave: string, titulo: string, descripcion: string, cantidad: float, umbral: int, severidad: SeveridadAnomalia}>
     */
    public function clasificar(array $metricas): array
    {
        $umbrales = $this->umbrales();

        $reglas = [
            'limpiezas_pendientes' => [
                'titulo' => 'Limpiezas pendientes',
                'descripcion' => 'Tareas de limpieza sin ejecutar o en progreso.',
            ],
            'tiempo_promedio_minutos' => [
                'titulo' => 'Tiempo promedio de limpieza',
                'descripcion' => 'Duración media de las limpiezas del período en minutos.',
            ],
            'habitaciones_bloqueadas' => [
                'titulo' => 'Habitaciones bloqueadas',
                'descripcion' => 'Habitaciones fuera de operación por mantenimiento, limpieza, sucio o inactivas.',
            ],
            'mantenimientos_vencidos' => [
                'titulo' => 'Mantenimientos vencidos',
                'descripcion' => 'Mantenimientos programados o en proceso cuya fecha ya venció.',
            ],
            'mantenimientos_proximos' => [
                'titulo' => 'Mantenimientos próximos',
                'descripcion' => 'Mantenimientos programados dentro de los próximos 7 días.',
            ],
            'activos_en_mantenimiento' => [
                'titulo' => 'Activos en mantenimiento',
                'descripcion' => 'Activos fijos actualmente en estado de mantenimiento.',
            ],
            'garantias_proximas' => [
                'titulo' => 'Garantías por vencer',
                'descripcion' => 'Activos cuya garantía vence dentro de los próximos 90 días.',
            ],
        ];

        $anomalias = [];
        foreach ($reglas as $clave => $meta) {
            $cantidad = is_numeric($metricas[$clave] ?? null) ? (float) $metricas[$clave] : 0.0;
            $umbral = $umbrales[$clave] ?? 0;

            if ($cantidad <= $umbral) {
                continue;
            }

            $anomalias[] = [
                'clave' => $clave,
                'titulo' => $meta['titulo'],
                'descripcion' => $meta['descripcion'],
                'cantidad' => $cantidad,
                'umbral' => $umbral,
                'severidad' => $this->severidad($cantidad, $umbral),
            ];
        }

        return $anomalias;
    }

    private function severidad(float $cantidad, int $umbral): SeveridadAnomalia
    {
        $exceso = $cantidad / max(1, $umbral);

        return match (true) {
            $exceso >= 3 => SeveridadAnomalia::Critica,
            $exceso >= 2 => SeveridadAnomalia::Alta,
            $exceso >= 1.5 => SeveridadAnomalia::Media,
            default => SeveridadAnomalia::Baja,
        };
    }
}
