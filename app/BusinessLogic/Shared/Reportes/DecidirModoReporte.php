<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared\Reportes;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Decide si un reporte debe generarse en tiempo real (directo) o en segundo plano (Job),
 * basándose exclusivamente en el rango de fechas solicitado.
 *
 * Regla:
 *   - Rango ≤ UMBRAL_DIAS → generar directo al navegador.
 *   - Rango >  UMBRAL_DIAS → despachar GenerarReporteJob.
 *   - Sin fechas (reportes de catálogo/resumen) → siempre directo.
 *
 * Lógica pura, sin queries a la base de datos.
 */
final readonly class DecidirModoReporte
{
    /** Días de rango máximo para generar el reporte en tiempo real. */
    public const int UMBRAL_DIAS = 31;

    /**
     * Determina si el reporte debe ir a la cola de segundo plano.
     *
     * @param  array<string, mixed>  $params  Parámetros del reporte (fecha_inicio, fecha_fin, etc.)
     */
    public function debeIrABackground(array $params): bool
    {
        if (! empty($params['segundo_plano'])) {
            return true;
        }

        $inicio = $this->resolverFecha($params, ['fecha_inicio', 'fecha_desde']);
        $fin = $this->resolverFecha($params, ['fecha_fin', 'fecha_hasta']);

        // Sin rango de fechas = reporte de catálogo/snapshot → siempre directo
        if ($inicio === null || $fin === null) {
            return false;
        }

        // Asegurar que inicio <= fin para el cálculo
        if ($inicio->greaterThan($fin)) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        $configUmbral = config('reportes.umbral_dias_background');
        $umbral = is_int($configUmbral) ? $configUmbral : self::UMBRAL_DIAS;

        return $inicio->diffInDays($fin) > $umbral;
    }

    /**
     * Retorna los días del rango o null si no hay fechas definidas.
     *
     * @param  array<string, mixed>  $params
     */
    public function diasDeRango(array $params): ?int
    {
        $inicio = $this->resolverFecha($params, ['fecha_inicio', 'fecha_desde']);
        $fin = $this->resolverFecha($params, ['fecha_fin', 'fecha_hasta']);

        if ($inicio === null || $fin === null) {
            return null;
        }

        return (int) $inicio->diffInDays($fin);
    }

    /**
     * Busca el primer valor de fecha válido en la lista de claves dada.
     *
     * @param  array<string, mixed>  $params
     * @param  list<string>  $claves
     */
    private function resolverFecha(array $params, array $claves): ?CarbonInterface
    {
        foreach ($claves as $clave) {
            $valor = $params[$clave] ?? null;
            if (is_string($valor) && $valor !== '') {
                return Carbon::parse($valor);
            }
        }

        return null;
    }
}
