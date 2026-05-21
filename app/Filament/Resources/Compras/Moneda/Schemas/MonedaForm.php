<?php

namespace App\Filament\Resources\Compras\Moneda\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MonedaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información de la Moneda')
                ->description('Defina el código, nombre y símbolo de la divisa.')
                ->columns(2)
                ->schema([
                    TextInput::make('codigo')
                        ->label('Código ISO')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(3)
                        ->placeholder('Ej. USD, NIO'),

                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Ej. Dólar Estadounidense'),

                    TextInput::make('simbolo')
                        ->label('Símbolo')
                        ->required()
                        ->maxLength(10)
                        ->placeholder('Ej. $, C$'),

                    Toggle::make('es_predeterminada')
                        ->label('Moneda Predeterminada')
                        ->helperText('Indica si es la moneda base de operación del hotel.')
                        ->default(false),
                ]),
        ]);
    }
}
