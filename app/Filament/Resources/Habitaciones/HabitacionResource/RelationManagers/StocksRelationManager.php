<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers;

use App\Enums\HabitacionesEspacios\EstadoStock;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\ProductoKit;
use App\UseCases\Habitaciones\Mutations\AsignarPackAHabitacion;
use App\UseCases\Habitaciones\Mutations\RegistrarConsumoHabitacion;
use App\UseCases\Inventario\Queries\Stock\VerificarStockPack;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StocksRelationManager extends RelationManager
{
    protected static string $relationship = 'habitacionStocks';

    protected static ?string $title = 'Inventario Operativo — Blancos, Toallas y Minibar';

    protected static ?string $label = 'Ítem en Habitación';

    protected static ?string $pluralLabel = 'Stock en Habitación';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('producto_variante_id')
                    ->label('Producto / Variante')
                    ->options(
                        ProductoVariante::with('producto')
                            ->get()
                            ->mapWithKeys(fn (ProductoVariante $v) => [
                                $v->id => "[{$v->producto?->nombre}] {$v->nombre_variante} ({$v->codigo})",
                            ])
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('cantidad_ideal')
                    ->label('Cantidad ideal')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),

                TextInput::make('cantidad_actual')
                    ->label('Cantidad actual')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('producto_nombre')
                    ->label('Producto')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->whereHas('variante.producto', fn (Builder $q) => $q->where('nombre', 'ilike', "%{$search}%"));
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('producto_variantes', 'habitacion_stocks.producto_variante_id', '=', 'producto_variantes.id')
                            ->leftJoin('productos', 'producto_variantes.producto_id', '=', 'productos.id')
                            ->orderBy('productos.nombre', $direction === 'desc' ? 'desc' : 'asc')
                            ->select('habitacion_stocks.*');
                    }),

                TextColumn::make('variante.nombre_variante')
                    ->label('Variante')
                    ->searchable(),

                TextColumn::make('cantidad_ideal')
                    ->label('Ideal')
                    ->sortable(),

                TextColumn::make('cantidad_actual')
                    ->label('Actual')
                    ->sortable(),

                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->state(fn ($record) => (float) $record->cantidad_actual - (float) $record->cantidad_ideal)
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state > 0 ? 'warning' : 'success')),

                TextColumn::make('estado_enum')
                    ->label('Estado')
                    ->badge()
                    ->state(fn ($record) => $record->estado_enum)
                    ->color(fn (EstadoStock $state) => $state->color())
                    ->icon(fn (EstadoStock $state) => $state->getIcon()),

                TextColumn::make('ultima_verificacion')
                    ->label('Últ. Verificación')
                    ->dateTime()
                    ->placeholder('Nunca')
                    ->sortable(),
            ])
            ->defaultSort('habitacion_stocks.id')
            ->headerActions([
                Action::make('surtir_pack')
                    ->label('Surtir Pack de Blancos/Toallas')
                    ->icon(Heroicon::ArchiveBoxArrowDown)
                    ->color('success')
                    ->modalHeading('Surtir Pack a la Habitación')
                    ->modalDescription('Seleccione el pack de productos y la bodega de origen. Los items se consumirán del inventario central y se asignarán a esta habitación.')
                    ->schema([
                        Select::make('producto_pack_id')
                            ->label('Pack / Kit')
                            ->placeholder('Seleccione un pack')
                            ->options(
                                Producto::whereIn('id', function ($q) {
                                    $q->select('producto_padre_id')->from('producto_kit');
                                })->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                $this->actualizarPreviewPack($set, $get, (int) $state);
                            }),

                        Select::make('bodega_origen_id')
                            ->label('Bodega de origen')
                            ->placeholder('Seleccione la bodega')
                            ->options(
                                Ubicacion::where('tipo', 'almacen')
                                    ->where('estado', 1)
                                    ->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                $packId = $get('producto_pack_id');
                                if ($packId && is_numeric($packId)) {
                                    $this->actualizarPreviewPack($set, $get, (int) $packId);
                                }
                            }),

                        TextInput::make('cantidad_packs')
                            ->label('Cantidad de packs')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get): void {
                                $packId = $get('producto_pack_id');
                                if ($packId && is_numeric($packId)) {
                                    $this->actualizarPreviewPack($set, $get, (int) $packId);
                                }
                            }),

                        Repeater::make('items_preview')
                            ->label('Items incluidos en el pack')
                            ->schema([
                                TextInput::make('producto')->disabled()->label('Producto'),
                                TextInput::make('variante')->disabled()->label('Variante'),
                                TextInput::make('necesario')->disabled()->label('Necesario'),
                                TextInput::make('disponible')->disabled()->label('Disponible'),
                                TextInput::make('estado')->disabled()->label('Estado'),
                            ])
                            ->disabled()
                            ->columns(5)
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->action(function (array $data, Action $action): void {
                        try {
                            $owner = $this->getOwnerRecord();
                            $ownerKey = $owner->getKey();
                            $habitacionId = is_numeric($ownerKey) ? (int) $ownerKey : 0;
                            app(AsignarPackAHabitacion::class)->execute(
                                habitacionId: $habitacionId,
                                productoPackId: (int) $data['producto_pack_id'],
                                bodegaOrigenId: (int) $data['bodega_origen_id'],
                                cantidadPacks: (float) $data['cantidad_packs'],
                                creadoPorId: (int) auth()->id(),
                            );
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->danger()
                                ->title('Stock insuficiente')
                                ->body($e->getMessage())
                                ->send();

                            $action->halt();
                        }
                    }),

                CreateAction::make()
                    ->label('Agregar Item Manual')
                    ->icon(Heroicon::Plus),
            ])
            ->actions([
                Action::make('consumir')
                    ->label('Registrar Consumo')
                    ->icon(Heroicon::MinusCircle)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Registrar Consumo en Habitación')
                    ->schema([
                        TextInput::make('cantidad')
                            ->label('Cantidad consumida')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->rule(function (Get $get, $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($record): void {
                                    if ((float) $value > (float) $record->cantidad_actual) {
                                        $fail("La cantidad no puede exceder el stock actual ({$record->cantidad_actual}).");
                                    }
                                };
                            }),
                        TextInput::make('motivo')
                            ->label('Motivo')
                            ->required()
                            ->default('Consumo de huésped'),
                    ])
                    ->action(function (array $data, $record): void {
                        app(RegistrarConsumoHabitacion::class)->execute(
                            habitacionStockId: $record->id,
                            cantidad: (float) $data['cantidad'],
                            motivo: $data['motivo'],
                            creadoPorId: (int) auth()->id(),
                        );
                    }),
            ]);
    }

    private function actualizarPreviewPack(callable $set, callable $get, int $productoPackId): void
    {
        $bodegaId = $get('bodega_origen_id');
        $cantidadPacks = (float) ($get('cantidad_packs') ?: 1);

        if (! $bodegaId) {
            $items = ProductoKit::with('variante.producto')
                ->where('producto_padre_id', $productoPackId)
                ->get();
            $preview = [];
            foreach ($items as $item) {
                $variante = $item->variante;
                $preview[] = [
                    'producto' => ($variante && $variante->producto) ? $variante->producto->nombre : '—',
                    'variante' => $variante->nombre_variante ?? 'N/A',
                    'necesario' => (string) ($item->cantidad * $cantidadPacks),
                    'disponible' => '—',
                    'estado' => 'Seleccione bodega',
                ];
            }
            $set('items_preview', $preview);

            return;
        }

        $verificacion = app(VerificarStockPack::class)->ejecutar(
            productoPackId: $productoPackId,
            bodegaOrigenId: (int) $bodegaId,
            cantidadPacks: $cantidadPacks,
        );

        $preview = [];
        foreach ($verificacion['items'] as $item) {
            $preview[] = [
                'producto' => $item['producto'],
                'variante' => $item['variante'],
                'necesario' => (string) $item['necesario'],
                'disponible' => (string) $item['disponible'],
                'estado' => $item['suficiente'] ? '✅ Suficiente' : '❌ Insuficiente',
            ];
        }
        $set('items_preview', $preview);
    }
}
