<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActPlanMantenimiento\Schemas;

use App\Enums\Activos\EstadoMantenimiento;
use App\Filament\Shared\Forms\ActivoSelect;
use App\Filament\Shared\Forms\MonedaSelect;
use App\Filament\Shared\Forms\ProveedorSelect;
use App\Filament\Shared\Forms\UserSelect;
use App\Repository\Models\Monedas\Moneda;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

readonly class ActPlanMantenimientoForm
{
    public function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos Generales del Plan')
                ->description('Configuración básica, fechas y presupuesto del plan de mantenimiento')
                ->icon(Heroicon::DocumentText)
                ->columns(1)
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->schema([
                            TextInput::make('nombre')
                                ->label('Nombre del Plan')
                                ->required()
                                ->maxLength(150)
                                ->columnSpan(2),

                            Select::make('tipo')
                                ->label('Tipo de Plan')
                                ->options([
                                    'preventivo' => 'Preventivo',
                                    'correctivo' => 'Correctivo',
                                    'garantia' => 'Garantía',
                                    'inspeccion' => 'Inspección',
                                ])
                                ->required()
                                ->native(false)
                                ->prefixIcon(Heroicon::Tag),

                            TextInput::make('frecuencia_dias')
                                ->label('Frecuencia')
                                ->numeric()
                                ->suffix('días')
                                ->prefixIcon(Heroicon::ArrowPath),
                        ])->columnSpanFull(),

                    Fieldset::make('Programación')
                        ->schema([
                            DatePicker::make('fecha_inicio')
                                ->label('Fecha de Inicio')
                                ->required()
                                ->default(now())
                                ->native(false)
                                ->prefixIcon(Heroicon::Calendar),

                            DatePicker::make('fecha_fin')
                                ->label('Fecha de Fin')
                                ->native(false)
                                ->prefixIcon(Heroicon::CalendarDays),
                        ])->columns(),

                    Fieldset::make('Presupuesto y Proveedor')
                        ->schema([
                            ProveedorSelect::make()
                                ->columnSpan(2),

                            TextInput::make('costo_estimado')
                                ->label('Costo Estimado')
                                ->numeric()
                                ->step(0.01)
                                ->prefixIcon(Heroicon::CurrencyDollar),

                            MonedaSelect::make()
                                ->default(fn () => Moneda::where('es_predeterminada', true)->value('id') ?? Moneda::first()?->id),
                        ])->columns(),

                    Textarea::make('descripcion')
                        ->label('Descripción detallada')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Activos Asignados')
                ->description('Lista de activos a los que se aplicará este plan de mantenimiento')
                ->icon(Heroicon::CpuChip)
                ->columnSpanFull()
                ->schema([
                    Repeater::make('mantenimientos')
                        ->hiddenLabel()
                        ->relationship('mantenimientos')
                        ->schema([
                            ActivoSelect::make('activo_id')
                                ->required()
                                ->columnSpan(8),

                            DatePicker::make('fecha_programada')
                                ->label('Fecha Programada')
                                ->default(now())
                                ->required()
                                ->native(false)
                                ->columnSpan(4)
                                ->prefixIcon(Heroicon::Calendar),

                            TextInput::make('costo_real')
                                ->label('Costo')
                                ->numeric()
                                ->step(0.01)
                                ->columnSpan(4)
                                ->prefixIcon(Heroicon::CurrencyDollar),

                            UserSelect::make('realizado_por_id')
                                ->columnSpan(4),

                            Select::make('estado')
                                ->label('Estado')
                                ->options(EstadoMantenimiento::class)
                                ->required()
                                ->native(false)
                                ->default(EstadoMantenimiento::Programado->value)
                                ->columnSpan(4)
                                ->prefixIcon(Heroicon::ArrowPath),
                        ])
                        ->columns(12)
                        ->collapsible()
                        ->collapsed(false)
                        ->addActionLabel('Añadir Activo al Plan')
                        ->defaultItems(1)
                        ->minItems(1),
                ]),
        ]);
    }
}
