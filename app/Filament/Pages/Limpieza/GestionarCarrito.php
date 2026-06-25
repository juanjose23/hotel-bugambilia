<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\UseCases\Inventario\Movimientos\Mutations\TrasladarEntreBodegas;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

/**
 * @property Schema $abastecerForm
 * @property Schema $devolverForm
 * @property Schema $traspasarForm
 * @property Ubicacion|null $carrito
 */
class GestionarCarrito extends Page implements HasForms
{
    use InteractsWithForms;

    #[Url(as: 'carrito')]
    public ?int $carritoId = null;

    /** @var array<string, mixed>|null */
    public ?array $abastecerData = [];

    /** @var array<string, mixed>|null */
    public ?array $devolverData = [];

    /** @var array<string, mixed>|null */
    public ?array $traspasarData = [];

    public string $activeTab = 'abastecer';

    protected string $view = 'filament.pages.limpieza.gestionar-carrito';

    protected static ?string $slug = 'limpieza/gestionar-carrito';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Gestionar Carrito';

    public function mount(): void
    {
        if (! $this->carritoId) {
            $this->redirect(AbastecerCarrito::getUrl());

            return;
        }

        $this->abastecerForm->fill();
        $this->devolverForm->fill();
        $this->traspasarForm->fill();
    }

    public function getCarritoProperty(): ?Ubicacion
    {
        return $this->carritoId ? Ubicacion::find($this->carritoId) : null;
    }

    public function getTitle(): string|Htmlable
    {
        return $this->carrito
            ? "Gestionar Carrito: {$this->carrito->nombre}"
            : 'Gestionar Carrito';
    }

    /** @return array<string, Schema> */
    protected function getForms(): array
    {
        return [
            'abastecerForm' => $this->makeSchema()
                ->schema([
                    Select::make('bodega_origen_id')
                        ->label('Bodega / Almacén de Origen')
                        ->placeholder('Seleccione bodega de origen')
                        ->options(fn () => Ubicacion::whereIn('tipo', ['almacen', 'bodega'])
                            ->where('nombre', 'not like', 'Carrito%')
                            ->pluck('nombre', 'id')
                            ->toArray())
                        ->required()
                        ->live()
                        ->native(false)
                        ->prefixIcon(Heroicon::Home),

                    Repeater::make('items')
                        ->label('Insumos a Trasladar')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            Select::make('stock_id')
                                ->label('Seleccionar Insumo (Lote & Disponible)')
                                ->placeholder('Seleccione insumo')
                                ->options(function (Get $get) {
                                    $origenId = $get('../../bodega_origen_id');
                                    if (! $origenId) {
                                        return [];
                                    }

                                    return Stock::with(['variante.producto', 'lote'])
                                        ->where('ubicacion_id', $origenId)
                                        ->where('cantidad', '>', 0)
                                        ->get()
                                        ->mapWithKeys(function ($stock) {
                                            $nombre = ($stock->variante->producto->nombre ?? 'Insumo')
                                                .($stock->variante?->nombre_variante ? " ({$stock->variante->nombre_variante})" : '')
                                                .' [Lote: '.($stock->lote->codigo_lote ?? 'N/A').']'
                                                ." (Disp: {$stock->cantidad})";

                                            return [$stock->id => $nombre];
                                        })
                                        ->toArray();
                                })
                                ->required()
                                ->live()
                                ->native(false)
                                ->prefixIcon(Heroicon::ListBullet)
                                ->afterStateUpdated(function (?int $state, Set $set) {
                                    if ($state) {
                                        $stock = Stock::find($state);
                                        if ($stock) {
                                            $set('max_qty', (float) $stock->cantidad);
                                        }
                                    } else {
                                        $set('max_qty', 0);
                                    }
                                }),

                            TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->required()
                                ->prefixIcon(Heroicon::Sparkles)
                                ->minValue(0.01)
                                ->maxValue(fn (Get $get) => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 999999.0),

                            Hidden::make('max_qty')
                                ->default(0),
                        ])
                        ->createItemButtonLabel('Agregar insumo'),
                ])
                ->statePath('abastecerData'),

