<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Queries\Shared\ObtenerOpcionesColaborador;
use Filament\Forms\Components\Select;

class ColaboradorSelect
{
    public static function make(string $column = 'colaborador_id'): Select
    {

        return Select::make($column)
            ->label('Colaborador')
            ->options(fn (ObtenerOpcionesColaborador $query): array => $query->ejecutar())
            ->searchable()
            ->preload()
            ->native(false);
    }
}
