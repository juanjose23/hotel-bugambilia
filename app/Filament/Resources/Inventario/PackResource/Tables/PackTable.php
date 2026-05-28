<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Tables;

use App\Enums\CatalogoTipo;
use App\Models\Inventario\ProductoKit;
use App\Models\Inventario\Stock;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Pack')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('kit_items_count')
                    ->label('Items')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('estado_stock')
                    ->label('Disponibilidad')
                    ->state(function ($record): string {
                        $items = ProductoKit::where('producto_padre_id', $record->id)
                            ->pluck('producto_variante_id', 'cantidad');

                        if ($items->isEmpty()) {
                            return 'Sin items';
                        }

                        $todosSuficientes = true;
                        foreach ($items as $necesario => $varianteId) {
                            $stockTotal = Stock::where('producto_variante_id', $varianteId)
                                ->sum('cantidad');
                            if ($stockTotal < (float) $necesario) {
                                $todosSuficientes = false;
                                break;
                            }
                        }

                        return $todosSuficientes ? 'Disponible' : 'Stock insuficiente';
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === 'Disponible' ? 'success' : 'danger')
                    ->icon(fn (string $state) => $state === 'Disponible' ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-circle'),

                TextColumn::make('variantes_min_stock')
                    ->label('Stock Mínimo')
                    ->state(function ($record): string {
                        $items = ProductoKit::where('producto_padre_id', $record->id)->get();
                        if ($items->isEmpty()) {
                            return '—';
                        }

                        $ratios = [];
                        foreach ($items as $item) {
                            $stockTotal = Stock::where('producto_variante_id', $item->producto_variante_id)
                                ->sum('cantidad');
                            $necesario = (float) $item->cantidad;
                            $ratios[] = $necesario > 0 ? (int) floor($stockTotal / $necesario) : 0;
                        }

                        $min = min($ratios);

                        return "{$min} pack".($min !== 1 ? 's' : '');
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === '0 packs' || str_starts_with($state, '0')
                        ? 'danger'
                        : ((int) filter_var($state, FILTER_SANITIZE_NUMBER_INT) < 3 ? 'warning' : 'success')),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship(
                        name: 'categoria',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'catalogoTipo',
                            fn (Builder $q) => $q->where('codigo', CatalogoTipo::CATEGORIA_PRODUCTO->value)
                        ),
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Más opciones'),
            ]);
    }
}
