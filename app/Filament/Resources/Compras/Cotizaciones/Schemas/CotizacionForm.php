<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Filament\Shared\Forms\MonedaConTasaCampos;
use App\Filament\Shared\Forms\ProductoSelect;
use App\Filament\Shared\Forms\ProductoVarianteSelect;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Solicitudes\ObtenerSolicitudConItems;
use App\Support\CachedOptions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CotizacionForm
{
    use InyectaDesdeContenedor;

    public function __construct(
        private readonly ObtenerSolicitudConItems $obtenerSolicitudConItems,
        private readonly MonedaConTasaCampos $monedaConTasaCampos,
    ) {}

    public static function configure(Schema $schema): Schema
    {
        return static::make()->doConfigure($schema);
    }

    private function doConfigure(Schema $schema): Schema
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
                            ->relationship(
                                name: 'solicitud',
                                titleAttribute: 'codigo',
                                modifyQueryUsing: fn (Builder $query) => $query->whereNotIn('estado', [EstadoSolicitud::Borrador, EstadoSolicitud::Cancelada])
                                    ->whereDoesntHave('ordenesCompra', fn ($q) => $q->where('estado', '!=', EstadoOrdenCompra::Cancelada))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => $this->loadSolicitudItems($state, $set))
                            ->default(fn () => request()->query('solicitud_id'))
                            ->getOptionLabelUsing(fn ($value) => Solicitud::find((int) $value)?->codigo)
                            ->prefixIcon(Heroicon::DocumentText),

                        Select::make('estado')
                            ->label('Estado de la Oferta')
                            ->options(EstadoCotizacion::class)
                            ->default(EstadoCotizacion::Activa)
                            ->required()
                            ->prefixIcon(Heroicon::InformationCircle),

                        Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedor', 'codigo')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "$record->codigo - ".(
                                ($record->persona && $record->persona->personaJuridica ? $record->persona->personaJuridica->razon_social : null)
                                ?? ($record->persona ? $record->persona->primer_nombre.' '.($record->persona->personaNatural ? $record->persona->personaNatural->primer_apellido : '') : '')
                            ))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::Identification),

                        Select::make('condicion_pago_id')
                            ->label('Condición de Pago')
                            ->options(fn () => CachedOptions::catalogos(CatalogoTipo::CONDICION_PAGO->value))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::CreditCard),

                        ...$this->monedaConTasaCampos->make('moneda_id', 'tasa_cambio', 'Moneda de Adjudicación'),
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
                            ->minItems(1)
                            ->default(function () {
                                $form = $this;
                                $solicitudId = request()->query('solicitud_id');
                                if (! $solicitudId) {
                                    return [];
                                }
                                $solicitud = $form->obtenerSolicitudConItems->execute((int) $solicitudId);
                                if (! $solicitud) {
                                    return [];
                                }

                                return $solicitud->items
                                    ->map(fn ($item) => [
                                        'producto_id' => $item->producto_id,
                                        'producto_variante_id' => $item->producto_variante_id,
                                        'cantidad' => $item->cantidad_aprobada > 0 ? $item->cantidad_aprobada : $item->cantidad_solicitada,
                                        'precio_unitario' => 0,
                                        'subtotal' => 0,
                                    ])->toArray();
                            })
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        ProductoSelect::make('producto_id')
                                            ->relationship('producto', 'nombre')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(6)
                                            ->prefixIcon(Heroicon::Cube),

                                        ProductoVarianteSelect::make('producto_variante_id', 'producto_id')
                                            ->label('Variante Ofrecida')
                                            ->required()
                                            ->columnSpan(6)
                                            ->prefixIcon(Heroicon::AdjustmentsHorizontal)
                                            ->helperText('Puede elegir una variante distinta a la solicitada si el proveedor ofrece una alternativa.'),

                                        TextInput::make('cantidad')
                                            ->label('Cant.')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(4)
                                            ->prefixIcon(Heroicon::Hashtag),

                                        TextInput::make('precio_unitario')
                                            ->label('Precio Unit.')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set, $get) => $set('subtotal', floatval($state) * floatval($get('cantidad'))))
                                            ->columnSpan(4),

                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(4)
                                            ->prefix('$')
                                            ->extraInputAttributes(['class' => 'text-right font-mono']),
                                    ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => $this->updateTotals($get, $set))
                            ->addActionLabel('Cargar productos desde solicitud')
                            ->itemLabel(fn (array $state): ?string => ($state['producto_id'] ?? null) ? 'Detalle de ítem' : null),
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
                            ->afterStateUpdated(fn ($get, $set) => $this->updateTotals($get, $set)),

                        TextInput::make('costo_envio')
                            ->label('Costo de Envío')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($get, $set) => $this->updateTotals($get, $set)),

                        TextInput::make('descuento')
                            ->label('Descuento Total')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($get, $set) => $this->updateTotals($get, $set)),

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
                            ->maxSize(10240)
                            ->columnSpanFull(),

                        Textarea::make('observaciones')
                            ->label('Notas Adicionales')
                            ->placeholder('Detalles sobre la negociación, garantías o términos especiales...')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }

    private function loadSolicitudItems(mixed $state, mixed $set): void
    {
        if (! $state) {
            return;
        }

        $solicitud = $this->obtenerSolicitudConItems->execute(is_numeric($state) ? intval($state) : 0);
        if (! $solicitud) {
            return;
        }

        // Mapeo inteligente de productos
        $items = $solicitud->items
            ->map(fn ($item) => [
                'producto_id' => $item->producto_id,
                'producto_variante_id' => $item->producto_variante_id,
                'cantidad' => $item->cantidad_aprobada > 0 ? $item->cantidad_aprobada : $item->cantidad_solicitada,
                'precio_unitario' => 0,
                'subtotal' => 0,
            ])->toArray();

        if (is_callable($set)) {
            $set('items', []);
            $set('items', $items);
            $this->updateTotals(fn ($key) => $key === 'items' ? $items : 0, $set);
        }
    }

    private function updateTotals(mixed $get, mixed $set): void
    {
        $items = is_array($get) ? $get['items'] : (is_callable($get) ? $get('items') : []);
        $subtotalGeneral = 0;

        foreach ($items as $key => $item) {
            $cantidad = floatval($item['cantidad'] ?? 0);
            $precio = floatval($item['precio_unitario'] ?? 0);
            $subtotalItem = $cantidad * $precio;

            if (is_callable($set)) {
                $set("items.$key.subtotal", $subtotalItem);
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
