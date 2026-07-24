<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\Reservas\TipoHuesped;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class FormularioHuesped
{
    /**
     * @return array<int, DatePicker|Select|TextInput|Toggle>
     */
    public static function make(int $columnSpan = 1): array
    {
        return [
            TextInput::make('nombre')
                ->label('Nombre completo')
                ->placeholder('Ej. María Pérez')
                ->required()
                ->maxLength(150)
                ->columnSpan($columnSpan),

            Select::make('tipo_identificacion')
                ->label('Tipo de identificación')
                ->options([
                    1 => 'Cédula',
                    2 => 'DNI',
                    3 => 'Pasaporte',
                    4 => 'Residencia',
                    5 => 'Otro',
                ])
                ->native(false)
                ->columnSpan($columnSpan),

            TextInput::make('identificacion')
                ->label('Número de identificación')
                ->placeholder('Ej. 001-010190-0001A')
                ->maxLength(100)
                ->columnSpan($columnSpan),

            Select::make('tipo_huesped')
                ->label('Tipo de huésped')
                ->options(TipoHuesped::class)
                ->default(TipoHuesped::ADULTO)
                ->required()
                ->native(false)
                ->columnSpan($columnSpan),

            Toggle::make('es_titular')
                ->label('Es el titular')
                ->default(false)
                ->columnSpan($columnSpan),

            DatePicker::make('fecha_nacimiento')
                ->label('Fecha de nacimiento')
                ->maxDate(now())
                ->columnSpan($columnSpan),

            TextInput::make('telefono')
                ->label('Teléfono')
                ->placeholder('Ej. +505 8888 8888')
                ->maxLength(50)
                ->columnSpan($columnSpan),

            TextInput::make('email')
                ->label('Correo electrónico')
                ->email()
                ->placeholder('huesped@ejemplo.com')
                ->maxLength(150)
                ->columnSpan($columnSpan),
        ];
    }
}
