<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;

final readonly class CrearCarrito
{
    public function __construct(
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    public function execute(string $nombre, ?string $descripcion = null): Ubicacion
    {
        return $this->ejecutar($nombre, $descripcion);
    }

    public function ejecutar(string $nombre, ?string $descripcion = null): Ubicacion
    {
        return $this->limpiezaRepositorio->crearCarrito($nombre, $descripcion);
    }
}
