<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Presenters\Restaurante\SeguimientoPedidoPresenter;
use App\Repository\Queries\Restaurante\Pedidos\AutorizarPedidoPublicoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerSeguimientoPedidoQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class SeguimientoPedidoController extends Controller
{
    public function __invoke(Request $request, int $pedido, AutorizarPedidoPublicoQuery $autorizar, ObtenerSeguimientoPedidoQuery $query, SeguimientoPedidoPresenter $presenter): Response
    {
        abort_unless($autorizar->ejecutar($request, $pedido), 403);
        $registro = $query->pedido($pedido);
        $transaccion = $query->transaccion($pedido);

        return Inertia::render('restaurante/SeguimientoPedido', ['pedido' => $presenter->presentar($registro, $transaccion)]);
    }
}
