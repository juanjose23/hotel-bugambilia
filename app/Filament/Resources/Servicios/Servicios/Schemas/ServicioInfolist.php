<?php

namespace App\Filament\Resources\Servicios\Servicios\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Servicios\Servicio;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServicioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del Servicio')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('codigo')
                            ->label('Código')
                            ->placeholder('-'),

                        TextEntry::make('nombre')
                            ->label('Nombre')
                            ->placeholder('-'),

                        TextEntry::make('categoria.nombre')
                            ->label('Categoría')
                            ->placeholder('-'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn ($state): ?string => is_string($color = EstadoGeneral::colorFor($state)) ? $color : null)
                            ->formatStateUsing(fn ($state): string => EstadoGeneral::labelFor($state)),

                        TextEntry::make('icono')
                            ->label('Icono Representativo')
                            ->placeholder('Ninguno')
                            ->icon(fn ($state) => $state)
                            ->formatStateUsing(fn ($state) => $state ? ucwords(str_replace(['heroicon-o-', '-'], ['', ' '], $state)) : 'Ninguno'),

                        ...TimestampsInfolistEntry::make(),

                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Sin descripción.')
                            ->columnSpanFull(),

                        ImageEntry::make('imagenes.url')
                            ->label('Galería de Imágenes')
                            ->placeholder('Sin imágenes registradas.')
                            ->columnSpanFull(),

                        TextEntry::make('deleted_at')
                            ->label('Fecha de Eliminación')
                            ->dateTime()
                            ->visible(fn (Servicio $record): bool => $record->trashed())
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
