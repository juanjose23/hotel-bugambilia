<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Schemas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Filament\Resources\Reservas\Schemas\Reserva\CamposPeriodoReserva;
use App\Filament\Resources\Reservas\Schemas\Reserva\SelectorHabitacionDisponible;
use App\Filament\Resources\Reservas\Schemas\Reserva\SelectorServiciosAdicionales;
use App\Filament\Shared\Forms\SelectorCliente;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                    ]),

                Section::make('Datos del Cliente')
                    ->columnSpanFull()
                    ->icon(Heroicon::User)
                    ->columns(3)
                    ->schema(SelectorCliente::make(columnSpan: 1)),

                Section::make('Periodo y Capacidad')
                    ->columnSpanFull()
                    ->icon(Heroicon::CalendarDays)
                    ->columns(3)
                    ->schema(array_merge(
                        CamposPeriodoReserva::make(columnSpan: 1),
                        [
                            Toggle::make('solicita_cuenta')
                                ->label('Solicita cuenta de consumo')
                                ->helperText('La cuenta será validada y abierta por recepción durante el check-in.')
                                ->live()
                                ->columnSpan(1),

                            TextInput::make('limite_cuenta_solicitado')
                                ->label('Límite solicitado')
                                ->numeric()
                                ->prefix('C$')
                                ->minValue(0)
                                ->visible(fn ($get): bool => (bool) $get('solicita_cuenta'))
                                ->columnSpan(1),
                        ],
                    )),

                Section::make('Registro de Acompañantes / Huéspedes')
                    ->columnSpanFull()
                    ->icon(Heroicon::UserGroup)
                    ->description('Registre los nombres e identificación de los acompañantes')
                    ->schema([
                        Repeater::make('acompanantes')
                            ->hiddenLabel()
                            ->disabledOn('edit')
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
                    ->schema(SelectorHabitacionDisponible::make(columnSpan: 1)),

                SelectorServiciosAdicionales::make(),

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
