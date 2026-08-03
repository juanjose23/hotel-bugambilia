<?php

declare(strict_types=1);

use App\Enums\Reservas\TipoPagoReserva;

test('calcula los montos permitidos para una reserva', function (TipoPagoReserva $tipo, float $esperado): void {
    expect($tipo->monto(1250.75))->toBe($esperado);
})->with([
    'sin pago' => [TipoPagoReserva::SIN_PAGO, 0.0],
    'abono del 50 por ciento' => [TipoPagoReserva::ABONO_50, 625.38],
    'pago completo' => [TipoPagoReserva::PAGO_COMPLETO, 1250.75],
]);
