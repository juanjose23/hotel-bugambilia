<?php

namespace App\Filament\Resources\Compras\Solicitudes\Tables;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Filament\Resources\Shared\Filters\FiltroEliminados;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use App\Models\Compras\Solicitud;
use App\UseCases\Compras\Solicitudes\Mutations\CancelarSolicitud;
use App\UseCases\Compras\Solicitudes\Mutations\RechazarSolicitud;
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
                    ->badge(),
            ])
            ->filters([
                FiltroEstado::make(EstadoSolicitud::class),
                FiltroEliminados::make(),
            ])
            ->actions([
                // 1. Acciones Principales
                // 2. Grupo de Acciones
                ActionGroup::make([
                    Action::make('aprobar')
                        ->label('Aprobar')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->url(fn (Solicitud $record) => SolicitudResource::getUrl('aprobar', ['record' => $record]))
                        ->visible(fn (Solicitud $record) => in_array($record->estado, [EstadoSolicitud::Borrador, EstadoSolicitud::Pendiente])),

                    Action::make('cancelar')
                        ->label('Cancelar')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Anular solicitud?')
                        ->modalDescription('Esta acción es irreversible y quedará registrada en el historial de trazabilidad.')
                        ->action(fn (Solicitud $record) => app(CancelarSolicitud::class)->execute($record))
                        ->visible(fn (Solicitud $record) => $record->estado === EstadoSolicitud::Aprobada
                            && ! $record->ordenes_compra_exists
                        ),
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon(Heroicon::NoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Solicitud $record) => app(RechazarSolicitud::class)->execute($record))
                        ->visible(fn (Solicitud $record) => $record->estado === EstadoSolicitud::Pendiente),
                    Action::make('ImprimirSolicitud')
                        ->icon(Heroicon::Printer)
                        ->color('info')
                        ->url(fn (Solicitud $record) => route('reporte.solicitud', $record))
                        ->label('Imprimir Solicitud')
                        ->visible(fn (Solicitud $record) => ! $record->trashed() && auth()->user()->can('Compras:ImprimirSolicitud'))
                        ->openUrlInNewTab(),

                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Más opciones'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('codigo', 'desc');
    }
}
