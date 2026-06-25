<?php

use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\Proveedor;
use App\Models\Compras\Solicitud;
use App\Models\Monedas\Moneda;
use App\Models\User;
use App\UseCases\Compras\Cotizaciones\Mutations\ActualizarEstadosCotizacionesSolicitud;
use App\UseCases\Compras\Cotizaciones\Mutations\ElegirCotizacionGanadora;
use App\UseCases\Compras\Cotizaciones\Mutations\SeleccionarItemGanador;
use App\UseCases\Compras\Cotizaciones\Queries\AnalizarScoringCotizaciones;
use App\UseCases\Compras\Cotizaciones\Queries\ObtenerCotizacionConItemsProveedor;
use App\UseCases\Compras\Cotizaciones\Queries\ObtenerCotizacionesPorSolicitud;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->catalogoTipo = CatalogoTipo::factory()->create();

    $this->moneda = Moneda::create([
        'codigo' => 'NIO',
        'nombre' => 'Córdoba',
        'simbolo' => 'C$',
    ]);

    $departamento = Catalogo::create([
        'nombre' => 'Cocina',
        'codigo' => 'DEP_COCINA',
        'catalogo_tipo_id' => $this->catalogoTipo->id,
        'estado' => 1,
        'orden' => 1,
    ]);

    $this->producto = Producto::factory()->create();

    $this->solicitud = Solicitud::create([
        'codigo' => 'S-COCI-001',
        'colaborador_id' => Colaborador::factory()->create()->id,
        'departamento_solicitante_id' => $departamento->id,
        'fecha_solicitud' => now(),
        'fecha_necesita' => now()->addDays(7),
        'motivo' => 'Prueba',
        'estado' => EstadoSolicitud::Pendiente,
    ]);

    $this->solicitudItem = $this->solicitud->items()->create([
        'producto_id' => $this->producto->id,
        'cantidad_solicitada' => 10,
        'cantidad_aprobada' => 10,
    ]);

    $this->proveedor = Proveedor::factory()->create();
});

describe('ActualizarEstadosCotizacionesSolicitud', function () {
    it('marca cotizacion como Aceptada cuando todos sus items son elegidos', function () {
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

        app(ActualizarEstadosCotizacionesSolicitud::class)->execute($this->solicitud->id);

        $cotizacion->refresh();
        expect($cotizacion->estado)->toBe(EstadoCotizacion::Aceptada);
        expect($cotizacion->es_elegida)->toBeTrue();
    });

    it('marca cotizacion como AceptadaParcial cuando solo algunos items son elegidos', function () {
        $producto2 = Producto::factory()->create();
        $this->solicitud->items()->create([
            'producto_id' => $producto2->id,
            'cantidad_solicitada' => 5,
            'cantidad_aprobada' => 5,
        ]);

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

        $cotizacion->items()->create([
            'producto_id' => $producto2->id,
            'cantidad' => 5,
            'precio_unitario' => 50,
            'subtotal' => 250,
            'es_elegido' => false,
        ]);

        app(ActualizarEstadosCotizacionesSolicitud::class)->execute($this->solicitud->id);

        $cotizacion->refresh();
        expect($cotizacion->estado)->toBe(EstadoCotizacion::AceptadaParcial);
        expect($cotizacion->es_elegida)->toBeFalse();
    });

    it('marca cotizacion como Rechazada cuando otra cotizacion tiene items elegidos', function () {
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

        $proveedor2 = Proveedor::factory()->create();
        $cotizacionGanadora = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor2->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 3,
            'subtotal' => 900,
            'impuestos' => 135,
            'total' => 1035,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $cotizacionGanadora->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 90,
            'subtotal' => 900,
            'es_elegido' => true,
        ]);

        app(ActualizarEstadosCotizacionesSolicitud::class)->execute($this->solicitud->id);

        expect($cotizacion->fresh()->estado)->toBe(EstadoCotizacion::Rechazada);
        expect($cotizacionGanadora->fresh()->estado)->toBe(EstadoCotizacion::Aceptada);
    });

    it('mantiene como Activa cuando no hay items elegidos en ninguna cotizacion', function () {
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

        app(ActualizarEstadosCotizacionesSolicitud::class)->execute($this->solicitud->id);

        expect($cotizacion->fresh()->estado)->toBe(EstadoCotizacion::Activa);
    });

    it('lanza exception si la solicitud no existe', function () {
        expect(fn () => app(ActualizarEstadosCotizacionesSolicitud::class)->execute(99999))
            ->toThrow(ModelNotFoundException::class);
    });
});

