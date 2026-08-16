<?php

declare(strict_types=1);

use App\Actions\CodigoQR\GenerarTokenQR;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;
use App\Support\Barcode\QrCodeGenerator;
use Illuminate\Support\Facades\Http;

test('generarTokenQR produce una firma HMAC valida y un QR Base64', function (): void {
    Http::fake([
        'quickchart.io/*' => Http::response('fake-png-bytes', 200, ['Content-Type' => 'image/png']),
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-QR-999',
        'nombre_cliente' => 'Cliente Token QR',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
    ]);

    $action = new GenerarTokenQR(new QrCodeGenerator);
    $resultado = $action->ejecutar($reserva);

    expect($resultado)->toHaveKeys(['token', 'qrBase64'])
        ->and($resultado['token'])->toBeString()->not->toBeEmpty()
        ->and($resultado['qrBase64'])->toBeString()->toStartWith('data:image/png;base64,');
});
