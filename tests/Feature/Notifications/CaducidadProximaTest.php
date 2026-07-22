<?php

use App\Enums\Inventario\EstadoLote;
use App\Notifications\Inventario\CaducidadProxima;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\User;

it('construye el mensaje de correo correctamente', function () {
    $user = User::factory()->create();
    $producto = Producto::factory()->create(['nombre' => 'Producto Test']);
    $ubicacion = Ubicacion::create([
        'nombre' => 'Almacén Principal',
        'tipo' => 'zona',
        'estado' => 1,
    ]);

    $lote = Lote::create([
        'codigo_lote' => 'LOT-CADUCIDAD-TEST',
        'producto_id' => $producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 100.0,
        'cantidad_inicial' => 100.0,
        'ubicacion_id' => $ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $notification = new CaducidadProxima($lote, 15);
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Caducidad próxima: LOT-CADUCIDAD-TEST');
    expect($mailMessage->introLines[0])->toContain('El lote LOT-CADUCIDAD-TEST vencerá en 15 días.');
    expect($mailMessage->introLines[1])->toContain("Producto ID: {$producto->id}");
    expect($mailMessage->introLines[2])->toContain('Cantidad: 100');
    expect($mailMessage->actionText)->toBe('Ver inventario');
    expect($mailMessage->actionUrl)->toBe(url('/admin/inventario/lotes'));
});
