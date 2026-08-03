<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

class EspacioSelect
{
    public static function make(
        string $column = 'espacio_id',
        bool $soloReservables = false,
        bool $soloActivos = false,
    ): Select {
        return Select::make($column)
            ->label('Espacio / Área del Hotel')
            ->placeholder('Seleccione espacio o área del hotel')
            ->options(fn (): array => self::obtenerOpciones($soloReservables, $soloActivos))
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::Map);
    }

    /**
     * @return array<int, string>
     */
    private static function obtenerOpciones(bool $soloReservables, bool $soloActivos): array
    {
        $query = Espacio::query()
            ->whereNotIn('tipo', [TipoEspacio::MESA->value]);

        if ($soloReservables) {
            $query->where('reservable', true);
        }

        if ($soloActivos) {
            $query->where('estado', '!=', 0);
        }

        $espacios = $query->with('padre')->orderBy('nombre')->get();
        $opciones = [];

        foreach ($espacios as $espacio) {
            $label = $espacio->getNombreCompleto();
            if ($espacio->capacidad_personas > 0) {
                $label .= " (Cap: {$espacio->capacidad_personas} pers.)";
            }
            $opciones[$espacio->id] = $label;
        }

        return $opciones;
    }
}
