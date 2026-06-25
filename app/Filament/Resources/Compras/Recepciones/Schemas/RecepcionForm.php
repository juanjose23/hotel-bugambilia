<?php

namespace App\Filament\Resources\Compras\Recepciones\Schemas;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\UseCases\Compras\OrdenesCompra\Queries\ObtenerOrdenCompraConItems;
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

class RecepcionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Encabezado de Recepción')
                    ->description('Vincule la recepción con una orden de compra activa')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código Recepción')
                            ->disabled()
                            ->dehydrated(true)
                            ->placeholder('REC-YYYY-NNN')
                            ->prefixIcon(Heroicon::QrCode),

                        Select::make('orden_compra_id')
                            ->label('Orden de Compra')
                            ->relationship(
                                name: 'ordenCompra',
                                titleAttribute: 'codigo',
                                modifyQueryUsing: fn (Builder $query) => $query->whereIn('estado', [
                                    EstadoOrdenCompra::Emitida,
                                    EstadoOrdenCompra::EnTransito,
                                    EstadoOrdenCompra::Parcial,
                                ])
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->prefixIcon(Heroicon::DocumentText)
                            ->afterStateUpdated(function ($state, $set) {
                                if (! $state) {
                                    $set('items', []);

                                    return;
                                }

                                $useCase = app(ObtenerOrdenCompraConItems::class);
                                $orden = $useCase->execute($state);
                                if ($orden) {
                                    $items = [];
                                    foreach ($orden->items as $item) {
                                        $pending = $item->cantidad_pendiente ?? (float) $item->cantidad;
                                        if ($pending > 0) {
                                            $items[] = [
                                                'orden_item_id' => $item->id,
                                                'cantidad_ordenada' => (float) $item->cantidad,
                                                'cantidad_pendiente' => $pending,
                                                'cantidad_recibida' => $pending,
                                                'cantidad_rechazada' => 0,
                                            ];
                                        }
                                    }
                                    $set('items', $items);
                                }
                            }),

                        DatePicker::make('fecha_recepcion')
                            ->label('Fecha de Recepción')
                            ->default(now())
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),

                        TextInput::make('guia_remision')
                            ->label('Guía de Remisión / Factura')
                            ->placeholder('Número de documento físico')
                            ->maxLength(50)
                            ->prefixIcon(Heroicon::Hashtag),

                        Hidden::make('estado'),

                        Select::make('recibido_por_id')
                            ->label('Recibido por')
                            ->relationship('receptor', 'name')
                            ->default(auth()->id())
                            ->required()
                            ->prefixIcon(Heroicon::User),

                        Select::make('ubicacion_id')
                            ->label('Ubicación de Destino General')
                            ->relationship(
                                name: 'ubicacion',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->where('estado', 1)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::MapPin),

                        Textarea::make('notas')
                            ->label('Notas de Almacén')
                            ->placeholder('Observaciones sobre el estado de la mercancía...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Detalle de Ítems Recibidos')
                    ->description('Registre las cantidades físicas que están ingresando al almacén')
                    ->icon(Heroicon::ArchiveBox)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship('items')
                            ->minItems(1)
                            ->schema([
                                Select::make('orden_item_id')
                                    ->label('Producto de la Orden')
                                    ->options(fn ($get) => self::getOrdenItemsOptions($get('../../orden_compra_id')))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if (! $state) {
                                            return;
                                        }
                                        $useCase = app(ObtenerOrdenCompraConItems::class);
                                        $pending = $useCase->getItemPendingQuantity($state);

                                        // Obtener cantidad original de la orden
                                        $ordenItem = OrdenCompraItem::find((int) $state);

                                        if (is_callable($set)) {
                                            $set('cantidad_ordenada', $ordenItem ? (float) $ordenItem->cantidad : 0);
                                        }
                                        $set('cantidad_pendiente', $pending);
                                        $set('cantidad_recibida', $pending);
                                        $set('cantidad_rechazada', 0);
                                    })
                                    ->searchable()
                                    ->columnSpan(8)
                                    ->prefixIcon(Heroicon::Cube),

                                TextInput::make('lote_proveedor')
                                    ->label('Lote Proveedor')
                                    ->placeholder('Lote del fabricante...')
                                    ->maxLength(100)
                                    ->columnSpan(4)
                                    ->prefixIcon(Heroicon::Hashtag),

                                TextInput::make('cantidad_ordenada')
                                    ->label('Cant. Ordenada')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($state, $set, $get) {
                                        $ordenItemId = $get('orden_item_id');
                                        if ($ordenItemId) {
                                            $ordenItem = OrdenCompraItem::find((int) $ordenItemId);
                                            if (is_callable($set)) {
                                                $set('cantidad_ordenada', $ordenItem ? (float) $ordenItem->cantidad : 0);
                                            }
                                        }
                                    })
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::ClipboardDocumentCheck),

                                TextInput::make('cantidad_pendiente')
                                    ->label('Cant. Pendiente')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($state, $set, $get) {
                                        $ordenItemId = $get('orden_item_id');
                                        if ($ordenItemId) {
                                            if (is_callable($set)) {
                                                $set('cantidad_pendiente', app(ObtenerOrdenCompraConItems::class)->getItemPendingQuantity((int) $ordenItemId));
                                            }
                                        }
                                    })
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::Clock),

                                TextInput::make('cantidad_recibida')
                                    ->label('Cant. Recibida')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(fn ($get) => (float) ($get('cantidad_pendiente') ?? 0))
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::Hashtag),

                                TextInput::make('cantidad_rechazada')
                                    ->label('Cant. Rechazada')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::XCircle),

                                DatePicker::make('fecha_vencimiento')
                                    ->label('Fecha Vencimiento')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(4),

                                Select::make('ubicacion_id')
                                    ->label('Ubicación de Destino')
                                    ->relationship(
                                        name: 'ubicacion',
                                        titleAttribute: 'nombre',
                                        modifyQueryUsing: fn (Builder $query) => $query->where('estado', 1)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('General por defecto')
                                    ->columnSpan(8)
                                    ->prefixIcon(Heroicon::MapPin),

                                Textarea::make('motivo_rechazo')
                                    ->label('Motivo de Rechazo')
                                    ->placeholder('Ej: Empaque dañado...')
                                    ->rows(1)
                                    ->columnSpan(8),

                                Textarea::make('nota')
                                    ->label('Nota / Observación')
                                    ->placeholder('Comentario general del ítem...')
                                    ->rows(1)
                                    ->columnSpanFull(),
                            ])
                            ->columns(12)
                            ->addActionLabel('Registrar otro ítem')
                            ->defaultItems(0),
                    ]),
            ]);
    }

    /** @return array<int|string, string> */
    private static function getOrdenItemsOptions(?int $ordenId): array
    {
        if (! $ordenId) {
            return [];
        }

        return app(ObtenerOrdenCompraConItems::class)->getItemOptions($ordenId);
    }
}
