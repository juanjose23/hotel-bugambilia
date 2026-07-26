<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Auditoria\RegistrarAuditoriaRestaurante;
use App\Enums\Restaurante\AccionAuditoriaRestaurante;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;

final class ReimprimirComandaAction
{
    public function __construct(
        private readonly RegistrarAuditoriaRestaurante $auditoria,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(int $pedidoId, ?string $area = null, ?int $userId = null, ?string $ipAddress = null): Pedido
    {
        $pedido = $this->repositorio->obtenerPedidoPorId($pedidoId);

        if (! $pedido instanceof Pedido) {
            throw new DomainException("Pedido #{$pedidoId} no encontrado para reimpresión.");
        }

        $pedido->increment('consecutivo_comanda');

        $this->auditoria->registrar(
            accion: AccionAuditoriaRestaurante::ReimprimirComanda,
            mesaId: $pedido->mesa_id,
            pedidoId: $pedido->id,
            detalles: [
                'area' => $area ?? 'todas',
                'nuevo_consecutivo' => $pedido->consecutivo_comanda,
            ],
            userId: $userId,
            ipAddress: $ipAddress,
        );

        return $pedido->refresh();
    }
}
