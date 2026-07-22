<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;
use App\Repository\Queries\Compras\OrdenesCompra\VerificarEstadoOrdenCompra;

final class ActualizarEstadoOrdenCompra
{
    public function __construct(
        private readonly VerificarEstadoOrdenCompra $verificarEstado,
        private readonly OrdenCompraRepositorioInterface $ordenCompraRepositorio,
    ) {}

    public function ejecutar(OrdenCompra $orden): void
    {
        $totales = $this->verificarEstado->execute($orden);

        $totalOrdenado = $totales['total_ordenado'];
        $totalRecibido = $totales['total_recibido'];

        if ($totalOrdenado > 0) {
            if ($totalRecibido >= $totalOrdenado) {
                $this->ordenCompraRepositorio->actualizarEstado($orden, EstadoOrdenCompra::Recibida);
            } elseif ($totalRecibido > 0.0) {
                $this->ordenCompraRepositorio->actualizarEstado($orden, EstadoOrdenCompra::Parcial);
            }
        }
    }
}
