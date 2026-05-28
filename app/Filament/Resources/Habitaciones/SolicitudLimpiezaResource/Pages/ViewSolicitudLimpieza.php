<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Pages;

use App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\SolicitudLimpiezaResource;
use App\Models\Limpieza\SolicitudLimpieza;
use App\UseCases\Limpieza\Mutations\IniciarLimpieza;
use App\UseCases\Limpieza\Mutations\TerminarLimpieza;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewSolicitudLimpieza extends ViewRecord
{
    protected static string $resource = SolicitudLimpiezaResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('iniciarLimpieza')
                ->label('Iniciar Limpieza')
                ->icon(Heroicon::Play)
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
                ->label('Terminar Limpieza')
                ->icon(Heroicon::CheckCircle)
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
        ];
    }
}
