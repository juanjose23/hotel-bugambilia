<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Schemas;

use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Resources\Catalogos\Productos\ProductoResource;
use App\Filament\Resources\Restaurante\PlatoResource\PlatoResource;
use App\Filament\Shared\Forms\SelectorCuenta;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Restaurante\Pedidos\BuscarClientesRapidoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerDatosPedidoFormQuery;
use App\Repository\Queries\Restaurante\Stock\ObtenerStockDisponibleRestauranteQuery;
use App\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

final class PedidoForm
{
    public static function configure(Schema $schema): Schema
    {
        $pedidoQuery = app(ObtenerDatosPedidoFormQuery::class);
        $stockQuery = app(ObtenerStockDisponibleRestauranteQuery::class);
        $monedaPredeterminada = app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar();
        $simboloMoneda = MonedaHelper::simbolo($monedaPredeterminada);

        return $schema
            ->columns(1)
            ->components([
                Section::make('Datos de la Comanda / Pedido')
                    ->description('Seleccione la mesa asignada y el cliente en caso de ser necesario.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('codigo')
                                ->label('Código Comanda')
                                ->disabled()
                                ->dehydrated(false)
                                ->maxLength(20)
                                ->prefixIcon(Heroicon::Key),

                            Select::make('mesa_id')
                                ->label('Mesa de Servicio')
                                ->options(fn () => $pedidoQuery->mesasDisponibles())
                                ->searchable()
                                ->required()
                                ->live()
                                ->extraAttributes(['dusk' => 'pedido-mesa'])
                                ->prefixIcon('hugeicons-restaurant-table'),

                            Select::make('estado')
                                ->label('Estado Comanda')
                                ->options(EstadoPedido::class)
                                ->default(EstadoPedido::ABIERTO)
                                ->disabled()
                                ->dehydrated(false)
                                ->prefixIcon(Heroicon::ArrowPath),
                        ]),

                        Grid::make(2)->schema([
                            SelectorCuenta::make(columnSpan: 1)
                                ->live()
                                ->afterStateUpdated(function (mixed $state, Set $set): void {
                                    if (! is_numeric($state)) {
                                        return;
                                    }

                                    $clienteId = Cuenta::query()
                                        ->whereKey((int) $state)
                                        ->value('cliente_id');

                                    if (is_numeric($clienteId)) {
                                        $set('cliente_id', (int) $clienteId);
                                    }
                                }),

                            TextInput::make('subtotal')
                                ->label('Subtotal Acumulado')
                                ->prefix($simboloMoneda)
                                ->formatStateUsing(fn ($record, $state): string => number_format(
                                    (float) ($record?->calcularSubtotal() ?? $state ?? 0.00),
                                    2
                                ))
                                ->disabled()
                                ->dehydrated(false),
                        ]),

                        Select::make('cliente_id')
                            ->label('Cliente (Opcional)')
                            ->placeholder('Buscar por nombre o teléfono...')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => app(BuscarClientesRapidoQuery::class)
                                ->ejecutar($search)
                                ->mapWithKeys(fn (Cliente $cliente): array => [
                                    $cliente->id => trim(
                                        ($cliente->persona->nombre_completo ?? 'Cliente #'.$cliente->id).' — '.($cliente->telefono ?? 'S/T')
                                    ),
                                ])
                                ->toArray())
                            ->getOptionLabelUsing(function ($value): ?string {
                                if (! is_numeric($value)) {
                                    return null;
                                }

                                $cliente = Cliente::query()
                                    ->with(['persona.personaNatural', 'persona.personaJuridica'])
                                    ->find((int) $value);
                                $persona = $cliente?->persona;

                                return $persona instanceof Persona ? ($persona->nombre_completo ?? 'Cliente #'.$value) : 'Cliente #'.$value;
                            })
                            ->nullable()
                            ->native(false)
                            ->extraAttributes(['dusk' => 'pedido-cliente'])
                            ->prefixIcon(Heroicon::User)
                            ->columnSpan(1),

                        Textarea::make('notas')
                            ->label('Notas Generales de la Comanda')
                            ->columnSpanFull()
                            ->rows(2)
                            ->placeholder('Ej. Mesa VIP / Solicitud especial de cliente...'),
                    ]),

                Section::make('Ítems de la Comanda: Menú & Productos')
                    ->description('Ordene platillos de cocina o agregue productos directos de inventario/bar con control de existencias en tiempo real.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship('items')
                            ->extraItemActions([
                                Action::make('verFicha')
                                    ->tooltip('Ver ficha del producto o receta en nueva pestaña')
                                    ->icon(Heroicon::ArrowTopRightOnSquare)
                                    ->color('gray')
                                    ->url(function (array $arguments, Repeater $component): ?string {
                                        $item = $component->getRawItemState($arguments['item']);
                                        $pId = $item['producto_id'] ?? null;
                                        if (is_numeric($pId)) {
                                            return ProductoResource::getUrl('view', ['record' => (int) $pId]);
                                        }
                                        $platoId = $item['plato_id'] ?? null;
                                        if (is_numeric($platoId)) {
                                            return PlatoResource::getUrl('view', ['record' => (int) $platoId]);
                                        }

                                        return null;
                                    })
                                    ->openUrlInNewTab()
                                    ->visible(function (array $arguments, Repeater $component): bool {
                                        $item = $component->getRawItemState($arguments['item']);

                                        return ! empty($item['producto_id']) || ! empty($item['plato_id']);
                                    }),
                            ])
                            ->schema([
                                Select::make('tipo_item')
                                    ->label('Tipo')
                                    ->options([
                                        'plato' => '🍳 Platillo Cocina',
                                        'producto' => '📦 Producto Bar/Stock',
                                    ])
                                    ->default('plato')
                                    ->selectablePlaceholder(false)
                                    ->live()
                                    ->columnSpan(3)
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('plato_id', null);
                                        $set('producto_id', null);
                                        $set('producto_variante_id', null);
                                        $set('precio_unitario', 0.00);
                                    }),

                                Select::make('plato_id')
                                    ->label('Platillo / Bebida Menú')
                                    ->options(fn () => $pedidoQuery->platosActivosAgrupadosPorCategoria())
                                    ->searchable()
                                    ->required(fn ($get): bool => $get('tipo_item') === 'plato' || empty($get('tipo_item')))
                                    ->visible(fn ($get): bool => $get('tipo_item') === 'plato' || empty($get('tipo_item')))
                                    ->live()
                                    ->extraAttributes(['dusk' => 'pedido-plato'])
                                    ->suffixAction(
                                        Action::make('abrirPlato')
                                            ->icon(Heroicon::ArrowTopRightOnSquare)
                                            ->tooltip('Abrir receta del platillo')
                                            ->url(fn ($state): ?string => is_numeric($state) ? PlatoResource::getUrl('view', ['record' => (int) $state]) : null)
                                            ->openUrlInNewTab()
                                            ->visible(fn ($state): bool => filled($state))
                                    )
                                    ->afterStateUpdated(function (mixed $state, Set $set) use ($pedidoQuery): void {
                                        if (is_numeric($state)) {
                                            $precio = $pedidoQuery->precioActualDePlato((int) $state);
                                            if ($precio !== null) {
                                                $set('precio_unitario', $precio);
                                            }
                                        }
                                    })
                                    ->columnSpan(4),

                                Select::make('producto_id')
                                    ->label('Producto de Inventario')
                                    ->options(fn () => $pedidoQuery->productosVendiblesAgrupadosPorCategoria())
                                    ->searchable()
                                    ->required(fn ($get): bool => $get('tipo_item') === 'producto')
                                    ->visible(fn ($get): bool => $get('tipo_item') === 'producto')
                                    ->live()
                                    ->suffixAction(
                                        Action::make('abrirProducto')
                                            ->icon(Heroicon::ArrowTopRightOnSquare)
                                            ->tooltip('Abrir ficha de inventario')
                                            ->url(fn ($state): ?string => is_numeric($state) ? ProductoResource::getUrl('view', ['record' => (int) $state]) : null)
                                            ->openUrlInNewTab()
                                            ->visible(fn ($state): bool => filled($state))
                                    )
                                    ->helperText(function ($get) use ($stockQuery): ?HtmlString {
                                        if ($get('tipo_item') !== 'producto' || ! is_numeric($get('producto_id'))) {
                                            return null;
                                        }
                                        $pId = (int) $get('producto_id');
                                        $vId = is_numeric($get('producto_variante_id')) ? (int) $get('producto_variante_id') : null;
                                        $mesaId = is_numeric($get('../../mesa_id')) ? (int) $get('../../mesa_id') : null;

                                        $stockInfo = $stockQuery->ejecutar($pId, $vId, $mesaId);
                                        $cant = $stockInfo['disponible'];
                                        $pv = e($stockInfo['punto_venta']);

                                        if ($cant > 0) {
                                            return new HtmlString("<span class='text-xs font-medium text-emerald-600 dark:text-emerald-400'>🟢 Stock disponible en {$pv}: <strong>{$cant}</strong></span>");
                                        }

                                        return new HtmlString("<span class='text-xs font-medium text-amber-600 dark:text-amber-400'>⚠️ Sin existencias en {$pv}</span>");
                                    })
                                    ->afterStateUpdated(function (mixed $state, Set $set, $get) use ($stockQuery, $pedidoQuery): void {
                                        $set('producto_variante_id', null);
                                        if (is_numeric($state)) {
                                            $pId = (int) $state;
                                            if (! $pedidoQuery->productoTieneVariantes($pId)) {
                                                $precio = $stockQuery->obtenerPrecioVenta($pId, null);
                                                if ($precio > 0) {
                                                    $set('precio_unitario', $precio);
                                                }
                                            }
                                        }
                                    })
                                    ->columnSpan(function ($get) use ($pedidoQuery): int {
                                        $pId = $get('producto_id');

                                        return is_numeric($pId) && $pedidoQuery->productoTieneVariantes((int) $pId) ? 2 : 4;
                                    }),

                                Select::make('producto_variante_id')
                                    ->label('Presentación / Variante')
                                    ->options(function ($get) use ($pedidoQuery): array {
                                        $pId = $get('producto_id');

                                        return is_numeric($pId) ? $pedidoQuery->variantesDeProducto((int) $pId) : [];
                                    })
                                    ->searchable()
                                    ->visible(function ($get) use ($pedidoQuery): bool {
                                        $pId = $get('producto_id');

                                        return $get('tipo_item') === 'producto' && is_numeric($pId) && $pedidoQuery->productoTieneVariantes((int) $pId);
                                    })
                                    ->required(function ($get) use ($pedidoQuery): bool {
                                        $pId = $get('producto_id');

                                        return $get('tipo_item') === 'producto' && is_numeric($pId) && $pedidoQuery->productoTieneVariantes((int) $pId);
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (mixed $state, Set $set, $get) use ($stockQuery): void {
                                        $pId = (int) ($get('producto_id') ?? 0);
                                        $vId = is_numeric($state) ? (int) $state : null;
                                        if ($pId > 0) {
                                            $precio = $stockQuery->obtenerPrecioVenta($pId, $vId);
                                            if ($precio > 0) {
                                                $set('precio_unitario', $precio);
                                            }
                                        }
                                    })
                                    ->columnSpan(2),

                                TextInput::make('cantidad')
                                    ->label('Cant.')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->extraInputAttributes(['dusk' => 'pedido-cantidad'])
                                    ->columnSpan(1),

                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->default(0.00)
                                    ->prefix($simboloMoneda)
                                    ->columnSpan(2),

                                TextInput::make('observaciones')
                                    ->label('Observaciones / Notas')
                                    ->columnSpan(2)
                                    ->placeholder('Ej. Término medio, con hielo...'),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Ítem a la Comanda'),
                    ]),
            ]);
    }
}
