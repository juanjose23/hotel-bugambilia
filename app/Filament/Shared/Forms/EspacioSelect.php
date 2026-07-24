<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Models\Espacios\Espacio;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class EspacioSelect
{
    public static function make(
        string $column = 'espacio_id',
        bool $soloReservables = false,
        bool $soloActivos = false,
    ): Select {
        return Select::make($column)
            ->label('Espacio')
            ->options(fn (): Collection => self::obtenerOpciones($soloReservables, $soloActivos))
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::Map);
    }

    /**
     * @return Collection<int, string>
     */
    private static function obtenerOpciones(bool $soloReservables, bool $soloActivos): Collection
    {
        $query = Espacio::query();

        if ($soloReservables) {
            $query->where('reservable', true);
        }

        if ($soloActivos) {
            $query->where('estado', '!=', 0);
        }

        /** @var array<int, string> $data */
        $data = $query->orderBy('nombre')->pluck('nombre', 'id')->toArray();

        return collect($data);
    }
}
