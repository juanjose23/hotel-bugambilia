<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Filament\Shared\Forms\ProductoSelect;
use App\Filament\Shared\Forms\ProductoVarianteSelect;
use App\Repository\Models\Compras\CotizacionItem;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\ProveedorContacto;
use App\Repository\Queries\Compras\Cotizaciones\ObtenerCotizacionConItemsProveedor;
use App\Repository\Queries\Compras\Cotizaciones\ObtenerCotizacionesPorSolicitud;
use App\Repository\Queries\Compras\Solicitudes\ObtenerSolicitudConItems;
use App\Support\CachedOptions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class OrdenCompraForm
{
    use InyectaDesdeContenedor;

    public function __construct(
        private readonly ObtenerSolicitudConItems $obtenerSolicitudConItems,
        private readonly ObtenerCotizacionesPorSolicitud $obtenerCotizacionesPorSolicitud,
        private readonly ObtenerCotizacionConItemsProveedor $obtenerCotizacionConItemsProveedor,
    ) {}

    public static function configure(Schema $schema): Schema
    {
        return static::make()->doConfigure($schema);
    }

    private function doConfigure(Schema $schema): Schema
    {
        $form = $this;

        return $schema
            ->components([
                Section::make('Identificación de la Orden')
                    ->description('Datos maestros y vinculación con el sistema')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Número de Orden')
                            ->placeholder('OC-YYYY-NNN')
                            ->disabled()
                            ->dehydrated()
                            ->prefixIcon(Heroicon::QrCode),

                        Select::make('estado')
                            ->label('Estado Actual')
                            ->options(EstadoOrdenCompra::class)
                            ->default(EstadoOrdenCompra::Borrador)
                            ->required()
                            ->native(false)
                            ->prefixIcon(fn ($state) => $state instanceof EstadoOrdenCompra ? $state->getIcon() : Heroicon::Clock),

                        Select::make('solicitud_id')
                            ->label('Solicitud de Origen')
                            ->relationship(
                                name: 'solicitud',
                                titleAttribute: 'codigo',
                                modifyQueryUsing: fn (Builder $query) => $query->where('estado', EstadoSolicitud::Aprobada)
                                    ->whereDoesntHave('ordenesCompra', fn ($q) => $q->whereIn('estado', [
                                        EstadoOrdenCompra::Emitida,
                                        EstadoOrdenCompra::EnTransito,
                                        EstadoOrdenCompra::Recibida,
                                        EstadoOrdenCompra::Parcial,
                                    ]))
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, mixed $set, mixed $get) use ($form): void {
                                if (is_callable($set)) {
                                    $set('cotizacion_id', null);
                                    $set('items', []);
                                }

                                if ($state) {
                                    $solicitud = $form->obtenerSolicitudConItems->execute(is_numeric($state) ? intval($state) : 0);
                                    if ($solicitud && is_callable($set)) {
                                        $set('items', $solicitud->items->map(fn ($item) => [
                                            'producto_id' => $item->producto_id,
                                            'producto_variante_id' => $item->producto_variante_id,
                                            'unidad_medida_id' => $item->unidad_medida_id,
                                            'cantidad' => $item->cantidad_aprobada > 0 ? $item->cantidad_aprobada : $item->cantidad_solicitada,
                                            'precio_unitario' => 0,
                                            'subtotal' => 0,
                                        ])->toArray());
                                    }
                                }
                                $form->updateTotals($get, $set);
                            })
                            ->prefixIcon(Heroicon::DocumentText),

                        Select::make('cotizacion_id')
                            ->label('Cotización Ganadora')
                            ->options(function (mixed $get) use ($form): array {
                                $solicitudId = is_callable($get) ? intval($get('solicitud_id')) : null;

                                return $form->obtenerCotizacionesPorSolicitud
                                    ->execute($solicitudId ?: null)
                                    ->filter(fn ($c) => in_array($c->estado, [EstadoCotizacion::Aceptada, EstadoCotizacion::AceptadaParcial]))
                                    ->mapWithKeys(fn ($c) => [$c->id => '#'.$c->id.' - '.($c->proveedor ? $c->proveedor->codigo : '')])
                                    ->toArray();
                            })
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, mixed $set, mixed $get) use ($form): void {
                                if (! $state) {
                                    $solicitudId = is_callable($get) ? intval($get('solicitud_id')) : null;
                                    if ($solicitudId) {
                                        $solicitud = $form->obtenerSolicitudConItems->execute($solicitudId);
                                        if ($solicitud && is_callable($set)) {
                                            $set('items', $solicitud->items->map(fn ($item) => [
                                                'producto_id' => $item->producto_id,
                                                'producto_variante_id' => $item->producto_variante_id,
                                                'unidad_medida_id' => $item->unidad_medida_id,
                                                'cantidad' => $item->cantidad_aprobada > 0 ? $item->cantidad_aprobada : $item->cantidad_solicitada,
                                                'precio_unitario' => 0,
                                                'subtotal' => 0,
                                            ])->toArray());
                                        }
                                    }

                                    return;
                                }

                                if (is_callable($set)) {
                                    $set('items', []);
                                }
                                $cotizacion = $form->obtenerCotizacionConItemsProveedor->execute(is_numeric($state) ? intval($state) : 0);
                                if ($cotizacion && is_callable($set)) {
                                    $set('proveedor_id', $cotizacion->proveedor_id);
                                    $set('condicion_pago_id', $cotizacion->condicion_pago_id);
                                    $set('tasa_cambio', $cotizacion->tasa_cambio ?? 1.0000);

                                    $set('items', $cotizacion->items->map(function ($item) {
                                        /** @var CotizacionItem $item */
                                        return [
                                            'producto_id' => $item->producto_id,
                                            'producto_variante_id' => $item->producto_variante_id,
                                            'unidad_medida_id' => $item->unidad_medida_id,
                                            'cantidad' => $item->cantidad,
                                            'precio_unitario' => $item->precio_unitario,
                                            'subtotal' => $item->subtotal,
                                        ];
                                    })->toArray());

                                    $form->updateTotals($get, $set);
                                }
                            })
                            ->prefixIcon(Heroicon::DocumentCheck)
                            ->helperText('Vincula esta OC a la cotización aprobada (trazabilidad P2P).'),

                        Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedor', 'codigo')
                            ->getOptionLabelFromRecordUsing(fn (Proveedor $record) => "$record->codigo - ".(
                                ($record->persona && $record->persona->personaJuridica)
                                    ? $record->persona->personaJuridica->razon_social
                                    : (($record->persona ? $record->persona->primer_nombre : '').' '.($record->persona && $record->persona->personaNatural ? $record->persona->personaNatural->primer_apellido : ''))
                            ))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('proveedor_contacto_id', null))
                            ->prefixIcon(Heroicon::UserGroup),

                        Select::make('condicion_pago_id')
                            ->label('Condición de Pago')
                            ->options(fn () => CachedOptions::catalogos(CatalogoTipo::CONDICION_PAGO->value))
                            ->required()
                            ->prefixIcon(Heroicon::CreditCard),

                        Select::make('proveedor_contacto_id')
                            ->label('Atención a / Contacto')
                            ->options(fn ($get) => ProveedorContacto::where('proveedor_id', $get('proveedor_id'))->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->prefixIcon(Heroicon::User)
                            ->helperText('Seleccione el contacto del proveedor que le atendió.'),

                        Hidden::make('tasa_cambio')
                            ->default(1.0000),
                    ]),

                // SECCIÓN 2: FECHAS
                Section::make('Cronograma de Compra')
                    ->columns()
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
                            ->hiddenLabel()
                            ->relationship()
                            ->minItems(1)
                            ->schema([
                                ProductoSelect::make('producto_id')
                                    ->relationship('producto', 'nombre')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($set) => $set('producto_variante_id', null))
                                    ->columnSpan(6)
                                    ->prefixIcon(Heroicon::Cube),

                                ProductoVarianteSelect::make('producto_variante_id', 'producto_id')
                                    ->columnSpan(6)
                                    ->prefixIcon(Heroicon::AdjustmentsHorizontal),

                                Select::make('unidad_medida_id')
                                    ->label('UM')
                                    ->options(fn () => CachedOptions::catalogos(CatalogoTipo::UNIDAD_MEDIDA->value))
                                    ->nullable()
                                    ->searchable()
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::Scale),

                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::Hashtag),

                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(3),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(3)
                                    ->prefix('$')
                                    ->extraInputAttributes(['class' => 'text-right font-mono']),
                            ])
                            ->columns(12)
                            ->collapsible()
                            ->live()
                            ->afterStateUpdated(function ($get, $set) use ($form) {
                                $form->updateTotals($get, $set);
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

    private function updateTotals(mixed $get, mixed $set): void
    {
        if (! is_callable($set) || ! is_callable($get)) {

            return;
        }

        $items = $get('items') ?? [];
        $subtotalGeneral = 0;

        foreach ($items as $key => $item) {
            $cantidad = floatval($item['cantidad'] ?? 0);
            $precio = floatval($item['precio_unitario'] ?? 0);
            $subtotalItem = $cantidad * $precio;

            $set("items.$key.subtotal", $subtotalItem);
            $subtotalGeneral += $subtotalItem;
        }

        $impuestos = floatval($get('impuestos') ?? 0);

        $set('subtotal', $subtotalGeneral);
        $set('total', $subtotalGeneral + $impuestos);
    }
}
