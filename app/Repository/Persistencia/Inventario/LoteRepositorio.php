<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\Lote;

class LoteRepositorio implements LoteRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Lote
    {
        return Lote::create($datos);
    }

    public function guardar(Lote $lote): void
    {
        $lote->save();
    }

    public function buscarPorId(int $id): ?Lote
    {
        return Lote::query()->find($id);
    }
}
