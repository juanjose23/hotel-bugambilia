<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Solicitudes\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Shared\Forms\ProductoSelect;
use App\Filament\Shared\Forms\ProductoVarianteSelect;
use App\Repository\Queries\Compras\Shared\ObtenerColaboradorDeSesion;
use App\Repository\Queries\Compras\Shared\ObtenerDepartamentosColaboradorQuery;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
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
                                ProductoSelect::make('producto_id')
                                    ->relationship('producto', 'nombre')
                                    ->required()
                                    ->live()
                                    ->columnSpan(6)
                                    ->prefixIcon(Heroicon::Cube)
                                    ->afterStateUpdated(fn ($set) => $set('producto_variante_id', null)),

                                ProductoVarianteSelect::make('producto_variante_id', 'producto_id')
                                    ->placeholder('Primero seleccione un producto')
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

    private static function getCurrentColaboradorLabel(): string
    {
        $colaborador = app(ObtenerColaboradorDeSesion::class)->ejecutar(lanzarSiNoExiste: false);

        if ($colaborador === null) {
            return '—';
        }

        $nombre = $colaborador->persona ? ObtenerNombrePersona::desde($colaborador->persona) : '';

        return $colaborador->codigo.' - '.$nombre;
    }

    /** @return array<int|string, string> */
    private static function getCurrentDepartamentosOptions(): array
    {
        $colaborador = app(ObtenerColaboradorDeSesion::class)->ejecutar(lanzarSiNoExiste: false);

        if ($colaborador === null) {
            return [];
        }

        return app(ObtenerDepartamentosColaboradorQuery::class)->ejecutar($colaborador->id);
    }
}
