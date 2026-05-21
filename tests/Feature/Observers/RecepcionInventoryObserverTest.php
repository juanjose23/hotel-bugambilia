<?php

use App\Enums\Compras\EstadoRecepcion;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\User;
use App\UseCases\Inventario\Recepciones\Mutations\RegistrarEntradaRecepcion;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén Principal',
        'tipo' => 'zona',
        'estado' => 1,
    ]);

    $this->orden = OrdenCompra::factory()->create();
});

it('desencadena RegistrarEntradaRecepcion cuando la recepcion pasa a estado Completa', function () {
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $this->orden->id,
        'estado' => EstadoRecepcion::Pendiente,
        'recibido_por_id' => $this->user->id,
    ]);

    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $this->orden->id,
        'producto_id' => $this->producto->id,
    ]);

    $item = RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_recibida' => 15.0,
        'cantidad_rechazada' => 0.0,
        'lote_proveedor' => 'LOTE-12345',
        'fecha_vencimiento' => now()->addYear(),
        'ubicacion_id' => $this->ubicacion->id,
    ]);

    // Mock del Use Case RegistrarEntradaRecepcion
    $mockUseCase = mock(RegistrarEntradaRecepcion::class);
    $mockUseCase->shouldReceive('execute')
        ->once()
        ->with(
            'Completa',
            [
                [
                    'id' => $item->id,
                    'producto_id' => $this->producto->id,
                    'producto_variante_id' => null,
                    'cantidad_recibida' => 15.0,
                    'cantidad_rechazada' => 0.0,
                    'lote_proveedor' => 'LOTE-12345',
                    'fecha_vencimiento' => $item->fecha_vencimiento->format('Y-m-d'),
                    'ubicacion_id' => $this->ubicacion->id,
                ],
            ],
            $this->orden->proveedor_id,
            $this->user->id
        );

    $this->app->instance(RegistrarEntradaRecepcion::class, $mockUseCase);

    // Actualizar el estado para disparar el observer
    $recepcion->update(['estado' => EstadoRecepcion::Completa]);
});

it('desencadena RegistrarEntradaRecepcion usando la ubicacion de la cabecera cuando el item no la especifica', function () {
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $this->orden->id,
        'estado' => EstadoRecepcion::Pendiente,
        'recibido_por_id' => $this->user->id,
        'ubicacion_id' => $this->ubicacion->id, // Establecido en el encabezado
    ]);

    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $this->orden->id,
        'producto_id' => $this->producto->id,
    ]);

    $item = RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_recibida' => 10.0,
        'cantidad_rechazada' => 0.0,
        'lote_proveedor' => 'LOTE-HEADER',
        'fecha_vencimiento' => now()->addYear(),
        'ubicacion_id' => null, // Dejado vacío en el item
    ]);

    // Mock del Use Case RegistrarEntradaRecepcion
    $mockUseCase = mock(RegistrarEntradaRecepcion::class);
    $mockUseCase->shouldReceive('execute')
        ->once()
        ->with(
            'Completa',
            [
                [
                    'id' => $item->id,
                    'producto_id' => $this->producto->id,
                    'producto_variante_id' => null,
                    'cantidad_recibida' => 10.0,
                    'cantidad_rechazada' => 0.0,
                    'lote_proveedor' => 'LOTE-HEADER',
                    'fecha_vencimiento' => $item->fecha_vencimiento->format('Y-m-d'),
                    'ubicacion_id' => $this->ubicacion->id, // Debe heredar de la cabecera
                ],
            ],
            $this->orden->proveedor_id,
            $this->user->id
        );

    $this->app->instance(RegistrarEntradaRecepcion::class, $mockUseCase);

    // Actualizar el estado para disparar el observer
    $recepcion->update(['estado' => EstadoRecepcion::Completa]);
});
