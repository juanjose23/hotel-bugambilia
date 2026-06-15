<?php

declare(strict_types=1);

namespace App\Filament\Resources\Servicios\Servicios\RelationManagers;

use App\Filament\Resources\Shared\Concerns\HasPreciosForm;
use App\Models\Servicios\ServiciosPrecio;
use Filament\Resources\RelationManagers\RelationManager;

class PreciosRelationManager extends RelationManager
{
    use HasPreciosForm;

    protected static string $relationship = 'precios';

    protected static ?string $title = 'Histórico de Precios';

    protected static ?string $label = 'Precio';

    protected static ?string $pluralLabel = 'Precios';

    protected function getPriceableModelClass(): string
    {
        return ServiciosPrecio::class;
    }

    protected function getPriceableForeignKey(): string
    {
        return 'servicio_id';
    }

    protected function getPriceableForeignType(): ?string
    {
        return null;
    }

    protected function hasTipoPrecioField(): bool
    {
        return false;
    }

    protected function getDefaultMonedaId(): ?int
    {
        return 1;
    }
}
