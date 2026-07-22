<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\PrefijoCodigo;

class PrefijoCodigoRepositorio implements PrefijoCodigoRepositorioInterface
{
    public function generarSiguienteCodigo(string $prefijo): string
    {
        $prefijoModel = PrefijoCodigo::lockForUpdate()->firstOrCreate(
            ['prefijo' => $prefijo],
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
