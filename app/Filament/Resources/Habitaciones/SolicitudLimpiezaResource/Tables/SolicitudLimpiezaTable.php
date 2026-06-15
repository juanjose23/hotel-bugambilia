<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Tables;

use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\SolicitudLimpieza;
use App\UseCases\Limpieza\Mutations\IniciarLimpieza;
use App\UseCases\Limpieza\Mutations\TerminarLimpieza;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SolicitudLimpiezaTable
{
    /**
     * Configura la tabla para SolicitudLimpieza.
     */
    public function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['limpiable', 'personal', 'creador']))
            ->columns([
                TextColumn::make('limpiable.nombre')
                    ->label('Ubicación / Área')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('limpiable_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Habitacion::class => 'primary',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Habitacion::class => 'Habitación',
                        default => 'Espacio',
                    })
                    ->sortable(),

                TextColumn::make('creador.name')
                    ->label('Solicitado Por')
                    ->placeholder('Sistema / Automático')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('personal.name')
                    ->label('Personal de Limpieza')
                    ->placeholder('Sin asignar')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'alta' => 'danger',
                        'normal' => 'info',
                        'baja' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado Solicitud')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'en_progreso' => 'info',
                        'completada' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_progreso' => 'En Progreso',
                        'completada' => 'Completada',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('prioridad')
                    ->options([
                        'baja' => 'Baja',
                        'normal' => 'Normal',
                        'alta' => 'Alta',
                    ]),
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_progreso' => 'En Progreso',
                        'completada' => 'Completada',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('iniciarLimpieza')
                    ->label('Iniciar')
                    ->icon('heroicon-m-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (SolicitudLimpieza $record): bool => $record->estado === 'pendiente')
                    ->action(function (SolicitudLimpieza $record) {
                        app(IniciarLimpieza::class)->execute($record, auth()->id());

                        Notification::make()
                            ->title('Limpieza iniciada')
                            ->success()
                            ->send();
                    }),

                Action::make('terminarLimpieza')
                    ->label('Terminar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SolicitudLimpieza $record): bool => $record->estado === 'en_progreso')
                    ->action(function (SolicitudLimpieza $record) {
                        app(TerminarLimpieza::class)->execute($record);

                        Notification::make()
                            ->title('Ubicación lista y disponible')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
