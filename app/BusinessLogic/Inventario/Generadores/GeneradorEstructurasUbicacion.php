<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Generadores;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\RecepcionItem;

class GeneradorEstructurasUbicacion
{
    /**
     * @return array<int, Ubicacion>
     */
    public function generar(
        RecepcionItem $item,
        ?int $parentId,
        string $prefijo,
        int $cantidad,
        int $niveles,
        int $posiciones
    ): array {
        $creadas = [];

        for ($u = 1; $u <= $cantidad; $u++) {
            $nombreEstructura = $cantidad > 1 ? "{$prefijo} {$u}" : $prefijo;

            $maxVal = Ubicacion::query()->where('padre_id', $parentId)->max('orden');
            $maxOrden = is_numeric($maxVal) ? (int) $maxVal : 0;

            $estructura = Ubicacion::query()->create([
                'nombre' => $nombreEstructura,
                'descripcion' => 'Estructura física convertida desde la recepción '.($item->recepcion ? $item->recepcion->codigo : 'N/A').' del producto: '.($item->producto ? $item->producto->nombre : 'N/A').'.',
                'tipo' => 'estante',
                'padre_id' => $parentId,
                'orden' => $maxOrden + 1,
                'estado' => 1,
            ]);

            $creadas[] = $estructura;

            for ($n = 1; $n <= $niveles; $n++) {
                $nivel = Ubicacion::query()->create([
                    'nombre' => "Nivel {$n}",
                    'descripcion' => "Nivel jerárquico físico de {$nombreEstructura}.",
                    'tipo' => 'nivel',
                    'padre_id' => $estructura->id,
                    'orden' => $n,
                    'estado' => 1,
                ]);

                $creadas[] = $nivel;

                for ($p = 1; $p <= $posiciones; $p++) {
                    $posicion = Ubicacion::query()->create([
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
    }
}
