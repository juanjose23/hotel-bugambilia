<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Shared;

use App\Repository\Models\Audits\AuditoriaJob;

final class AuditoriaJobRepositorio implements AuditoriaJobRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function registrarInicio(array $datos): AuditoriaJob
    {
        return AuditoriaJob::query()->create($datos);
    }

    /** @param array<string, mixed> $datos */
    public function actualizarEstado(AuditoriaJob $registro, array $datos): AuditoriaJob
    {
        $registro->update($datos);

        return $registro->fresh() ?? $registro;
    }
}
