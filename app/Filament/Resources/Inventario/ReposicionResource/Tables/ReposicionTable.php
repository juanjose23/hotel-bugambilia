<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ReposicionResource\Tables;

use App\Models\Inventario\Reposicion;
use App\UseCases\Inventario\Reposiciones\Mutations\GenerarReposicionesBodega;
use App\UseCases\Inventario\Reposiciones\Mutations\ProcesarReposicion;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReposicionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('origen.nombre')
                    ->label('Origen')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('destino.nombre')
                    ->label('Destino')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pendiente' => 'warning',
                        'procesada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                TextColumn::make('creadoPor.name')
                    ->label('Solicitado por')
                    ->placeholder('Sistema (PAR Stock)'),

                TextColumn::make('created_at')
                    ->label('Fecha Solicitud')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'procesada' => 'Procesada',
                        'cancelada' => 'Cancelada',
                    ]),
                SelectFilter::make('origen_id')
                    ->label('Bodega Origen')
                    ->relationship('origen', 'nombre'),
                SelectFilter::make('destino_id')
                    ->label('Bodega Destino')
                    ->relationship('destino', 'nombre'),
            ])
            ->headerActions([
                Action::make('sugerir_reposiciones')
                    ->label('Calcular PAR Stock')
                    ->icon(Heroicon::Sparkles)
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Calcular Necesidades (PAR Stock)')
                    ->modalDescription('El sistema analizará el stock real en todas las bodegas en comparación con sus niveles PAR configurados, y creará órdenes de reposición pendientes desde el Almacén General.')
                    ->action(function () {
                        try {
                            $generadas = app(GenerarReposicionesBodega::class)->execute(auth()->id());
                            $cant = count($generadas);

                            if ($cant > 0) {
                                Notification::make()
                                    ->title('Sugerencias Generadas')
                                    ->body("Se han generado {$cant} órdenes de reposición pendientes exitosamente.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Sin Necesidades')
                                    ->body('Todas las bodegas se encuentran por encima de sus límites mínimos de PAR stock.')
                                    ->info()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al calcular')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray'),

                Action::make('procesar_reposicion')
                    ->label('Procesar')
                    ->icon(Heroicon::Play)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Procesar y Despachar Reposición')
                    ->modalDescription('Se consumirá el stock de los productos solicitados desde la bodega origen (resolviendo FEFO) y se transferirá a la bodega destino física y lógicamente.')
                    ->action(function (Reposicion $record) {
                        try {
                            app(ProcesarReposicion::class)->execute($record->id, auth()->id());

                            Notification::make()
                                ->title('Reposición Procesada')
                                ->body("La reposición #{$record->id} fue procesada y distribuida correctamente.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al procesar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Reposicion $record) => $record->estado === 'pendiente'),
            ]);
    }
}
