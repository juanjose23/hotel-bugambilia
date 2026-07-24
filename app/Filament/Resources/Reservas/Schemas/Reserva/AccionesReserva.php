<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\Reservas\EstadoReserva;
use App\Filament\Pages\Reservas\CheckInPage;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Interactors\Reservas\CancelarReserva;
use App\Interactors\Reservas\ConfirmarReserva;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class AccionesReserva
{
    /** @return array<int, Action> */
    public static function make(): array
    {
        return [
            Action::make('confirmar')
                ->label('Confirmar')
                ->icon(Heroicon::CheckCircle)
                ->color('info')
                ->visible(fn ($record): bool => $record->estado === EstadoReserva::PENDIENTE)
                ->action(function ($record, ConfirmarReserva $interactor): void {
                    $interactor->ejecutar($record, self::usuarioId());
                    Notification::make()->title('Reserva confirmada')->success()->send();
                }),

            Action::make('check_in')
                ->label('Check-In')
                ->icon(Heroicon::Key)
                ->color('success')
                ->visible(fn ($record): bool => $record->estado === EstadoReserva::CONFIRMADA)
                ->url(fn ($record): string => CheckInPage::getUrl(['record' => $record->id]))
                ->openUrlInNewTab(),

            Action::make('check_out')
                ->label('Check-Out')
                ->icon(Heroicon::ArrowRightEndOnRectangle)
                ->color('warning')
                ->visible(fn ($record): bool => $record->estado === EstadoReserva::CHECKED_IN)
                ->url(fn ($record): string => CheckOutPage::getUrl(['record' => $record->id]))
                ->openUrlInNewTab(),

            Action::make('voucher')
                ->label('Voucher PDF')
                ->icon(Heroicon::DocumentArrowDown)
                ->color('gray')
                ->url(fn ($record): string => route('reservas.voucher', ['reserva' => $record->id]))
                ->openUrlInNewTab(),

            Action::make('cancelar')
                ->label('Cancelar')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn ($record): bool => in_array($record->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA], true))
                ->action(function ($record, CancelarReserva $interactor): void {
                    $interactor->ejecutar($record, self::usuarioId());
                    Notification::make()->title('Reserva cancelada')->success()->send();
                }),
        ];
    }

    private static function usuarioId(): ?int
    {
        $id = auth()->id();

        return is_numeric($id) ? (int) $id : null;
    }
}
