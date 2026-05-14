<?php

namespace App\Filament\Resources\Compras\Solicitudes\Tables;

use App\Enums\Compras\EstadoSolicitud;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Solicitud;
use App\Services\Compras\NotificadorCompras;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SolicitudTable
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
                    ->copyable(),

                TextColumn::make('fecha_solicitud')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('colaborador.persona.nombre_completo')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Solicitud $record) => $record->colaborador->codigo),

                TextColumn::make('departamentoSolicitante.nombre')
                    ->label('Departamento')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->alignCenter(),

                TextColumn::make('cotizaciones_count')
                    ->label('Cotiz.')
                    ->counts('cotizaciones')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 3 => 'success',
                        $state > 0 => 'warning',
                        default => 'gray',
                    })
                    ->alignCenter(),

                TextColumn::make('estado')
                    ->badge()
                    ->formatStateUsing(fn (EstadoSolicitud $state) => $state->label())
                    ->color(fn (EstadoSolicitud $state) => $state->color())
                    ->icon(fn (EstadoSolicitud $state) => $state->icon()),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options(EstadoSolicitud::class),
                TrashedFilter::make(),
            ])
            ->actions([
                // 1. Acciones Principales
                // 2. Grupo de Acciones
                ActionGroup::make([
                    Action::make('aprobar')
                        ->label('Aprobar')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Solicitud $record) {
                            $record->update(['estado' => EstadoSolicitud::Aprobada]);
                            app(NotificadorCompras::class)->solicitudAprobada($record);
                        })
                        ->visible(fn (Solicitud $record) => in_array($record->estado, [EstadoSolicitud::Borrador, EstadoSolicitud::Pendiente])),

                    Action::make('cancelar')
                        ->label('Cancelar')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Anular solicitud?')
                        ->modalDescription('Esta acción es irreversible y quedará registrada en el historial de trazabilidad.')
                        ->action(function (Solicitud $record) {
                            $record->update(['estado' => EstadoSolicitud::Cancelada]);
                            app(NotificadorCompras::class)->solicitudCancelada($record);
                        })
                        ->visible(fn (Solicitud $record) => $record->estado === EstadoSolicitud::Aprobada &&
                            ! OrdenCompra::where('solicitud_id', $record->id)->exists()
                        ),
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon(Heroicon::NoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Solicitud $record) {
                            $record->update(['estado' => EstadoSolicitud::Rechazada]);
                            app(NotificadorCompras::class)->solicitudRechazada($record);
                        })
                        ->visible(fn (Solicitud $record) => $record->estado === EstadoSolicitud::Pendiente),

                    Action::make('imprimir')
                        ->label('Imprimir')
                        ->icon(Heroicon::Printer)
                        ->color('gray')
                        ->url(fn (Solicitud $record) => route('reporte.solicitud', $record))
                        ->openUrlInNewTab()
                        ->visible(fn () => auth()->user()->can('ImprimirSolicitud') || auth()->user()->hasRole('super_admin')),

                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
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
