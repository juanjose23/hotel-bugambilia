<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\Tables;

use App\BusinessLogic\Restaurante\Platos\CalcularCostoPlato;
use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroCategoria;
use App\Repository\Models\Restaurante\Plato;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class PlatoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagenes.url')
                    ->label('')
                    ->circular()
                    ->limit(1)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('codigo')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('categoria.nombre')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                EstadoBadgeColumn::make(EstadoGeneral::class),

                IconColumn::make('web')
                    ->label('Web')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FiltroCategoria::make(CatalogoTipo::CATEGORIA_SERVICIO),
                TernaryFilter::make('web')
                    ->label('Visible en Web'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('calcularCosto')
                        ->label('Calcular Costo')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->modalHeading(fn (Plato $record): string => "Desglose de Costo: {$record->nombre}")
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->infolist(function (Plato $record): array {
                            $recetaId = $record->producto_receta_id ?? $record->id;
                            $calculo = app(CalcularCostoPlato::class)->ejecutar($recetaId);

                            return [
                                Section::make('Resumen de Costos & Margen')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('costo_total')
                                            ->label('Costo Ingredientes')
                                            ->money('NIO')
                                            ->state($calculo['costo_ingredientes']),
                                        TextEntry::make('margen_pct')
                                            ->label('Margen Sugerido')
                                            ->state($calculo['margen_sugerido_pct'].'%'),
                                        TextEntry::make('precio_sugerido')
                                            ->label('Precio Sugerido')
                                            ->money('NIO')
                                            ->state($calculo['precio_sugerido']),
                                    ]),
                            ];
                        }),
                ])->icon(Heroicon::EllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
