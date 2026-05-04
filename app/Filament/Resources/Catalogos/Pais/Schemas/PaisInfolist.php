<?php

namespace App\Filament\Resources\Catalogos\Pais\Schemas;

use App\Enums\EstadoCatalogo;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class PaisInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información General')
                ->description('Detalles oficiales del país y códigos ISO.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            ImageEntry::make('codigo_iso2')
                                ->label('Bandera')
                                ->getStateUsing(fn($record) => asset('banderas/128x96/' . strtolower($record->codigo_iso2) . '.png'))
                                ->extraImgAttributes(['class' => 'rounded shadow-sm']),
                            TextEntry::make('nombre')
                                ->label('Nombre del País')
                                ->weight(FontWeight::Bold)
                                ->color('primary'),
                            TextEntry::make('estado')
                                ->badge()
                                ->color(fn($state): string => EstadoCatalogo::colorFor($state))
                                ->formatStateUsing(fn($state): string => EstadoCatalogo::labelFor($state)),
                        ]),
                ]),

            Section::make('Códigos y Telefonía')
                ->compact()
                ->columns(3)
                ->schema([
                    TextEntry::make('codigo_iso2')
                        ->label('Código ISO 2')
                        ->copyable()
                        ->badge(),

                    TextEntry::make('codigo_iso3')
                        ->label('Código ISO 3')
                        ->copyable()
                        ->badge(),

                    TextEntry::make('codigo_telefono')
                        ->label('Prefijo Telefónico')
                        ->icon('heroicon-m-phone')
                        ->placeholder('No asignado')
                        ->prefix('+'),
                ]),

            Section::make('Auditoría')
                ->description('Fechas de registro en el sistema')
                ->collapsed()
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Fecha de Creación')
                        ->dateTime('d/m/Y H:i'),

                    TextEntry::make('updated_at')
                        ->label('Última Actualización')
                        ->dateTime('d/m/Y H:i'),
                ]),
        ]);
    }
}