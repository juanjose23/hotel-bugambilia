<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Shared;

interface ReporteRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function registrarAuditoria(array $datos): void;
}
