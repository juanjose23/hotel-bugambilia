<?php

declare(strict_types=1);

namespace App\Actions\CodigoQR;

use App\Repository\Models\Reservas\Reserva;
use App\Support\Barcode\QrCodeGenerator;

final class GenerarTokenQR
{
    public function __construct(
        private readonly QrCodeGenerator $qrCodeGenerator,
    ) {}

    /**
     * Genera un token QR firmado y seguro para auto check-in o comprobantes sin exponer datos sensibles.
     *
     * @return array{token: string, qrBase64: string}
     */
    public function ejecutar(Reserva $reserva, int $size = 180): array
    {
        $configKey = config('app.key');
        $secret = is_string($configKey) && $configKey !== '' ? $configKey : 'hotel-bugambilia-secret';
        $payload = "reserva-{$reserva->id}-{$reserva->codigo_reserva}-{$reserva->created_at}";
        $token = hash_hmac('sha256', $payload, $secret);

        $qrData = "HTB-QR|{$reserva->codigo_reserva}|".substr($token, 0, 16);
        $qrBase64 = $this->qrCodeGenerator->base64(text: $qrData, size: $size);

        return [
            'token' => $token,
            'qrBase64' => $qrBase64,
        ];
    }
}
