<?php

namespace App\Filament\Resources\Compras\Recepciones\Tables;

use App\Enums\Compras\EstadoRecepcion;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Filament\Resources\Compras\Recepciones\Actions\RecepcionEstadoActions;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use App\Models\Compras\RecepcionCompra;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RecepcionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Recepción')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('ordenCompra.codigo')
                    ->label('Orden de Compra')
                    ->searchable()
                    ->sortable()
                    ->url(fn (RecepcionCompra $record) => OrdenCompraResource::getUrl('edit', ['record' => $record->orden_compra_id])),

                TextColumn::make('fecha_recepcion')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('receptor.name')
                    ->label('Recibido por')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),

                TextColumn::make('items_count')
                    ->label('Ítems')
                    ->counts('items')
                    ->alignCenter(),
            ])
            ->filters([
                FiltroEstado::make(EstadoRecepcion::class),

                SelectFilter::make('orden_compra_id')
                    ->label('Orden de Compra')
                    ->relationship('ordenCompra', 'codigo')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make(RecepcionEstadoActions::make())
                    ->label('Cambiar Estado')
                    ->icon(Heroicon::ArrowPath)
                    ->color('warning')
                    ->button(),
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('imprimir')
                        ->label('Imprimir')
                        ->icon(Heroicon::Printer)
                        ->color('gray')
                        ->url(fn (RecepcionCompra $record) => route('reporte.recepcion', $record))
                        ->openUrlInNewTab()
                        ->visible(fn () => auth()->user()?->can('Compras:ImprimirRecepcion') ?? false),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Más opciones'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
