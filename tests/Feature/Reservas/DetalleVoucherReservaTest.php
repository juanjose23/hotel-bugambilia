<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Reservas\ObtenerDetallePreordenReservaQuery;
use App\Support\Barcode\BarcodeGenerator;

test('prepara los valores de cada platillo y el código de barras del voucher', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-BAR-2026-001',
        'nombre_cliente' => 'Cliente restaurante',
        'tipo_reserva' => TipoReserva::RESTAURANTE,
        'fecha_check_in' => '2026-08-10',
        'adultos' => 4,
        'estado' => EstadoReserva::CONFIRMADA,
        'subtotal' => 500,
        'descuento' => 0,
        'total' => 575,
    ]);

    $reserva->crearEntradaBitacora('preorden', ['items' => [[
        'plato_id' => 99999,
        'cantidad' => 2,
        'precio_unitario' => 125,
        'observaciones' => 'Sin cebolla',
    ]]]);

    $detalle = app(ObtenerDetallePreordenReservaQuery::class)->ejecutar($reserva);
    $barcode = app(BarcodeGenerator::class)->base64($reserva->codigo_reserva);

    expect($detalle)->toHaveCount(1)
        ->and($detalle[0]['nombre'])->toBe('Platillo #99999')
        ->and($detalle[0]['cantidad'])->toBe(2)
        ->and($detalle[0]['precio_unitario'])->toBe(125.0)
        ->and($detalle[0]['subtotal'])->toBe(250.0)
        ->and($barcode)->toStartWith('data:image/png;base64,');
});
