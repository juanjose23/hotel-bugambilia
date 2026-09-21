<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoRecepcion;
use App\Repository\Models\Compras\OrdenCompraItem;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\RecepcionItem;

final class RecepcionRepositorio implements RecepcionRepositorioInterface
{
    public function actualizarEstado(RecepcionCompra $recepcion, EstadoRecepcion $estado): void
    {
        $recepcion->update(['estado' => $estado]);
    }

    public function buscarItemPorId(int $id): ?RecepcionItem
    {
        return RecepcionItem::with(['ordenItem.ordenCompra', 'variante'])->find($id);
    }

    public function buscarOrdenCompraItemConSumaRecepcion(int $id): ?OrdenCompraItem
    {
        return OrdenCompraItem::withSum('recepcionItems', 'cantidad_recibida')
            ->with('producto')
            ->find($id);
    }

    public function obtenerUltimoCodigoPorAno(int $year): ?string
    {
        $ultimo = RecepcionCompra::withTrashed()
            ->where('codigo', 'like', "REC-{$year}-%")
            ->orderBy('codigo', 'desc')
            ->lockForUpdate()
            ->get()
            ->first(fn ($rec) => (bool) preg_match('/\-(\d+)$/', $rec->codigo));

        return $ultimo?->codigo;
    }
}
