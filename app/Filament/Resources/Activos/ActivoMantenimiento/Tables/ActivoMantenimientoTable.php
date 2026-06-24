<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoMantenimiento\Tables;

use App\Enums\Activos\EstadoMantenimiento;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use App\Models\Activos\ActivoMantenimiento;
use App\UseCases\Activos\Mutations\Mantenimiento\CompletarMantenimiento;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class ActivoMantenimientoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['activo', 'plan.moneda', 'realizadoPor']))
            ->columns([
                TextColumn::make('activo.codigo_inventario')
                    ->label('Activo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('activo.nombre_descriptivo')
                    ->label('Descripción del Activo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plan.tipo')
                    ->label('Tipo de Plan')
                    ->badge()
                    ->sortable(),

                TextColumn::make('fecha_programada')
                    ->label('Fecha Programada')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_realizada')
                    ->label('Fecha Realizada')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('En taller / Pendiente'),

                TextColumn::make('costo_real')
                    ->label('Costo Real')
                    ->money(fn ($record) => $record->plan?->moneda->codigo ?? 'NIO')
                    ->sortable()
                    ->placeholder('0.00'),

                TextColumn::make('realizadoPor.name')
                    ->label('Técnico')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                FiltroEstado::make(EstadoMantenimiento::class),
            ])
            ->recordActions([
                Action::make('completar_mantenimiento')
                    ->label('Completar')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Completar Orden de Mantenimiento')
                    ->schema([
                        DatePicker::make('fecha_realizada')
                            ->label('Fecha Realizada')
                            ->required()
                            ->default(now()),

                        TextInput::make('costo_real')
                            ->label('Costo Real / Final')
                            ->numeric()
                            ->required()
                            ->placeholder('0.00'),

                        Textarea::make('notas')
                            ->label('Notas Finales / Informe de Reparación')
                            ->required()
                            ->placeholder('Describa el trabajo final de taller'),
                    ])
                    ->action(function (array $data, ActivoMantenimiento $record): void {
                        try {
                            app(CompletarMantenimiento::class)->execute(
                                mantenimiento: $record,
                                fechaRealizada: $data['fecha_realizada'],
                                costoReal: (float) $data['costo_real'],
                                notas: $data['notas'],
                                usuarioId: (int) auth()->id(),
                            );

                            Notification::make()
                                ->title('Mantenimiento Completado')
                                ->body('La orden ha sido completada y el activo se reincorporó de taller exitosamente.')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Error al Completar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(
                        fn (ActivoMantenimiento $record) => ! in_array($record->estado, [
                            EstadoMantenimiento::Completado,
                            EstadoMantenimiento::Cancelado,
                        ])
                    ),

                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Más opciones'),
            ]);
    }
}
