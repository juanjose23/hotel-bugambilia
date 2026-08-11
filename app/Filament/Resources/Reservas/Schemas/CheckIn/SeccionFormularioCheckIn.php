<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\CheckIn;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;

/**
 * Sección de formulario simplificada para check-in.
 * Ahora actúa como contenedor de los datos de cuenta/llaves/observaciones.
 * Los pasos de huéspedes y garantía se manejan en secciones dedicadas.
 */
class SeccionFormularioCheckIn
{
    public static function make(): Group
    {
        return Group::make([
            SeccionGarantiaYCuenta::make(),
        ]);
    }

    /**
     * Campos del formulario de acompañantes para uso en el paso 2.
     * Retorna solo el repeater, para embeberse en la vista manual del paso.
     */
    public static function camposHuespedes(): Repeater
    {
        return Repeater::make('huespedes_nuevos')
            ->label('Acompañantes / Huéspedes de la Habitación')
            ->defaultItems(0)
            ->columns(4)
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre Completo')
                    ->required()
                    ->maxLength(150),

                Select::make('tipo_identificacion')
                    ->label('Tipo Documento')
                    ->options([
                        'cedula' => 'Cédula de Identidad',
                        'pasaporte' => 'Pasaporte',
                        'residencia' => 'Carnet Residencia',
                    ])
                    ->default('cedula')
                    ->required()
                    ->native(false),

                TextInput::make('identificacion')
                    ->label('Número de Cédula / ID')
                    ->required()
                    ->maxLength(100),

                Select::make('tipo')
                    ->label('Categoría')
                    ->options([
                        'adulto' => 'Adulto',
                        'nino' => 'Niño',
                        'infante' => 'Infante',
                    ])
                    ->default('adulto')
                    ->required()
                    ->native(false),
            ]);
    }
}
