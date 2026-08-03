<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Comun;

use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class NotasReservaSeccion
{
    public static function make(): Section
    {
        return Section::make('Notas & Especificaciones')
            ->columnSpanFull()
            ->icon(Heroicon::DocumentText)
            ->schema([
                RichEditor::make('notas')
                    ->hiddenLabel()
                    ->placeholder('Indicaciones especiales del cliente, requerimientos dietéticos, solicitudes especiales...'),
            ]);
    }
}
