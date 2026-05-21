<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Espacios;

use App\Models\Espacios\InventarioFijo;
use Illuminate\Support\Facades\DB;

class AsignarActivoAEspacio
{
    public function execute(
        string $espacioTipo,
        int $espacioId,
        int $productoId,
        ?int $varianteId,
        float $cantidad,
        int $usuarioId
    ): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        if (! in_array($espacioTipo, ['habitacion', 'area'], true)) {
            throw new \InvalidArgumentException('El tipo de espacio debe ser "habitacion" o "area".');
        }

        DB::transaction(function () use ($espacioTipo, $espacioId, $productoId, $varianteId, $cantidad) {
            InventarioFijo::updateOrCreate(
                [
                    'espacio_tipo' => $espacioTipo,
                    'espacio_id' => $espacioId,
                    'producto_id' => $productoId,
                    'producto_variante_id' => $varianteId,
                ],
                [
                    'cantidad' => $cantidad,
                    'estado' => 'operativo',
                ]
            );
        });
    }
}
