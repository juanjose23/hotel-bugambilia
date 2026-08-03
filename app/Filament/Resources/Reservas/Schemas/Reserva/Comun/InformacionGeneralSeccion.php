<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva\Comun;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class InformacionGeneralSeccion
{
    public static function make(): Section
    {
        return Section::make('Información de la Reserva')
            ->columnSpanFull()
            ->icon(Heroicon::InformationCircle)
            ->columns(3)
            ->schema([
                TextInput::make('codigo_reserva')
                    ->label('Código de Reserva')
                    ->placeholder('Generación automática')
                    ->disabled()
                    ->dehydrated()
                    ->columnSpan(1),

                Select::make('tipo_reserva')
                    ->label('Tipo de Reserva')
                    ->options(TipoReserva::options())
                    ->default(TipoReserva::HABITACION->value)
                    ->required()
                    ->validationMessages([
                        'required' => 'Seleccione el tipo de reserva que desea registrar.',
                    ])
                    ->disabledOn('edit')
                    ->live()
                    ->native(false)
                    ->columnSpan(1),

                Select::make('estado')
                    ->label('Estado Actual')
                    ->options(EstadoReserva::options())
                    ->default(EstadoReserva::PENDIENTE->value)
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->native(false)
                    ->columnSpan(1),
            ]);
    }
}
