<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionStockResource\Tables;

use App\Enums\HabitacionesEspacios\EstadoStock;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\ProductoKit;
use App\UseCases\Habitaciones\Mutations\AsignarPackAHabitacion;
use App\UseCases\Habitaciones\Mutations\RegistrarConsumoHabitacion;
use App\UseCases\Inventario\Queries\Stock\VerificarStockPack;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HabitacionStockTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('habitacion.codigo')
                    ->label('Habitación')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Nombre Hab.')
                    ->searchable(),

                TextColumn::make('variante.producto.nombre')
                    ->label('Producto')
                    ->searchable(),

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
            ->defaultSort('habitacion.codigo')
            ->filters([
                SelectFilter::make('habitacion_id')
                    ->label('Habitación')
                    ->options(Habitacion::pluck('nombre', 'id'))
                    ->searchable()
                    ->preload()
                    ->attribute('habitacion_id'),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoStock::options())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if ($value === null || $value === '') {
                            return $query;
                        }
                        $estado = EstadoStock::tryFrom((int) $value);
                        if ($estado === null) {
                            return $query;
                        }

                        return match ($estado) {
                            EstadoStock::Completo => $query->whereColumn('cantidad_actual', '=', 'cantidad_ideal'),
                            EstadoStock::Faltante => $query->whereColumn('cantidad_actual', '<', 'cantidad_ideal'),
                            EstadoStock::Sobrante => $query->whereColumn('cantidad_actual', '>', 'cantidad_ideal'),
                        };
                    }),

                SelectFilter::make('variante.producto_id')
                    ->label('Producto')
                    ->options(Producto::pluck('nombre', 'id'))
                    ->searchable()
                    ->preload()
                    ->attribute('variante.producto_id'),
            ])
            ->headerActions([
                Action::make('surtir_pack')
                    ->label('Surtir Pack')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->modalHeading('Surtir Pack a Habitación')
                    ->modalDescription('Seleccione el pack, la habitación destino y la bodega de origen.')
                    ->form([
                        Select::make('habitacion_id')
                            ->label('Habitación destino')
                            ->options(Habitacion::pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('producto_pack_id')
                            ->label('Pack / Kit')
                            ->options(
                                Producto::whereIn('id', function ($q) {
                                    $q->select('producto_padre_id')->from('producto_kit');
                                })->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get, $state): void {
                                self::actualizarPreviewPack($set, $get, $state);
                            }),

                        Select::make('bodega_origen_id')
                            ->label('Bodega de origen')
                            ->options(
                                Ubicacion::where('tipo', 'almacen')
                                    ->where('estado', 1)
                                    ->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get, $state): void {
                                $packId = $get('producto_pack_id');
                                if ($packId) {
                                    self::actualizarPreviewPack($set, $get, $packId);
                                }
                            }),

                        TextInput::make('cantidad_packs')
                            ->label('Cantidad de packs')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1)
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get): void {
                                $packId = $get('producto_pack_id');
                                if (! $packId) {
                                    return;
                                }
                                self::actualizarPreviewPack($set, $get, $packId);
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
                            app(AsignarPackAHabitacion::class)->execute(
                                habitacionId: (int) $data['habitacion_id'],
                                productoPackId: (int) $data['producto_pack_id'],
                                bodegaOrigenId: (int) $data['bodega_origen_id'],
                                cantidadPacks: (float) $data['cantidad_packs'],
                                creadoPorId: auth()->id(),
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
            ])
            ->actions([
                Action::make('consumir')
                    ->label('Registrar Consumo')
                    ->icon('heroicon-o-minus-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Registrar Consumo en Habitación')
                    ->form([
                        TextInput::make('cantidad')
                            ->label('Cantidad consumida')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->rule(function (callable $get, $record) {
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
                            creadoPorId: auth()->id(),
                        );
                    }),
            ]);
    }

    private static function actualizarPreviewPack(callable $set, callable $get, int $productoPackId): void
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
                    'producto' => $variante->producto->nombre ?? '—',
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
