<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared;

use App\Filament\Resources\Shared\Concerns\HasPreciosForm;
use App\Models\Monedas\Moneda;
use App\Models\Shared\Precio;
use Filament\Resources\RelationManagers\RelationManager;

class PreciosRelationManager extends RelationManager
{
    use HasPreciosForm;

    protected static string $relationship = 'precioEntries';

    protected static ?string $title = 'Precios';

    protected static ?string $label = 'Precio';

    protected static ?string $pluralLabel = 'Precios';

    protected function getPriceableModelClass(): string
    {
        return Precio::class;
    }

    protected function getPriceableForeignKey(): string
    {
        return 'priceable_id';
    }

    protected function getPriceableForeignType(): ?string
    {
        return 'priceable_type';
    }

    protected function hasTipoPrecioField(): bool
    {
        return true;
    }

    protected function getDefaultMonedaId(): ?int
    {
        return Moneda::query()
            ->where('codigo', 'NIO')
            ->value('id')
            ?? Moneda::query()
                ->where('es_predeterminada', true)
                ->value('id');
    }

    protected function getUniquePrecioErrorMessage(): string
    {
        return 'Ya existe un precio o tarifa vigente activa para este registro, esta moneda y este concepto. Desactive el precio anterior antes de guardar.';
    }
}
