<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\Schemas;

use App\Enums\Shared\EstadoGeneral;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlatoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del Plato')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('codigo')
                            ->label('Codigo'),

                        TextEntry::make('nombre')
                            ->label('Nombre'),

                        TextEntry::make('categoria.nombre')
                            ->label('Categoria'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (int $state): string => EstadoGeneral::tryFrom($state)?->label() ?? 'Desconocido')
                            ->color(fn (int $state): string => match ($state) {
                                1 => 'success',
                                0 => 'danger',
                                default => 'warning',
                            }),

                        TextEntry::make('web')
                            ->label('Visible en Web')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Si' : 'No'),

                        TextEntry::make('tiempo_preparacion')
                            ->label('Tiempo Prep.')
                            ->placeholder('No definido'),

                        TextEntry::make('receta.nombre')
                            ->label('Receta')
                            ->placeholder('Sin receta'),

                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Actualizado')
                            ->dateTime(),

                        TextEntry::make('descripcion')
                            ->label('Descripcion')
                            ->columnSpanFull(),

                        ImageEntry::make('imagenes.url')
                            ->label('Imagenes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