describe('ElegirCotizacionGanadora', function () {
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
        ]);

        $this->cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
        ]);
    });

    it('marca cotizacion como elegida y todos sus items como elegidos', function () {
        app(ElegirCotizacionGanadora::class)->execute($this->cotizacion->id);

        expect($this->cotizacion->fresh()->es_elegida)->toBeTrue();
        expect($this->cotizacion->fresh()->elegida_por)->toBe($this->user->id);
        expect($this->cotizacion->fresh()->elegida_en)->not->toBeNull();
        expect($this->cotizacion->fresh()->items->first()->es_elegido)->toBeTrue();
    });

    it('desmarca otras cotizaciones de la misma solicitud al elegir una nueva ganadora', function () {
        $proveedor2 = Proveedor::factory()->create();
        $cotizacion2 = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor2->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 3,
            'subtotal' => 900,
            'impuestos' => 135,
            'total' => 1035,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
            'es_elegida' => true,
        ]);

        $cotizacion2->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 90,
            'subtotal' => 900,
            'es_elegido' => true,
        ]);

        app(ElegirCotizacionGanadora::class)->execute($this->cotizacion->id);

        expect($this->cotizacion->fresh()->es_elegida)->toBeTrue();
        expect($cotizacion2->fresh()->es_elegida)->toBeFalse();
        expect($cotizacion2->fresh()->elegida_por)->toBeNull();
        expect($cotizacion2->items->first()->es_elegido)->toBeFalse();
    });

    it('lanza exception si la cotizacion no existe', function () {
        expect(fn () => app(ElegirCotizacionGanadora::class)->execute(99999))
            ->toThrow(ModelNotFoundException::class);
    });
});

describe('SeleccionarItemGanador', function () {
    beforeEach(function () {
        $producto2 = Producto::factory()->create();
        $this->solicitud->items()->create([
            'producto_id' => $producto2->id,
            'cantidad_solicitada' => 5,
            'cantidad_aprobada' => 5,
        ]);

        $this->cotizacion = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 1250,
            'impuestos' => 187.5,
            'total' => 1437.5,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $this->item1 = $this->cotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
            'subtotal' => 1000,
        ]);

        $this->item2 = $this->cotizacion->items()->create([
            'producto_id' => $producto2->id,
            'cantidad' => 5,
            'precio_unitario' => 50,
            'subtotal' => 250,
        ]);
    });

    it('marca item como elegido en la cotizacion', function () {
        app(SeleccionarItemGanador::class)->execute($this->cotizacion->id, $this->producto->id);

        expect($this->item1->fresh()->es_elegido)->toBeTrue();
        expect($this->item2->fresh()->es_elegido)->toBeFalse();
        expect($this->cotizacion->fresh()->elegida_por)->toBe($this->user->id);
    });

    it('desmarca el mismo producto como elegido en otras cotizaciones de la solicitud', function () {
        $proveedor2 = Proveedor::factory()->create();
        $otraCotizacion = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor2->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 3,
            'subtotal' => 1100,
            'impuestos' => 165,
            'total' => 1265,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $otroItem = $otraCotizacion->items()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 110,
            'subtotal' => 1100,
            'es_elegido' => true,
        ]);

        app(SeleccionarItemGanador::class)->execute($this->cotizacion->id, $this->producto->id);

        expect($this->item1->fresh()->es_elegido)->toBeTrue();
        expect($otroItem->fresh()->es_elegido)->toBeFalse();
    });

    it('no vuelve a marcar elegida_por si ya estaba establecido', function () {
        $this->cotizacion->update([
            'elegida_por' => $this->user->id,
            'elegida_en' => now()->subDay(),
        ]);

        sleep(1);
        app(SeleccionarItemGanador::class)->execute($this->cotizacion->id, $this->producto->id);

        expect($this->item1->fresh()->es_elegido)->toBeTrue();
        $freshCot = $this->cotizacion->fresh();
        expect($freshCot->elegida_por)->toBe($this->user->id);
    });

    it('lanza exception si la cotizacion no existe', function () {
        expect(fn () => app(SeleccionarItemGanador::class)->execute(99999, $this->producto->id))
            ->toThrow(ModelNotFoundException::class);
    });
});

