<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared\Filters;

use Filament\Tables\Filters\SelectFilter;

class FiltroEstado
{
    public static function make(string $enumClass, string $column = 'estado'): SelectFilter
    {
        $filter = SelectFilter::make($column)
            ->label('Estado');

        if (method_exists($enumClass, 'options')) {
            $filter->options($enumClass::options());
        } else {
            $filter->options($enumClass);
        }

        return $filter;
    }
}
