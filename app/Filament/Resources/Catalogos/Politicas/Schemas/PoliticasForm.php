<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogos\Politicas\Schemas;

use App\Enums\Politicas\UnidadAnticipacion;
use App\Enums\Shared\EstadoGeneral;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PoliticasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Política')
                    ->description('Define el título, estado y la descripción general de la política.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Título')
                            ->placeholder('Ej. Política de Cancelación')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoGeneral::options())
                            ->default(EstadoGeneral::Activo->value)
                            ->required(),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Describe el detalle de la política...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('aplica_penalizacion')
                            ->label('Aplica Reglas Ejecutables de Penalización')
                            ->helperText('Active esta opción si esta política calcula penalizaciones financieras por cancelación o no-show.')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),
                    ]),

                Section::make('Rangos de Penalización por Cancelación')
                    ->description('Defina los rangos de anticipación y los porcentajes de cobro por cancelación o no-show.')
                    ->columnSpanFull()
                    ->hidden(fn (callable $get) => ! $get('aplica_penalizacion'))
                    ->schema([
                        Repeater::make('penalizaciones')
                            ->relationship('penalizaciones')
                            ->label('Reglas por Anticipación')
                            ->columns(6)
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->orderColumn('orden')
                            ->schema([
                                TextInput::make('min_unidades')
                                    ->label('Mínimo')
                                    ->numeric()
                                    ->placeholder('0')
                                    ->nullable(),

                                TextInput::make('max_unidades')
                                    ->label('Máximo')
                                    ->numeric()
                                    ->placeholder('30')
                                    ->nullable(),

                                Select::make('unidad')
                                    ->label('Unidad')
                                    ->options(UnidadAnticipacion::options())
                                    ->default(UnidadAnticipacion::DIAS->value)
                                    ->required(),

                                TextInput::make('porcentaje')
                                    ->label('% Cobro')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(100)
                                    ->required(),

                                Toggle::make('aplica_no_show')
                                    ->label('Aplica No-Show')
                                    ->default(false),

                                TextInput::make('orden')
                                    ->label('Orden')
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }
}
