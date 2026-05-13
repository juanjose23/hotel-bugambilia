<?php

namespace App\Filament\Resources\Compras\Recepciones\Schemas;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\OrdenCompraItem;
use App\UseCases\Compras\ObtenerOrdenCompraConItems;
use Filament\Forms\Components\DatePicker;
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
                                    EstadoOrdenCompra::Recibida,
                                ])
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->prefixIcon(Heroicon::DocumentText)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (! $state) {
                                    $set('items', []);

                                    return;
                                }

                                // Si ya hay ítems y corresponden a esta orden (precarga), no sobrescribir
                                if (count($get('items') ?? []) > 0) {
                                    return;
                                }

                                $orden = app(ObtenerOrdenCompraConItems::class)->execute($state);
                                if ($orden) {
                                    $set('items', $orden->items->map(fn ($item) => [
                                        'orden_item_id' => $item->id,
                                        'cantidad_recibida' => $item->cantidad,
                                        'cantidad_rechazada' => 0,
                                    ])->toArray());
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

                        Select::make('estado')
                            ->label('Estado de Recepción')
                            ->options(EstadoRecepcion::class)
                            ->required()
                            ->preload()
                            ->prefixIcon(Heroicon::CheckCircle),

                        Select::make('recibido_por_id')
                            ->label('Recibido por')
                            ->relationship('receptor', 'name')
                            ->default(auth()->id())
                            ->required()
                            ->prefixIcon(Heroicon::User),

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
                            ->label('')
                            ->relationship('items')
                            ->schema([
                                Select::make('orden_item_id')
                                    ->label('Producto de la Orden')
                                    ->options(fn ($get) => self::getOrdenItemsOptions($get('../../orden_compra_id')))
                                    ->required()
                                    ->live()
                                    ->searchable()
                                    ->columnSpan(3)
                                    ->prefixIcon(Heroicon::Cube),

                                TextInput::make('cantidad_recibida')
                                    ->label('Cant. Recibida')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->columnSpan(1)
                                    ->prefixIcon(Heroicon::Hashtag),

                                TextInput::make('cantidad_rechazada')
                                    ->label('Cant. Rechazada')
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(1)
                                    ->prefixIcon(Heroicon::XCircle),

                                Textarea::make('observaciones')
                                    ->label('Notas del Ítem')
                                    ->placeholder('Ej: Empaque dañado')
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
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

        return OrdenCompraItem::where('orden_compra_id', $ordenId)
            ->with(['producto', 'variante'])
            ->get()
            ->mapWithKeys(function ($item) {
                $label = "{$item->producto->nombre}";
                if ($item->variante) {
                    $label .= " ({$item->variante->codigo})";
                }
                $label .= " | Ordenado: {$item->cantidad}";

                return [$item->id => $label];
            })
            ->toArray();
    }
}
