<?php

declare(strict_types=1);

namespace App\Filament\Shared\Filters;

use Filament\Tables\Filters\SelectFilter;

class FiltroEstado
{
    public static function make(string $enumClass, string $column = 'estado'): SelectFilter
    {
        $filter = SelectFilter::make($column)
            ->options($enumClass::options())
            ->label('Estado');

        return $filter;
    }
}
