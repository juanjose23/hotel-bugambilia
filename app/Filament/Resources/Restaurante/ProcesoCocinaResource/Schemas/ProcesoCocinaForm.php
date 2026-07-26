<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Schemas;

use App\Repository\Queries\Restaurante\Cocina\ObtenerDatosProcesoCocinaQuery;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ProcesoCocinaForm
{
    public static function configure(Schema $schema): Schema
    {
        $cocinaQuery = app(ObtenerDatosProcesoCocinaQuery::class);

        return $schema
            ->components([
                Section::make('Receta del Plato')
                    ->columns(2)
                    ->schema([
                        Select::make('plato_id')
                            ->label('Plato')
                            ->options(fn () => $cocinaQuery->platosConReceta())
                            ->searchable()
                            ->required(),
                        TextInput::make('cantidad_platos')
                            ->label('Cantidad de Platos')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ]),
                Section::make('Ingredientes')->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('producto_destino_id')
                                ->label('Ingrediente')
                                ->options(fn () => $cocinaQuery->productosDisponibles())
                                ->searchable()
                                ->required()
                                ->columnSpan(4),
                            TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->default(1)
                                ->columnSpan(2),
                            TextInput::make('peso_unitario')
                                ->label('Peso Unit.')
                                ->numeric()
                                ->columnSpan(2),
                            TextInput::make('peso_total')
                                ->label('Peso Total')
                                ->numeric()
                                ->columnSpan(2),
                            Toggle::make('es_merma')
                                ->label('Es Merma')
                                ->columnSpan(1),
                            TextInput::make('costo_asignado')
                                ->label('Costo C$')
                                ->numeric()
                                ->disabled()
                                ->columnSpan(2),
                        ])
                        ->columns(12)
                        ->defaultItems(0)
                        ->addActionLabel('Agregar Ingrediente'),
                ]),
            ]);
    }
}
