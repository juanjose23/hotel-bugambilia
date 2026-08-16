<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Enums\Catalogos\CatalogoTipo;
use App\Support\CachedOptions;
use Filament\Forms\Components\Select;

final class CategoriaSelect
{
    public static function make(
        CatalogoTipo|string $tipo,
        string $column = 'categoria_id',
        string $label = 'Categoría',
    ): Select {
        $codigoTipo = $tipo instanceof CatalogoTipo ? $tipo->value : $tipo;

        return Select::make($column)
            ->label($label)
            ->options(fn () => CachedOptions::catalogos($codigoTipo))
            ->searchable()
            ->preload()
            ->native(false);
    }
}
