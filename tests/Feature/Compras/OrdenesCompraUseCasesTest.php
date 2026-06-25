<?php

use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Models\Monedas\Moneda;
use App\Models\User;
use App\UseCases\Compras\OrdenesCompra\Mutations\CancelarOrdenCompra;
use App\UseCases\Compras\OrdenesCompra\Mutations\EmitirOrdenCompra;
use App\UseCases\Compras\OrdenesCompra\Mutations\FinalizarOrdenCompra;
use App\UseCases\Compras\OrdenesCompra\Mutations\GenerarCodigoOrdenCompra;
use App\UseCases\Compras\OrdenesCompra\Mutations\GenerarOrdenDesdeCotizacion;
use App\UseCases\Compras\OrdenesCompra\Mutations\GenerarOrdenesDesdeComparativa;
use App\UseCases\Compras\OrdenesCompra\Queries\ObtenerOrdenCompraConItems;
use App\UseCases\Compras\OrdenesCompra\Queries\ObtenerRecomendacionLogistica;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->catalogoTipo = CatalogoTipo::factory()->create();

    $this->moneda = Moneda::create([
        'codigo' => 'NIO',
        'nombre' => 'Córdoba',
        'simbolo' => 'C$',
    ]);

    $this->departamento = Catalogo::create([
        'nombre' => 'Cocina',
        'codigo' => 'DEP_COCINA',
        'catalogo_tipo_id' => $this->catalogoTipo->id,
        'estado' => 1,
        'orden' => 1,
    ]);

    $this->proveedor = Proveedor::factory()->create();

    $this->solicitud = Solicitud::create([
        'codigo' => 'S-COCI-001',
        'colaborador_id' => Colaborador::factory()->create()->id,
        'departamento_solicitante_id' => $this->departamento->id,
        'fecha_solicitud' => now(),
        'fecha_necesita' => now()->addDays(7),
        'motivo' => 'Prueba',
        'estado' => EstadoSolicitud::Aprobada,
    ]);

    $this->solicitudItem = $this->solicitud->items()->create([
        'producto_id' => $this->producto->id,
        'cantidad_solicitada' => 10,
        'cantidad_aprobada' => 10,
    ]);
});

describe('CancelarOrdenCompra', function () {
    it('cambia estado a Cancelada y notifica', function () {
        $orden = OrdenCompra::factory()->create([
            'estado' => EstadoOrdenCompra::Emitida,
        ]);

        app(CancelarOrdenCompra::class)->execute($orden);

        expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Cancelada);
    });

    it('lanza excepcion si la orden tiene recepciones registradas', function () {
        $orden = OrdenCompra::factory()->create([
            'estado' => EstadoOrdenCompra::Emitida,
        ]);

        RecepcionCompra::factory()->create([
            'orden_compra_id' => $orden->id,
        ]);

        expect(fn () => app(CancelarOrdenCompra::class)->execute($orden))
            ->toThrow(DomainException::class, 'No se puede cancelar una orden que ya tiene recepciones');
    });

    it('permite cancelar orden sin recepciones aunque tenga items', function () {
        $orden = OrdenCompra::factory()->create([
            'estado' => EstadoOrdenCompra::Emitida,
        ]);

        OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'cantidad' => 10,
        ]);

        app(CancelarOrdenCompra::class)->execute($orden);

        expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Cancelada);
    });
});

describe('EmitirOrdenCompra', function () {
    it('cambia estado a Emitida', function () {
        $orden = OrdenCompra::factory()->create([
            'estado' => EstadoOrdenCompra::Borrador,
        ]);

        app(EmitirOrdenCompra::class)->execute($orden);

        expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Emitida);
    });
});

describe('FinalizarOrdenCompra', function () {
    it('cambia estado a Recibida', function () {
        $orden = OrdenCompra::factory()->create([
            'estado' => EstadoOrdenCompra::Emitida,
        ]);

        app(FinalizarOrdenCompra::class)->execute($orden);

        expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Recibida);
    });
});

describe('GenerarCodigoOrdenCompra', function () {
    it('genera el primer codigo OC-YYYY-001 cuando no existen ordenes', function () {
        OrdenCompra::whereNotNull('id')->delete();

        $codigo = app(GenerarCodigoOrdenCompra::class)->execute();

        $year = now()->year;
        expect($codigo)->toBe("OC-{$year}-001");
    });

    it('genera codigos correlativos secuenciales', function () {
        OrdenCompra::whereNotNull('id')->delete();
        $year = now()->year;

        $codigo1 = app(GenerarCodigoOrdenCompra::class)->execute();
        expect($codigo1)->toBe("OC-{$year}-001");

        OrdenCompra::factory()->create(['codigo' => $codigo1]);

        $codigo2 = app(GenerarCodigoOrdenCompra::class)->execute();
        expect($codigo2)->toBe("OC-{$year}-002");
    });

    it('ignora ordenes de otros anios al generar', function () {
        OrdenCompra::whereNotNull('id')->delete();
        $year = now()->year;

        OrdenCompra::factory()->create(['codigo' => 'OC-1999-050']);

        $codigo = app(GenerarCodigoOrdenCompra::class)->execute();
        expect($codigo)->toBe("OC-{$year}-001");
    });
});