            'devolverForm' => $this->makeSchema()
                ->schema([
                    Select::make('stock_id')
                        ->label('Insumo en Carrito')
                        ->placeholder('Seleccione insumo')
                        ->options(fn () => $this->carritoId ? Stock::with(['variante.producto', 'lote'])
                            ->where('ubicacion_id', $this->carritoId)
                            ->where('cantidad', '>', 0)
                            ->get()
                            ->mapWithKeys(function ($stock) {
                                $nombre = ($stock->variante->producto->nombre ?? 'Insumo')
                                    .($stock->variante?->nombre_variante ? " ({$stock->variante->nombre_variante})" : '')
                                    .' [Lote: '.($stock->lote->codigo_lote ?? 'N/A').']'
                                    ." (Disp: {$stock->cantidad})";

                                return [$stock->id => $nombre];
                            })
                            ->toArray() : []
                        )
                        ->required()
                        ->live()
                        ->native(false)
                        ->afterStateUpdated(function (?int $state, Set $set) {
                            if ($state) {
                                $stock = Stock::find($state);
                                if ($stock) {
                                    $set('cantidad', $stock->cantidad);
                                    $set('max_qty', $stock->cantidad);
                                }
                            }
                        }),

                    Select::make('bodega_destino_id')
                        ->label('Bodega de Destino')
                        ->placeholder('Seleccione destino')
                        ->options(fn () => Ubicacion::whereIn('tipo', ['almacen', 'bodega'])
                            ->where('nombre', 'not like', 'Carrito%')
                            ->pluck('nombre', 'id')
                            ->toArray())
                        ->required()
                        ->native(false),

                    TextInput::make('cantidad')
                        ->label('Cantidad a Devolver')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->maxValue(fn (Get $get) => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 99999.0),

                    Hidden::make('max_qty')
                        ->default(0),
                ])
                ->statePath('devolverData'),

