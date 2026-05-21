<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Recepciones\Mutations;

use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\RecepcionItem;
use Illuminate\Support\Facades\DB;

class ConvertirItemAUbicaciones
{
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
            $item = RecepcionItem::findOrFail($data['recepcion_item_id']);
            $parentId = $data['parent_id'] ?? null;
            $prefijo = $data['nombre_prefijo'];
            $cantidad = (int) $data['cantidad_a_convertir'];
            $niveles = (int) $data['niveles_por_unidad'];
            $posiciones = (int) $data['posiciones_por_nivel'];

            $creadas = [];

            for ($u = 1; $u <= $cantidad; $u++) {
                // 1. Crear la estructura base (ej. Estante 1)
                $nombreEstructura = $cantidad > 1 ? "{$prefijo} {$u}" : $prefijo;

                // Obtener el orden correlativo máximo bajo este padre
                $maxOrden = Ubicacion::where('padre_id', $parentId)->max('orden') ?? 0;

                $estructura = Ubicacion::create([
                    'nombre' => $nombreEstructura,
                    'descripcion' => "Estructura física convertida desde la recepción {$item->recepcion->codigo} del producto: {$item->producto->nombre}.",
                    'tipo' => 'estante',
                    'padre_id' => $parentId,
                    'orden' => $maxOrden + 1,
                    'estado' => 1, // Activo
                ]);

                $creadas[] = $estructura;

                // 2. Crear los niveles
                for ($n = 1; $n <= $niveles; $n++) {
                    $nivel = Ubicacion::create([
                        'nombre' => "Nivel {$n}",
                        'descripcion' => "Nivel jerárquico físico de {$nombreEstructura}.",
                        'tipo' => 'nivel',
                        'padre_id' => $estructura->id,
                        'orden' => $n,
                        'estado' => 1,
                    ]);

                    $creadas[] = $nivel;

                    // 3. Crear las posiciones/compartimientos
                    for ($p = 1; $p <= $posiciones; $p++) {
                        $posicion = Ubicacion::create([
                            'nombre' => "Posición {$p}",
                            'descripcion' => "Compartimiento o posición física en {$nombreEstructura} > Nivel {$n}.",
                            'tipo' => 'posicion',
                            'padre_id' => $nivel->id,
                            'orden' => $p,
                            'estado' => 1,
                        ]);

                        $creadas[] = $posicion;
                    }
                }
            }

            return $creadas;
        });
    }
}
