<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms\Limpieza;

use App\Repository\Queries\Limpieza\Carrito\ObtenerOpcionesBodegasLimpieza;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

final class BodegaLimpiezaSelect
{
    public static function make(string $column, string $label): Select
    {
        return Select::make($column)
            ->label($label)
            ->placeholder('Seleccione bodega')
            ->options(fn (): array => app(ObtenerOpcionesBodegasLimpieza::class)->execute())
            ->required()
            ->native(false)
            ->prefixIcon(Heroicon::Home);
    }
}
