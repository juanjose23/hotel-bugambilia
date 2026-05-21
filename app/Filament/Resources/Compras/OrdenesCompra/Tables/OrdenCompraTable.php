<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Tables;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Models\Compras\OrdenCompra;
use App\UseCases\Compras\OrdenesCompra\Mutations\CancelarOrdenCompra;
use App\UseCases\Compras\OrdenesCompra\Mutations\EmitirOrdenCompra;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdenCompraTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->description(fn (OrdenCompra $record) => $record->cotizacion_id
                            ? "Origen: COT-#{$record->cotizacion_id} ({$record->solicitud->codigo})"
                            : ($record->solicitud_id ? "Ref: {$record->solicitud->codigo}" : 'Compra Directa')
                    ),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->getStateUsing(fn (OrdenCompra $record) => $record->solicitud_id ? 'Flujo Solicitud' : 'Compra Directa')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Flujo Solicitud' => 'info',
                        'Compra Directa' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('proveedor.codigo')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->description(fn (OrdenCompra $record) => $record->proveedor->persona->personaJuridica->razon_social
                        ?? $record->proveedor->persona->primer_nombre
                    ),

                TextColumn::make('cotizacion_id')
                    ->label('Cotización')
                    ->placeholder('— Directa')
                    ->formatStateUsing(fn ($state) => $state ? "#COT-{$state}" : null)
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fecha_orden')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('progreso')
                    ->label('Progreso')
                    ->getStateUsing(function (OrdenCompra $record): string {
                        $total = (float) $record->items->sum('cantidad');

                        if ($record->estado === EstadoOrdenCompra::Recibida) {
                            return "{$total}/{$total}";
                        }

                        if ($record->recepciones_exists) {
                            $received = $record->totalReceivedQuantity();

                            return "{$received}/{$total}";
                        }

                        return "0/{$total}";
                    })
                    ->badge()
                    ->color(fn (OrdenCompra $record): string => match (true) {
                        $record->estado === EstadoOrdenCompra::Recibida => 'success',
                        $record->recepciones_exists => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options(EstadoOrdenCompra::class),
                SelectFilter::make('tipo')
                    ->label('Origen')
                    ->options([
                        'solicitud' => 'Flujo Solicitud',
                        'directa' => 'Compra Directa',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'solicitud') {
                            return $query->whereNotNull('solicitud_id');
                        }
                        if ($data['value'] === 'directa') {
                            return $query->whereNull('solicitud_id');
                        }

                        return $query;
                    }),
                SelectFilter::make('proveedor_id')
                    ->label('Filtrar por Proveedor')
                    ->relationship('proveedor', 'codigo')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->codigo}] - ".($record->persona->personaJuridica->razon_social ?? $record->persona->primer_nombre)
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('emitir')
                        ->label('Emitir OC')
                        ->icon(Heroicon::PaperAirplane)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Al emitir la orden, se considera un compromiso oficial con el proveedor y dejará de ser editable.')
                        ->action(fn (OrdenCompra $record) => app(EmitirOrdenCompra::class)->execute($record))
                        ->visible(fn (OrdenCompra $record) => $record->estado === EstadoOrdenCompra::Borrador),

                    Action::make('registrar_recepcion')
                        ->label('Registrar Recepción')
                        ->icon(Heroicon::ArchiveBox)
                        ->color('success')
                        ->url(fn (OrdenCompra $record) => RecepcionResource::getUrl('create', ['orden_compra_id' => $record->id]))
                        ->visible(fn (OrdenCompra $record) => in_array($record->estado, [EstadoOrdenCompra::Emitida, EstadoOrdenCompra::EnTransito, EstadoOrdenCompra::Parcial])),

                    Action::make('completar')
                        ->label('Finalizar Orden')
                        ->icon(Heroicon::CheckBadge)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('¿Finalizar Orden de Compra?')
                        ->modalDescription('Esta acción marcará la orden como Recibida/Completada y ajustará los costos y cantidades finales a lo realmente entregado.')
                        ->action(fn (OrdenCompra $record) => $record->update(['estado' => EstadoOrdenCompra::Recibida]))
                        ->visible(fn (OrdenCompra $record) => $record->estado === EstadoOrdenCompra::Parcial),

                    Action::make('imprimir')
                        ->label('Imprimir')
                        ->icon(Heroicon::Printer)
                        ->color('gray')
                        ->url(fn (OrdenCompra $record) => route('reporte.orden-compra', $record))
                        ->openUrlInNewTab()
                        ->visible(fn () => auth()->user()->can('Compras:ImprimirOrdenCompra')),

                    Action::make('cancelar')
                        ->label('Cancelar')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Anular Orden de Compra?')
                        ->modalDescription('Esta acción anula el compromiso legal. Solo permitido si no hay recepciones parciales vinculadas.')
                        ->action(fn (OrdenCompra $record) => app(CancelarOrdenCompra::class)->execute($record))
                        ->visible(fn (OrdenCompra $record) => in_array($record->estado, [EstadoOrdenCompra::Emitida, EstadoOrdenCompra::EnTransito]) &&
                            ! $record->recepciones_exists
                        ),

                    DeleteAction::make()
                        ->visible(fn (OrdenCompra $record) => $record->estado === EstadoOrdenCompra::Borrador),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Más opciones'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('codigo', 'desc');
    }
}
