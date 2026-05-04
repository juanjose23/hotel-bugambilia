<?php

namespace App\Filament\Resources\Catalogos\Pais\Schemas;

use App\Enums\EstadoCatalogo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->description('Detalles oficiales del país y códigos ISO.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo_iso2')
                            ->label('Código ISO 2')
                            ->placeholder('CO')
                            ->minLength(2)
                            ->maxLength(2)
                            ->required(),
                        TextInput::make('codigo_iso3')
                            ->label('Código ISO 3')
                            ->placeholder('COL')
                            ->minLength(3)
                            ->maxLength(3)
                            ->required(),
                        TextInput::make('nombre')
                            ->label('Nombre del país')
                            ->maxLength(150)
                            ->required(),
                        TextInput::make('codigo_telefono')
                            ->label('Prefijo telefónico')
                            ->placeholder('57')
                            ->tel(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoCatalogo::options())
                            ->default(EstadoCatalogo::Activo->value)
                            ->required(),

                    ]),
            ]);
    }
}