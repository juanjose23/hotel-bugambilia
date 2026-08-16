<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource\Schemas;

use App\Repository\Queries\Shared\ObtenerColaboradoresLimpieza;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TurnoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del Turno')
                    ->description('Registre el nombre del bloque de turno, horarios y responsables.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del Turno')
                            ->placeholder('Ej: Turno Mañana A')
                            ->required()
                            ->maxLength(100)
                            ->prefixIcon(Heroicon::CheckBadge),

                        Toggle::make('estado')
                            ->label('Turno Activo')
                            ->default(true)
                            ->inline(false),

                        Select::make('lider_id')
                            ->label('Líder de Turno')
                            ->placeholder('Seleccione el líder')
                            ->options(ObtenerColaboradoresLimpieza::opciones())
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::User),

                        Select::make('apoyo_id')
                            ->label('Colaborador de Apoyo')
                            ->placeholder('Seleccione el apoyo (opcional)')
                            ->options(ObtenerColaboradoresLimpieza::opciones())
                            ->searchable()
                            ->native(false)
                            ->prefixIcon(Heroicon::UserGroup),

                        Select::make('carritos')
                            ->label('Carritos / Bodegas de Limpieza')
                            ->placeholder('Seleccione los carritos o bodegas')
                            ->relationship('carritos', 'nombre')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->prefixIcon(Heroicon::ShoppingBag),

                        TimePicker::make('hora_inicio')
                            ->label('Hora de Inicio')
                            ->required()
                            ->prefixIcon(Heroicon::Clock),

                        TimePicker::make('hora_fin')
                            ->label('Hora de Fin')
                            ->required()
                            ->prefixIcon(Heroicon::Clock),
                    ]),
            ]);
    }
}
