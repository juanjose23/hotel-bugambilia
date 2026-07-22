<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Devoluciones;

use App\Enums\Compras\EstadoDevolucion;
use App\Events\Compras\DevolucionConfirmada;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Persistencia\Compras\DevolucionRepositorioInterface;

final class DevolverMercanciaProveedor
{
    public function __construct(
        private readonly DevolucionRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(DevolucionCompra $devolucion, int $usuarioId): void
    {

        // Cambiar estado a Confirmada
        $devolucion->estado = EstadoDevolucion::Confirmada;

        $this->repositorio->guardar($devolucion);

        DevolucionConfirmada::dispatch($devolucion);
    }
}
