<?php

declare(strict_types=1);

namespace App\Notifications\Inventario;

use App\Filament\Resources\Inventario\InventarioFisico\InventarioFisicoResource;
use App\Filament\Resources\Inventario\Lote\LoteResource;
use App\Repository\Models\Inventario\InventarioFisico;
use App\Repository\Models\Inventario\Lote;

final class UrlNotificador
{
    public function lote(Lote $lote): string
    {
        return LoteResource::getUrl('view', ['record' => $lote->id]);
    }

    public function inventarioFisico(InventarioFisico $inventario): string
    {
        return InventarioFisicoResource::getUrl('view', ['record' => $inventario->id]);
    }
}
