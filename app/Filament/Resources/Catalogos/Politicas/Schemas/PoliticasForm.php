<?php

namespace App\Filament\Resources\Catalogos\Politicas\Schemas;

use App\Enums\Shared\EstadoGeneral;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PoliticasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Política')
                    ->description('Define el título y la descripción de la política.')
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
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