describe('AnalizarScoringCotizaciones', function () {
    it('retorna el ID de la cotizacion con mejor score', function () {
        $cot1 = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 10,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $proveedor2 = Proveedor::factory()->create();
        $cot2 = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor2->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 800,
            'impuestos' => 120,
            'total' => 920,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $ganadoraId = app(AnalizarScoringCotizaciones::class)->execute($this->solicitud);

        expect($ganadoraId)->toBe($cot2->id);
    });

    it('retorna null cuando hay menos de 2 cotizaciones', function () {
        Cotizacion::create([
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

        $resultado = app(AnalizarScoringCotizaciones::class)->execute($this->solicitud);

        expect($resultado)->toBeNull();
    });

    it('retorna null cuando no hay cotizaciones', function () {
        $solicitudSinCotizaciones = Solicitud::create([
            'codigo' => 'S-TEST-002',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->solicitud->departamento_solicitante_id,
            'fecha_solicitud' => now(),
            'fecha_necesita' => now()->addDays(7),
            'motivo' => 'Sin cotizaciones',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        $resultado = app(AnalizarScoringCotizaciones::class)->execute($solicitudSinCotizaciones);

        expect($resultado)->toBeNull();
    });

    it('getDetailedScoring retorna coleccion detallada con scores', function () {
        $cot1 = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 10,
            'subtotal' => 1000,
            'impuestos' => 150,
            'total' => 1150,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $proveedor2 = Proveedor::factory()->create();
        $cot2 = Cotizacion::create([
            'solicitud_id' => $this->solicitud->id,
            'proveedor_id' => $proveedor2->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 5,
            'subtotal' => 800,
            'impuestos' => 120,
            'total' => 920,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $detalles = app(AnalizarScoringCotizaciones::class)->getDetailedScoring($this->solicitud);

        expect($detalles)->toHaveCount(2);
        expect($detalles[0])->toHaveKeys(['cotizacion_id', 'score_precio', 'score_tiempo', 'score_total']);
        expect($detalles[1]['cotizacion_id'])->toBe($cot2->id);
        expect($detalles[1]['score_total'])->toBeGreaterThan($detalles[0]['score_total']);
    });

    it('getDetailedScoring retorna coleccion vacia si no hay cotizaciones', function () {
        $solicitudSinCotizaciones = Solicitud::create([
            'codigo' => 'S-TEST-003',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->solicitud->departamento_solicitante_id,
            'fecha_solicitud' => now(),
            'motivo' => 'Sin cotizaciones',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        $resultado = app(AnalizarScoringCotizaciones::class)->getDetailedScoring($solicitudSinCotizaciones);

        expect($resultado)->toHaveCount(0);
    });
});

describe('ObtenerCotizacionConItemsProveedor', function () {
    it('retorna cotizacion con items y proveedor cargados', function () {
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
        ]);

        $resultado = app(ObtenerCotizacionConItemsProveedor::class)->execute($cotizacion->id);

        expect($resultado)->not->toBeNull();
        expect($resultado->relationLoaded('items'))->toBeTrue();
        expect($resultado->relationLoaded('proveedor'))->toBeTrue();
        expect($resultado->items)->toHaveCount(1);
    });

    it('retorna null cuando la cotizacion no existe', function () {
        $resultado = app(ObtenerCotizacionConItemsProveedor::class)->execute(99999);

        expect($resultado)->toBeNull();
    });
});

describe('ObtenerCotizacionesPorSolicitud', function () {
    it('retorna cotizaciones filtradas por solicitud', function () {
        $cot1 = Cotizacion::create([
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

        $resultado = app(ObtenerCotizacionesPorSolicitud::class)->execute($this->solicitud->id);

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($cot1->id);
    });

    it('retorna todas las cotizaciones cuando solicitudId es null', function () {
        Cotizacion::create([
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

        $solicitud2 = Solicitud::create([
            'codigo' => 'S-TEST-004',
            'colaborador_id' => Colaborador::factory()->create()->id,
            'departamento_solicitante_id' => $this->solicitud->departamento_solicitante_id,
            'fecha_solicitud' => now(),
            'motivo' => 'Otra',
            'estado' => EstadoSolicitud::Pendiente,
        ]);

        Cotizacion::create([
            'solicitud_id' => $solicitud2->id,
            'proveedor_id' => $this->proveedor->id,
            'fecha_cotizacion' => now(),
            'dias_entrega' => 3,
            'subtotal' => 800,
            'impuestos' => 120,
            'total' => 920,
            'moneda_id' => $this->moneda->id,
            'estado' => EstadoCotizacion::Activa,
        ]);

        $resultado = app(ObtenerCotizacionesPorSolicitud::class)->execute(null);

        expect($resultado)->toHaveCount(2);
    });

    it('retorna coleccion vacia cuando no hay cotizaciones para la solicitud', function () {
        $resultado = app(ObtenerCotizacionesPorSolicitud::class)->execute(99999);

        expect($resultado)->toHaveCount(0);
    });

    it('carga la relacion proveedor en los resultados', function () {
        Cotizacion::create([
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

        $resultado = app(ObtenerCotizacionesPorSolicitud::class)->execute($this->solicitud->id);

        expect($resultado->first()->relationLoaded('proveedor'))->toBeTrue();
    });
});
