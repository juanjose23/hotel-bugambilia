<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Tables;

use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use App\Models\Compras\Cotizacion;
use App\Services\Compras\NotificadorCompras;
use App\UseCases\Compras\OrdenesCompra\Mutations\GenerarOrdenDesdeCotizacion;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CotizacionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('solicitud.codigo')
                    ->label('Solicitud')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Cotizacion $record) => "ID: {$record->solicitud_id}"),

                TextColumn::make('proveedor.codigo')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->proveedor->persona->personaJuridica->razon_social
                        ?? $record->proveedor->persona->primer_nombre
                    ),

                TextColumn::make('fecha_cotizacion')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('dias_entrega')
                    ->label('Entrega')
                    ->suffix(' días')
                    ->alignCenter(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn (Cotizacion $record) => $record->moneda->codigo ?? 'USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                IconColumn::make('es_elegida')
                    ->label('Ganadora')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckBadge)
                    ->trueColor('success')
                    ->alignCenter(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('items_elegidos_count')
                    ->label('Ítems Elegidos')
                    ->getStateUsing(function (Cotizacion $record) {
                        return $record->items_elegidos_count > 0 ? $record->items_elegidos_count : null;
                    })
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('creadaPor.name')
                    ->label('Registrada por')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('elegidaPor.name')
                    ->label('Elegida por')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('elegida_en')
                    ->label('Fecha Selección')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('proveedor_id')
                    ->label('Proveedor')
                    ->relationship('proveedor', 'codigo')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->codigo}] - ".($record->persona->personaJuridica->razon_social ?? $record->persona->primer_nombre)
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('solicitud_id')
                    ->relationship('solicitud', 'codigo')
                    ->label('Solicitud'),

                SelectFilter::make('es_elegida')
                    ->label('Solo Ganadoras')
                    ->options([
                        '1' => 'Sí',
                        '0' => 'No',
                    ]),

                FiltroEstado::make(EstadoCotizacion::class),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('generarOrden')
                        ->label('Generar Orden de Compra')
                        ->icon(Heroicon::ShoppingCart)
                        ->color('primary')
                        ->visible(fn (Cotizacion $record) => ($record->es_elegida || $record->items_elegidos_count > 0)
                            && ! $record->solicitud->ordenesCompra
                                ->where('proveedor_id', $record->proveedor_id)
                                ->where('estado', '!=', EstadoOrdenCompra::Cancelada)
                                ->isNotEmpty()
                        )
                        ->action(function (Cotizacion $record) {
                            try {
                                $orden = app(GenerarOrdenDesdeCotizacion::class)->execute($record->id);

                                app(NotificadorCompras::class)->ganadorSeleccionado($record);

                                Notification::make()
                                    ->title('Orden de Compra Generada')
                                    ->body("Se ha creado la orden {$orden->codigo}.")
                                    ->success()
                                    ->send();

                                return redirect(OrdenCompraResource::getUrl('edit', ['record' => $orden]));
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error al generar la orden')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    EditAction::make()
                        ->visible(fn (Cotizacion $record) => ! $record->ordenCompra || $record->ordenCompra->estado === EstadoOrdenCompra::Cancelada),

                    ViewAction::make(),

                    Action::make('verComparativa')
                        ->label('Ver Comparativa')
                        ->icon(Heroicon::ArrowsRightLeft)
                        ->color('success')
                        ->url(fn (Cotizacion $record) => CotizacionResource::getUrl('comparativa', ['solicitud_id' => $record->solicitud_id]))
                        ->visible(fn () => auth()->user()->can('Compras:ViewComparativaCotizaciones')),

                    Action::make('imprimirComparativa')
                        ->label('Imprimir Comparativo')
                        ->icon(Heroicon::Printer)
                        ->color('info')
                        ->url(fn (Cotizacion $record) => route('reporte.comparativa', ['solicitud' => $record->solicitud_id]))
                        ->openUrlInNewTab()
                        ->visible(fn () => auth()->user()->can('Compras:ImprimirComparativa')),

                    Action::make('imprimir')
                        ->label('Imprimir Cotización')
                        ->icon(Heroicon::DocumentText)
                        ->color('gray')
                        ->url(fn (Cotizacion $record) => route('reporte.cotizacion', $record))
                        ->openUrlInNewTab()
                        ->visible(fn () => auth()->user()->can('Compras:ImprimirCotizacion')),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Más opciones'),
            ]);
    }
}
