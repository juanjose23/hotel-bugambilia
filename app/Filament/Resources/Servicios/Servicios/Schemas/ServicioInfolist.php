<?php

declare(strict_types=1);

namespace App\Filament\Resources\Servicios\Servicios\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Interactors\Servicios\ObtenerAforoServicio;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Queries\Servicios\ObtenerListadoHeroicons;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
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

                        TextEntry::make('aforo')
                            ->label('Aforo / Flota Fija')
                            ->placeholder('-')
                            ->state(fn (Servicio $record): int => app(ObtenerAforoServicio::class)->ejecutar((int) $record->id)),

                        TextEntry::make('icono')
                            ->label('Icono Representativo')
                            ->placeholder('Ninguno')
                            ->icon(fn (?string $state): string => ObtenerListadoHeroicons::resolverParaFilament($state))
                            ->formatStateUsing(fn (?string $state): string => $state ? ucwords(str_replace(['heroicon-o-', 'heroicon-s-', 'heroicon-m-', '-'], ['', '', '', ' '], $state)) : 'Ninguno'),

                        ...TimestampsInfolistEntry::make(),

                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Sin descripción.')
                            ->columnSpanFull(),

                        RepeatableEntry::make('imagenes')
                            ->label('Galería de Imágenes')
                            ->grid(['default' => 1, 'sm' => 2, 'md' => 3])
                            ->schema([
                                ImageEntry::make('url')
                                    ->hiddenLabel()
                                    ->state(fn (Imagen $record): string => $record->url_completa)
                                    ->imageHeight(160)
                                    ->columnSpanFull()
                                    ->extraImgAttributes([
                                        'class' => 'rounded-xl object-cover w-full shadow-sm border border-gray-200 dark:border-gray-800',
                                        'style' => 'width: 100%; height: 160px; object-fit: cover;',
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->placeholder('Sin imágenes registradas.'),

                        TextEntry::make('deleted_at')
                            ->label('Fecha de Eliminación')
                            ->dateTime()
                            ->visible(fn (Servicio $record): bool => $record->trashed())
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
