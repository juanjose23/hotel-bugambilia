<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\User;
use App\Services\Inventario\NotificadorInventario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
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
    expect($notification->data['title'])->toBe('Lote en Cuarentena');
    expect($notification->data['body'])->toBe('El lote LOT-CUAR-TEST (Producto: Producto Inventario) ha sido puesto en CUARENTENA. Motivo: Sospecha de contaminación.');
    expect($notification->data['icon'])->toBe('heroicon-o-shield-exclamation');
    expect($notification->data['iconColor'])->toBe('warning');
    expect($notification->data['status'])->toBe('warning');
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
    expect($notification->data['title'])->toBe('Lote Liberado de Cuarentena');
    expect($notification->data['body'])->toBe('El lote LOT-LIB-TEST (Producto: Producto Inventario) ha sido liberado de cuarentena y ahora está Disponible.');
    expect($notification->data['icon'])->toBe('heroicon-o-check-circle');
    expect($notification->data['iconColor'])->toBe('success');
    expect($notification->data['status'])->toBe('success');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/inventario/lotes/{$lote->id}"));
});
