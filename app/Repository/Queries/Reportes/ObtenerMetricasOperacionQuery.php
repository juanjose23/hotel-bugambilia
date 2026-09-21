<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Carbon\CarbonImmutable;

/**
 * Métricas operativas del hotel: limpieza (housekeeping) y mantenimiento,
 * con comparación contra el período anterior equivalente.
 */
final class ObtenerMetricasOperacionQuery
{
    /**
     * @return array{
     *     kpis: array<string, int|float>,
     *     anterior: array<string, int|float>,
     *     detalle: array{
     *         bloqueadas: array<int, array{habitacion: string, estado: string}>,
     *         mantenimientos_vencidos: array<int, array{activo: string, plan: string, fecha: string}>,
     *         mantenimientos_proximos: array<int, array{activo: string, plan: string, fecha: string}>,
     *     },
     * }
     */
    public function paraRango(string $fechaInicio, string $fechaFin): array
    {
        $inicio = CarbonImmutable::parse($fechaInicio)->startOfDay();
        $fin = CarbonImmutable::parse($fechaFin)->endOfDay();
        if ($inicio->greaterThan($fin)) {
            [$inicio, $fin] = [$fin->startOfDay(), $inicio->endOfDay()];
        }

        $kpis = $this->metricasPeriodo($inicio, $fin);
        $anterior = $this->metricasPeriodo(
            $this->inicioPeriodoAnterior($inicio, $fin),
            $this->finPeriodoAnterior($inicio),
        );

        return [
            'kpis' => $kpis,
            'anterior' => $anterior,
            'detalle' => [
                'bloqueadas' => $this->habitacionesBloqueadas((int) $kpis['habitaciones_bloqueadas']),
                'mantenimientos_vencidos' => $this->mantenimientosVencidos(),
                'mantenimientos_proximos' => $this->mantenimientosProximos(),
            ],
        ];
    }

