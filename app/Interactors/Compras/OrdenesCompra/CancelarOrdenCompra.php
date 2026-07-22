<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\BusinessLogic\Compras\ValidarCancelacionOrden;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Events\Compras\OrdenCancelada;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;
use App\Repository\Queries\Compras\Recepciones\VerificarRecepcionesOrden;

final class CancelarOrdenCompra
{
    public function __construct(
        private readonly OrdenCompraRepositorioInterface $ordenCompraRepositorio,
        private readonly VerificarRecepcionesOrden $verificarRecepciones,
        private readonly ValidarCancelacionOrden $validarCancelacion,
    ) {}

    public function ejecutar(OrdenCompra $orden): void
    {
        $tieneRecepciones = $this->verificarRecepciones->ejecutar($orden->id);

        $this->validarCancelacion->validar($tieneRecepciones);

        $this->ordenCompraRepositorio->actualizarEstado($orden, EstadoOrdenCompra::Cancelada);

        OrdenCancelada::dispatch($orden);
    }
}
