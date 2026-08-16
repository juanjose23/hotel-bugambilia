<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Tables;

use App\BusinessLogic\Inventario\CalcularRatioMinStock;
use App\BusinessLogic\Inventario\VerificarDisponibilidadPack;
use App\Enums\Catalogos\CatalogoTipo;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Filament\Shared\Filters\FiltroCategoria;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

readonly class PackTable
{
    use InyectaDesdeContenedor;

    public function __construct(
        private VerificarDisponibilidadPack $verificarDisponibilidad,
        private CalcularRatioMinStock $calcularRatioMinStock,
    ) {}

    public static function configure(Table $table): Table
    {
        return static::make()->doConfigure($table);
    }

    private function doConfigure(Table $table): Table
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
                        $resultado = $this->verificarDisponibilidad->ejecutar($record->id);

                        return match (true) {
                            $resultado->items->isEmpty() => 'Sin items',
                            $resultado->disponible => 'Disponible',
                            default => 'Stock insuficiente',
                        };
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === 'Disponible' ? 'success' : 'danger')
                    ->icon(fn (string $state) => $state === 'Disponible' ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-circle'),

                TextColumn::make('variantes_min_stock')
                    ->label('Stock Mínimo')
                    ->state(fn ($record): string => $this->calcularRatioMinStock->ejecutar($record->id))
                    ->badge()
                    ->color(fn (string $state) => $state === '0 packs' || str_starts_with($state, '0')
                        ? 'danger'
                        : ((int) filter_var($state, FILTER_SANITIZE_NUMBER_INT) < 3 ? 'warning' : 'success')),

                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                FiltroCategoria::make(CatalogoTipo::CATEGORIA_PRODUCTO),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ]);
    }
}
