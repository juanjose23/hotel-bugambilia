<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Schemas;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\CatalogoTipo;
use App\Enums\EstadoCatalogo;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Compras\Proveedor;
use App\UseCases\Compras\ObtenerCotizacionConItemsProveedor;
use App\UseCases\Compras\ObtenerCotizacionesPorSolicitud;
use App\UseCases\Compras\ObtenerSolicitudConItems;
use App\Models\Compras\Solicitud;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class OrdenCompraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SECCIÓN 1: IDENTIFICACIÓN (Inspirada en el Encabezado de Solicitud)
                Section::make('Identificación de la Orden')
                    ->description('Datos maestros y vinculación con el sistema')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Número de Orden')
                            ->placeholder('OC-YYYY-NNN')
                            ->disabled()
                            ->dehydrated(true)
                            ->prefixIcon(Heroicon::QrCode),

                        Select::make('estado')
                            ->label('Estado Actual')
                            ->options(EstadoOrdenCompra::class)
                            ->default(EstadoOrdenCompra::Borrador)
                            ->required()
                            ->native(false)
                            ->prefixIcon(fn($state) => $state instanceof EstadoOrdenCompra ? $state->icon() : Heroicon::Clock),

                        Select::make('solicitud_id')
                            ->label('Solicitud de Origen')
                            ->relationship('solicitud', 'codigo')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $set('cotizacion_id', null);
                                $set('items', []); // Limpieza previa obligatoria
                                
                                if ($state) {
                                    $solicitud = app(ObtenerSolicitudConItems::class)->execute($state);
                                    if ($solicitud) {
                                        $set('items', $solicitud->items->map(fn ($item) => [
                                            'producto_id' => $item->producto_id,
                                            'producto_variante_id' => $item->producto_variante_id,
                                            'unidad_medida_id' => $item->unidad_medida_id,
                                            'cantidad' => $item->cantidad_aprobada > 0 ? $item->cantidad_aprobada : $item->cantidad,
                                            'precio_unitario' => 0,
                                            'subtotal' => 0,
                                        ])->toArray());
                                    }
                                }
                                self::updateTotals($get, $set);
                            })
                            ->prefixIcon(Heroicon::DocumentText),

                        Select::make('cotizacion_id')
                            ->label('Cotización Ganadora')
                            ->options(fn ($get) => 
                                app(ObtenerCotizacionesPorSolicitud::class)
                                    ->execute($get('solicitud_id'))
                                    ->mapWithKeys(fn ($c) => [$c->id => "#{$c->id} - " . ($c->proveedor?->codigo ?? 'Sin Proveedor')])
                            )
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (!$state) {
                                    // Si quitan la cotización, intentar volver a cargar de la solicitud si existe
                                    $solicitudId = $get('solicitud_id');
                                    if ($solicitudId) {
                                        $solicitud = app(ObtenerSolicitudConItems::class)->execute($solicitudId);
                                        if ($solicitud) {
                                            $set('items', $solicitud->items->map(fn ($item) => [
                                                'producto_id' => $item->producto_id,
                                                'producto_variante_id' => $item->producto_variante_id,
                                                'unidad_medida_id' => $item->unidad_medida_id,
                                                'cantidad' => $item->cantidad_aprobada > 0 ? $item->cantidad_aprobada : $item->cantidad,
                                                'precio_unitario' => 0,
                                                'subtotal' => 0,
                                            ])->toArray());
                                        }
                                    }
                                    return;
                                }
                                
                                $set('items', []); // Limpieza previa obligatoria
                                $cotizacion = app(ObtenerCotizacionConItemsProveedor::class)->execute($state);
                                if ($cotizacion) {
                                    $set('proveedor_id', $cotizacion->proveedor_id);
                                    $set('condicion_pago_id', $cotizacion->condicion_pago_id);
                                    
                                    $set('items', $cotizacion->items->map(fn ($item) => [
                                        'producto_id' => $item->producto_id,
                                        'producto_variante_id' => $item->producto_variante_id,
                                        'unidad_medida_id' => $item->unidad_medida_id,
                                        'cantidad' => $item->cantidad,
                                        'precio_unitario' => $item->precio_unitario,
                                        'subtotal' => $item->subtotal,
                                    ])->toArray());
                                    
                                    self::updateTotals($get, $set);
                                }
                            })
                            ->prefixIcon(Heroicon::DocumentCheck)
                            ->helperText('Vincula esta OC a la cotización aprobada (trazabilidad P2P).'),

                        Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedor', 'codigo')
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                $record->persona->personaJuridica->razon_social
                                ?? "{$record->persona->primer_nombre} {$record->persona->personaNatural?->primer_apellido}"
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2)
                            ->prefixIcon(Heroicon::UserGroup),

                        Select::make('condicion_pago_id')
                            ->label('Condición de Pago')
                            ->options(fn () => Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::CONDICION_PAGO->value))->pluck('nombre', 'id'))
                            ->required()
                            ->prefixIcon(Heroicon::CreditCard),
                    ]),

                // SECCIÓN 2: FECHAS
                Section::make('Cronograma de Compra')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('fecha_orden')
                            ->label('Fecha de Emisión')
                            ->default(now())
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),

                        DatePicker::make('fecha_entrega_estimada')
                            ->label('Fecha de Entrega Estimada')
                            ->nullable()
                            ->prefixIcon(Heroicon::Clock),
                    ]),

                // SECCIÓN 3: PRODUCTOS (Igual que Productos Solicitados)
                Section::make('Detalle de Productos')
                    ->description('Ítems incluidos en esta orden de compra')
                    ->icon(Heroicon::ShoppingBag)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->relationship('producto', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($set) => $set('producto_variante_id', null))
                                    ->columnSpan(2)
                                    ->prefixIcon(Heroicon::Cube),

                                Select::make('producto_variante_id')
                                    ->label('Variante')
                                    ->options(fn ($get) => \App\Models\Catalogos\ProductoVariante::where('producto_id', $get('producto_id'))->pluck('codigo', 'id'))
                                    ->searchable()
                                    ->columnSpan(1),

                                Select::make('unidad_medida_id')
                                    ->label('UM')
                                    ->options(fn () => \App\Models\Catalogos\Catalogo::whereHas(
                                        'catalogoTipo',
                                        fn ($q) => $q->where('codigo', \App\Enums\CatalogoTipo::UNIDAD_MEDIDA->value)
                                    )->pluck('nombre', 'id'))
                                    ->nullable()
                                    ->searchable()
                                    ->columnSpan(1),

                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(1),

                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(1),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1)
                                    ->prefix('$')
                                    ->extraInputAttributes(['class' => 'text-right font-mono']),
                            ])
                            ->columns(6) 
                            ->collapsible()
                            ->live()
                            ->afterStateUpdated(function ($get, $set) {
                                self::updateTotals($get, $set);
                            })
                            ->addActionLabel('Agregar producto a la orden'),
                    ]),

                // SECCIÓN 4: TOTALES Y NOTAS
                Section::make('Resumen y Observaciones')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->disabled()
                            ->prefix('$')
                            ->extraInputAttributes(['class' => 'text-right font-mono']),
                        
                        TextInput::make('impuestos')
                            ->label('Impuestos / IVA')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->prefix('$')
                            ->extraInputAttributes(['class' => 'text-right font-mono']),

                        TextInput::make('total')
                            ->label('Total de la Orden')
                            ->disabled()
                            ->prefix('$')
                            ->extraInputAttributes(['class' => 'text-right font-bold text-lg text-primary-600 font-mono']),

                        Textarea::make('notas')
                            ->label('Notas u Observaciones')
                            ->placeholder('Indique detalles sobre la entrega o términos de compra...')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }

    public static function updateTotals($get, $set): void
    {
        $items = $get('items') ?? [];
        $subtotalGeneral = 0;

        foreach ($items as $key => $item) {
            $cantidad = floatval($item['cantidad'] ?? 0);
            $precio = floatval($item['precio_unitario'] ?? 0);
            $subtotalItem = $cantidad * $precio;
            
            $set("items.{$key}.subtotal", $subtotalItem);
            $subtotalGeneral += $subtotalItem;
        }

        $impuestos = floatval($get('impuestos') ?? 0);
        
        $set('subtotal', $subtotalGeneral);
        $set('total', $subtotalGeneral + $impuestos);
    }
}
