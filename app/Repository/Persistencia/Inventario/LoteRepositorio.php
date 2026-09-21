<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;

final class LoteRepositorio implements LoteRepositorioInterface
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

    /** @param array<string, mixed> $datos */
    public function actualizar(Lote $lote, array $datos): void
    {
        $lote->update($datos);
    }

    public function buscarPorId(int $id): ?Lote
    {
        return Lote::query()->find($id);
    }

    public function procesarVencidosChunk(callable $callback, int $chunkSize = 200): void
    {
        Lote::query()
            ->whereIn('estado', [EstadoLote::Disponible, EstadoLote::Cuarentena])
            ->where('cantidad_disponible', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now()->toDateString())
            ->chunkById($chunkSize, $callback);
    }

    public function procesarProximosAVencerChunk(int $dias, callable $callback, int $chunkSize = 200): void
    {
        Lote::query()
            ->where('estado', EstadoLote::Disponible)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString())
            ->chunkById($chunkSize, $callback);
    }

    /** @param array<int, int> $ids */
    public function marcarComoVencidos(array $ids): void
    {
        Lote::query()->whereIn('id', $ids)->update([
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 0,
        ]);
    }
}