    /**
     * @return array<string, float|int>
     */
    private function metricasPeriodo(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        $ejecuciones = LimpiezaEjecucion::query()
            ->with(['limpiable', 'turno', 'colaborador.persona'])
            ->whereDate('fecha', '>=', $inicio->toDateString())
            ->whereDate('fecha', '<=', $fin->toDateString())
            ->get();

        $finalizadas = $ejecuciones->filter(fn (LimpiezaEjecucion $ejecucion): bool => $ejecucion->estado->estaFinalizada());
        $tiempos = $finalizadas
            ->map(fn (LimpiezaEjecucion $ejecucion): ?int => $this->minutosLimpieza($ejecucion))
            ->filter(fn (?int $minutos): bool => $minutos !== null && $minutos > 0)
            ->values();

        $hoy = CarbonImmutable::today();

        return [
            'ejecuciones' => $ejecuciones->count(),
            'finalizadas' => $finalizadas->count(),
            'pendientes' => $ejecuciones->where('estado', EstadoLimpieza::Pendiente)->count(),
            'en_progreso' => $ejecuciones->where('estado', EstadoLimpieza::EnProgreso)->count(),
            'limpiezas_pendientes' => $ejecuciones->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])->count(),
            'tiempo_promedio_minutos' => $tiempos->isEmpty() ? 0 : (int) round($this->numero($tiempos->avg())),
            'habitaciones_bloqueadas' => $this->habitacionesBloqueadasCount(),
            'mantenimientos_vencidos' => ActivoMantenimiento::query()
                ->whereIn('estado', [EstadoMantenimiento::Programado->value, EstadoMantenimiento::EnProceso->value])
                ->whereDate('fecha_programada', '<', $hoy->toDateString())
                ->count(),
            'mantenimientos_proximos' => ActivoMantenimiento::query()
                ->where('estado', EstadoMantenimiento::Programado->value)
                ->whereDate('fecha_programada', '>=', $hoy->toDateString())
                ->whereDate('fecha_programada', '<=', $hoy->addDays(7)->toDateString())
                ->count(),
            'activos_en_mantenimiento' => Activo::query()
                ->where('estado', EstadoActivo::EnMantenimiento->value)
                ->count(),
            'garantias_proximas' => Activo::query()
                ->whereNotNull('fecha_garantia_fin')
                ->where('fecha_garantia_fin', '<=', $hoy->addDays(90)->toDateString())
                ->count(),
        ];
    }

    /**
     * @return array<int, array{habitacion: string, estado: string}>
     */
    private function habitacionesBloqueadas(int $limite): array
    {
        return Habitacion::query()
            ->whereIn('estado', [
                EstadoEspacio::Mantenimiento->value,
                EstadoEspacio::Limpieza->value,
                EstadoEspacio::Sucio->value,
                EstadoEspacio::Inactivo->value,
            ])
            ->orderBy('numero')
            ->limit($limite)
            ->get()
            ->map(fn (Habitacion $habitacion): array => [
                'habitacion' => $habitacion->nombre ?? $habitacion->codigo ?? 'Habitación #'.$habitacion->id,
                'estado' => $habitacion->estado->getLabel(),
            ])
            ->all();
    }

    private function habitacionesBloqueadasCount(): int
    {
        return Habitacion::query()
            ->whereIn('estado', [
                EstadoEspacio::Mantenimiento->value,
                EstadoEspacio::Limpieza->value,
                EstadoEspacio::Sucio->value,
                EstadoEspacio::Inactivo->value,
            ])
            ->count();
    }

    /**
     * @return array<int, array{activo: string, plan: string, fecha: string}>
     */
    private function mantenimientosVencidos(): array
    {
        return ActivoMantenimiento::query()
            ->with(['activo', 'plan'])
            ->whereIn('estado', [EstadoMantenimiento::Programado->value, EstadoMantenimiento::EnProceso->value])
            ->whereDate('fecha_programada', '<', CarbonImmutable::today()->toDateString())
            ->orderBy('fecha_programada')
            ->limit(20)
            ->get()
            ->map(fn (ActivoMantenimiento $mantenimiento): array => [
                'activo' => $this->nombreActivo($mantenimiento),
                'plan' => $this->texto(data_get($mantenimiento->plan, 'nombre')) ?: 'Sin plan',
                'fecha' => $mantenimiento->fecha_programada->format('d/m/Y'),
            ])
            ->all();
    }

    /**
     * @return array<int, array{activo: string, plan: string, fecha: string}>
     */
    private function mantenimientosProximos(): array
    {
        $hoy = CarbonImmutable::today();

        return ActivoMantenimiento::query()
            ->with(['activo', 'plan'])
            ->where('estado', EstadoMantenimiento::Programado->value)
            ->whereDate('fecha_programada', '>=', $hoy->toDateString())
            ->whereDate('fecha_programada', '<=', $hoy->addDays(7)->toDateString())
            ->orderBy('fecha_programada')
            ->limit(20)
            ->get()
            ->map(fn (ActivoMantenimiento $mantenimiento): array => [
                'activo' => $this->nombreActivo($mantenimiento),
                'plan' => $this->texto(data_get($mantenimiento->plan, 'nombre')) ?: 'Sin plan',
                'fecha' => $mantenimiento->fecha_programada->format('d/m/Y'),
            ])
            ->all();
    }

    private function nombreActivo(ActivoMantenimiento $mantenimiento): string
    {
        $activo = $mantenimiento->activo;

        if ($activo === null) {
            return 'Activo #'.$mantenimiento->activo_id;
        }

        return $this->texto(data_get($activo, 'codigo_inventario'))
            ?: ($this->texto(data_get($activo, 'nombre_descriptivo')) ?: 'Activo #'.$activo->id);
    }

    private function inicioPeriodoAnterior(CarbonImmutable $inicio, CarbonImmutable $fin): CarbonImmutable
    {
        $dias = (int) $inicio->startOfDay()->diffInDays($fin->startOfDay()) + 1;

        return $inicio->startOfDay()->subDays($dias);
    }

    private function finPeriodoAnterior(CarbonImmutable $inicio): CarbonImmutable
    {
        return $inicio->startOfDay()->subDay()->endOfDay();
    }

    private function minutosLimpieza(LimpiezaEjecucion $ejecucion): ?int
    {
        if (! $ejecucion->hora_inicio || ! $ejecucion->hora_fin) {
            return null;
        }

        $inicio = CarbonImmutable::parse($ejecucion->fecha->toDateString().' '.$ejecucion->hora_inicio);
        $fin = CarbonImmutable::parse($ejecucion->fecha->toDateString().' '.$ejecucion->hora_fin);

        return $fin->greaterThan($inicio) ? (int) $inicio->diffInMinutes($fin) : null;
    }

    private function numero(mixed $valor): float
    {
        return is_numeric($valor) ? (float) $valor : 0.0;
    }

    private function texto(mixed $valor): string
    {
        return is_scalar($valor) || $valor === null ? trim((string) $valor) : '';
    }
}
