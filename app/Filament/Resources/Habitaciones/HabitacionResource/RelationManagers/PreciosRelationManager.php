<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers;

use App\Filament\Resources\Shared\Concerns\HasPreciosForm;
use App\Models\Habitaciones\PrecioHabitacion;
use App\Models\Monedas\Moneda;
use Filament\Resources\RelationManagers\RelationManager;

class PreciosRelationManager extends RelationManager
{
    use HasPreciosForm;

    protected static string $relationship = 'precioshabitacion';

    protected static ?string $title = 'Precios de Habitación';

    protected static ?string $label = 'Precio';

    protected static ?string $pluralLabel = 'Precios';

    protected function getPriceableModelClass(): string
    {
        return PrecioHabitacion::class;
    }

    protected function getPriceableForeignKey(): string
    {
        return 'habitacion_id';
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
        return Moneda::query()
            ->where('codigo', 'NIO')
            ->value('id')
            ?? Moneda::query()
                ->where('es_predeterminada', true)
                ->value('id');
    }

    protected function getUniquePrecioErrorMessage(): string
    {
        return 'Ya existe un precio vigente activo para esta habitación y esta moneda. Desactive el precio anterior antes de guardar.';
    }
}
