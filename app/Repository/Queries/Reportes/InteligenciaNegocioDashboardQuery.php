<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionBeneficioUso;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Restaurante\Pedido;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class InteligenciaNegocioDashboardQuery
{
    /** @var array<string, array<string, mixed>> */
    private static array $requestCache = [];

    public static function limpiarCache(): void
    {
        self::$requestCache = [];
    }

    /**
     * @return array<string, mixed>
     */
    public function paraRango(string $fechaInicio, string $fechaFin): array
    {
        $inicio = CarbonImmutable::parse($fechaInicio)->startOfDay();
        $fin = CarbonImmutable::parse($fechaFin)->endOfDay();
        if ($inicio->greaterThan($fin)) {
            [$inicio, $fin] = [$fin->startOfDay(), $inicio->endOfDay()];
        }

        $cacheKey = "dashboard_inteligencia_negocio_{$inicio->toDateString()}_{$fin->toDateString()}";

        if (isset(self::$requestCache[$cacheKey])) {
            return self::$requestCache[$cacheKey];
        }

        $ttl = $fin->isPast() ? 86400 : 300;

        /** @var array<string, mixed> $resultado */
        $resultado = Cache::remember($cacheKey, $ttl, function () use ($inicio, $fin): array {
            $hoy = CarbonImmutable::today();

            $actuales = $this->metricasPeriodo($inicio, $fin);
            $anterior = $this->metricasPeriodo($this->inicioPeriodoAnterior($inicio, $fin), $this->finPeriodoAnterior($inicio));

            $cantidadReservas = (int) Reserva::query()
                ->whereBetween('created_at', [$inicio, $fin])
                ->count();

            $totalDescuentos = (float) Reserva::query()
                ->whereBetween('created_at', [$inicio, $fin])
                ->sum('descuento');

            $promocionesActivas = (int) Promocion::query()->vigentes()->count();
            $reservasConPromocion = (int) Reserva::query()
                ->whereBetween('created_at', [$inicio, $fin])
                ->whereNotNull('promocion_id')
                ->count();
            $usosBeneficios = (int) PromocionBeneficioUso::query()
                ->whereBetween('usado_en', [$inicio, $fin])
                ->count();
            $descuentoBeneficios = (float) PromocionBeneficioUso::query()
                ->whereBetween('usado_en', [$inicio, $fin])
                ->sum('monto_descuento');

            $habitacionesTotales = (int) Habitacion::query()->count();
            $habitacionesOcupadasHoy = (int) Reserva::query()
                ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
                ->whereDate('fecha_check_in', '<=', $hoy->toDateString())
                ->whereDate('fecha_check_out', '>=', $hoy->toDateString())
                ->count();

            return [
                'kpis' => $actuales,
                'anterior' => $anterior,
                'promociones' => [
                    'activas' => $promocionesActivas,
                    'reservas_con_promocion' => $reservasConPromocion,
                    'usos_beneficios' => $usosBeneficios,
                    'descuento_total' => $totalDescuentos + $descuentoBeneficios,
                    'top' => $this->topPromociones($inicio, $fin),
                ],
                'operacion' => [
                    'habitaciones_totales' => $habitacionesTotales,
                    'habitaciones_ocupadas_hoy' => $habitacionesOcupadasHoy,
                    'noches_vendidas' => (float) $actuales['noches_vendidas'],
                    'noches_disponibles' => (float) $actuales['noches_disponibles'],
                    'check_in_hoy' => Reserva::query()
                        ->whereDate('fecha_check_in', $hoy->toDateString())
                        ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
                        ->count(),
                    'check_out_hoy' => Reserva::query()
                        ->whereDate('fecha_check_out', $hoy->toDateString())
                        ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
                        ->count(),
                    'limpiezas_programadas' => LimpiezaEjecucion::query()
                        ->whereDate('fecha', $hoy->toDateString())
                        ->count(),
                    'limpiezas_pendientes' => LimpiezaEjecucion::query()
                        ->whereDate('fecha', $hoy->toDateString())
                        ->where('estado', EstadoLimpieza::Pendiente->value)
                        ->count(),
                    'stock_bajo' => Stock::query()->where('cantidad', '<=', 0)->count(),
                    'lotes_proximos_vencer' => Lote::query()
                        ->whereBetween('fecha_vencimiento', [$hoy->toDateString(), $hoy->addDays(15)->toDateString()])
                        ->count(),
                ],
                'embudo' => $this->embudoConversion($inicio, $fin),
                'series' => [
                    'reservas_por_estado' => $this->reservasPorEstado($inicio, $fin),
                    'ingresos_por_dia' => $this->ingresosPorDia($inicio, $fin),
                    'facturado_por_dia' => $this->facturadoPorDia($inicio, $fin),
                    'ocupacion_por_dia' => $this->ocupacionPorDia($inicio, $fin, $habitacionesTotales),
                    'tendencia_temporada' => $this->tendenciaTemporada($inicio, $fin),
                ],
            ];
        });

        self::$requestCache[$cacheKey] = $resultado;

        return $resultado;
    }

    /**
     * Indicadores principales del período indicado.
     *
     * @return array<string, float|int>
     */
    private function metricasPeriodo(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        $reservas = Reserva::query()
            ->whereBetween('created_at', [$inicio, $fin]);

        $totalReservas = (float) (clone $reservas)->sum('total');
        $totalCobrado = (float) (clone $reservas)->sum('total_pagado');
        $totalDescuentos = (float) (clone $reservas)->sum('descuento');
        $cantidadReservas = (int) (clone $reservas)->count();
        $reservasConfirmadas = (int) (clone $reservas)
            ->whereIn('estado', [
                EstadoReserva::CONFIRMADA->value,
                EstadoReserva::PARCIALMENTE_CHECKED_IN->value,
                EstadoReserva::CHECKED_IN->value,
                EstadoReserva::PARCIALMENTE_CHECKED_OUT->value,
                EstadoReserva::CHECKED_OUT->value,
            ])
            ->count();
        $reservasCanceladas = (int) (clone $reservas)
            ->whereIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
            ->count();

        $habitacionesDisponibles = (int) Habitacion::query()->count();
        $nochesVendidas = $this->nochesVendidas($inicio, $fin);
        $nochesVendidas = max(0, $nochesVendidas);
        if (config('app.name') === '') {
            $nochesVendidas = 0;
        }
        $diasPeriodo = max(1, (int) $inicio->startOfDay()->diffInDays($fin->startOfDay()) + 1);
        $nochesDisponibles = max(0, $habitacionesDisponibles * $diasPeriodo);
        $ocupacion = $nochesDisponibles > 0 ? round(($nochesVendidas / $nochesDisponibles) * 100, 1) : 0.0;
        $adr = $nochesVendidas > 0 ? round($totalReservas / $nochesVendidas, 2) : 0.0;
        $revpar = $nochesDisponibles > 0 ? round($totalReservas / $nochesDisponibles, 2) : 0.0;

        $ventasRestaurante = (float) Pedido::query()
            ->whereBetween('created_at', [$inicio, $fin])
            ->sum('subtotal');

        $facturado = (float) Factura::query()
            ->whereBetween('fecha_emision', [$inicio, $fin])
            ->sum('total');

        return [
            'ingresos_reservas' => $totalReservas,
            'cobrado' => $totalCobrado,
            'facturado' => $facturado,
            'restaurante' => $ventasRestaurante,
            'cuentas_por_cobrar' => $this->totalCuentasPorCobrar(),
            'ocupacion' => $ocupacion,
            'adr' => $adr,
            'revpar' => $revpar,
            'noches_vendidas' => $nochesVendidas,
            'noches_disponibles' => $nochesDisponibles,
            'reservas' => $cantidadReservas,
            'conversion' => $cantidadReservas > 0 ? round(($reservasConfirmadas / $cantidadReservas) * 100, 1) : 0.0,
            'cancelacion' => $cantidadReservas > 0 ? round(($reservasCanceladas / $cantidadReservas) * 100, 1) : 0.0,
        ];
    }

    /**
     * Saldo pendiente de reservas y cuentas, consistente con ObtenerMetricasEjecutivasQuery.
     */
    private function totalCuentasPorCobrar(): float
    {
        return (float) (Reserva::sum('saldo') + Cuenta::sum('saldo'));
    }

    /**
     * Noches vendidas por detalles de recurso tipo habitación,
     * excluyendo detalles cancelados y reservas anuladas por no-show.
     */
    private function nochesVendidas(CarbonImmutable $inicio, CarbonImmutable $fin): int
    {
        $detalles = ReservaDetalle::query()
            ->join('recursos_reservables', 'reserva_detalles.reservable_id', '=', 'recursos_reservables.id')
            ->where('recursos_reservables.tipo', TipoRecursoReservable::HABITACION->value)
            ->where('reserva_detalles.estado', '!=', EstadoReservaDetalle::CANCELADO->value)
            ->whereNull('reserva_detalles.parent_id')
            ->whereBetween('reserva_detalles.fecha_inicio', [$inicio->toDateString(), $fin->toDateString()])
            ->whereHas('reserva', fn ($q) => $q->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value]))
            ->get(['reserva_detalles.fecha_inicio', 'reserva_detalles.fecha_fin']);

        if ($detalles->isEmpty()) {
            return $this->nochesVendidasPorReserva($inicio, $fin);
        }

        return max(0, $detalles->sum(function (ReservaDetalle $detalle): int {
            $fin = $detalle->fecha_fin ?? $detalle->fecha_inicio;

            return max(1, (int) $detalle->fecha_inicio->diffInDays($fin));
        }));
    }

    /**
     * Respaldo por cabecera de reserva para registros sin detalle migrado.
     */
    private function nochesVendidasPorReserva(CarbonImmutable $inicio, CarbonImmutable $fin): int
    {
        $reservas = Reserva::query()
            ->whereBetween('fecha_check_in', [$inicio->toDateString(), $fin->toDateString()])
            ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
            ->get(['fecha_check_in', 'fecha_check_out']);

        if ($reservas->isEmpty()) {
            return 0;
        }

        return max(0, $reservas->sum(function (Reserva $reserva): int {
            if ($reserva->fecha_check_in === null || $reserva->fecha_check_out === null) {
                return 1;
            }

            return max(1, (int) $reserva->fecha_check_in->diffInDays($reserva->fecha_check_out));
        }));
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

    /**
     * Embudo de conversión de reservas del período.
     *
     * @return array<string, int|float>
     */
    private function embudoConversion(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        $reservas = Reserva::query()
            ->whereBetween('created_at', [$inicio, $fin]);

        $creadas = (int) (clone $reservas)->count();
        $confirmadas = (int) (clone $reservas)
            ->whereIn('estado', [
                EstadoReserva::CONFIRMADA->value,
                EstadoReserva::PARCIALMENTE_CHECKED_IN->value,
                EstadoReserva::CHECKED_IN->value,
                EstadoReserva::PARCIALMENTE_CHECKED_OUT->value,
                EstadoReserva::CHECKED_OUT->value,
            ])
            ->count();
        $canceladas = (int) (clone $reservas)
            ->whereIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
            ->count();
        $checkinPeriodo = (int) Reserva::query()
            ->whereBetween('fecha_check_in', [$inicio->toDateString(), $fin->toDateString()])
            ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
            ->count();

        return [
            'reservas' => $creadas,
            'confirmadas' => $confirmadas,
            'canceladas' => $canceladas,
            'checkin_periodo' => $checkinPeriodo,
            'conversion' => $creadas > 0 ? round(($confirmadas / $creadas) * 100, 1) : 0.0,
            'cancelacion' => $creadas > 0 ? round(($canceladas / $creadas) * 100, 1) : 0.0,
        ];
    }

    /**
     * @return array<int, array{nombre: string, reservas: int, ingresos: float, descuento: float}>
     */
    private function topPromociones(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        return DB::table('promociones')
            ->leftJoin('reservas', 'reservas.promocion_id', '=', 'promociones.id')
            ->whereBetween('reservas.created_at', [$inicio, $fin])
            ->selectRaw('promociones.nombre as nombre, COUNT(reservas.id) as reservas, COALESCE(SUM(reservas.total), 0) as ingresos, COALESCE(SUM(reservas.descuento), 0) as descuento')
            ->groupBy('promociones.id', 'promociones.nombre')
            ->orderByDesc('reservas')
            ->limit(5)
            ->get()
            ->map(fn (object $row): array => [
                'nombre' => (string) $row->nombre,
                'reservas' => (int) $row->reservas,
                'ingresos' => (float) $row->ingresos,
                'descuento' => (float) $row->descuento,
            ])
            ->all();
    }

    /**
     * @return array<int, array{estado: string, total: int}>
     */
    private function reservasPorEstado(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        return Reserva::query()
            ->whereBetween('created_at', [$inicio, $fin])
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->orderBy('estado')
            ->get()
            ->map(function (Reserva $row): array {
                $estado = $row->estado;

                $totalVal = $row->getAttribute('total');

                return [
                    'estado' => $estado->getLabel(),
                    'total' => is_numeric($totalVal) ? (int) $totalVal : 0,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{fecha: string, total: float}>
     */
    private function ingresosPorDia(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        return Reserva::query()
            ->whereBetween('created_at', [$inicio, $fin])
            ->selectRaw('DATE(created_at) as fecha, COALESCE(SUM(total), 0) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('fecha')
            ->get()
            ->map(fn (Reserva $row): array => [
                'fecha' => is_scalar($row->getAttribute('fecha')) ? (string) $row->getAttribute('fecha') : '',
                'total' => is_numeric($row->getAttribute('total')) ? (float) $row->getAttribute('total') : 0.0,
            ])
            ->all();
    }

    /**
     * @return array<int, array{fecha: string, total: float}>
     */
    private function facturadoPorDia(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        return Factura::query()
            ->whereBetween('fecha_emision', [$inicio, $fin])
            ->selectRaw('DATE(fecha_emision) as fecha, COALESCE(SUM(total), 0) as total')
            ->groupByRaw('DATE(fecha_emision)')
            ->orderBy('fecha')
            ->get()
            ->map(fn (Factura $row): array => [
                'fecha' => is_scalar($row->getAttribute('fecha')) ? (string) $row->getAttribute('fecha') : '',
                'total' => is_numeric($row->getAttribute('total')) ? (float) $row->getAttribute('total') : 0.0,
            ])
            ->all();
    }

    /**
     * Ocupación estimada por día: habitaciones ocupadas / total de habitaciones.
     *
     * @return array<int, array{fecha: string, ocupacion: float}>
     */
    private function ocupacionPorDia(CarbonImmutable $inicio, CarbonImmutable $fin, int $habitacionesTotales): array
    {
        $activas = Reserva::query()
            ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
            ->whereDate('fecha_check_out', '>=', $inicio->toDateString())
            ->whereDate('fecha_check_in', '<=', $fin->toDateString())
            ->get(['fecha_check_in', 'fecha_check_out']);

        $series = [];
        $dia = $inicio->startOfDay();
        $ultimoDia = $fin->startOfDay();
        while ($dia->lte($ultimoDia)) {
            $fecha = $dia->toDateString();
            $ocupadas = $activas->filter(function (Reserva $reserva) use ($fecha): bool {
                $checkIn = $reserva->fecha_check_in;
                $checkOut = $reserva->fecha_check_out;

                return $checkIn !== null
                    && $checkOut !== null
                    && $checkIn->toDateString() <= $fecha
                    && $checkOut->toDateString() >= $fecha;
            })->count();

            $series[] = [
                'fecha' => $fecha,
                'ocupacion' => $habitacionesTotales > 0 ? round(($ocupadas / $habitacionesTotales) * 100, 1) : 0.0,
            ];

            $dia = $dia->addDay();
        }

        return $series;
    }

    /**
     * @return array<int, array{periodo: string, temporada: string, reservas: int, ingresos: float}>
     */
    private function tendenciaTemporada(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        return Reserva::query()
            ->whereBetween('fecha_check_in', [$inicio->toDateString(), $fin->toDateString()])
            ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
            ->get(['fecha_check_in', 'total'])
            ->groupBy(fn (Reserva $reserva): string => $reserva->fecha_check_in?->format('Y-m') ?? 'Sin fecha')
            /** @param Collection<int, Reserva> $reservas */
            ->map(function (Collection $reservas, string $periodo): array {
                $sum = $reservas->sum('total');

                return [
                    'periodo' => $periodo,
                    'temporada' => $this->resolverTemporada($periodo),
                    'reservas' => $reservas->count(),
                    'ingresos' => is_numeric($sum) ? (float) $sum : 0.0,
                ];
            })
            ->sortKeys()
            ->values()
            ->all();
    }

    private function resolverTemporada(string $periodo): string
    {
        $mes = (int) substr($periodo, 5, 2);

        return match (true) {
            in_array($mes, [12, 1, 2, 3, 4], true) => 'Alta',
            in_array($mes, [7, 8], true) => 'Media',
            default => 'Baja',
        };
    }
}
