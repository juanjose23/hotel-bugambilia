<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Schemas;

use App\Enums\Activos\EstadoActivo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Monedas\Moneda;
use App\UseCases\Activos\Queries\AutocompletarActivoDesdeRecepcion;
use App\UseCases\Activos\Queries\ObtenerOpcionesRecepcionItems;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ActivoForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Detalles del Activo')
                ->columnSpanFull()
                ->tabs([
                    self::tabOrigenAdquisicion(),
                    self::tabInformacionGeneral(),
                    self::tabComercialFinanciera(),
                    self::tabUbicacionAsignacion(),
                    self::tabObservaciones(),
                ]),
        ]);
    }

    // ─── Tabs ────────────────────────────────────────────────────────────────

    private static function tabOrigenAdquisicion(): Tab
    {
        return Tab::make('Origen y Adquisición')
            ->icon(Heroicon::ShoppingBag)
            ->schema([
                Select::make('recepcion_item_id')
                    ->label('Asociar a una Compra / Recepción')
                    ->placeholder('Seleccione una recepción (opcional)')
                    ->options(ObtenerOpcionesRecepcionItems::ejecutar())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?int $state, Set $set): void {
                        if (! $state) {
                            return;
                        }

                        $campos = AutocompletarActivoDesdeRecepcion::ejecutar($state);

                        foreach ($campos as $campo => $valor) {
                            $set($campo, $valor);
                        }
                    })
                    ->prefixIcon(Heroicon::ShoppingBag)
                    ->columnSpanFull(),
            ]);
    }

    private static function tabInformacionGeneral(): Tab
    {
        return Tab::make('Información General')
            ->icon(Heroicon::InformationCircle)
            ->columns(2)
            ->schema([
                TextInput::make('codigo_inventario')
                    ->label('Código de Inventario')
                    ->prefixIcon(Heroicon::Hashtag)
                    ->disabled()
                    ->placeholder('Se genera automáticamente'),

                TextInput::make('nombre_descriptivo')
                    ->label('Nombre Descriptivo')
                    ->prefixIcon(Heroicon::Tag)
                    ->required()
                    ->maxLength(200)
                    ->placeholder('Ej. TV Habitación 101'),

                TextInput::make('numero_serie')
                    ->label('Número de Serie')
                    ->prefixIcon(Heroicon::QrCode)
                    ->maxLength(100)
                    ->placeholder('Ingrese número de serie del fabricante'),

                Select::make('producto_id')
                    ->label('Producto')
                    ->placeholder('Seleccione un producto')
                    ->relationship('producto', 'nombre')
                    ->options(Producto::where('tipo', 3)->pluck('nombre', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->prefixIcon(Heroicon::Cube),

                Select::make('producto_variante_id')
                    ->label('Variante')
                    ->placeholder('Sin variante / Única')
                    ->relationship('variante', 'nombre_variante')
                    ->options(function (Get $get) {
                        $productoId = $get('producto_id');
                        if (! $productoId) {
                            return [];
                        }

                        return ProductoVariante::where('producto_id', $productoId)
                            ->pluck('nombre_variante', 'id');
                    })
                    ->searchable()
                    ->native(false)
                    ->prefixIcon(Heroicon::RectangleStack)
                    ->helperText('Seleccione la variante específica del producto si aplica.'),

                Select::make('estado')
                    ->label('Estado Operativo')
                    ->placeholder('Seleccione un estado')
                    ->options(EstadoActivo::class)
                    ->required()
                    ->native(false)
                    ->prefixIcon(Heroicon::ArrowPath)
                    ->default(EstadoActivo::Activo)
                    ->hiddenOn('create'),

                DatePicker::make('fecha_adquisicion')
                    ->label('Fecha de Adquisición')
                    ->prefixIcon(Heroicon::Calendar)
                    ->required()
                    ->default(now()),
            ]);
    }

    private static function tabComercialFinanciera(): Tab
    {
        return Tab::make('Comercial y Financiera')
            ->icon(Heroicon::CurrencyDollar)
            ->columns(2)
            ->schema([
                TextInput::make('costo_adquisicion')
                    ->label('Costo de Adquisición')
                    ->prefixIcon(Heroicon::CurrencyDollar)
                    ->numeric()
                    ->step(0.01)
                    ->placeholder('0.00')
                    ->helperText('Costo unitario del activo en la moneda seleccionada.'),

                Select::make('moneda_id')
                    ->label('Moneda')
                    ->placeholder('Seleccione moneda')
                    ->relationship('moneda', 'nombre')
                    ->options(Moneda::pluck('nombre', 'id'))
                    ->default(fn () => Moneda::where('codigo', 'NIO')->value('id')
                        ?? Moneda::where('es_predeterminada', true)->value('id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->prefixIcon(Heroicon::Banknotes),

                Select::make('proveedor_id')
                    ->label('Proveedor')
                    ->placeholder('Seleccione un proveedor')
                    ->relationship('proveedor', 'codigo')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo} — ".(
                        $record->persona->personaJuridica->razon_social
                        ?? "{$record->persona->primer_nombre} {$record->persona->personaNatural?->primer_apellido}"
                    ))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->prefixIcon(Heroicon::BuildingOffice2)
                    ->helperText('Proveedor o fabricante del activo.'),

                TextInput::make('vida_util_meses')
                    ->label('Vida Útil Estimada')
                    ->prefixIcon(Heroicon::Clock)
                    ->numeric()
                    ->minValue(0)
                    ->placeholder('Ej. 60')
                    ->suffix('meses')
                    ->helperText('Período estimado de vida útil del activo en meses.'),

                DatePicker::make('fecha_garantia_fin')
                    ->label('Fin de Garantía')
                    ->prefixIcon(Heroicon::ShieldCheck)
                    ->helperText('Fecha de vencimiento de la garantía del fabricante.'),
            ]);
    }

    private static function tabUbicacionAsignacion(): Tab
    {
        return Tab::make('Ubicación y Asignación')
            ->icon(Heroicon::MapPin)
            ->columns(2)
            ->schema([
                Select::make('asignacion_tipo')
                    ->label('Tipo de Destino')
                    ->placeholder('Seleccione tipo de destino')
                    ->options([
                        Habitacion::class => 'Habitación',
                        Ubicacion::class => 'Ubicación / Bodega',
                        Espacio::class => 'Espacio / Área Común',
                    ])
                    ->live()
                    ->native(false)
                    ->prefixIcon(Heroicon::BuildingOffice2)
                    ->helperText('Tipo de lugar donde se encuentra físicamente el activo.')
                    ->afterStateUpdated(fn (callable $set) => $set('asignacion_destino_id', null)),

                Select::make('asignacion_destino_id')
                    ->label('Destino Específico')
                    ->placeholder('Primero seleccione un tipo de destino')
                    ->options(function (Get $get) {
                        return match ($get('asignacion_tipo')) {
                            Habitacion::class => Habitacion::pluck('nombre', 'id'),
                            Ubicacion::class => Ubicacion::pluck('nombre', 'id'),
                            Espacio::class => Espacio::pluck('nombre', 'id'),
                            default => [],
                        };
                    })
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->prefixIcon(Heroicon::MapPin)
                    ->hidden(fn (callable $get) => blank($get('asignacion_tipo'))),

                TextInput::make('asignacion_motivo')
                    ->label('Motivo de Asignación')
                    ->prefixIcon(Heroicon::PencilSquare)
                    ->placeholder('Ej. Asignación inicial del activo')
                    ->helperText('Razón de la ubicación actual del activo.')
                    ->columnSpanFull(),
            ]);
    }

    private static function tabObservaciones(): Tab
    {
        return Tab::make('Observaciones')
            ->icon(Heroicon::DocumentText)
            ->schema([
                RichEditor::make('notas')
                    ->hiddenLabel()
                    ->placeholder('Detalles de estado físico, procedencia o historial del activo.')
                    ->columnSpanFull(),
            ]);
    }
}
