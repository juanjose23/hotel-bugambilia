<?php

declare(strict_types=1);

namespace App\Filament\Shared\Columns;

use Filament\Tables\Columns\TextColumn;

class FechaStandardColumn
{
    public static function make(string $column = 'created_at', string $label = 'Creado'): TextColumn
    {
        return TextColumn::make($column)
            ->label($label)
            ->dateTime('d/m/Y H:i')
            ->sortable();
    }
}
