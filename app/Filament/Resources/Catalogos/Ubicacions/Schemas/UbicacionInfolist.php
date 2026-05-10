<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Schemas;

use App\Enums\EstadoCatalogo;
use App\Enums\TipoUbicacion;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UbicacionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la ubicación')
                    ->schema([
                        TextEntry::make('padre.nombre')
                            ->label('Ubicación superior')
                            ->placeholder('Ninguna (raíz)')
                            ->hint(fn ($record) => $record->padre?->tipo_label),

                        TextEntry::make('tipo')
                            ->label('Tipo')
                            ->badge()
                            ->color(fn (string $state): string => TipoUbicacion::colorFor($state)),

                        TextEntry::make('nombre')
                            ->label('Nombre')
                            ->weight('bold'),

                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Sin descripción')
                            ->markdown(),

                        TextEntry::make('orden')
                            ->label('Orden')
                            ->numeric(),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn ($state): string => EstadoCatalogo::colorFor($state))
                            ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state)),
                    ])
                    ->columns(2),

                Section::make('Metadatos')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Actualizado')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('deleted_at')
                            ->label('Eliminado')
                            ->placeholder('—')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }
}
