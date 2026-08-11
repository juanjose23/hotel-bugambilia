<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Comun;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class ResumenFinancieroPreviewSeccion
{
    public static function make(): Section
    {
        return Section::make('Resumen en vivo')
            ->icon(Heroicon::Banknotes)
            ->description('Se actualiza al completar habitación, mesa, fechas y servicios.')
            ->columnSpan(['default' => 1, 'xl' => 1])
            ->extraAttributes([
                'class' => 'xl:sticky xl:top-4 xl:self-start',
            ])
            ->schema([
                TextEntry::make('resumen_financiero_lateral')
                    ->hiddenLabel()
                    ->helperText(fn (Get $get) => ResumenFinancieroVistaPrevia::html($get, compacto: true))
                    ->columnSpanFull(),
            ]);
    }
}
