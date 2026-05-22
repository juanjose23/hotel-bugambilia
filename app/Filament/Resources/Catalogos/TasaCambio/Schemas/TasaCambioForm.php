<?php

namespace App\Filament\Resources\Catalogos\TasaCambio\Schemas;

use App\Models\Monedas\Moneda;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TasaCambioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información de la Tasa de Cambio')
                ->description('Registre la tasa de cambio diaria de dólares a córdobas.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            DatePicker::make('fecha')
                                ->label('Fecha')
                                ->required()
                                ->default(now())
                                ->native(false)
                                ->displayFormat('d/m/Y'),

                            TextInput::make('tasa')
                                ->label('Tasa de Cambio')
                                ->required()
                                ->numeric()
                                ->minValue(0.0001)
                                ->placeholder('Ej. 36.5200')
                                ->suffix('C$')
                                ->extraInputAttributes(['step' => '0.0001']),

                            Toggle::make('es_fija')
                                ->label('Tasa Fija / Respaldo')
                                ->default(false)
                                ->inline(false)
                                ->helperText('Usa esta tasa si no hay otra registrada para el día.'),
                        ]),

                    Grid::make(2)
                        ->schema([
                            Select::make('moneda_origen_id')
                                ->label('Moneda de Origen')
                                ->options(Moneda::pluck('nombre', 'id'))
                                ->required()
                                ->default(fn () => Moneda::where('codigo', 'USD')->first()?->id)
                                ->searchable()
                                ->preload()
                                ->live(),

                            Select::make('moneda_destino_id')
                                ->label('Moneda de Destino')
                                ->options(fn ($get) => Moneda::where('id', '!=', $get('moneda_origen_id'))
                                    ->pluck('nombre', 'id')
                                )
                                ->required()
                                ->default(fn () => Moneda::where('es_predeterminada', true)->first()?->id)
                                ->searchable()
                                ->preload(),
                        ]),
                ]),
        ]);
    }
}
