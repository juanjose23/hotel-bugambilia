<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Schemas;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Enums\Catalogos\TipoUbicacion;
use App\Filament\Resources\Shared\InfolistTimestamps;
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
                            ->color(fn (string $state): ?string => is_string($color = TipoUbicacion::colorFor($state)) ? $color : null),

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
                            ->color(fn ($state): ?string => is_string($color = EstadoCatalogo::colorFor($state)) ? $color : null)
                            ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state)),
                    ])
                    ->columns(2),

                Section::make('Metadatos')
                    ->schema([
                        ...InfolistTimestamps::make(format: 'd/m/Y H:i'),
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
