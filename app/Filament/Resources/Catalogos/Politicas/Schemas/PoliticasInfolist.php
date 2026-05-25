<?php

namespace App\Filament\Resources\Catalogos\Politicas\Schemas;

use App\Enums\EstadoCatalogo;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PoliticasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información de la Política')
                ->description('Detalles de la política registrada.')
                ->columns(2)
                ->schema([
                    TextEntry::make('titulo')
                        ->label('Título')
                        ->weight('bold')
                        ->color('primary'),

                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge()
                        ->color(fn ($state): string => EstadoCatalogo::colorFor($state))
                        ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state)),

                    TextEntry::make('descripcion')
                        ->label('Descripción')
                        ->placeholder('Sin descripción.')
                        ->columnSpanFull(),
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

                    TextEntry::make('deleted_at')
                        ->label('Fecha de Eliminación')
                        ->dateTime('d/m/Y H:i')
                        ->visible(fn ($record): bool => $record?->trashed() ?? false),
                ]),
        ]);
    }
}
