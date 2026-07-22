<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\Lote;

interface LoteRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Lote;

    public function guardar(Lote $lote): void;

    public function buscarPorId(int $id): ?Lote;
}
