<?php

declare(strict_types=1);

use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Notifications\TipoNotificacion;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\CheckInRegistrado;
use App\Events\Reservas\CheckOutRegistrado;
use App\Events\Reservas\ReservaCancelada;
use App\Events\Reservas\ReservaConfirmada;
use App\Events\Reservas\ReservaCreada;
use App\Listeners\Reservas\EnviarCorreoCheckIn;
use App\Listeners\Reservas\EnviarCorreoCheckOut;
use App\Listeners\Reservas\EnviarCorreoReservaCancelada;
use App\Listeners\Reservas\EnviarCorreoReservaConfirmada;
use App\Listeners\Reservas\EnviarCorreoReservaCreada;
use App\Mail\NotificacionSistema;
use App\Notifications\Reservas\NotificadorHuesped;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\Mail;

function crearReservaHuespedCorreo(string $codigo = 'RES-CORREO-001', ?string $email = 'huesped@correo.test', string $nombre = 'Ana Pérez'): Reserva
{
    return Reserva::query()->create([
        'codigo_reserva' => $codigo,
        'nombre_cliente' => $nombre,
        'email_cliente' => $email,
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'estado' => EstadoReserva::CONFIRMADA,
        'total' => 2500.00,
    ]);
}

function crearEstanciaHuespedCorreo(Reserva $reserva): Estancia
{
    return Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'fecha_entrada_programada' => now()->subDay(),
        'fecha_salida_programada' => now()->addHour(),
        'check_in_at' => now()->subDay(),
        'estado' => EstadoEstancia::ACTIVA,
    ]);
}

it('envía por correo la reserva creada al huésped', function (): void {
    Mail::fake();
    $reserva = crearReservaHuespedCorreo();

    app(EnviarCorreoReservaCreada::class)->handle(new ReservaCreada($reserva));

    Mail::assertSent(NotificacionSistema::class, function (NotificacionSistema $mail) use ($reserva): bool {
        return $mail->hasTo('huesped@correo.test')
            && $mail->datos->title === 'Reserva registrada'
            && $mail->datos->type === TipoNotificacion::ReservationCreated
            && str_contains($mail->datos->body, $reserva->codigo_reserva);
    });
});

it('envía por correo la confirmación de reserva al huésped', function (): void {
    Mail::fake();
    $reserva = crearReservaHuespedCorreo();

    app(EnviarCorreoReservaConfirmada::class)->handle(new ReservaConfirmada($reserva));

    Mail::assertSent(NotificacionSistema::class, function (NotificacionSistema $mail) use ($reserva): bool {
        return $mail->hasTo('huesped@correo.test')
            && $mail->datos->title === 'Tu reserva está confirmada'
            && $mail->datos->type === TipoNotificacion::ReservationConfirmed
            && str_contains($mail->datos->body, $reserva->codigo_reserva)
            && str_contains($mail->datos->body, now()->addDay()->format('d/m/Y'))
            && str_contains($mail->datos->body, '2,500.00 USD');
    });
});

it('envía por correo la cancelación de reserva al huésped', function (): void {
    Mail::fake();
    $reserva = crearReservaHuespedCorreo();

    app(EnviarCorreoReservaCancelada::class)->handle(new ReservaCancelada($reserva));

    Mail::assertSent(NotificacionSistema::class, function (NotificacionSistema $mail) use ($reserva): bool {
        return $mail->hasTo('huesped@correo.test')
            && $mail->datos->title === 'Reserva cancelada'
            && $mail->datos->type === TipoNotificacion::ReservationCancelled
            && str_contains($mail->datos->body, $reserva->codigo_reserva);
    });
});

it('envía por correo el recordatorio de reserva al huésped', function (): void {
    Mail::fake();
    $reserva = crearReservaHuespedCorreo();

    app(NotificadorHuesped::class)->recordatorio($reserva, now()->addMinutes(30));

    Mail::assertSent(NotificacionSistema::class, function (NotificacionSistema $mail) use ($reserva): bool {
        return $mail->hasTo('huesped@correo.test')
            && $mail->datos->title === 'Tu reserva inicia pronto'
            && $mail->datos->type === TipoNotificacion::ReservationReminder
            && str_contains($mail->datos->body, $reserva->codigo_reserva);
    });
});

it('envía por correo el check-in y el check-out al huésped', function (): void {
    Mail::fake();
    $reserva = crearReservaHuespedCorreo();
    $estancia = crearEstanciaHuespedCorreo($reserva);

    app(EnviarCorreoCheckIn::class)->handle(new CheckInRegistrado($estancia));

    Mail::assertSent(NotificacionSistema::class, function (NotificacionSistema $mail): bool {
        return $mail->hasTo('huesped@correo.test')
            && $mail->datos->title === 'Check-in completado';
    });

    Mail::fake();

    app(EnviarCorreoCheckOut::class)->handle(new CheckOutRegistrado($estancia));

    Mail::assertSent(NotificacionSistema::class, function (NotificacionSistema $mail): bool {
        return $mail->hasTo('huesped@correo.test')
            && $mail->datos->title === 'Check-out completado';
    });
});

it('no envía correo al huésped cuando la reserva no tiene email', function (): void {
    Mail::fake();
    $reserva = crearReservaHuespedCorreo(email: null);

    app(EnviarCorreoReservaConfirmada::class)->handle(new ReservaConfirmada($reserva));

    Mail::assertNothingSent();
});

it('registra los listeners de correo en los eventos de reservas', function (): void {
    $dispatcher = app('events');

    expect($dispatcher->getListeners(ReservaCreada::class))->not->toBeEmpty()
        ->and($dispatcher->getListeners(ReservaConfirmada::class))->not->toBeEmpty()
        ->and($dispatcher->getListeners(ReservaCancelada::class))->not->toBeEmpty()
        ->and($dispatcher->getListeners(CheckInRegistrado::class))->not->toBeEmpty()
        ->and($dispatcher->getListeners(CheckOutRegistrado::class))->not->toBeEmpty();
});
