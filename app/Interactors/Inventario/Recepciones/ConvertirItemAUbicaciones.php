<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Recepciones;

use App\BusinessLogic\Inventario\Generadores\GeneradorEstructurasUbicacion;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Queries\Compras\Recepciones\ObtenerRecepcionItemConProducto;
use Illuminate\Support\Facades\DB;

class ConvertirItemAUbicaciones
{
    public function __construct(
        private readonly GeneradorEstructurasUbicacion $generador,
        private readonly ObtenerRecepcionItemConProducto $obtenerRecepcionItem,
    ) {}

    /**
     * Convierte un ítem recibido en una jerarquía de sub-ubicaciones recursivas.
     *
     * @param array{
     *     recepcion_item_id: int,
     *     parent_id: int|null,
     *     nombre_prefijo: string,
     *     cantidad_a_convertir: int,
     *     niveles_por_unidad: int,
     *     posiciones_por_nivel: int
     * } $data
     * @return array<int, Ubicacion> Las ubicaciones creadas.
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $item = $this->obtenerRecepcionItem->ejecutar($data['recepcion_item_id']);

            return $this->generador->generar(
                item: $item,
                parentId: $data['parent_id'] ?? null,
                prefijo: $data['nombre_prefijo'],
                cantidad: (int) $data['cantidad_a_convertir'],
                niveles: (int) $data['niveles_por_unidad'],
                posiciones: (int) $data['posiciones_por_nivel']
            );
        });
    }
}
