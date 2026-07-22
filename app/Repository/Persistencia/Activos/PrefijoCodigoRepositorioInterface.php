<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

interface PrefijoCodigoRepositorioInterface
{
    public function generarSiguienteCodigo(string $prefijo): string;
}
