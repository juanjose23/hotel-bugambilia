<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Models\Servicios\Servicio;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class ServicioSelect
{
    public static function make(string $column = 'servicio_id', bool $soloActivos = false): Select
    {
        return Select::make($column)
            ->label('Servicio')
            ->options(fn (): Collection => $soloActivos
                ? Servicio::activos()->orderBy('nombre')->pluck('nombre', 'id')
                : Servicio::orderBy('nombre')->pluck('nombre', 'id')
            )
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::WrenchScrewdriver);
    }
}
