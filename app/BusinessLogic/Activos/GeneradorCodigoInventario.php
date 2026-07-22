<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Repository\Models\Activos\PrefijoCodigo;
use App\Repository\Models\Catalogos\Producto;

class GeneradorCodigoInventario
{
    public function generar(Producto $producto): string
    {
        $prefijoStr = 'ACT';
        $nombreProducto = strtolower($producto->nombre ?? '');

        if (str_contains($nombreProducto, 'tv') || str_contains($nombreProducto, 'tele')) {
            $prefijoStr = 'TV';
        } elseif (str_contains($nombreProducto, 'aire') || str_contains($nombreProducto, 'ac') || str_contains($nombreProducto, 'clima')) {
            $prefijoStr = 'AC';
        } elseif (str_contains($nombreProducto, 'cama') || str_contains($nombreProducto, 'colch')) {
            $prefijoStr = 'CAM';
        }

        $prefijoModel = PrefijoCodigo::lockForUpdate()->firstOrCreate(
            ['prefijo' => $prefijoStr],
            ['ultimo_numero' => 0]
        );
        $prefijoModel->ultimo_numero++;
        $prefijoModel->save();

        return sprintf(
            '%s-%s-%s',
            $prefijoModel->prefijo,
            now()->format('Y'),
            str_pad((string) $prefijoModel->ultimo_numero, 4, '0', STR_PAD_LEFT)
        );
    }
}
