<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\PrefijoCodigo\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrefijoCodigoForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detalles del Prefijo')
                ->description('Configura los prefijos de inventario para la generación de códigos correlativos automáticos.')
                ->schema([
                    TextInput::make('prefijo')
                        ->label('Prefijo')
                        ->required()
                        ->unique(ignorable: fn ($record) => $record)
                        ->maxLength(20)
                        ->placeholder('Ej. TV, AC, CAM'),

                    TextInput::make('ultimo_numero')
                        ->label('Último número correlativo')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->placeholder('Último número asignado'),
                ])
                ->columns(1),
        ]);
    }
}
