<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Support\CachedOptions;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

class MonedaSelect
{
    public static function make(string $column = 'moneda_id'): Select
    {
        return Select::make($column)
            ->label('Moneda')
            ->options(fn () => CachedOptions::monedas())
            ->searchable()
            ->preload()
            ->prefixIcon(Heroicon::Banknotes)
            ->native(false);
    }
}
