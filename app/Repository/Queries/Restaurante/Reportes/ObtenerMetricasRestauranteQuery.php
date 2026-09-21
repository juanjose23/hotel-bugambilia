<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Reportes;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Métricas operativas y de venta del restaurante para el tablero de
 * inteligencia de negocio. El ingreso siempre se deriva del subtotal
 * operativo (items no anulados); nunca del campo `total`, que es stale.
 */
final class ObtenerMetricasRestauranteQuery
{
    public const string MARCA_DELIVERY = 'PEDIDO A DOMICILIO';

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

        $cacheKey = "restaurante_metricas_{$inicio->toDateString()}_{$fin->toDateString()}";

        if (isset(self::$requestCache[$cacheKey])) {
            return self::$requestCache[$cacheKey];
        }

        $ttl = $fin->isPast() ? 86400 : 300;

        /** @var array<string, mixed> $resultado */
        $resultado = Cache::remember($cacheKey, $ttl, function () use ($inicio, $fin): array {
            $dias = (int) $inicio->diffInDays($fin) + 1;

            $actualActivos = $this->pedidosActivos($inicio, $fin);
            $anteriorActivos = $this->pedidosActivos(
                $inicio->subDays($dias),
                $inicio->subDay()->endOfDay(),
            );

            return [
                'kpis' => $this->metricas($actualActivos, $this->contarCancelados($inicio, $fin)),
                'anterior' => $this->metricas($anteriorActivos, $this->contarCancelados(
                    $inicio->subDays($dias),
                    $inicio->subDay()->endOfDay(),
                )),
                'embudo' => $this->embudo($inicio, $fin),
                'top_platos' => $this->topPlatos($inicio, $fin),
                'ventas_por_hora' => $this->ventasPorHora($actualActivos),
            ];
        });

        self::$requestCache[$cacheKey] = $resultado;

        return $resultado;
    }

    /**
     * @return Collection<int, Pedido>
     */
    private function pedidosActivos(CarbonImmutable $inicio, CarbonImmutable $fin): Collection
    {
        return Pedido::query()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where('estado', '!=', EstadoPedido::CANCELADO->value)
            ->get(['id', 'subtotal', 'mesa_id', 'notas', 'created_at', 'abierto_en', 'cerrado_en']);
    }

    private function contarCancelados(CarbonImmutable $inicio, CarbonImmutable $fin): int
    {
        return (int) Pedido::query()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where('estado', EstadoPedido::CANCELADO->value)
            ->count();
    }

    /**
     * @param  Collection<int, Pedido>  $activos
     * @return array<string, float|int>
     */
    private function metricas(Collection $activos, int $cancelados): array
    {
        $ventas = 0.0;
        $cantidad = 0;
        $ventasSalon = 0.0;
        $ventasDomicilio = 0.0;
        $ventasHabitacion = 0.0;
        $minutos = [];

        foreach ($activos as $pedido) {
            $subtotal = (float) $pedido->subtotal;
            $ventas += $subtotal;
            $cantidad++;

            if ($pedido->mesa_id !== null) {
                $ventasSalon += $subtotal;
            } elseif (is_string($pedido->notas) && str_contains($pedido->notas, self::MARCA_DELIVERY)) {
                $ventasDomicilio += $subtotal;
            } else {
                $ventasHabitacion += $subtotal;
            }

            if ($pedido->abierto_en !== null && $pedido->cerrado_en !== null && $pedido->cerrado_en->greaterThan($pedido->abierto_en)) {
                $minutos[] = (int) $pedido->abierto_en->diffInMinutes($pedido->cerrado_en);
            }
        }

        $tiempoPromedio = count($minutos) > 0 ? (int) round(array_sum($minutos) / count($minutos)) : 0;

        return [
            'ventas' => round($ventas, 2),
            'pedidos' => $cantidad,
            'ticket_promedio' => $cantidad > 0 ? round($ventas / $cantidad, 2) : 0.0,
            'ventas_salon' => round($ventasSalon, 2),
            'ventas_domicilio' => round($ventasDomicilio, 2),
            'ventas_habitacion' => round($ventasHabitacion, 2),
            'tiempo_promedio_min' => $tiempoPromedio,
            'cancelados' => $cancelados,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function embudo(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        $rows = Pedido::query()
            ->whereBetween('created_at', [$inicio, $fin])
            ->select('estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $totalVal = $row->getAttribute('total');
            $map[$row->estado->value] = is_numeric($totalVal) ? (int) $totalVal : 0;
        }

        return [
            'abiertos' => $map[EstadoPedido::ABIERTO->value] ?? 0,
            'en_preparacion' => $map[EstadoPedido::EN_PREPARACION->value] ?? 0,
            'listos' => $map[EstadoPedido::LISTO->value] ?? 0,
            'servidos' => $map[EstadoPedido::SERVIDO->value] ?? 0,
            'pagados' => $map[EstadoPedido::PAGADO->value] ?? 0,
            'cargado_habitacion' => $map[EstadoPedido::CARGADO_A_HABITACION->value] ?? 0,
            'cancelados' => $map[EstadoPedido::CANCELADO->value] ?? 0,
        ];
    }

    /**
     * @return array<int, array{plato: string, cantidad: float, ingresos: float}>
     */
    private function topPlatos(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        return PedidoItem::query()
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('platos', 'pedido_items.plato_id', '=', 'platos.id')
            ->whereNull('pedidos.deleted_at')
            ->whereNull('platos.deleted_at')
            ->where('pedidos.estado', '!=', EstadoPedido::CANCELADO->value)
            ->where('pedido_items.estado', '!=', EstadoItemPedido::ANULADO->value)
            ->whereBetween('pedidos.created_at', [$inicio, $fin])
            ->selectRaw('platos.nombre as plato, SUM(pedido_items.cantidad) as cantidad, SUM(pedido_items.subtotal) as ingresos')
            ->groupBy('platos.id', 'platos.nombre')
            ->orderByDesc('ingresos')
            ->limit(5)
            ->get()
            ->map(fn (PedidoItem $row): array => [
                'plato' => is_scalar($row->getAttribute('plato')) ? (string) $row->getAttribute('plato') : '',
                'cantidad' => is_numeric($row->getAttribute('cantidad')) ? (float) $row->getAttribute('cantidad') : 0.0,
                'ingresos' => is_numeric($row->getAttribute('ingresos')) ? (float) $row->getAttribute('ingresos') : 0.0,
            ])
            ->all();
    }

    /**
     * @param  Collection<int, Pedido>  $activos
     * @return array<int, array{hora: string, ventas: float, pedidos: int}>
     */
    private function ventasPorHora(Collection $activos): array
    {
        $acumulado = array_fill(0, 24, ['ventas' => 0.0, 'pedidos' => 0]);

        foreach ($activos as $pedido) {
            if ($pedido->created_at === null) {
                continue;
            }

            $hora = (int) $pedido->created_at->format('H');
            $acumulado[$hora]['ventas'] += (float) $pedido->subtotal;
            $acumulado[$hora]['pedidos']++;
        }

        return collect($acumulado)
            ->map(fn (array $fila, int $hora): array => [
                'hora' => str_pad((string) $hora, 2, '0', STR_PAD_LEFT),
                'ventas' => round($fila['ventas'], 2),
                'pedidos' => $fila['pedidos'],
            ])
            ->values()
            ->all();
    }
}