            'traspasarForm' => $this->makeSchema()
                ->schema([
                    Select::make('stock_id')
                        ->label('Insumo en Carrito')
                        ->placeholder('Seleccione insumo')
                        ->options(fn () => $this->carritoId ? Stock::with(['variante.producto', 'lote'])
                            ->where('ubicacion_id', $this->carritoId)
                            ->where('cantidad', '>', 0)
                            ->get()
                            ->mapWithKeys(function ($stock) {
                                $nombre = ($stock->variante->producto->nombre ?? 'Insumo')
                                    .($stock->variante?->nombre_variante ? " ({$stock->variante->nombre_variante})" : '')
                                    .' [Lote: '.($stock->lote->codigo_lote ?? 'N/A').']'
                                    ." (Disp: {$stock->cantidad})";

                                return [$stock->id => $nombre];
                            })
                            ->toArray() : []
                        )
                        ->required()
                        ->live()
                        ->native(false)
                        ->afterStateUpdated(function (?int $state, Set $set) {
                            if ($state) {
                                $stock = Stock::find($state);
                                if ($stock) {
                                    $set('cantidad', $stock->cantidad);
                                    $set('max_qty', $stock->cantidad);
                                }
                            }
                        }),

                    Select::make('carrito_destino_id')
                        ->label('Carrito de Destino')
                        ->placeholder('Seleccione carrito destino')
                        ->options(fn () => Ubicacion::where('nombre', 'like', 'Carrito%')
                            ->where('id', '!=', $this->carritoId)
                            ->pluck('nombre', 'id')
                            ->toArray())
                        ->required()
                        ->native(false),

                    TextInput::make('cantidad')
                        ->label('Cantidad a Traspasar')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->maxValue(fn (Get $get) => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 99999.0),

                    Hidden::make('max_qty')
                        ->default(0),
                ])
                ->statePath('traspasarData'),
        ];
    }

    public function submitAbastecer(TrasladarEntreBodegas $trasladarEntreBodegas): void
    {
        $data = $this->abastecerForm->getState();
        if (empty($data)) {
            return;
        }
        $origenId = isset($data['bodega_origen_id']) && is_numeric($data['bodega_origen_id']) ? (int) $data['bodega_origen_id'] : 0;
        $destinoId = (int) $this->carritoId;
        $items = $data['items'] ?? [];
        if (! is_array($items)) {
            $items = [];
        }

        if (empty($items)) {
            Notification::make()->title('Error')->body('Debe agregar al menos un insumo.')->danger()->send();

            return;
        }

        try {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $stock = isset($item['stock_id']) ? Stock::find($item['stock_id']) : null;
                if ($stock instanceof Stock) {
                    $cantidad = isset($item['cantidad']) && is_numeric($item['cantidad']) ? (float) $item['cantidad'] : 0.0;
                    $trasladarEntreBodegas->execute(
                        productoId: (int) $stock->producto_id,
                        loteId: (int) $stock->lote_id,
                        cantidad: $cantidad,
                        origenId: $origenId,
                        destinoId: $destinoId,
                        productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                        creadoPorId: (int) auth()->id(),
                        referencia: "Abastecimiento de Carrito #{$destinoId}",
                        notas: 'Carga de insumos para carrito de limpieza.'
                    );
                }
            }
            Notification::make()->title('Abastecimiento Exitoso')->body('Se cargaron los insumos correctamente al carrito.')->success()->send();
            $this->abastecerForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error de traslado')->body($e->getMessage())->danger()->send();
        }
    }

    public function submitDevolver(TrasladarEntreBodegas $trasladarEntreBodegas): void
    {
        $data = $this->devolverForm->getState();
        if (empty($data)) {
            return;
        }
        $stock = isset($data['stock_id']) ? Stock::find($data['stock_id']) : null;
        if (! $stock instanceof Stock) {
            return;
        }

        try {
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $destinoId = isset($data['bodega_destino_id']) && is_numeric($data['bodega_destino_id']) ? (int) $data['bodega_destino_id'] : 0;
            $trasladarEntreBodegas->execute(
                productoId: (int) $stock->producto_id,
                loteId: (int) $stock->lote_id,
                cantidad: $cantidad,
                origenId: (int) $this->carritoId,
                destinoId: $destinoId,
                productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                creadoPorId: (int) auth()->id(),
                referencia: "Devolución de Carrito #{$this->carritoId} a Bodega #{$destinoId}",
                notas: 'Devolución de insumos sobrantes a almacén.'
            );
            Notification::make()->title('Devolución Exitosa')->body('Se devolvió el insumo a la bodega.')->success()->send();
            $this->devolverForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function submitTraspasar(TrasladarEntreBodegas $trasladarEntreBodegas): void
    {
        $data = $this->traspasarForm->getState();
        if (empty($data)) {
            return;
        }
        $stock = isset($data['stock_id']) ? Stock::find($data['stock_id']) : null;
        if (! $stock instanceof Stock) {
            return;
        }

        try {
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $destinoId = isset($data['carrito_destino_id']) && is_numeric($data['carrito_destino_id']) ? (int) $data['carrito_destino_id'] : 0;
            $trasladarEntreBodegas->execute(
                productoId: (int) $stock->producto_id,
                loteId: (int) $stock->lote_id,
                cantidad: $cantidad,
                origenId: (int) $this->carritoId,
                destinoId: $destinoId,
                productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                creadoPorId: (int) auth()->id(),
                referencia: "Traspaso de Carrito #{$this->carritoId} a Carrito #{$destinoId}",
                notas: 'Traspaso de insumos entre carritos de limpieza.'
            );
            Notification::make()->title('Traspaso Exitoso')->body('Se traspasó el insumo al otro carrito.')->success()->send();
            $this->traspasarForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->carritoId
            ? Stock::with(['variante.producto', 'lote'])
                ->where('ubicacion_id', $this->carritoId)
                ->where('cantidad', '>', 0)
                ->get()
            : new Collection;
    }

    /**
     * @return Collection<int, MovimientoStock>
     */
    public function getMovimientos(): Collection
    {
        if (! $this->carritoId) {
            return new Collection;
        }

        return MovimientoStock::with(['producto', 'lote', 'ubicacionOrigen', 'ubicacionDestino', 'creadoPor.persona'])
            ->where(function ($q) {
                $q->where('ubicacion_origen_id', $this->carritoId)
                    ->orWhere('ubicacion_destino_id', $this->carritoId);
            })
            ->latest()
            ->take(15)
            ->get();
    }
}
