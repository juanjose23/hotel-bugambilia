<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Schemas;

use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SolicitudLimpiezaForm
{
    /**
     * Configura el esquema del formulario para SolicitudLimpieza.
     */
    public function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Detalles de la Solicitud')
                    ->description('Registre la información general de la solicitud de limpieza')
                    ->icon(Heroicon::Sparkles)
                    ->columns(2)
                    ->schema([
                        Select::make('limpiable_type')
                            ->label('Tipo de Ubicación')
                            ->options([
                                Habitacion::class => 'Habitación',
                                Espacio::class => 'Espacio Común / Mesa',
                            ])
                            ->required()
                            ->live()
                            ->native(false)
                            ->prefixIcon(Heroicon::RectangleStack),

                        Select::make('limpiable_id')
                            ->label('Ubicación Específica')
                            ->placeholder('Seleccione la ubicación')
                            ->options(function (Get $get) {
                                $type = $get('limpiable_type');
                                if (! $type) {
                                    return [];
                                }

                                return $type::pluck('nombre', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::Home),

                        Select::make('creador_id')
                            ->label('Creado Por')
                            ->placeholder('Sistema / Automático')
                            ->relationship('creador', 'name')
                            ->disabled()
                            ->dehydrated(false)
                            ->native(false)
                            ->prefixIcon(Heroicon::User),

                        Select::make('personal_id')
                            ->label('Personal Asignado')
                            ->placeholder('Seleccione el operario')
                            ->relationship('personal', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->prefixIcon(Heroicon::User),

                        Select::make('prioridad')
                            ->label('Prioridad')
                            ->options([
                                'baja' => 'Baja',
                                'normal' => 'Normal',
                                'alta' => 'Alta',
                            ])
                            ->default('normal')
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::ExclamationCircle),

                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'en_progreso' => 'En Progreso',
                                'completada' => 'Completada',
                            ])
                            ->default('pendiente')
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::ArrowPath),

                        Textarea::make('notas')
                            ->label('Notas / Instrucciones')
                            ->placeholder('Escriba indicaciones específicas para el personal de limpieza...')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
