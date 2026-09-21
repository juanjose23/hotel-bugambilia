<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Shared;

use App\Repository\Models\Audits\AuditoriaJob;

interface AuditoriaJobRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function registrarInicio(array $datos): AuditoriaJob;

    /** @param array<string, mixed> $datos */
    public function actualizarEstado(AuditoriaJob $registro, array $datos): AuditoriaJob;
}
