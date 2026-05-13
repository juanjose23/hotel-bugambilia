<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Schemas;

use App\Enums\CatalogoTipo;
use App\Enums\EstadoCatalogo;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Compras\Proveedor;
use App\UseCases\Compras\ObtenerSolicitudConItems;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CotizacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SECCIÓN 1: VINCULACIÓN Y PROVEEDOR (Ancho completo para evitar squashing)
                Section::make('Información del Proveedor')
                    ->description('Seleccione la solicitud aprobada y el proveedor que emite la oferta')
                    ->icon(Heroicon::UserGroup)
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('solicitud_id')
                            ->label('Solicitud Relacionada')
                            ->options(fn () => \App\Models\Compras\Solicitud::where('estado', \App\Enums\Compras\EstadoSolicitud::Aprobada)
                                ->whereDoesntHave('ordenesCompra')
                                ->pluck('codigo', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => self::loadSolicitudItems($state, $set))
                            ->prefixIcon(Heroicon::DocumentText),

                        Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedor', 'codigo')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::Identification),

                        Select::make('condicion_pago_id')
                            ->label('Condición de Pago')
                            ->options(fn () => Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::CONDICION_PAGO->value))->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::CreditCard),
                    ]),

                // SECCIÓN 2: VIGENCIA Y LOGÍSTICA
                Section::make('Vigencia y Logística')
                    ->description('Tiempos de entrega y validez de la oferta')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('fecha_cotizacion')
                            ->label('Fecha Cotización')
                            ->default(now())
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),

                        DatePicker::make('fecha_vencimiento')
                            ->label('Válida hasta')
                            ->prefixIcon(Heroicon::Clock),

                        TextInput::make('dias_entrega')
                            ->label('Días para Entrega')
                            ->numeric()
                            ->suffix('días hábiles')
                            ->required()
                            ->prefixIcon(Heroicon::Truck),
                    ]),

                // SECCIÓN 3: DETALLE DE PRODUCTOS (Grid 12 para máximo control de anchos)
                Section::make('Análisis de Precios por Ítem')
                    ->description('Cargue los precios unitarios ofrecidos para cada producto aprobado')
                    ->icon(Heroicon::ShoppingBag)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('producto_id')
                                            ->label('Producto')
                                            ->relationship('producto', 'nombre')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(5)
                                            ->prefixIcon(Heroicon::Cube),

                                        Select::make('producto_variante_id')
                                            ->label('Variante Ofrecida')
                                            ->options(fn ($get) => \App\Models\Catalogos\ProductoVariante::where('producto_id', $get('producto_id'))->pluck('codigo', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(3)
                                            ->helperText('Puede elegir una variante distinta a la solicitada si el proveedor ofrece una alternativa.'),

                                        TextInput::make('cantidad')
                                            ->label('Cant.')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),

                                        TextInput::make('precio_unitario')
                                            ->label('Precio Unit.')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set, $get) => $set('subtotal', floatval($state) * floatval($get('cantidad'))))
                                            ->columnSpan(1),

                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(2)
                                            ->prefix('$')
                                            ->extraInputAttributes(['class' => 'text-right font-mono']),
                                    ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::updateTotals($get, $set))
                            ->addActionLabel('Cargar productos desde solicitud')
                            ->itemLabel(fn (array $state): ?string => ($state['producto_id'] ?? null) ? "Detalle de ítem" : null),
                    ]),

                // SECCIÓN 4: RESUMEN FINANCIERO (Layout tipo factura)
                Section::make('Resumen Financiero')
                    ->description('Totales acumulados, impuestos y descuentos aplicables')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal Neto')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('$')
                            ->extraInputAttributes(['class' => 'text-right font-mono']),

                        TextInput::make('impuestos')
                            ->label('Impuestos / IVA')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($get, $set) => self::updateTotals($get, $set)),

                        TextInput::make('costo_envio')
                            ->label('Costo de Envío')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($get, $set) => self::updateTotals($get, $set)),

                        TextInput::make('descuento')
                            ->label('Descuento Total')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($get, $set) => self::updateTotals($get, $set)),

                        TextInput::make('total')
                            ->label('TOTAL DE LA COTIZACIÓN')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->prefix('$')
                            ->extraInputAttributes(['class' => 'text-right font-bold text-3xl text-primary-600 font-mono']),
                    ]),

                // SECCIÓN 5: SOPORTE DOCUMENTAL
                Section::make('Documentación y Notas')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('archivo_pdf')
                            ->label('Adjuntar PDF de la Cotización')
                            ->disk('public')
                            ->directory('compras/cotizaciones')
                            ->acceptedFileTypes(['application/pdf'])
                            ->columnSpanFull(),

                        Textarea::make('observaciones')
                            ->label('Notas Adicionales')
                            ->placeholder('Detalles sobre la negociación, garantías o términos especiales...')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }

    protected static function loadSolicitudItems($state, $set): void
    {
        if (!$state) return;
        
        $solicitud = app(ObtenerSolicitudConItems::class)->execute($state);
        if (!$solicitud) return;

        // Mapeo inteligente de productos
        $items = $solicitud->items
            ->map(fn ($item) => [
                'producto_id' => $item->producto_id,
                'producto_variante_id' => $item->producto_variante_id,
                'cantidad' => $item->cantidad_aprobada > 0 ? $item->cantidad_aprobada : $item->cantidad,
                'precio_unitario' => 0,
                'subtotal' => 0,
            ])->toArray();

        $set('items', []);
        $set('items', $items);
        self::updateTotals(fn($key) => $key === 'items' ? $items : 0, $set);
    }

    public static function updateTotals($get, $set): void
    {
        $items = is_array($get) ? $get['items'] : (is_callable($get) ? $get('items') : []);
        $subtotalGeneral = 0;

        foreach ($items as $key => $item) {
            $cantidad = floatval($item['cantidad'] ?? 0);
            $precio = floatval($item['precio_unitario'] ?? 0);
            $subtotalItem = $cantidad * $precio;
            
            if (is_callable($set)) {
                $set("items.{$key}.subtotal", $subtotalItem);
            }
            $subtotalGeneral += $subtotalItem;
        }

        $envio = floatval(is_callable($get) ? $get('costo_envio') : 0);
        $impuestos = floatval(is_callable($get) ? $get('impuestos') : 0);
        $descuento = floatval(is_callable($get) ? $get('descuento') : 0);
        
        if (is_callable($set)) {
            $set('subtotal', $subtotalGeneral);
            $set('total', ($subtotalGeneral + $envio + $impuestos) - $descuento);
        }
    }
}
