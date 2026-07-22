<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\RegistroIndividualizacion;

interface RegistroIndividualizacionRepositorioInterface
{
    public function buscarPorId(int $id): ?RegistroIndividualizacion;

    public function guardar(RegistroIndividualizacion $registro): void;

    /** @param array<string, mixed> $datosDefault */
    public function buscarOrCreate(int $recepcionItemId, array $datosDefault): RegistroIndividualizacion;
}
