<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Tables;

use App\Enums\Inventario\EstadoLote;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Inventario\Lotes\LiberarCuarentena\LiberarLotesCuarentena;
use App\Interactors\Inventario\Lotes\RechazarCuarentena\RechazarLotesCuarentena;
use App\Repository\Models\Inventario\Lote;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

readonly class LoteTable
{
    use InyectaDesdeContenedor;

    public function __construct(
        private LiberarLotesCuarentena $liberarLotesCuarentena,
        private RechazarLotesCuarentena $rechazarLotesCuarentena,
    ) {}

    public static function configure(Table $table): Table
    {
        return static::make()->doConfigure($table);
    }

    private function doConfigure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_lote')
                    ->label('Lote')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cantidad_disponible')
                    ->label('Disponible')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color(fn (float $state) => $state > 0 ? 'success' : 'danger'),
                EstadoBadgeColumn::make(EstadoLote::class),
                TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->searchable(),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (?string $state) => $state && $state <= now()->format('Y-m-d') ? 'danger' : null),
                TextColumn::make('lote_proveedor')
                    ->label('Lote Prov.')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('fecha_recepcion')
                    ->label('Recibido')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                FiltroEstado::make(EstadoLote::class),
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray'),
                ActionGroup::make([
                    AsignarSubUbicacionAction::make(),
                    RegistrarMermaAction::make(),
                    TrasladarLoteAction::make(),
                    EnviarCuarentenaAction::make(),

                    Action::make('liberar_cuarentena_individual')
                        ->label('Liberar de Cuarentena')
                        ->icon(Heroicon::ShieldCheck)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Liberar Lote de Cuarentena')
                        ->modalDescription('El lote será marcado como Disponible y se le asignará una ubicación física según la PutawayPolicy.')
                        ->action(function (Lote $record) {
                            $this->liberarLotesCuarentena->execute([(int) $record->id], (int) auth()->id());

                            Notification::make()
                                ->title('Lote liberado')
                                ->body("El lote $record->codigo_lote ha sido liberado de cuarentena y reubicado exitosamente.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Lote $record) => $record->estado === EstadoLote::Cuarentena),

                    Action::make('rechazar_cuarentena_individual')
                        ->label('Rechazar / Desperdicio')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Rechazar Lote / Marcar como Desperdicio')
                        ->modalDescription('El lote será clasificado como RECHAZADO, su stock disponible será 0 y será reubicado físicamente en la Zona de Merma.')
                        ->schema([
                            Textarea::make('motivo')
                                ->label('Motivo del Rechazo (Obligatorio)')
                                ->required(),
                        ])
                        ->action(function (array $data, Lote $record) {
                            $this->rechazarLotesCuarentena->execute([(int) $record->id], $data['motivo'], (int) auth()->id());

                            Notification::make()
                                ->title('Lote Rechazado')
                                ->body("El lote $record->codigo_lote ha sido clasificado como rechazado y trasladado a mermas.")
                                ->danger()
                                ->send();
                        })
                        ->visible(fn (Lote $record) => $record->estado === EstadoLote::Cuarentena),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->tooltip('Más acciones'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('liberar_cuarentena')
                        ->label('Liberar de Cuarentena')
                        ->icon(Heroicon::ShieldCheck)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            /** @var array<int> $loteIds */
                            $loteIds = $records->pluck('id')->map(fn ($id) => is_numeric($id) ? (int) $id : 0)->all();
                            $this->liberarLotesCuarentena->execute($loteIds);

                            Notification::make()
                                ->title('Lotes liberados')
                                ->body('Los lotes seleccionados han sido liberados de cuarentena exitosamente.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => auth()->user()?->can('liberar-lotes-cuarentena') ?? true),

                    BulkAction::make('rechazar_cuarentena_lote')
                        ->label('Rechazar en Lote')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Rechazar Lotes seleccionados')
                        ->schema([
                            Textarea::make('motivo')
                                ->label('Motivo del Rechazo Masivo')
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            /** @var array<int> $loteIds */
                            $loteIds = $records->pluck('id')->map(fn ($id) => is_numeric($id) ? (int) $id : 0)->all();
                            $motivo = is_string($data['motivo']) ? $data['motivo'] : '';
                            $this->rechazarLotesCuarentena->execute($loteIds, $motivo, (int) auth()->id());

                            Notification::make()
                                ->title('Lotes Rechazados')
                                ->body('Los lotes seleccionados han sido rechazados y trasladados a la zona de merma.')
                                ->danger()
                                ->send();
                        })
                        ->visible(fn () => auth()->user()?->can('rechazar-lotes-cuarentena') ?? true),
                ]),
            ])
            ->groups([
                Group::make('lote_proveedor')
                    ->label('Agrupar por Lote de Proveedor')
                    ->collapsible(),
                Group::make('fecha_recepcion')
                    ->label('Agrupar por Fecha de Recepción')
                    ->collapsible(),
            ]);
    }
}
