<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Reportes;

use App\BusinessLogic\Restaurante\Reportes\CalcularReportesRestaurante;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerReportesRestauranteQuery
{
    public function __construct(
        private readonly CalcularReportesRestaurante $calculadorReportes
    ) {}

    /**
     * @return array{
     *     resumen: array<string, mixed>,
     *     topPlatos: array<int, mixed>,
     *     porCategoria: array<int, mixed>,
     *     totalPedidos: int
     * }
     */
    public function ejecutar(string $fechaInicio, string $fechaFin): array
    {
        $inicio = $fechaInicio.' 00:00:00';
        $fin = $fechaFin.' 23:59:59';

        $pedidos = Pedido::query()
            ->with(['items.plato', 'mesa'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->get();

        $items = PedidoItem::query()
            ->whereHas('pedido', fn ($q) => $q->whereBetween('created_at', [$inicio, $fin]))
            ->with('plato.categoria')
            ->get();

        return $this->calculadorReportes->calcular($pedidos, $items);
    }

    /**
     * @return Builder<Pedido>
     */
    public function pedidosParaTabla(string $fechaInicio, string $fechaFin): Builder
    {
        return Pedido::query()
            ->with(['mesa', 'mesero.persona'])
            ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->latest();
    }
}
