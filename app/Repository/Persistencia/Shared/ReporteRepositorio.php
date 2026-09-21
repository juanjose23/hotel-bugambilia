<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Shared;

use Illuminate\Support\Facades\DB;

final class ReporteRepositorio implements ReporteRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function registrarAuditoria(array $datos): void
    {
        DB::table('auditoria_reportes')->insert($datos);
    }
}
