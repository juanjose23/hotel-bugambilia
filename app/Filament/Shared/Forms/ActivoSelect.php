<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Models\Activos\Activo;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class ActivoSelect
{
    public static function make(string $column = 'activo_id', bool $soloActivos = false): Select
    {
        return Select::make($column)
            ->label('Activo')
            ->options(fn (): Collection => $soloActivos
                ? Activo::where('estado', '!=', 3)->orderBy('nombre_descriptivo')->pluck('nombre_descriptivo', 'id')
                : Activo::orderBy('nombre_descriptivo')->pluck('nombre_descriptivo', 'id')
            )
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::CpuChip);
    }
}
