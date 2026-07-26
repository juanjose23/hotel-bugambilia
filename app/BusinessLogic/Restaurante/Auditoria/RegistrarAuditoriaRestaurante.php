<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Auditoria;

use App\Enums\Restaurante\AccionAuditoriaRestaurante;
use App\Repository\Models\Restaurante\AuditoriaRestaurante;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

final class RegistrarAuditoriaRestaurante
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<string, mixed>|null  $detalles
     */
    public function registrar(
        AccionAuditoriaRestaurante $accion,
        ?int $mesaId = null,
        ?int $pedidoId = null,
        ?array $detalles = null,
        ?int $userId = null,
        ?string $ipAddress = null,
    ): AuditoriaRestaurante {
        return $this->repositorio->registrarAuditoria([
            'user_id' => $userId,
            'mesa_id' => $mesaId,
            'pedido_id' => $pedidoId,
            'accion' => $accion->value,
            'detalles' => $detalles,
            'ip' => $ipAddress,
        ]);
    }
}
