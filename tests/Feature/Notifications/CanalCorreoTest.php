<?php

use App\Enums\Notifications\CanalNotificacion;
use App\Enums\Notifications\TipoNotificacion;
use App\Mail\NotificacionSistema;
use App\Notifications\DatosNotificacion;
use App\Notifications\Reportes\Shared\NotificadorReportes;
use App\Repository\Models\User;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Mail;

it('envía por correo la notificación de reporte listo', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'jefe@bugambiliashotel.com']);

    app(NotificadorReportes::class)->reporteListo(
        $user,
        'RPT-2026-001',
        url('/admin/reportes/RPT-2026-001'),
    );

    Mail::assertSent(NotificacionSistema::class, function (NotificacionSistema $mail) use ($user): bool {
        return $mail->hasTo($user->email)
            && $mail->datos->title === 'Reporte listo';
    });
});

it('omite el envío por correo cuando el usuario no tiene email', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => null]);

    app(NotificadorReportes::class)->reporteListo($user, 'RPT-2026-002');

    Mail::assertNothingSent();
});

it('renderiza la plantilla de correo de notificación', function () {
    $datos = new DatosNotificacion(
        title: 'Reporte listo',
        body: 'El reporte RPT-2026-001 ha sido generado y esta disponible.',
        type: TipoNotificacion::Success,
        actions: [
            Action::make('descargar')
                ->label('Descargar')
                ->url('https://ejemplo.test/reporte.pdf'),
        ],
        channels: [CanalNotificacion::BaseDeDatos, CanalNotificacion::Correo],
    );

    $html = (new NotificacionSistema($datos))->render();

    expect($html)
        ->toContain('Reporte listo')
        ->toContain('Descargar')
        ->toContain('https://ejemplo.test/reporte.pdf');
});
