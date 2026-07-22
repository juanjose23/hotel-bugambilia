<?php

use App\Enums\Inventario\EstadoLote;
use App\Notifications\Inventario\NotificadorInventario;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $this->user->assignRole('super_admin');
    $this->actingAs($this->user);
    $this->notificador = app(NotificadorInventario::class);

    $this->producto = Producto::factory()->create(['nombre' => 'Producto Inventario']);
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén Principal',
        'tipo' => 'zona',
        'estado' => 1,
    ]);
});

it('despacha notificacion para loteEnCuarentena', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-CUAR-TEST',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Cuarentena,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $this->notificador->loteEnCuarentena($lote, 'Sospecha de contaminación');

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Lote LOT-CUAR-TEST en cuarentena');
    expect($notification->data['body'])->toBe('El lote del producto "Producto Inventario" ha sido enviado a cuarentena. Motivo: Sospecha de contaminación');
    expect($notification->data['icon'])->toBe('heroicon-s-exclamation-triangle');
    expect($notification->data['color'])->toBe('warning');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/inventario/lotes/{$lote->id}"));
});

it('despacha notificacion para loteLiberado', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-LIB-TEST',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $this->notificador->loteLiberado($lote);

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Lote LOT-LIB-TEST liberado');
    expect($notification->data['body'])->toBe('El lote del producto "Producto Inventario" ha sido liberado de cuarentena y reubicado.');
    expect($notification->data['icon'])->toBe('heroicon-s-check-circle');
    expect($notification->data['color'])->toBe('success');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/inventario/lotes/{$lote->id}"));
});
