<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Tables;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\RegistrarCheckIn;
use App\Interactors\Reservas\RegistrarCheckOut;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReservaTable
{
    public function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_reserva')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre_cliente')
                    ->label('Huésped / Cliente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipo_reserva')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('espacio.nombre')
                    ->label('Ambiente/Mesa')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('fecha_check_in')
                    ->label('Check-In')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('fecha_check_out')
                    ->label('Check-Out')
                    ->date('Y-m-d')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('NIO')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('tipo_reserva')
                    ->options(TipoReserva::options()),
                SelectFilter::make('estado')
                    ->options(EstadoReserva::options()),
            ])
            ->recordActions([
                Action::make('check_in')
                    ->label('Check-In')
                    ->icon(Heroicon::Key)
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA]))
                    ->action(function ($record, RegistrarCheckIn $interactor) {
                        $interactor->ejecutar($record);
                        Notification::make()
                            ->title('Check-In registrado')
                            ->success()
                            ->send();
                    }),

                Action::make('check_out')
                    ->label('Check-Out')
                    ->icon(Heroicon::ArrowRightOnRectangle)
                    ->color('warning')
                    ->visible(fn ($record) => $record->estado === EstadoReserva::CHECKED_IN)
                    ->action(function ($record, RegistrarCheckOut $interactor) {
                        $interactor->ejecutar($record);
                        Notification::make()
                            ->title('Check-Out registrado')
                            ->success()
                            ->send();
                    }),

                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}
