<?php

declare(strict_types=1);

namespace App\Filament\Shared\Columns;

use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;

class ColaboradorNombreColumn
{
    public static function make(string $column = 'colaborador.nombre_completo'): TextColumn
    {
        return TextColumn::make($column)
            ->label('Colaborador')
            ->weight(FontWeight::Bold)
            ->sortable()
            ->searchable();
    }
}
