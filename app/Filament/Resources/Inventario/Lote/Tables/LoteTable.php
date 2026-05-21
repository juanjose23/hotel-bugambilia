<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Tables;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Services\Inventario\NotificadorInventario;
use App\UseCases\Inventario\Lotes\Mutations\LiberarLotesCuarentena;
use App\UseCases\Inventario\Lotes\Mutations\RechazarLotesCuarentena;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class LoteTable
{
    public static function configure(Table $table): Table
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
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
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
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoLote::options()),
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray'),
                ActionGroup::make([
                    Action::make('registrar_merma')
                        ->label('Registrar Merma')
                        ->icon(Heroicon::ArchiveBoxXMark)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Registrar Merma o Pérdida')
                        ->form([
                            TextInput::make('cantidad')
                                ->label('Cantidad a descontar')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->maxValue(fn (Lote $record) => $record->cantidad_disponible),
                            Textarea::make('motivo')
                                ->label('Motivo / Justificación')
                                ->required(),
                        ])
                        ->action(function (array $data, Lote $record) {
                            $cantidad = (float) $data['cantidad'];

                            $record->cantidad_disponible -= $cantidad;
                            if ($record->cantidad_disponible <= 0) {
                                $record->estado = EstadoLote::Agotado;
                            }
                            $record->save();

                            MovimientoStock::create([
                                'tipo' => 'AJUSTE_SALIDA',
                                'lote_id' => $record->id,
                                'producto_id' => $record->producto_id,
                                'cantidad' => $cantidad,
                                'ubicacion_origen_id' => $record->ubicacion_id,
                                'referencia' => 'Merma: '.$data['motivo'],
                                'creado_por_id' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Merma registrada')
                                ->body("Se ha descontado el stock y registrado el movimiento para el lote {$record->codigo_lote}.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Lote $record) => $record->cantidad_disponible > 0),

                    Action::make('trasladar_lote')
                        ->label('Trasladar')
                        ->icon(Heroicon::ArrowsRightLeft)
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Trasladar Lote a Nueva Ubicación')
                        ->form([
                            Select::make('ubicacion_destino_id')
                                ->label('Ubicación de Destino')
                                ->options(function () {
                                    $ubicaciones = Ubicacion::where('estado', 1)->get();
                                    $map = $ubicaciones->keyBy('id');

                                    $buildPath = function ($u) use (&$buildPath, $map) {
                                        if (! $u) {
                                            return '';
                                        }
                                        if ($u->padre_id && isset($map[$u->padre_id])) {
                                            return $buildPath($map[$u->padre_id]).' ➔ '.$u->nombre;
                                        }

                                        return $u->nombre;
                                    };

                                    $options = [];
                                    foreach ($ubicaciones as $u) {
                                        $options[$u->id] = $buildPath($u);
                                    }

                                    asort($options);

                                    return $options;
                                })
                                ->required()
                                ->notIn(fn (Lote $record) => [$record->ubicacion_id])
                                ->searchable(),
                            Textarea::make('motivo')
                                ->label('Motivo del traslado')
                                ->required(),
                        ])
                        ->action(function (array $data, Lote $record) {
                            $ubicacionOrigen = $record->ubicacion_id;
                            $ubicacionDestino = (int) $data['ubicacion_destino_id'];

                            $record->ubicacion_id = $ubicacionDestino;
                            $record->save();

                            MovimientoStock::create([
                                'tipo' => 'MOV_TRANSFERENCIA',
                                'lote_id' => $record->id,
                                'producto_id' => $record->producto_id,
                                'cantidad' => $record->cantidad_disponible,
                                'ubicacion_origen_id' => $ubicacionOrigen,
                                'ubicacion_destino_id' => $ubicacionDestino,
                                'referencia' => 'Traslado: '.$data['motivo'],
                                'creado_por_id' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Lote trasladado')
                                ->body("El lote {$record->codigo_lote} ha sido movido exitosamente.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Lote $record) => $record->cantidad_disponible > 0),

                    Action::make('liberar_cuarentena_individual')
                        ->label('Liberar de Cuarentena')
                        ->icon(Heroicon::ShieldCheck)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Liberar Lote de Cuarentena')
                        ->modalDescription('El lote será marcado como Disponible y se le asignará una ubicación física según la PutawayPolicy.')
                        ->action(function (Lote $record) {
                            app(LiberarLotesCuarentena::class)->execute([$record->id], auth()->id());

                            Notification::make()
                                ->title('Lote liberado')
                                ->body("El lote {$record->codigo_lote} ha sido liberado de cuarentena y reubicado exitosamente.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Lote $record) => $record->estado === EstadoLote::Cuarentena),

                    Action::make('enviar_cuarentena_individual')
                        ->label('Enviar a Cuarentena')
                        ->icon(Heroicon::ShieldExclamation)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Enviar Lote a Cuarentena')
                        ->modalDescription('El lote será retenido y no podrá ser utilizado para consumo por FEFO hasta que sea liberado.')
                        ->form([
                            Textarea::make('motivo')
                                ->label('Motivo de Retención / Control de Calidad')
                                ->required(),
                        ])
                        ->action(function (array $data, Lote $record) {
                            $record->estado = EstadoLote::Cuarentena;
                            $record->save();

                            MovimientoStock::create([
                                'tipo' => 'MOV_TRANSFERENCIA',
                                'lote_id' => $record->id,
                                'producto_id' => $record->producto_id,
                                'cantidad' => $record->cantidad_disponible,
                                'ubicacion_origen_id' => $record->ubicacion_id,
                                'ubicacion_destino_id' => $record->ubicacion_id,
                                'referencia' => 'Envío a cuarentena: '.$data['motivo'],
                                'creado_por_id' => auth()->id(),
                                'notas' => 'Retención manual por control de calidad.',
                            ]);

                            app(NotificadorInventario::class)->loteEnCuarentena($record, $data['motivo']);

                            Notification::make()
                                ->title('Lote en Cuarentena')
                                ->body("El lote {$record->codigo_lote} ha sido puesto en cuarentena exitosamente.")
                                ->warning()
                                ->send();
                        })
                        ->visible(fn (Lote $record) => $record->estado === EstadoLote::Disponible && $record->cantidad_disponible > 0),

                    Action::make('rechazar_cuarentena_individual')
                        ->label('Rechazar / Desperdicio')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Rechazar Lote / Marcar como Desperdicio')
                        ->modalDescription('El lote será clasificado como RECHAZADO, su stock disponible será 0 y será reubicado físicamente en la Zona de Merma.')
                        ->form([
                            Textarea::make('motivo')
                                ->label('Motivo del Rechazo (Obligatorio)')
                                ->required(),
                        ])
                        ->action(function (array $data, Lote $record) {
                            app(RechazarLotesCuarentena::class)->execute([$record->id], $data['motivo'], auth()->id());

                            Notification::make()
                                ->title('Lote Rechazado')
                                ->body("El lote {$record->codigo_lote} ha sido clasificado como rechazado y trasladado a mermas.")
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
            ->bulkActions([
                BulkAction::make('liberar_cuarentena')
                    ->label('Liberar de Cuarentena')
                    ->icon(Heroicon::ShieldCheck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        app(LiberarLotesCuarentena::class)
                            ->execute($records->pluck('id')->all());

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
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo del Rechazo Masivo')
                            ->required(),
                    ])
                    ->action(function (array $data, Collection $records) {
                        app(RechazarLotesCuarentena::class)->execute($records->pluck('id')->all(), $data['motivo'], auth()->id());

                        Notification::make()
                            ->title('Lotes Rechazados')
                            ->body('Los lotes seleccionados han sido rechazados y trasladados a la zona de merma.')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn () => auth()->user()?->can('rechazar-lotes-cuarentena') ?? true),
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
