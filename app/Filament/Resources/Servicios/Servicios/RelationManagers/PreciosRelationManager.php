<?php

declare(strict_types=1);

namespace App\Filament\Resources\Servicios\Servicios\RelationManagers;

use App\Filament\Resources\Shared\Concerns\HasPreciosForm;
use App\Models\Monedas\Moneda;
use App\Models\Shared\Precio;
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
        return false;
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
        return 'Ya existe un precio vigente activo para este servicio y esta moneda. Desactive el precio anterior antes de guardar.';
    }
}
