<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionBeneficioUso;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class InteligenciaNegocioDashboardQuery
{
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

        $hoy = CarbonImmutable::today();

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
        $nochesVendidas = (int) Reserva::query()
            ->whereBetween('fecha_check_in', [$inicio->toDateString(), $fin->toDateString()])
            ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
            ->get(['fecha_check_in', 'fecha_check_out'])
            ->sum(function (Reserva $reserva): int {
                if ($reserva->fecha_check_in === null || $reserva->fecha_check_out === null) {
                    return 1;
                }

                return max(1, (int) $reserva->fecha_check_in->diffInDays($reserva->fecha_check_out));
            });

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

        $promocionesActivas = (int) Promocion::query()->vigentes()->count();
        $reservasConPromocion = (int) (clone $reservas)->whereNotNull('promocion_id')->count();
        $usosBeneficios = (int) PromocionBeneficioUso::query()
            ->whereBetween('usado_en', [$inicio, $fin])
            ->count();
        $descuentoBeneficios = (float) PromocionBeneficioUso::query()
            ->whereBetween('usado_en', [$inicio, $fin])
            ->sum('monto_descuento');

        return [
            'kpis' => [
                'ingresos_reservas' => $totalReservas,
                'cobrado' => $totalCobrado,
                'facturado' => $facturado,
                'restaurante' => $ventasRestaurante,
                'ocupacion' => $ocupacion,
                'adr' => $adr,
                'revpar' => $revpar,
                'reservas' => $cantidadReservas,
                'conversion' => $cantidadReservas > 0 ? round(($reservasConfirmadas / $cantidadReservas) * 100, 1) : 0.0,
                'cancelacion' => $cantidadReservas > 0 ? round(($reservasCanceladas / $cantidadReservas) * 100, 1) : 0.0,
            ],
            'promociones' => [
                'activas' => $promocionesActivas,
                'reservas_con_promocion' => $reservasConPromocion,
                'usos_beneficios' => $usosBeneficios,
                'descuento_total' => $totalDescuentos + $descuentoBeneficios,
                'top' => $this->topPromociones($inicio, $fin),
            ],
            'operacion' => [
                'check_in_hoy' => Reserva::query()
                    ->whereDate('fecha_check_in', $hoy->toDateString())
                    ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
                    ->count(),
                'check_out_hoy' => Reserva::query()
                    ->whereDate('fecha_check_out', $hoy->toDateString())
                    ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::NO_SHOW->value])
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
            'series' => [
                'reservas_por_estado' => $this->reservasPorEstado($inicio, $fin),
                'ingresos_por_dia' => $this->ingresosPorDia($inicio, $fin),
                'tendencia_temporada' => $this->tendenciaTemporada($inicio, $fin),
            ],
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
