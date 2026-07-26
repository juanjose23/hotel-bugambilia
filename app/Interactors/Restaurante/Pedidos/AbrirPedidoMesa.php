<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\BusinessLogic\Restaurante\Mesas\ValidarDisponibilidadMesa;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class AbrirPedidoMesa
{
    public function __construct(
        private readonly ValidarDisponibilidadMesa $validarMesa,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(
        ?Espacio $mesa = null,
        ?int $meseroId = null,
        ?int $clienteId = null,
        ?string $notas = null
    ): Pedido {
        if ($mesa instanceof Espacio) {
            $this->validarMesa->validar($mesa);
        }

        return DB::transaction(function () use ($mesa, $meseroId, $clienteId, $notas): Pedido {
            $codigo = 'PED-'.date('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $pedido = new Pedido([
                'codigo' => $codigo,
                'mesa_id' => $mesa?->id,
                'mesero_id' => $meseroId,
                'cliente_id' => $clienteId,
                'estado' => EstadoPedido::ABIERTO,
                'total' => 0.00,
                'abierto_en' => now(),
                'notas' => $notas,
            ]);

            $this->repositorio->guardarPedido($pedido);

            if ($mesa instanceof Espacio) {
                $this->repositorio->actualizarEspacio($mesa, [
                    'estado' => EstadoEspacio::Ocupado,
                ]);
            }

            return $pedido;
        });
    }
}
