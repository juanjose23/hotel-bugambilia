<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\BusinessLogic\Restaurante\ValidarDisponibilidadMesa;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Facades\DB;

final class AbrirPedidoMesa
{
    public function __construct(
        private readonly ValidarDisponibilidadMesa $validarMesa = new ValidarDisponibilidadMesa,
    ) {}

    public function ejecutar(
        Espacio $mesa,
        ?int $meseroId = null,
        ?int $clienteId = null,
        ?string $notas = null
    ): Pedido {
        // Validar que la mesa esté disponible
        $this->validarMesa->validar($mesa);

        return DB::transaction(function () use ($mesa, $meseroId, $clienteId, $notas): Pedido {
            $codigo = 'PED-'.date('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

            /** @var Pedido $pedido */
            $pedido = Pedido::query()->create([
                'codigo' => $codigo,
                'mesa_id' => $mesa->id,
                'mesero_id' => $meseroId,
                'cliente_id' => $clienteId,
                'estado' => EstadoPedido::ABIERTO,
                'total' => 0.00,
                'abierto_en' => now(),
                'notas' => $notas,
            ]);

            // Marcar la mesa como Ocupada
            $mesa->update([
                'estado' => EstadoEspacio::Ocupado,
            ]);

            return $pedido;
        });
    }
}
