<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Support\CachedOptions;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

class ProveedorSelect
{
    public static function make(string $column = 'proveedor_id'): Select
    {
        return Select::make($column)
            ->label('Proveedor')
            ->options(fn (): array => app(CachedOptions::class)->proveedores()->toArray())
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::BuildingOffice2);
    }
}