describe('GenerarOrdenDesdeCotizacion', function () {
    beforeEach(function () {
        $this->cotizacion = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
            'es_elegida' => true,
        ]);
    });

    it('crea orden de compra desde cotizacion elegida con todos sus items', function () {
        $this->cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
        ]);

        $orden = app(GenerarOrdenDesdeCotizacion::class)->execute($this->cotizacion->id);

        expect($orden)->toBeInstanceOf(OrdenCompra::class);
        expect($orden->codigo)->toMatch('/^OC-\d{4}-\d{3}$/');
        expect($orden->proveedor_id)->toBe($this->proveedor->id);
        expect($orden->solicitud_id)->toBe($this->solicitud->id);
        expect($orden->cotizacion_id)->toBe($this->cotizacion->id);
        expect($orden->estado)->toBe(EstadoOrdenCompra::Borrador);
        expect($orden->subtotal)->toBe('1000.00');
        expect($orden->impuestos)->toBe('150.00');
        expect($orden->total)->toBe('1150.00');
        expect($orden->items)->toHaveCount(1);
    });

    it('crea orden con items elegidos especificamente cuando existen', function () {
        $producto2 = Producto::factory()->create();
        $this->solicitud->items()->create([
            'producto_id' => $producto2->id,
            'cantidad_solicitada' => 5,
            'cantidad_aprobada' => 5,
        ]);

        $itemElegido = $this->cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
            'es_elegido' => true,
        ]);

        $this->cotizacion->items()->create([
            'producto_id' => $producto2->id,
            'cantidad' => 5,
            'precio_unitario' => 50,
            'subtotal' => 250,
            'es_elegido' => false,
        ]);

        $orden = app(GenerarOrdenDesdeCotizacion::class)->execute($this->cotizacion->id);

        expect($orden->items)->toHaveCount(1);
        expect($orden->items->first()->producto_id)->toBe($this->producto->id);
    });

    it('lanza excepcion si no hay items elegidos y la cotizacion no es elegida', function () {
        $this->cotizacion->update(['es_elegida' => false]);

        $this->cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
            'es_elegido' => false,
        ]);

        expect(fn () => app(GenerarOrdenDesdeCotizacion::class)->execute($this->cotizacion->id))
            ->toThrow(Exception::class, 'Debe seleccionar al menos un ítem');
    });

    it('marca la solicitud como Aprobada', function () {
        $this->cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
            'es_elegido' => true,
        ]);

        app(GenerarOrdenDesdeCotizacion::class)->execute($this->cotizacion->id);

        expect($this->solicitud->fresh()->estado)->toBe(EstadoSolicitud::Aprobada);
    });

    it('lanza exception si la cotizacion no existe', function () {
        expect(fn () => app(GenerarOrdenDesdeCotizacion::class)->execute(99999))
            ->toThrow(ModelNotFoundException::class);
    });
});

describe('GenerarOrdenesDesdeComparativa', function () {
    it('crea ordenes para cada cotizacion con items elegidos', function () {
        $cotizacion = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
            'es_elegido' => true,
        ]);

        $cantidad = app(GenerarOrdenesDesdeComparativa::class)->execute($this->solicitud->id);

        expect($cantidad)->toBe(1);

        $orden = OrdenCompra::where('solicitud_id', $this->solicitud->id)->first();
        expect($orden)->not->toBeNull();
        expect($orden->cotizacion_id)->toBe($cotizacion->id);
        expect($orden->items)->toHaveCount(1);
    });

    it('retorna 0 cuando no hay cotizaciones con items elegidos', function () {
        $cotizacion = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
            'es_elegido' => false,
        ]);

        $cantidad = app(GenerarOrdenesDesdeComparativa::class)->execute($this->solicitud->id);

        expect($cantidad)->toBe(0);
    });

    it('salta cotizaciones que ya tienen orden no cancelada', function () {
        $cotizacion = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
            'es_elegido' => true,
        ]);

        OrdenCompra::factory()->create([
            'solicitud_id' => $this->solicitud->id,
            'cotizacion_id' => $cotizacion->id,
            'estado' => EstadoOrdenCompra::Borrador,
        ]);

        $cantidad = app(GenerarOrdenesDesdeComparativa::class)->execute($this->solicitud->id);

        expect($cantidad)->toBe(0);
    });

    it('lanza exception si la solicitud no existe', function () {
        expect(fn () => app(GenerarOrdenesDesdeComparativa::class)->execute(99999))
            ->toThrow(ModelNotFoundException::class);
    });
});

