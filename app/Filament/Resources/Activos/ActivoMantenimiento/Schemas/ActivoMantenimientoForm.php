<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoMantenimiento\Schemas;

use App\Enums\Activos\EstadoMantenimiento;
use App\Filament\Shared\Forms\MonedaSelect;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use App\Support\CachedOptions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ActivoMantenimientoForm
{
    private static ?CachedOptions $cachedOptions = null;

    private static function resolve(): void
    {
        self::$cachedOptions ??= app(CachedOptions::class);
    }

    public static function form(Schema $schema): Schema
    {
        self::resolve();

        return $schema->components([
            Section::make('Intervención y Trabajo Realizado')
                ->description('Detalles del activo, el tipo de mantenimiento y la fecha programada')
                ->icon(Heroicon::Wrench)
                ->columns()
                ->columnSpanFull()
                ->schema([
                    Select::make('plan_id')
                        ->label('Plan de Mantenimiento')
                        ->placeholder('Seleccione un plan (opcional)')
                        ->relationship('plan', 'nombre')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->prefixIcon(Heroicon::Document)
                        ->createOptionForm([
                            Grid::make()
                                ->schema([
                                    TextInput::make('nombre')
                                        ->label('Nombre del Plan')
                                        ->placeholder('Ej. Mantenimiento Preventivo Anual')
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
                                        ->placeholder('Ej. 30')
                                        ->prefixIcon(Heroicon::ArrowPath),
                                ])->columns(4),

                            Fieldset::make('Programación')
                                ->schema([
                                    DatePicker::make('fecha_inicio')
                                        ->label('Fecha de Inicio')
                                        ->required()
                                        ->default(now())
                                        ->prefixIcon(Heroicon::Calendar),

                                    DatePicker::make('fecha_fin')
                                        ->label('Fecha de Fin')
                                        ->placeholder('Opcional')
                                        ->prefixIcon(Heroicon::CalendarDays),
                                ])->columns(),

                            Fieldset::make('Presupuesto y Proveedor')
                                ->schema([
                                    Select::make('proveedor_id')
                                        ->label('Proveedor Externo')
                                        ->placeholder('Seleccione un proveedor (opcional)')
                                        ->options(fn () => (self::$cachedOptions ??= app(CachedOptions::class))->proveedores())
                                        ->searchable()
                                        ->native(false)
                                        ->prefixIcon(Heroicon::BuildingOffice)
                                        ->columnSpan(2),

                                    TextInput::make('costo_estimado')
                                        ->label('Costo Estimado')
                                        ->placeholder('0.00')
                                        ->numeric()
                                        ->step(0.01)
                                        ->prefixIcon(Heroicon::CurrencyDollar),

                                    MonedaSelect::make()
                                        ->default(fn () => Moneda::where('es_predeterminada', true)->value('id') ?? Moneda::first()?->id),
                                ])->columns(),

                            Textarea::make('descripcion')
                                ->label('Descripción detallada')
                                ->placeholder('Agregue instrucciones, especificaciones técnicas o cualquier detalle relevante del plan...')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->createOptionModalHeading('Crear Nuevo Plan de Mantenimiento')
                        ->columns(1),

                    Select::make('activo_id')
                        ->label('Activo Fijo')
                        ->placeholder('Seleccione un activo')
                        ->relationship('activo', 'nombre_descriptivo')
                        ->options(Activo::pluck('nombre_descriptivo', 'id'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->prefixIcon(Heroicon::CpuChip),

                    DatePicker::make('fecha_programada')
                        ->label('Fecha Programada')
                        ->prefixIcon(Heroicon::Calendar)
                        ->required()
                        ->default(now()),

                    DatePicker::make('fecha_realizada')
                        ->label('Fecha Realizada')
                        ->prefixIcon(Heroicon::CalendarDays)
                        ->placeholder('Intervención en curso si queda vacío'),
                ]),

            Section::make('Costos y Responsabilidad')
                ->description('Técnico responsable y costo real de la intervención')
                ->icon(Heroicon::CurrencyDollar)
                ->columns()
                ->columnSpanFull()
                ->schema([
                    TextInput::make('costo_real')
                        ->label('Costo Real')
                        ->prefixIcon(Heroicon::CurrencyDollar)
                        ->numeric()
                        ->step(0.01)
                        ->placeholder('0.00')
                        ->helperText('Costo final del servicio ejecutado.'),

                    Select::make('realizado_por_id')
                        ->label('Técnico Responsable')
                        ->placeholder('Seleccione un técnico')
                        ->relationship('realizadoPor', 'name')
                        ->options(User::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->prefixIcon(Heroicon::User),
                ]),

            Section::make('Estado y Notas Adicionales')
                ->description('Estado de la orden y observaciones finales de taller')
                ->icon(Heroicon::DocumentText)
                ->columns()
                ->columnSpanFull()
                ->schema([
                    Select::make('estado')
                        ->label('Estado')
                        ->placeholder('Seleccione estado')
                        ->options(EstadoMantenimiento::class)
                        ->required()
                        ->native(false)
                        ->prefixIcon(Heroicon::ArrowPath)
                        ->default(EstadoMantenimiento::Programado->value),

                    Textarea::make('notas')
                        ->label('Notas / Informe')
                        ->placeholder('Observaciones internas sobre el mantenimiento o problema reportado')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
