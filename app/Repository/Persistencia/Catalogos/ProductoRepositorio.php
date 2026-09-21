<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Catalogos;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;

final readonly class ProductoRepositorio implements ProductoRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $atributos
     * @param  array<string, mixed>  $valores
     */
    public function firstOrCreate(array $atributos, array $valores = []): Producto
    {
        return Producto::query()->firstOrCreate($atributos, $valores);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearVariante(Producto $producto, array $datos): ProductoVariante
    {
        /** @var ProductoVariante $variante */
        $variante = $producto->variantes()->create($datos);

        return $variante;
    }

    public function buscarPorId(int $id): ?Producto
    {
        return Producto::find($id);
    }

    public function buscarVariantePorId(int $id): ?ProductoVariante
    {
        return ProductoVariante::find($id);
    }
}
