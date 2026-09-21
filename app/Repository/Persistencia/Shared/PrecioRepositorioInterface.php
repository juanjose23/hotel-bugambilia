<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Shared;

use App\Repository\Models\Shared\Precio;

interface PrecioRepositorioInterface
{
    public function expirarPreciosAnterioresSiCorresponde(
        string $priceableType,
        int $priceableId,
        int $monedaId,
        string $tipoPrecio,
        int $estado,
        bool $esOferta,
    ): void;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Precio;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Precio $precio, array $datos): Precio;
}
