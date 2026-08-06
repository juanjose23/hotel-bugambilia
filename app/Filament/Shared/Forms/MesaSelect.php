<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

class MesaSelect
{
    public static function make(
        string $column = 'espacio_id',
        bool $soloReservables = false,
        bool $soloActivos = true,
        bool $incluirRestaurante = true,
        ?string $dusk = null,
    ): Select {
        $select = Select::make($column)
            ->label('Mesa / Espacio del Restaurante')
            ->placeholder('Seleccione mesa o área del restaurante')
            ->options(fn (): array => self::obtenerOpciones($soloReservables, $soloActivos, $incluirRestaurante))
            ->searchable()
            ->preload()
            ->native(false)
            ->prefixIcon(Heroicon::UserGroup);

        if ($dusk !== null) {
            $select->extraAttributes(['dusk' => $dusk]);
        }

        return $select;
    }

    /**
     * @return array<int, string>
     */
    private static function obtenerOpciones(bool $soloReservables, bool $soloActivos, bool $incluirRestaurante): array
    {
        $query = Espacio::query()
            ->whereIn('tipo', $incluirRestaurante
                ? [TipoEspacio::MESA->value, TipoEspacio::RESTAURANTE->value]
                : [TipoEspacio::MESA->value]);

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
