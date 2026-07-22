<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Tables;

use App\Enums\Compras\EstadoDevolucion;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Filament\Shared\Concerns\TieneAccionesImprimirExportar;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Compras\Devoluciones\DevolverMercanciaProveedor;
use App\Repository\Models\Compras\DevolucionCompra;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DevolucionCompraTable
{
    use InyectaDesdeContenedor;
    use TieneAccionesImprimirExportar;

    public function __construct(
        private readonly DevolverMercanciaProveedor $devolverMercanciaProveedor,
    ) {}

    public static function configure(Table $table): Table
    {
        return static::make()->doConfigure($table);
    }

    private function doConfigure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Devolucion')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('ordenCompra.codigo')
                    ->label('Orden de Compra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('recepcionCompra.codigo')
                    ->label('Recepcion')
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

                EstadoBadgeColumn::make(EstadoDevolucion::class),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->alignCenter(),
            ])
            ->filters([
                FiltroEstado::make(EstadoDevolucion::class),

                SelectFilter::make('orden_compra_id')
                    ->label('Orden de Compra')
                    ->relationship('ordenCompra', 'codigo')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('confirmar_devolucion')
                    ->label('Confirmar Devolucion')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Devolucion al Proveedor')
                    ->modalDescription('Al confirmar, se retirara el stock fisico del inventario (registrando movimientos de tipo DEVOLUCION_PROVEEDOR) y se liberara el saldo de la Orden de Compra para futuras recepciones. Esta accion no se puede deshacer.')
                    ->action(function (DevolucionCompra $record) {
                        try {
                            $this->devolverMercanciaProveedor->ejecutar($record, (int) auth()->id());

                            Notification::make()
                                ->title('Devolucion Confirmada')
                                ->body("La devolucion $record->codigo ha sido procesada de manera exitosa. El stock fisico ha sido descontado y el PO ha sido liberado.")
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Error al procesar devolucion')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (DevolucionCompra $record) => $record->estado !== EstadoDevolucion::Confirmada),

                ActionGroup::make([
                    self::makeImprimirAction('reporte.devolucion', 'Compras:ImprimirDevolucion'),
                    EditAction::make()
                        ->visible(fn (DevolucionCompra $record) => $record->estado !== EstadoDevolucion::Confirmada),
                    DeleteAction::make()
                        ->visible(fn (DevolucionCompra $record) => $record->estado !== EstadoDevolucion::Confirmada),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Mas opciones'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
