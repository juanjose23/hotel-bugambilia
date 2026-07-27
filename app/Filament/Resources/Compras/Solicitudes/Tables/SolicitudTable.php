<?php

namespace App\Filament\Resources\Compras\Solicitudes\Tables;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Concerns\TieneAccionesImprimirExportar;
use App\Filament\Shared\Filters\FiltroEliminados;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Compras\Solicitudes\CancelarSolicitud;
use App\Interactors\Compras\Solicitudes\RechazarSolicitud;
use App\Repository\Models\Compras\Solicitud;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

readonly class SolicitudTable
{
    use TieneAccionesImprimirExportar;

    public function __construct(
        private CancelarSolicitud $cancelarSolicitud,
        private RechazarSolicitud $rechazarSolicitud,
    ) {}

    public function configure(Table $table): Table
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
                    ->description(fn (Solicitud $record) => $record->colaborador ? $record->colaborador->codigo : ''),

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

                EstadoBadgeColumn::make(EstadoSolicitud::class),
            ])
            ->filters([
                FiltroEstado::make(EstadoSolicitud::class),
                FiltroEliminados::make(),
            ])
            ->recordActions([
                // 1. Acciones Principales
                // 2. Grupo de Acciones
                ActionGroup::make([
                    Action::make('aprobar')
                        ->label('Aprobar')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->url(fn (?Solicitud $record) => $record ? SolicitudResource::getUrl('aprobar', ['record' => $record]) : '#')
                        ->visible(fn (?Solicitud $record) => $record && in_array($record->estado, [EstadoSolicitud::Borrador, EstadoSolicitud::Pendiente])),

                    Action::make('cancelar')
                        ->label('Cancelar')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Anular solicitud?')
                        ->modalDescription('Esta acción es irreversible y quedará registrada en el historial de trazabilidad.')
                        ->action(fn (Solicitud $record) => $this->cancelarSolicitud->ejecutar($record))
                        ->visible(fn (?Solicitud $record) => $record
                            && $record->estado === EstadoSolicitud::Aprobada
                            && ! $record->ordenes_compra_exists
                        ),

                    Action::make('ver')
                        ->label('Ver')
                        ->icon(Heroicon::Eye)
                        ->color('gray')
                        ->url(fn (?Solicitud $record) => $record ? SolicitudResource::getUrl('view', ['record' => $record]) : '#')
                        ->visible(fn (?Solicitud $record) => $record !== null),

                    Action::make('editar')
                        ->label('Editar')
                        ->icon(Heroicon::PencilSquare)
                        ->url(fn (?Solicitud $record) => $record ? SolicitudResource::getUrl('edit', ['record' => $record]) : '#')
                        ->visible(fn (?Solicitud $record) => $record && $record->estado === EstadoSolicitud::Borrador),

                    Action::make('rechazar')
                        ->label('Rechazar')
                        ->icon(Heroicon::NoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Solicitud $record) => $this->rechazarSolicitud->ejecutar($record))
                        ->visible(fn (?Solicitud $record) => $record && $record->estado === EstadoSolicitud::Pendiente),

                    Action::make('imprimir')
                        ->label('Imprimir')
                        ->icon(Heroicon::Printer)
                        ->color('gray')
                        ->url(fn (Solicitud $record) => route('admin.compras.reportes.solicitud', $record))
                        ->openUrlInNewTab()
                        ->visible(fn () => auth()->user()?->can('Compras:ImprimirSolicitud') ?? false),

                    Action::make('eliminar')
                        ->label('Eliminar')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Solicitud $record) => $record->delete())
                        ->visible(fn (?Solicitud $record) => $record && $record->estado === EstadoSolicitud::Borrador),

                    Action::make('restaurar')
                        ->label('Restaurar')
                        ->icon(Heroicon::ArrowUturnLeft)
                        ->color('warning')
                        ->action(fn (Solicitud $record) => $record->restore())
                        ->visible(fn (?Solicitud $record) => $record && $record->trashed()),

                    Action::make('eliminar-permanente')
                        ->label('Eliminar permanentemente')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Solicitud $record) => $record->forceDelete())
                        ->visible(fn (?Solicitud $record) => $record && $record->trashed()),
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
