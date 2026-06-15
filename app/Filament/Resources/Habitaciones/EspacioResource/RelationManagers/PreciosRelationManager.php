<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers;

use App\Filament\Resources\Shared\Concerns\HasPreciosForm;
use App\Models\Espacios\PrecioEspacio;
use App\Models\Monedas\Moneda;
use Filament\Resources\RelationManagers\RelationManager;

class PreciosRelationManager extends RelationManager
{
    use HasPreciosForm;

    protected static string $relationship = 'precios';

    protected static ?string $title = 'Precios de Espacio';

    protected static ?string $label = 'Precio';

    protected static ?string $pluralLabel = 'Precios';

    protected function getPriceableModelClass(): string
    {
        return PrecioEspacio::class;
    }

    protected function getPriceableForeignKey(): string
    {
        return 'espacio_id';
    }

    protected function getPriceableForeignType(): ?string
    {
        return null;
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
        return 'Ya existe un precio o tarifa vigente activa para este espacio, esta moneda y este concepto. Desactive el precio anterior antes de guardar.';
    }
}
