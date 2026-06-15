<?php

namespace App\Filament\Resources\Compras\Solicitudes\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Catalogos\EstadoCatalogo;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Colaboradores\Colaborador;
use App\Models\Colaboradores\ColaboradorCargoHistorial;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SolicitudForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Encabezado')
                    ->description('Datos generales de la solicitud')
                    ->columns()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->visible(fn (string $operation): bool => $operation !== 'create')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefixIcon(Heroicon::QrCode),

                        TextInput::make('solicitante_info')
                            ->label('Solicitante')
                            ->default(fn () => self::getCurrentColaboradorLabel())
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->prefixIcon(Heroicon::User),

                        Select::make('departamento_solicitante_id')
                            ->label('Departamento')
                            ->placeholder('Seleccionar departamento')
                            ->options(fn (): array => self::getCurrentDepartamentosOptions())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::BuildingOffice2),

                        DatePicker::make('fecha_solicitud')
                            ->label('Fecha de Solicitud')
                            ->default(now())
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),

                        DatePicker::make('fecha_necesita')
                            ->label('Fecha Necesita')
                            ->helperText('Fecha límite en que se necesita el producto')
                            ->nullable()
                            ->afterOrEqual('fecha_solicitud')
                            ->prefixIcon(Heroicon::Clock),

                        Textarea::make('motivo')
                            ->label('Motivo')
                            ->placeholder('Justificación de la solicitud')
                            ->nullable()
                            ->columnSpanFull(),

                        Textarea::make('notas')
                            ->label('Nota del área de compras')
                            ->placeholder('Motivo de la cancelación')
                            ->visible(fn (string $operation, $record): bool => $operation !== 'create' && $record?->estado === EstadoSolicitud::Cancelada)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Productos Solicitados')
                    ->description('Detalle de los productos que se necesitan')
                    ->icon(Heroicon::ShoppingBag)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship('items')
                            ->minItems(1)
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->placeholder('Seleccionar producto')
                                    ->relationship('producto', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->columnSpan(6)
                                    ->prefixIcon(Heroicon::Cube)
                                    ->afterStateUpdated(fn ($set) => $set('producto_variante_id', null)),

                                Select::make('producto_variante_id')
                                    ->label('Variante')
                                    ->placeholder('Primero seleccione un producto')
                                    ->options(fn ($get): array => self::getVariantesOptions($get('producto_id')))
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->columnSpan(6)
                                    ->prefixIcon(Heroicon::AdjustmentsHorizontal),

                                TextInput::make('cantidad_solicitada')
                                    ->label('Cantidad')
                                    ->placeholder('0.00')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::Hashtag),

                                Select::make('unidad_medida_id')
                                    ->label('U.M.')
                                    ->placeholder('U.M.')
                                    ->relationship(
                                        name: 'unidadMedida',
                                        titleAttribute: 'nombre',
                                        modifyQueryUsing: fn ($query) => $query->whereHas(
                                            'catalogoTipo',
                                            fn ($q) => $q->where('codigo', CatalogoTipo::UNIDAD_MEDIDA->value)
                                        )
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::Scale),

                                Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->placeholder('Notas para este producto')
                                    ->rows(1)
                                    ->columnSpan(6)
                                    ->nullable(),
                            ])
                            ->columns(12)
                            ->collapsible()
                            ->collapsed(false)
                            ->addActionLabel('Agregar Producto')
                            ->defaultItems(1)
                            ->minItems(1),
                    ]),
            ]);
    }

    /** @return array<int, string> */
    private static function getVariantesOptions(?int $productoId): array
    {
        if ($productoId === null) {
            return [];
        }

        return ProductoVariante::where('producto_id', $productoId)
            ->get()
            ->mapWithKeys(function (ProductoVariante $v) {
                $info = $v->codigo;

                if ($v->atributos) {
                    $attrs = collect($v->atributos)
                        ->map(fn ($val, $key) => "{$key}: {$val}")
                        ->implode(', ');
                    $info .= " | {$attrs}";
                }

                if ($v->unidadMedida) {
                    $info .= " ({$v->unidadMedida->nombre})";
                }

                return [$v->id => $info];
            })
            ->toArray();
    }

    private static function getCurrentColaborador(): ?Colaborador
    {
        $personaId = auth()->user()?->persona_id;

        if ($personaId === null) {
            return null;
        }

        return Colaborador::where('persona_id', $personaId)->first();
    }

    private static function getCurrentColaboradorLabel(): string
    {
        $colaborador = self::getCurrentColaborador();

        if ($colaborador === null) {
            return '—';
        }

        return "{$colaborador->codigo} - {$colaborador->persona?->primer_nombre}";
    }

    /** @return array<int, string> */
    private static function getCurrentDepartamentosOptions(): array
    {
        $colaborador = self::getCurrentColaborador();

        if ($colaborador === null) {
            return [];
        }

        return ColaboradorCargoHistorial::where('colaborador_id', $colaborador->id)
            ->where('estado', EstadoCatalogo::Activo->value)
            ->whereNull('fecha_fin')
            ->with('departamento')
            ->get()
            ->pluck('departamento.nombre', 'departamento.id')
            ->filter()
            ->toArray();
    }
}
