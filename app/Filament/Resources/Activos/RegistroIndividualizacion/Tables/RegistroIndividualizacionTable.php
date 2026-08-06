<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\RegistroIndividualizacion\Tables;

use App\Enums\Activos\EstadoIndividualizacion;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Activos\Gestion\IndividualizarActivos;
use App\Repository\Models\Activos\RegistroIndividualizacion;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

readonly class RegistroIndividualizacionTable
{
    public function __construct(
        private IndividualizarActivos $individualizarActivos,
    ) {}

    public function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recepcionItem.recepcion.codigo')
                    ->label('Recepción')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cantidad_total')
                    ->label('Total a Individualizar')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('cantidad_registrada')
                    ->label('Individualizados')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn (RegistroIndividualizacion $record) => $record->cantidad_registrada === $record->cantidad_total ? 'success' : 'warning'),

                EstadoBadgeColumn::make(EstadoIndividualizacion::class)
                    ->sortable(),

                FechaStandardColumn::make('fecha_completado', 'Completado El')
                    ->toggleable(),

                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FiltroEstado::make(EstadoIndividualizacion::class),
            ])
            ->actions([
                Action::make('individualizar')
                    ->label('Individualizar')
                    ->icon(Heroicon::Sparkles)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Individualizar Unidades Físicas')
                    ->modalDescription(fn (RegistroIndividualizacion $record) => 'Estás a punto de registrar '.($record->cantidad_total - $record->cantidad_registrada)." unidades físicas para el producto '".($record->producto ? $record->producto->nombre : '')."'.")
                    ->schema([
                        Repeater::make('unidades')
                            ->label('Listado de Unidades Físicas')
                            ->schema([
                                TextInput::make('numero_serie')
                                    ->label('Número de Serie')
                                    ->placeholder('Ingrese número de serie del fabricante (opcional)'),

                                TextInput::make('nombre_descriptivo')
                                    ->label('Nombre Descriptivo / Ubicación Tentativa')
                                    ->placeholder('Ej. TV Suite 101, TV Sala de Estar'),

                                TextInput::make('notas')
                                    ->label('Notas Internas')
                                    ->placeholder('Estado del embalaje, detalles, etc.'),
                            ])
                            ->default(fn (?RegistroIndividualizacion $record) => $record ? array_fill(0, max(0, $record->cantidad_total - $record->cantidad_registrada), [
                                'numero_serie' => '',
                                'nombre_descriptivo' => '',
                                'notas' => '',
                            ]) : [])
                            ->columns(3),
                    ])
                    ->action(function (array $data, RegistroIndividualizacion $record) {
                        try {
                            $this->individualizarActivos->ejecutar(
                                $record->id,
                                $data['unidades'],
                                (int) auth()->id()
                            );

                            Notification::make()
                                ->title('Individualización Completada')
                                ->body('Las unidades físicas del activo fijo han sido registradas y agregadas al almacén general.')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Error en Registro')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (RegistroIndividualizacion $record) => $record->estado !== EstadoIndividualizacion::Completado),
            ]);
    }
}
