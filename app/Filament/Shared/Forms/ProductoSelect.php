<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Support\CachedOptions;
use Filament\Forms\Components\Select;

class ProductoSelect
{
    public static function make(string $column = 'producto_id'): Select
    {
        return Select::make($column)
            ->label('Producto')
            ->options(fn () => CachedOptions::productos())
            ->searchable()
            ->preload()
            ->native(false);
    }
}
