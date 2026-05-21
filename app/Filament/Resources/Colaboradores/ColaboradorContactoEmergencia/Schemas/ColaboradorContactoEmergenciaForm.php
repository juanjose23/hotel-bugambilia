<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\Schemas;

use App\Enums\EstadoCatalogo;
use App\UseCases\Colaboradores\Queries\ObtenerNombreCompleto;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ColaboradorContactoEmergenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contacto de Emergencia')
                ->description('Información de la persona a contactar en caso de necesidad.')
                ->columnSpanFull()
                ->schema([
                    Select::make('colaborador_id')
                        ->relationship('colaborador', 'id')
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => app(ObtenerNombreCompleto::class)
                                ->nombreCompletoConCodigo($record)
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->prefixIcon(Heroicon::User)
                        ->helperText('Seleccione el colaborador.')
                        ->columnSpanFull(),

                    TextInput::make('nombre')
                        ->label('Nombre Completo')
                        ->placeholder('Ej. Juan Pérez')
                        ->required()
                        ->maxLength(150)
                        ->prefixIcon(Heroicon::Identification)
                        ->helperText('Nombre de la persona de contacto.'),

                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->placeholder('Ej. 8888-8888')
                        ->required()
                        ->maxLength(20)
                        ->prefixIcon(Heroicon::Phone)
                        ->helperText('Número telefónico de contacto.'),

                    TextInput::make('parentesco')
                        ->label('Parentesco')
                        ->placeholder('Ej. Padre, Madre, Cónyuge')
                        ->maxLength(50)
                        ->prefixIcon(Heroicon::UserGroup)
                        ->helperText('Relación con el colaborador.'),

                    Select::make('estado')
                        ->label('Estado')
                        ->options(EstadoCatalogo::options())
                        ->default(EstadoCatalogo::Activo->value)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->prefixIcon(Heroicon::CheckCircle)
                        ->helperText('Vigencia del contacto.'),
                ])->columns(2),
        ]);
    }
}