describe('ObtenerOrdenCompraConItems', function () {
    it('retorna orden con items y cantidad_pendiente calculada', function () {
        $orden = OrdenCompra::factory()->create();
        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'cantidad' => 10,
        ]);

        $recepcion = RecepcionCompra::factory()->create([
            'orden_compra_id' => $orden->id,
            'estado' => EstadoRecepcion::Completa,
        ]);

        $recepcion->items()->create([
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $ordenItem->producto_id,
            'cantidad_recibida' => 4,
            'cantidad_rechazada' => 0,
        ]);

        $resultado = app(ObtenerOrdenCompraConItems::class)->execute($orden->id);

        expect($resultado)->not->toBeNull();
        expect($resultado->items)->toHaveCount(1);
        expect((float) $resultado->items->first()->cantidad_pendiente)->toBe(6.0);
    });

    it('retorna null cuando la orden no existe', function () {
        $resultado = app(ObtenerOrdenCompraConItems::class)->execute(99999);

        expect($resultado)->toBeNull();
    });

    it('getItemPendingQuantity retorna la cantidad pendiente correcta', function () {
        $orden = OrdenCompra::factory()->create();
        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'cantidad' => 10,
        ]);

        $recepcion = RecepcionCompra::factory()->create([
            'orden_compra_id' => $orden->id,
            'estado' => EstadoRecepcion::Completa,
        ]);

        $recepcion->items()->create([
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $ordenItem->producto_id,
            'cantidad_recibida' => 7,
        ]);

        $pendiente = app(ObtenerOrdenCompraConItems::class)->getItemPendingQuantity($ordenItem->id);

        expect($pendiente)->toBe(3.0);
    });

    it('getItemOptions retorna solo items con pendiente mayor a cero', function () {
        $orden = OrdenCompra::factory()->create();
        $itemPendiente = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'cantidad' => 10,
        ]);

        $itemCompleto = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'cantidad' => 5,
        ]);

        $recepcion = RecepcionCompra::factory()->create([
            'orden_compra_id' => $orden->id,
            'estado' => EstadoRecepcion::Completa,
        ]);

        $recepcion->items()->create([
            'orden_item_id' => $itemCompleto->id,
            'producto_id' => $itemCompleto->producto_id,
            'cantidad_recibida' => 5,
        ]);

        $opciones = app(ObtenerOrdenCompraConItems::class)->getItemOptions($orden->id);

        expect($opciones)->toHaveCount(1);
        expect(array_key_first($opciones))->toBe($itemPendiente->id);
    });
});

describe('ObtenerRecomendacionLogistica', function () {
    it('retorna SIN_DATOS cuando no hay cotizaciones', function () {
        $resultado = app(ObtenerRecomendacionLogistica::class)->execute($this->solicitud);

        expect($resultado['tipo'])->toBe('SIN_DATOS');
        expect($resultado['color'])->toBe('gray');
    });

    it('retorna PROVEEDOR_UNICO por eficiencia operativa cuando un proveedor cubre todo y su TCO es mejor', function () {
        $cot = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 7,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cot->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => $this->solicitudItem->cantidad_solicitada,
            'precio_unitario' => 100,
            'subtotal' => 1000,
        ]);

        $proveedor2 = Proveedor::factory()->create();
        $cot2 = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor2->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 14,
            'subtotal' => 2000,
            'impuestos' => 300,
            'total' => 2300,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cot2->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => $this->solicitudItem->cantidad_solicitada,
            'precio_unitario' => 200,
            'subtotal' => 2000,
        ]);

        $resultado = app(ObtenerRecomendacionLogistica::class)->execute($this->solicitud);

        expect($resultado['tipo'])->toBe('PROVEEDOR ÚNICO');
        expect($resultado['subtipo'])->toBe('EFICIENCIA_OPERATIVA');
        expect($resultado['color'])->toBe('success');
    });

    it('retorna COMPRA_DIVIDIDA cuando dividir es mas economico', function () {
        $producto2 = Producto::factory()->create();
        $this->solicitud->items()->create([
            'producto_id' => $producto2->id,
            'cantidad_solicitada' => 5,
            'cantidad_aprobada' => 5,
        ]);

        $cot = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 7,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cot->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => $this->solicitudItem->cantidad_solicitada,
            'precio_unitario' => 100,
            'subtotal' => 1000,
        ]);

        $proveedor2 = Proveedor::factory()->create();
        $cot2 = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor2->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 200,
            'impuestos' => 30,
            'total' => 230,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cot2->items()->create([
            'producto_id' => $producto2->id,
            'cantidad' => 5,
            'precio_unitario' => 40,
            'subtotal' => 200,
        ]);

        $resultado = app(ObtenerRecomendacionLogistica::class)->execute($this->solicitud);

        expect($resultado['tipo'])->toBe('COMPRA DIVIDIDA');
        expect($resultado['color'])->toBe('warning');
    });
});
