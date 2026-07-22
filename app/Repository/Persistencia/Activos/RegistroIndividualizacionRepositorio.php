<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\RegistroIndividualizacion;

class RegistroIndividualizacionRepositorio implements RegistroIndividualizacionRepositorioInterface
{
    public function buscarPorId(int $id): ?RegistroIndividualizacion
    {
        return RegistroIndividualizacion::find($id);
    }

    public function guardar(RegistroIndividualizacion $registro): void
    {
        $registro->save();
    }

    /** @param array<string, mixed> $datosDefault */
    public function buscarOrCreate(int $recepcionItemId, array $datosDefault): RegistroIndividualizacion
    {
        return RegistroIndividualizacion::lockForUpdate()->firstOrCreate(
            ['recepcion_item_id' => $recepcionItemId],
            $datosDefault
        );
    }
}
