<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Cocina;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;

/**
 * Base compartida para los seeders de cocina del Restaurante.
 */
abstract class BaseCocinaSeeder extends Seeder
{
    protected function variantePorNombreProducto(string $nombreProducto): ?ProductoVariante
    {
        $variante = ProductoVariante::query()
            ->whereHas('producto', static fn ($query) => $query->where('nombre', $nombreProducto))
            ->orderBy('id')
            ->first();

        return $variante instanceof ProductoVariante ? $variante : null;
    }

    protected function usuarioId(): ?int
    {
        $usuario = User::query()->orderBy('id')->first();

        return $usuario instanceof User ? $usuario->id : null;
    }
}
