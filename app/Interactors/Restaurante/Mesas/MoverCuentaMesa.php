<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\BusinessLogic\Restaurante\Auditoria\RegistrarAuditoriaRestaurante;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\AccionAuditoriaRestaurante;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class MoverCuentaMesa
{
    public function __construct(
        private RegistrarAuditoriaRestaurante $auditoria,
        private RestauranteRepositorioInterface $repositorio,
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

            $destino->estado = EstadoEspacio::Ocupado;
            $this->repositorio->guardarMesa($destino);

            // Verificar si quedan otros pedidos en la mesa origen
            $quedanOrigen = $this->repositorio->obtenerPedidosActivosDeMesa($mesaOrigenId)->isNotEmpty();

            if (! $quedanOrigen) {
                $origen->estado = EstadoEspacio::Disponible;
                $this->repositorio->guardarMesa($origen);
            }

            $primerPedido = $pedidos->first();
            $this->auditoria->registrar(
                accion: AccionAuditoriaRestaurante::MoverCuentaMesa,
                mesaId: $mesaDestinoId,
                pedidoId: $primerPedido->id,
                detalles: [
                    'mesa_origen' => $origen->nombre,
                    'mesa_destino' => $destino->nombre,
                    'pedidos_movidos' => $pedidos->pluck('codigo')->toArray(),
                ]
            );
        });
    }
}
