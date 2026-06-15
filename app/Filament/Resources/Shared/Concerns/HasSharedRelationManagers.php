<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared\Concerns;

use App\Filament\Resources\Shared\InventarioFijoRelationManager;
use App\Filament\Resources\Shared\PreciosRelationManager;
use App\Filament\Resources\Shared\ServiciosRelationManager;
use App\Filament\Resources\Shared\StocksRelationManager;

trait HasSharedRelationManagers
{
    protected static function getSharedRelationManagers(): array
    {
        return [
            ServiciosRelationManager::class,
            PreciosRelationManager::class,
            StocksRelationManager::class,
            InventarioFijoRelationManager::class,
        ];
    }
}
