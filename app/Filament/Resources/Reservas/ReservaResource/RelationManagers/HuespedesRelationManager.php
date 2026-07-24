<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\RelationManagers;

use App\Enums\Reservas\TipoHuesped;
use App\Filament\Resources\Reservas\Schemas\Reserva\FormularioHuesped;
use App\Interactors\Reservas\RegistrarHuespedes;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class HuespedesRelationManager extends RelationManager
{
    protected static string $relationship = 'huespedes';

    protected static ?string $title = 'Huéspedes y acompañantes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema(FormularioHuesped::make());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('tipo_huesped')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (TipoHuesped $state): string => match ($state) {
                        TipoHuesped::ADULTO => 'Adulto',
                        TipoHuesped::NINO => 'Niño',
                        TipoHuesped::INFANTE => 'Infante',
                    })
                    ->color(fn (TipoHuesped $state): string => match ($state) {
                        TipoHuesped::ADULTO => 'info',
                        TipoHuesped::NINO => 'warning',
                        TipoHuesped::INFANTE => 'gray',
                    }),
                TextColumn::make('identificacion')->label('Identificación')->placeholder('—'),
                IconColumn::make('es_titular')->label('Titular')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar huésped')
                    ->icon('heroicon-o-user-plus')
                    ->mutateDataUsing(function (array $data): array {
                        $data['tipo_huesped'] = $this->mapTipoHuesped($data['tipo_huesped'] ?? 'adulto');

                        return $data;
                    })
                    ->action(function (array $data): void {
                        try {
                            /** @var Reserva $reserva */
                            $reserva = $this->getOwnerRecord();

                            $interactor = app(RegistrarHuespedes::class);
                            $interactor->agregar($reserva, $data);

                            Notification::make()
                                ->title('Huésped agregado')
                                ->success()
                                ->send();
                        } catch (DomainException $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['tipo_huesped'] = $this->mapTipoHuesped($data['tipo_huesped'] ?? 'adulto');

                        return $data;
                    })
                    ->action(function ($record, array $data): void {
                        try {
                            /** @var Reserva $reserva */
                            $reserva = $this->getOwnerRecord();

                            $interactor = app(RegistrarHuespedes::class);
                            $interactor->actualizar($reserva, $record, $data);

                            Notification::make()
                                ->title('Huésped actualizado')
                                ->success()
                                ->send();
                        } catch (DomainException $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteAction::make()
                    ->action(function ($record): void {
                        try {
                            /** @var Reserva $reserva */
                            $reserva = $this->getOwnerRecord();

                            $interactor = app(RegistrarHuespedes::class);
                            $interactor->eliminar($reserva, $record);

                            Notification::make()
                                ->title('Huésped eliminado')
                                ->success()
                                ->send();
                        } catch (DomainException $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    private function mapTipoHuesped(string $value): int
    {
        return match ($value) {
            'adulto', '1', 'Adulto' => TipoHuesped::ADULTO->value,
            'nino', '2', 'Niño' => TipoHuesped::NINO->value,
            'infante', '3', 'Infante' => TipoHuesped::INFANTE->value,
            default => TipoHuesped::ADULTO->value,
        };
    }
}
