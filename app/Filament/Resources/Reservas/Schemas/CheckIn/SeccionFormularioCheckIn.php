<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\CheckIn;

use App\Repository\Models\Reservas\Reserva;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

class SeccionFormularioCheckIn
{
    public static function make(): Group
    {
        return Group::make([
            Section::make('Datos del Cliente / Titular')
                ->icon('heroicon-o-user')
                ->description('Completar o verificar la información del cliente registrado en la reserva.')
                ->schema([
                    Select::make('tipo_persona')
                        ->label('Tipo de Persona')
                        ->options([
                            'natural' => 'Persona Natural',
                            'juridica' => 'Persona Jurídica / Empresa',
                        ])
                        ->default('natural')
                        ->required()
                        ->live()
                        ->native(false),

                    // Campos para Persona Natural
                    TextInput::make('nombre_cliente')
                        ->label('Nombre Completo del Cliente')
                        ->required()
                        ->maxLength(150),

                    Select::make('tipo_identificacion')
                        ->label('Tipo de Documento')
                        ->options([
                            'cedula' => 'Cédula de Identidad',
                            'pasaporte' => 'Pasaporte',
                            'residencia' => 'Carnet de Residencia',
                        ])
                        ->default('cedula')
                        ->native(false)
                        ->visible(fn (callable $get): bool => $get('tipo_persona') === 'natural'),

                    TextInput::make('identificacion_cliente')
                        ->label('Número de Identificación')
                        ->maxLength(100)
                        ->visible(fn (callable $get): bool => $get('tipo_persona') === 'natural'),

                    TextInput::make('telefono_cliente')
                        ->label('Teléfono de Contacto')
                        ->tel()
                        ->maxLength(50),

                    TextInput::make('email_cliente')
                        ->label('Correo Electrónico')
                        ->email()
                        ->maxLength(150),

                    TextInput::make('direccion_cliente')
                        ->label('Dirección')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    // Campos para Persona Jurídica
                    TextInput::make('razon_social')
                        ->label('Razón Social / Nombre Comercial')
                        ->required(fn (callable $get): bool => $get('tipo_persona') === 'juridica')
                        ->maxLength(150)
                        ->visible(fn (callable $get): bool => $get('tipo_persona') === 'juridica'),

                    TextInput::make('numero_ruc')
                        ->label('Número RUC')
                        ->required(fn (callable $get): bool => $get('tipo_persona') === 'juridica')
                        ->maxLength(50)
                        ->visible(fn (callable $get): bool => $get('tipo_persona') === 'juridica'),

                    TextInput::make('representante_legal')
                        ->label('Representante Legal')
                        ->maxLength(150)
                        ->visible(fn (callable $get): bool => $get('tipo_persona') === 'juridica'),
                ])
                ->columns(2),

            Section::make('Datos del Check-In')
                ->icon('heroicon-o-key')
                ->schema([
                    Repeater::make('huespedes_nuevos')
                        ->label('Huéspedes pendientes de registrar')
                        ->defaultItems(0)
                        ->columns(3)
                        ->schema([
                            TextInput::make('nombre')->required()->maxLength(150),
                            TextInput::make('identificacion')->label('Identificación')->maxLength(100),
                            Select::make('tipo')
                                ->options([
                                    'adulto' => 'Adulto',
                                    'nino' => 'Niño',
                                    'infante' => 'Infante',
                                ])
                                ->default('adulto')
                                ->required()
                                ->native(false),
                        ]),

                    TextInput::make('cantidad_llaves')
                        ->label('Cantidad de llaves entregadas')
                        ->integer()
                        ->minValue(1)
                        ->default(1)
                        ->required(),

                    Toggle::make('abrir_cuenta')
                        ->label('Abrir cuenta de consumo')
                        ->default(function ($record, $livewire): bool {
                            /** @var Reserva|null $reserva */
                            $reserva = $record instanceof Reserva ? $record : ($livewire->reserva ?? null);

                            if ($reserva === null) {
                                return false;
                            }

                            return (bool) $reserva->solicita_cuenta;
                        })
                        ->live(),

                    TextInput::make('limite_cuenta')
                        ->label('Límite autorizado')
                        ->numeric()
                        ->prefix('C$')
                        ->minValue(0)
                        ->visible(fn (callable $get): bool => (bool) $get('abrir_cuenta')),

                    Textarea::make('observaciones')
                        ->label('Observaciones de entrada')
                        ->maxLength(2000),
                ]),
        ]);
    }
}
