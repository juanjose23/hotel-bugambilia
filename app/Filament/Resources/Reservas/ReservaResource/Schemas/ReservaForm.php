<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Schemas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ReservaForm
{
    public function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información de la Reserva')
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
                            ->live()
                            ->native(false)
                            ->columnSpan(1),

                        Select::make('estado')
                            ->label('Estado Actual')
                            ->options(EstadoReserva::options())
                            ->default(EstadoReserva::PENDIENTE->value)
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        Select::make('cliente_id')
                            ->label('Cliente Registrar')
                            ->placeholder('Seleccione cliente o llene datos')
                            ->options(fn () => User::query()->pluck('name', 'id')->mapWithKeys(fn ($v, $k) => [(int) $k => $v])->all())
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->columnSpan(1),

                        TextInput::make('nombre_cliente')
                            ->label('Nombre del Huésped')
                            ->placeholder('Nombre completo')
                            ->required()
                            ->maxLength(150)
                            ->columnSpan(1),

                        TextInput::make('telefono_cliente')
                            ->label('Teléfono de Contacto')
                            ->placeholder('Ej. +505 8888 8888')
                            ->columnSpan(1),

                        TextInput::make('email_cliente')
                            ->label('Correo Electrónico')
                            ->email()
                            ->placeholder('cliente@ejemplo.com')
                            ->columnSpan(1),

                        DatePicker::make('fecha_check_in')
                            ->label('Fecha Check-In / Reservación')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),

                        DatePicker::make('fecha_check_out')
                            ->label('Fecha Check-Out (Salida)')
                            ->nullable()
                            ->columnSpan(1),

                        TextInput::make('hora_reserva')
                            ->label('Hora de Reservación')
                            ->placeholder('Ej. 19:00')
                            ->columnSpan(1),

                        TextInput::make('adultos')
                            ->label('Adultos')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->columnSpan(1),

                        TextInput::make('ninos')
                            ->label('Niños')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->columnSpan(1),
                    ]),

                Section::make('Registro de Acompañantes / Huéspedes')
                    ->columnSpanFull()
                    ->icon(Heroicon::UserGroup)
                    ->description('Registre los nombres e identificación de los acompañantes')
                    ->schema([
                        Repeater::make('acompanantes')
                            ->hiddenLabel()
                            ->columns(3)
                            ->itemLabel(fn (array $state): string => (string) ($state['nombre'] ?? 'Acompañante'))
                            ->schema([
                                TextInput::make('nombre')
                                    ->label('Nombre Completo')
                                    ->placeholder('Ej. María Pérez')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('identificacion')
                                    ->label('DNI / Cédula / Pasaporte')
                                    ->placeholder('Ej. 001-010190-0001A')
                                    ->columnSpan(1),

                                Select::make('tipo')
                                    ->label('Categoría / Edad')
                                    ->options([
                                        'adulto' => 'Adulto',
                                        'nino' => 'Niño',
                                        'infante' => 'Infante',
                                    ])
                                    ->default('adulto')
                                    ->native(false)
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Asignación del Ítem Reservado')
                    ->columnSpanFull()
                    ->icon(Heroicon::Home)
                    ->columns(3)
                    ->schema([
                        Select::make('habitacion_id')
                            ->label('Habitación Asignada')
                            ->placeholder('Seleccione habitación')
                            ->options(function () {
                                return Habitacion::with('categoria')->get()->mapWithKeys(function ($h) {
                                    $cat = $h->categoria->nombre ?? 'Sin Categ.';

                                    return [$h->id => "{$h->nombre} ({$cat})"];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->visible(fn ($get) => $get('tipo_reserva') === TipoReserva::HABITACION->value)
                            ->columnSpan(1),

                        Select::make('espacio_id')
                            ->label('Ambiente / Espacio / Mesa')
                            ->placeholder('Seleccione ambiente, espacio o mesa')
                            ->options(function () {
                                return Espacio::all()->mapWithKeys(function ($e) {
                                    return [$e->id => $e->getNombreCompleto()];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->visible(fn ($get) => in_array($get('tipo_reserva'), [TipoReserva::RESTAURANTE->value, TipoReserva::SERVICIO->value], true))
                            ->columnSpan(1),

                        Select::make('servicio_id')
                            ->label('Servicio Especial')
                            ->placeholder('Seleccione servicio')
                            ->options(fn () => Servicio::query()->pluck('nombre', 'id')->mapWithKeys(fn ($v, $k) => [(int) $k => $v])->all())
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->visible(fn ($get) => $get('tipo_reserva') === TipoReserva::SERVICIO->value)
                            ->columnSpan(1),

                        TextInput::make('total')
                            ->label('Monto Total')
                            ->numeric()
                            ->prefix('C$')
                            ->default(0.00)
                            ->required()
                            ->columnSpan(1),
                    ]),

                Section::make('Notas & Especificaciones')
                    ->columnSpanFull()
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        RichEditor::make('notas')
                            ->hiddenLabel()
                            ->placeholder('Indicaciones especiales del cliente, requerimientos dietéticos, solicitudes de cama extra...'),
                    ]),
            ]);
    }
}
