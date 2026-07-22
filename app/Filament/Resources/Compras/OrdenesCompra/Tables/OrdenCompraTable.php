<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\OrdenesCompra\Tables;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Filament\Shared\Concerns\TieneAccionesImprimirExportar;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Compras\OrdenesCompra\CancelarOrdenCompra;
use App\Interactors\Compras\OrdenesCompra\EmitirOrdenCompra;
use App\Interactors\Compras\OrdenesCompra\FinalizarOrdenCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\OrdenCompraItem;
use App\Repository\Models\Compras\Proveedor;
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
use Illuminate\Database\Eloquent\Collection;

class OrdenCompraTable
{
    use InyectaDesdeContenedor;
    use TieneAccionesImprimirExportar;
    use TieneAccionesImprimirExportar;

    private readonly EmitirOrdenCompra $emitirOrdenCompra;

    private readonly FinalizarOrdenCompra $finalizarOrdenCompra;

    private readonly CancelarOrdenCompra $cancelarOrdenCompra;

    public function __construct(
        EmitirOrdenCompra $emitirOrdenCompra,
        FinalizarOrdenCompra $finalizarOrdenCompra,
        CancelarOrdenCompra $cancelarOrdenCompra,
    ) {
        $this->emitirOrdenCompra = $emitirOrdenCompra;
        $this->finalizarOrdenCompra = $finalizarOrdenCompra;
        $this->cancelarOrdenCompra = $cancelarOrdenCompra;
    }

    public static function configure(Table $table): Table
    {
        return static::make()->doConfigure($table);
    }

    private function doConfigure(Table $table): Table
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
                            ? 'Origen: COT-#'.$record->cotizacion_id.' ('.($record->solicitud ? $record->solicitud->codigo : 'N/A').')'
                            : ($record->solicitud_id ? 'Ref: '.($record->solicitud ? $record->solicitud->codigo : 'N/A') : 'Compra Directa')
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
                    ->description(fn (OrdenCompra $record) => ($record->proveedor && $record->proveedor->persona && $record->proveedor->persona->personaJuridica)
                            ? $record->proveedor->persona->personaJuridica->razon_social
                            : (($record->proveedor && $record->proveedor->persona) ? $record->proveedor->persona->primer_nombre : '')
                    ),

                TextColumn::make('cotizacion_id')
                    ->label('Cotización')
                    ->placeholder('— Directa')
                    ->formatStateUsing(fn ($state) => $state ? "#COT-$state" : null)
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

                EstadoBadgeColumn::make(EstadoOrdenCompra::class)
                    ->sortable(),

                TextColumn::make('progreso')
                    ->label('Progreso')
                    ->getStateUsing(function (OrdenCompra $record): string {
                        /** @var Collection<int, OrdenCompraItem> $items */
                        $items = $record->items;
                        /** @var int|float $total */
                        $total = $items->sum('cantidad');

                        if ($record->estado === EstadoOrdenCompra::Recibida) {
                            return "$total/$total";
                        }

                        if ($record->recepciones_exists) {
                            $received = $record->totalReceivedQuantity();

                            return "$received/$total";
                        }

                        return "0/$total";
                    })
                    ->badge()
                    ->color(fn (OrdenCompra $record): string => match (true) {
                        $record->estado === EstadoOrdenCompra::Recibida => 'success',
                        $record->recepciones_exists => 'warning',
                        default => 'gray',
                    }),

                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FiltroEstado::make(EstadoOrdenCompra::class),
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
                    ->getOptionLabelFromRecordUsing(fn (Proveedor $record) => "[$record->codigo] - ".(
                        ($record->persona && $record->persona->personaJuridica)
                            ? $record->persona->personaJuridica->razon_social
                            : ($record->persona ? $record->persona->primer_nombre : '')
                    )
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('emitir')
                        ->label('Emitir OC')
                        ->icon(Heroicon::PaperAirplane)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Al emitir la orden, se considera un compromiso oficial con el proveedor y dejará de ser editable.')
                        ->action(fn (OrdenCompra $record) => $this->emitirOrdenCompra->ejecutar($record))
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
                        ->action(fn (OrdenCompra $record) => $this->finalizarOrdenCompra->ejecutar($record))
                        ->visible(fn (OrdenCompra $record) => $record->estado === EstadoOrdenCompra::Parcial),

                    self::makeImprimirAction('reporte.orden-compra', 'Compras:ImprimirOrdenCompra'),

                    Action::make('cancelar')
                        ->label('Cancelar')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Anular Orden de Compra?')
                        ->modalDescription('Esta acción anula el compromiso legal. Solo permitido si no hay recepciones parciales vinculadas.')
                        ->action(fn (OrdenCompra $record) => $this->cancelarOrdenCompra->ejecutar($record))
                        ->visible(fn (OrdenCompra $record) => in_array($record->estado, [EstadoOrdenCompra::Emitida, EstadoOrdenCompra::EnTransito]) &&
                            ! $record->recepciones_exists
                        ),

                    DeleteAction::make()
                        ->visible(fn (OrdenCompra $record) => $record->estado === EstadoOrdenCompra::Borrador),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('codigo', 'desc');
    }
}
