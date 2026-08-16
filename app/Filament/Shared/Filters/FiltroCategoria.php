<?php

declare(strict_types=1);

namespace App\Filament\Shared\Filters;

use App\Enums\Catalogos\CatalogoTipo;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

final class FiltroCategoria
{
    public static function make(
        CatalogoTipo|string $tipo,
        string $column = 'categoria_id',
        string $label = 'Categoría',
        string $relationship = 'categoria',
        bool $multiple = true,
    ): SelectFilter {
        $codigoTipo = $tipo instanceof CatalogoTipo ? $tipo->value : $tipo;

        return SelectFilter::make($column)
            ->label($label)
            ->relationship(
                name: $relationship,
                titleAttribute: 'nombre',
                modifyQueryUsing: fn (Builder $query): Builder => $query->whereHas(
                    'catalogoTipo',
                    fn (Builder $q): Builder => $q->where('codigo', $codigoTipo)
                )
            )
            ->multiple($multiple)
            ->searchable()
            ->preload();
    }
}
