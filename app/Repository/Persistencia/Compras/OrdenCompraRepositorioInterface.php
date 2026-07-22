<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\CotizacionItem;
use App\Repository\Models\Compras\OrdenCompra;
use Illuminate\Support\Collection;

interface OrdenCompraRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): OrdenCompra;

    /**
     * @param  Collection<int, CotizacionItem>  $itemsElegidos
     * @param  array{subtotal: float, impuestos: float, total: float}  $totales
     */
    public function crearConItems(
        Cotizacion $cotizacion,
        Collection $itemsElegidos,
        string $codigo,
        array $totales,
        string $notas
    ): OrdenCompra;

    public function actualizarEstado(OrdenCompra $orden, EstadoOrdenCompra $estado): void;
}
