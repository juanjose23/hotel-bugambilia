<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDatosMedicos\Schemas;

use App\Enums\EstadoCatalogo;
use App\UseCases\Colaboradores\ObtenerNombreCompleto;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ColaboradorDatosMedicosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información de Salud')
                ->description('Datos médicos y condiciones especiales del colaborador.')
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
                        ->unique('colaborador_datos_medicos', 'colaborador_id', ignoreRecord: true)
                        ->prefixIcon(Heroicon::User)
                        ->helperText('Seleccione el colaborador. Solo se permite un registro médico por persona.')
                        ->columnSpanFull(),

                    TextInput::make('tipo_sangre')
                        ->label('Tipo de Sangre')
                        ->placeholder('Ej. O+, A-')
                        ->maxLength(5)
                        ->prefixIcon(Heroicon::Beaker)
                        ->helperText('Factor RH y grupo sanguíneo.'),

                    Select::make('estado')
                        ->label('Estado')
                        ->options(EstadoCatalogo::options())
                        ->default(EstadoCatalogo::Activo->value)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->prefixIcon(Heroicon::CheckCircle)
                        ->helperText('Vigencia de la información médica.'),

                    Textarea::make('alergias')
                        ->label('Alergias')
                        ->placeholder('Ej. Penicilina, Mariscos...')
                        ->rows(3)
                        ->helperText('Liste las alergias conocidas.')
                        ->columnSpanFull(),

                    Textarea::make('enfermedades_cronicas')
                        ->label('Condiciones Crónicas')
                        ->placeholder('Ej. Diabetes, Hipertensión...')
                        ->rows(3)
                        ->helperText('Enfermedades o condiciones de larga duración.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
