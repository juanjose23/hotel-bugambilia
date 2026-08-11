<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Filament\Pages\Reservas\CheckInPage;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Interactors\Reservas\Gestion\CancelarReserva;
use App\Interactors\Reservas\Gestion\ConfirmarReserva;
use App\Interactors\Restaurante\Mesas\ConfirmarLlegadaReservaMesa;
use App\Notifications\Reservas\NotificadorReservas;
use App\Repository\Models\Reservas\Reserva;
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
                ->visible(fn ($record): bool => $record->estado === EstadoReserva::CONFIRMADA && $record->tipo_reserva === TipoReserva::HABITACION)
                ->url(fn ($record): string => CheckInPage::getUrl(['record' => $record->id]))
                ->openUrlInNewTab(),

            Action::make('confirmar_llegada_restaurante')
                ->label('Confirmar Llegada (Mesa)')
                ->icon(Heroicon::UserPlus)
                ->color('success')
                ->visible(fn ($record): bool => in_array($record->estado, [EstadoReserva::CONFIRMADA, EstadoReserva::PENDIENTE], true) && $record->tipo_reserva === TipoReserva::RESTAURANTE)
                ->action(function ($record): void {
                    if ($record->espacio_id !== null) {
                        try {
                            app(ConfirmarLlegadaReservaMesa::class)->ejecutar((int) $record->espacio_id, self::usuarioId());
                            Notification::make()->title('Llegada a restaurante confirmada')->body('Comanda abierta en la mesa.')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error al confirmar llegada')->body($e->getMessage())->danger()->send();
                        }
                    } else {
                        Notification::make()->title('Mesa no asignada')->body('Asigne una mesa a la reservación antes de confirmar la llegada.')->warning()->send();
                    }
                }),

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

            Action::make('enviar_voucher_email')
                ->label('Enviar PDF por Email')
                ->icon(Heroicon::Envelope)
                ->color('info')
                ->requiresConfirmation()
                ->action(function (Reserva $record): void {
                    if (empty($record->email_cliente)) {
                        Notification::make()->title('Cliente sin correo')->body('La reservación no posee un correo electrónico registrado.')->warning()->send();

                        return;
                    }

                    try {
                        app(NotificadorReservas::class)->reservaCreada($record);
                        Notification::make()->title('Comprobante enviado')->body("El expediente PDF de la reserva fue enviado a {$record->email_cliente}.")->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error al enviar')->body($e->getMessage())->danger()->send();
                    }
                }),

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
