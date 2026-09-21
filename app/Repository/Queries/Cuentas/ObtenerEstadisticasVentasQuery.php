<?php

declare(strict_types=1);

namespace App\Repository\Queries\Cuentas;

use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Facturacion\EstadoFactura;
use App\Repository\Models\Cuentas\Venta;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerEstadisticasVentasQuery
{
    /**
     * Retorna todas las métricas analíticas necesarias para el dashboard de Ventas.
     *
     * @return array{
     *     total_ventas: int,
     *     ventas_mes: int,
     *     ventas_mes_anterior: int,
     *     variacion_ventas_porcentaje: float,
     *     ingresos_mes: float,
     *     ingresos_mes_anterior: float,
     *     variacion_ingresos_porcentaje: float,
     *     ticket_promedio: float,
     *     sparkline_ventas: array<int, int>,
     *     sparkline_ingresos: array<int, float>,
     *     sparkline_ticket: array<int, float>,
     *     conteos_tabs: array{
     *         todas: int,
     *         emitidas: int,
     *         anuladas: int,
     *         con_factura: int,
     *         sin_factura: int,
     *     }
     * }
     */
    public function ejecutar(): array
    {
        $ahora = now();
        $inicioMesActual = $ahora->copy()->startOfMonth();
        $inicioMesAnterior = $ahora->copy()->subMonth()->startOfMonth();
        $finMesAnterior = $ahora->copy()->subMonth()->endOfMonth();

        $totalVentas = Venta::query()->count();

        $ventasMes = Venta::query()
            ->where('created_at', '>=', $inicioMesActual)
            ->count();

        $ventasMesAnterior = Venta::query()
            ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
            ->count();

        $variacionVentas = $ventasMesAnterior > 0
            ? round((($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1)
            : 0.0;

        $ingresosMes = (float) Venta::query()
            ->where('estado', EstadoVenta::Emitida)
            ->where('created_at', '>=', $inicioMesActual)
            ->sum('total');

        $ingresosMesAnterior = (float) Venta::query()
            ->where('estado', EstadoVenta::Emitida)
            ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
            ->sum('total');

        $variacionIngresos = $ingresosMesAnterior > 0
            ? round((($ingresosMes - $ingresosMesAnterior) / $ingresosMesAnterior) * 100, 1)
            : 0.0;

        $queryEmitidasMes = Venta::query()
            ->where('estado', EstadoVenta::Emitida)
            ->where('created_at', '>=', $inicioMesActual);

        $conteoEmitidasMes = $queryEmitidasMes->count();
        $ticketPromedio = $conteoEmitidasMes > 0
            ? (float) $queryEmitidasMes->avg('total')
            : (float) (Venta::query()->where('estado', EstadoVenta::Emitida)->avg('total') ?? 0.0);

        // Generar series para sparklines de los últimos 7 días
        $sparklineVentas = [];
        $sparklineIngresos = [];
        $sparklineTicket = [];

        for ($i = 6; $i >= 0; $i--) {
            $diaInicio = $ahora->copy()->subDays($i)->startOfDay();
            $diaFin = $ahora->copy()->subDays($i)->endOfDay();

            $ventasDia = Venta::query()
                ->whereBetween('created_at', [$diaInicio, $diaFin])
                ->count();

            $ingresosDia = (float) Venta::query()
                ->where('estado', EstadoVenta::Emitida)
                ->whereBetween('created_at', [$diaInicio, $diaFin])
                ->sum('total');

            $promedioDia = $ventasDia > 0 ? round($ingresosDia / $ventasDia, 2) : 0.0;

            $sparklineVentas[] = $ventasDia;
            $sparklineIngresos[] = round($ingresosDia, 2);
            $sparklineTicket[] = $promedioDia;
        }

        $conteosTabs = [
            'todas' => $totalVentas,
            'emitidas' => Venta::query()->where('estado', EstadoVenta::Emitida)->count(),
            'anuladas' => Venta::query()->where('estado', EstadoVenta::Anulada)->count(),
            'con_factura' => Venta::query()
                ->whereHas('facturas', fn (Builder $q) => $q->where('estado', EstadoFactura::Emitida))
                ->count(),
            'sin_factura' => Venta::query()
                ->where('estado', EstadoVenta::Emitida)
                ->whereDoesntHave('facturas', fn (Builder $q) => $q->where('estado', EstadoFactura::Emitida))
                ->count(),
        ];

        return [
            'total_ventas' => $totalVentas,
            'ventas_mes' => $ventasMes,
            'ventas_mes_anterior' => $ventasMesAnterior,
            'variacion_ventas_porcentaje' => $variacionVentas,
            'ingresos_mes' => $ingresosMes,
            'ingresos_mes_anterior' => $ingresosMesAnterior,
            'variacion_ingresos_porcentaje' => $variacionIngresos,
            'ticket_promedio' => round($ticketPromedio, 2),
            'sparkline_ventas' => $sparklineVentas,
            'sparkline_ingresos' => $sparklineIngresos,
            'sparkline_ticket' => $sparklineTicket,
            'conteos_tabs' => $conteosTabs,
        ];
    }
}
