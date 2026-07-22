<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Queries\Shared\ObtenerVariantesProductoQuery;
use Filament\Forms\Components\Select;

class ProductoVarianteSelect
{
    public static function make(string $column = 'producto_variante_id', string $productoField = 'producto_id'): Select
    {
        return Select::make($column)
            ->label('Variante')
            ->options(fn ($get): array => app(ObtenerVariantesProductoQuery::class)->ejecutar($get($productoField) ? (int) $get($productoField) : null))
            ->searchable()
            ->preload()
            ->native(false);
    }
}
