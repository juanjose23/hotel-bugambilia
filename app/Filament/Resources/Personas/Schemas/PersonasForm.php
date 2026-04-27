<?php

namespace App\Filament\Resources\Personas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;


class PersonasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('primer_nombre')
                    ->label('Primer Nombre')
                    ->required()
                    ->maxLength(100),

                TextInput::make('segundo_nombre')
                    ->label('Segundo Nombre')
                    ->maxLength(100),

                Select::make('tipo_persona')
                    ->label('Tipo de Persona')
                    ->options([
                        'natural' => 'Natural',
                        'juridica' => 'Jurídica',
                    ])
                    ->required(),

                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20),

                Textarea::make('direccion')
                    ->label('Dirección')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}