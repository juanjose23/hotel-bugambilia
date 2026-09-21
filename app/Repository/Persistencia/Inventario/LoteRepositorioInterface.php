<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\Lote;

interface LoteRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Lote;

    public function guardar(Lote $lote): void;

    /** @param array<string, mixed> $datos */
    public function actualizar(Lote $lote, array $datos): void;

    public function buscarPorId(int $id): ?Lote;

    public function procesarVencidosChunk(callable $callback, int $chunkSize = 200): void;

    public function procesarProximosAVencerChunk(int $dias, callable $callback, int $chunkSize = 200): void;

    /** @param array<int, int> $ids */
    public function marcarComoVencidos(array $ids): void;
}
