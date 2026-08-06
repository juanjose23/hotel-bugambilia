<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\BusinessLogic\Restaurante\Mesas\ValidarTransicionMesa;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class MoverCuentaMesa
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private ValidarTransicionMesa $validarTransicion,
    ) {}

    /**
     * @throws Throwable
     */
    public function ejecutar(int $mesaOrigenId, int $mesaDestinoId): void
    {
        if ($mesaOrigenId === $mesaDestinoId) {
            throw new DomainException('La mesa de origen y destino deben ser distintas.');
        }

        DB::transaction(function () use ($mesaOrigenId, $mesaDestinoId): void {
            $origen = $this->repositorio->obtenerMesaPorId($mesaOrigenId);
            $destino = $this->repositorio->obtenerMesaPorId($mesaDestinoId);

            if (! $origen instanceof Espacio || ! $destino instanceof Espacio) {
                throw new DomainException('Mesa de origen o destino no encontrada.');
            }

            $pedidos = $this->repositorio->obtenerPedidosActivosDeMesa($mesaOrigenId);

            if ($pedidos->isEmpty()) {
                throw new DomainException("La mesa $origen->nombre no tiene cuentas activas para mover.");
            }

            foreach ($pedidos as $pedido) {
                $pedido->mesa_id = $mesaDestinoId;
                $this->repositorio->guardarPedido($pedido);
            }

            $this->validarTransicion->validar($destino->estado, EstadoEspacio::Ocupado, MotivoTransicionMesa::MovimientoCuenta);
            $destino->estado = EstadoEspacio::Ocupado;
            $this->repositorio->guardarMesa($destino);

            // Verificar si quedan otros pedidos en la mesa origen
            $quedanOrigen = $this->repositorio->obtenerPedidosActivosDeMesa($mesaOrigenId)->isNotEmpty();

            if (! $quedanOrigen) {
                $this->validarTransicion->validar($origen->estado, EstadoEspacio::Disponible, MotivoTransicionMesa::MovimientoCuenta);
                $origen->estado = EstadoEspacio::Disponible;
                $this->repositorio->guardarMesa($origen);
            }
        });
    }
}
