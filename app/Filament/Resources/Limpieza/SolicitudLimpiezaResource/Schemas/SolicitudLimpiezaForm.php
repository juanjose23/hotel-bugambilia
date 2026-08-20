<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\Schemas;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Queries\Limpieza\Ubicacion\ObtenerOpcionesLimpiables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class SolicitudLimpiezaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Solicitud de Limpieza')
                    ->description('Registre una nueva solicitud de limpieza para una habitación o espacio.')
                    ->columns(2)
                    ->schema([
                        Select::make('limpiable_type')
                            ->label('Tipo de Objeto a Limpiar')
                            ->options([
                                Habitacion::class => 'Habitación',
                                Espacio::class => 'Espacio / Mesa',
                            ])
                            ->required()
                            ->live()
                            ->native(false),

                        Select::make('limpiable_id')
                            ->label('Objeto Específico')
                            ->searchable()
                            ->required()
                            ->options(function (Get $get): array {
                                $type = $get('limpiable_type');

                                return app(ObtenerOpcionesLimpiables::class)->execute(is_string($type) ? $type : null);
                            }),

                        Select::make('prioridad')
                            ->label('Prioridad')
                            ->options([
                                'alta' => 'Alta',
                                'normal' => 'Normal',
                                'baja' => 'Baja',
                            ])
                            ->default('normal')
                            ->required()
                            ->native(false),

                        Select::make('personal_id')
                            ->label('Personal Asignado')
                            ->relationship('personal', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoLimpieza::class)
                            ->default(EstadoLimpieza::Pendiente)
                            ->required()
                            ->native(false),

                        Textarea::make('notas')
                            ->label('Notas / Instrucciones')
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Ej. Limpieza profunda, cambiar sábanas, desinfectar...'),
                    ]),
            ]);
    }
}
