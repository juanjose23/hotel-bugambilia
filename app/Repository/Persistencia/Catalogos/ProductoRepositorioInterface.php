<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Catalogos;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;

interface ProductoRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $atributos
     * @param  array<string, mixed>  $valores
     */
    public function firstOrCreate(array $atributos, array $valores = []): Producto;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearVariante(Producto $producto, array $datos): ProductoVariante;

    public function buscarPorId(int $id): ?Producto;

    public function buscarVariantePorId(int $id): ?ProductoVariante;
}
