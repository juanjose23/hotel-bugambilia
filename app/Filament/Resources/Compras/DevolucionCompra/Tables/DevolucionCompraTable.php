<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Tables;

use App\Enums\Compras\EstadoDevolucion;
use App\Models\Compras\DevolucionCompra;
use App\UseCases\Compras\Devoluciones\Mutations\DevolverMercanciaProveedor;
use Filament\Actions\Action as TableAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DevolucionCompraTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Devolución')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('ordenCompra.codigo')
                    ->label('Orden de Compra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('recepcionCompra.codigo')
                    ->label('Recepción')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha_devolucion')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('creador.name')
                    ->label('Responsable')
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
                SelectFilter::make('estado')
                    ->options(EstadoDevolucion::class),

                SelectFilter::make('orden_compra_id')
                    ->label('Orden de Compra')
                    ->relationship('ordenCompra', 'codigo')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                TableAction::make('confirmar_devolucion')
                    ->label('Confirmar Devolución')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Devolución al Proveedor')
                    ->modalDescription('Al confirmar, se retirará el stock físico del inventario (registrando movimientos de tipo DEVOLUCION_PROVEEDOR) y se liberará el saldo de la Orden de Compra para futuras recepciones. Esta acción no se puede deshacer.')
                    ->action(function (DevolucionCompra $record) {
                        try {
                            app(DevolverMercanciaProveedor::class)->execute($record, auth()->id());

                            Notification::make()
                                ->title('Devolución Confirmada')
                                ->body("La devolución {$record->codigo} ha sido procesada de manera exitosa. El stock físico ha sido descontado y el PO ha sido liberado.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al procesar devolución')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (DevolucionCompra $record) => $record->estado !== EstadoDevolucion::Confirmada),

                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (DevolucionCompra $record) => $record->estado !== EstadoDevolucion::Confirmada),
                    DeleteAction::make()
                        ->visible(fn (DevolucionCompra $record) => $record->estado !== EstadoDevolucion::Confirmada),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Más opciones'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
