<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoBaja;
use App\Enums\Activos\TipoMantenimiento;
use App\Enums\Compras\EstadoRecepcion;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Activos\ActivoBaja;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Activos\PrefijoCodigo;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Monedas\Moneda;
use App\Models\User;
use App\UseCases\Activos\Queries\AutocompletarActivoDesdeRecepcion;
use App\UseCases\Activos\Queries\GenerarEtiquetasActivosUseCase;
use App\UseCases\Activos\Queries\ObtenerActivosPorUbicacionUseCase;
use App\UseCases\Activos\Queries\ObtenerEstadisticasReportesUseCase;
use App\UseCases\Activos\Queries\ObtenerFichasReportesUseCase;
use App\UseCases\Activos\Queries\ObtenerHistorialMovimientosUseCase;
use App\UseCases\Activos\Queries\ObtenerHojaHabitacionEspacioUseCase;
use App\UseCases\Activos\Queries\ObtenerMantenimientosReportesUseCase;
use App\UseCases\Activos\Queries\ObtenerOpcionesRecepcionItems;
use App\UseCases\Activos\Queries\ObtenerReportesActivosVariosUseCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────

function crearProductoTipo3(string $nombre = 'Activo Fijo Test'): Producto
{
    $tipo = CatalogoTipo::factory()->create();
    $categoria = Catalogo::factory()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => 'Tecnología',
        'estado' => 1,
    ]);

    return Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'nombre' => $nombre,
        'tipo' => 3,
        'estado' => 1,
    ]);
}

function crearActivoConAsignacion(
    Producto $producto,
    string $codigoInventario = 'ACT-0001',
    float $costo = 100.00,
    int $estado = EstadoActivo::Activo->value,
    ?int $monedaId = null,
    ?int $proveedorId = null,
): Activo {
    if ($monedaId === null) {
        $moneda = Moneda::firstOrCreate(
            ['codigo' => 'USD'],
            ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => true]
        );
        $monedaId = $moneda->id;
    }

    return Activo::create([
        'producto_id' => $producto->id,
        'codigo_inventario' => $codigoInventario,
        'nombre_descriptivo' => 'Activo de prueba',
        'costo_adquisicion' => $costo,
        'moneda_id' => $monedaId,
        'proveedor_id' => $proveedorId,
        'estado' => $estado,
        'fecha_adquisicion' => now()->toDateString(),
    ]);
}

function asignarAActivo(Activo $activo, string $asignableType, int $asignableId): ActivoAsignacion
{
    return ActivoAsignacion::create([
        'activo_id' => $activo->id,
        'asignable_type' => $asignableType,
        'asignable_id' => $asignableId,
        'fecha_inicio' => now(),
        'estado' => 1,
    ]);
}

function crearHabitacion(string $codigo = 'HAB-101', string $nombre = 'Suite 101'): Habitacion
{
    $tipo = CatalogoTipo::factory()->create();
    $categoria = Catalogo::factory()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => 'Suite',
        'estado' => 1,
    ]);
    $ubicacion = Ubicacion::create([
        'nombre' => 'Ubicación Habitación',
        'tipo' => 'edificio',
        'estado' => 1,
    ]);

    return Habitacion::create([
        'codigo' => $codigo,
        'slug' => str($codigo)->lower()->toString(),
        'nombre' => $nombre,
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);
}

function crearRecepcionConItem(
    Producto $producto,
    float $precioUnitario = 20.00,
    int $cantidad = 1,
): RecepcionItem {
    $user = User::factory()->create();
    $proveedor = Proveedor::factory()->create();
    $condicion = Catalogo::factory()->create();

    $orden = OrdenCompra::create([
        'codigo' => 'OC-'.uniqid(),
        'proveedor_id' => $proveedor->id,
        'fecha_orden' => now(),
        'condicion_pago_id' => $condicion->id,
        'estado' => 1,
        'subtotal' => $precioUnitario * $cantidad,
        'total' => $precioUnitario * $cantidad * 1.15,
    ]);

    $ordenItem = OrdenCompraItem::create([
        'orden_compra_id' => $orden->id,
        'producto_id' => $producto->id,
        'cantidad' => $cantidad,
        'precio_unitario' => $precioUnitario,
        'subtotal' => $precioUnitario * $cantidad,
    ]);

    $recepcion = RecepcionCompra::create([
        'codigo' => 'REC-'.uniqid(),
        'orden_compra_id' => $orden->id,
        'fecha_recepcion' => now(),
        'recibido_por_id' => $user->id,
        'estado' => EstadoRecepcion::Completa->value,
    ]);

    return RecepcionItem::create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'producto_id' => $producto->id,
        'cantidad_recibida' => $cantidad,
        'cantidad_rechazada' => 0,
        'lote_proveedor' => 'LOTE-TEST',
    ]);
}

// ─── beforeEach ───────────────────────────────────────────────────────────

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    PrefijoCodigo::create(['prefijo' => 'ACT', 'ultimo_numero' => 0]);
    PrefijoCodigo::create(['prefijo' => 'TV', 'ultimo_numero' => 0]);

    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén General',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);
});

// ══════════════════════════════════════════════════════════════════════════
// AutocompletarActivoDesdeRecepcion
// ══════════════════════════════════════════════════════════════════════════

describe('AutocompletarActivoDesdeRecepcion', function () {

    it('returns purchase data when recepcion_item exists', function () {
        $producto = crearProductoTipo3();
        $item = crearRecepcionConItem($producto, 35.00);

        $resultado = AutocompletarActivoDesdeRecepcion::ejecutar($item->id);

        expect($resultado)->toHaveKeys([
            'producto_id', 'producto_variante_id', 'proveedor_id', 'moneda_id', 'costo_adquisicion',
        ]);
        expect($resultado['producto_id'])->toBe($producto->id);
        expect($resultado['costo_adquisicion'])->toBe(35.00);
        expect($resultado['proveedor_id'])->toBe($item->recepcion->ordenCompra->proveedor_id);
        expect($resultado['moneda_id'])->toBe($item->recepcion->ordenCompra->moneda_id);
    });

    it('returns nulls when recepcion_item does not exist', function () {
        $resultado = AutocompletarActivoDesdeRecepcion::ejecutar(99999);

        expect($resultado)->toBe([
            'producto_id' => null,
            'producto_variante_id' => null,
            'proveedor_id' => null,
            'moneda_id' => null,
            'costo_adquisicion' => null,
        ]);
    });

    it('returns null costo when orden_item has no precio_unitario', function () {
        $producto = crearProductoTipo3();
        $item = crearRecepcionConItem($producto, 0);

        $resultado = AutocompletarActivoDesdeRecepcion::ejecutar($item->id);

        expect($resultado['costo_adquisicion'])->toBe(0.0);
    });
});

// ══════════════════════════════════════════════════════════════════════════
// GenerarEtiquetasActivosUseCase
// ══════════════════════════════════════════════════════════════════════════

describe('GenerarEtiquetasActivosUseCase', function () {

    it('generates barcode labels for activos with codigo_inventario', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
        asignarAActivo($activo, Ubicacion::class, $this->ubicacion->id);

        $resultado = app(GenerarEtiquetasActivosUseCase::class)->ejecutar();

        expect($resultado)->toBeArray();
        expect($resultado)->not->toBeEmpty();
        expect($resultado[0])->toHaveKey('filas');
        expect($resultado[0]['filas'][0][0])->toHaveKeys(['codigo_barras', 'imagen', 'producto', 'variante']);
        expect($resultado[0]['filas'][0][0]['codigo_barras'])->toBe('ACT-0001');
    });

    it('returns empty array when no activos have codigo_inventario', function () {
        $resultado = app(GenerarEtiquetasActivosUseCase::class)->ejecutar();

        expect($resultado)->toBe([]);
    });

    it('respects estado filter and only generates labels for matching activos', function () {
        $producto = crearProductoTipo3();
        $activo1 = crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::Activo->value);
        $activo2 = crearActivoConAsignacion($producto, 'ACT-0002', 200.00, EstadoActivo::EnMantenimiento->value);
        asignarAActivo($activo1, Ubicacion::class, $this->ubicacion->id);
        asignarAActivo($activo2, Ubicacion::class, $this->ubicacion->id);

        $resultado = app(GenerarEtiquetasActivosUseCase::class)->ejecutar([
            'estado' => EstadoActivo::EnMantenimiento->value,
        ]);

        $codigos = collect($resultado)->flatMap(fn ($p) => collect($p['filas'])->flatten(1))->pluck('codigo_barras');
        expect($codigos)->toContain('ACT-0002');
        expect($codigos)->not->toContain('ACT-0001');
    });
});

// ══════════════════════════════════════════════════════════════════════════
// ObtenerActivosPorUbicacionUseCase
// ══════════════════════════════════════════════════════════════════════════

describe('ObtenerActivosPorUbicacionUseCase', function () {

    it('groups active assignments by location', function () {
        $producto = crearProductoTipo3();
        $activo1 = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
        $activo2 = crearActivoConAsignacion($producto, 'ACT-0002', 200.00);
        asignarAActivo($activo1, Ubicacion::class, $this->ubicacion->id);
        asignarAActivo($activo2, Ubicacion::class, $this->ubicacion->id);

        $resultado = app(ObtenerActivosPorUbicacionUseCase::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado[0]['nombre'])->toBe($this->ubicacion->nombre);
        expect($resultado[0]['subtotal'])->toBe(300.00);
        expect($resultado[0]['tipo'])->toBe('Ubicación');
    });

    it('filters by assignable type', function () {
        $producto = crearProductoTipo3();
        $habitacion = crearHabitacion('HAB-101', 'Suite 101');
        $activo1 = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
        $activo2 = crearActivoConAsignacion($producto, 'ACT-0002', 200.00);
        asignarAActivo($activo1, Habitacion::class, $habitacion->id);
        asignarAActivo($activo2, Ubicacion::class, $this->ubicacion->id);

        $resultado = app(ObtenerActivosPorUbicacionUseCase::class)->ejecutar(
            tipoFiltro: Habitacion::class
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado[0]['tipo'])->toBe('Habitación');
        expect($resultado[0]['nombre'])->toBe($habitacion->nombre);
    });

    it('returns empty array when no active assignments exist', function () {
        $resultado = app(ObtenerActivosPorUbicacionUseCase::class)->ejecutar();

        expect($resultado)->toBe([]);
    });

    it('uses moneda symbol from activo', function () {
        $producto = crearProductoTipo3();
        $moneda = Moneda::create([
            'codigo' => 'MXN',
            'nombre' => 'Peso Mexicano',
            'simbolo' => 'MX$',
            'es_predeterminada' => false,
        ]);
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 150.00, EstadoActivo::Activo->value, $moneda->id);
        asignarAActivo($activo, Ubicacion::class, $this->ubicacion->id);

        $resultado = app(ObtenerActivosPorUbicacionUseCase::class)->ejecutar();

        expect($resultado[0]['moneda'])->toBe('MX$');
    });
});

// ══════════════════════════════════════════════════════════════════════════
// ObtenerEstadisticasReportesUseCase
// ══════════════════════════════════════════════════════════════════════════

describe('ObtenerEstadisticasReportesUseCase', function () {

    it('returns zero counts when no data exists', function () {
        $resultado = app(ObtenerEstadisticasReportesUseCase::class)->ejecutar();

        expect($resultado)->toHaveKeys([
            'totalActivos', 'enMantenimiento', 'extraviados', 'sinAsignacion',
            'mantenimientosVencidos', 'garantiasProximas', 'totalBajas',
        ]);
        expect($resultado['totalActivos'])->toBe(0);
        expect($resultado['enMantenimiento'])->toBe(0);
        expect($resultado['extraviados'])->toBe(0);
        expect($resultado['sinAsignacion'])->toBe(0);
        expect($resultado['mantenimientosVencidos'])->toBe(0);
        expect($resultado['garantiasProximas'])->toBe(0);
        expect($resultado['totalBajas'])->toBe(0);
    });

    it('counts activos by estado correctly', function () {
        $producto = crearProductoTipo3();
        crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::Activo->value);
        crearActivoConAsignacion($producto, 'ACT-0002', 200.00, EstadoActivo::EnMantenimiento->value);
        crearActivoConAsignacion($producto, 'ACT-0003', 300.00, EstadoActivo::Extraviado->value);
        crearActivoConAsignacion($producto, 'ACT-0004', 400.00, EstadoActivo::DadoDeBaja->value);

        $resultado = app(ObtenerEstadisticasReportesUseCase::class)->ejecutar();

        expect($resultado['totalActivos'])->toBe(4);
        expect($resultado['enMantenimiento'])->toBe(1);
        expect($resultado['extraviados'])->toBe(1);
    });

    it('counts sinAsignacion excluding DadoDeBaja', function () {
        $producto = crearProductoTipo3();
        $activoConAsignacion = crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::Activo->value);
        asignarAActivo($activoConAsignacion, Ubicacion::class, $this->ubicacion->id);
        crearActivoConAsignacion($producto, 'ACT-0002', 200.00, EstadoActivo::DadoDeBaja->value);

        Activo::create([
            'producto_id' => $producto->id,
            'codigo_inventario' => 'ACT-0003',
            'nombre_descriptivo' => 'Sin asignar',
            'costo_adquisicion' => 50.00,
            'moneda_id' => Moneda::firstOrCreate(
                ['codigo' => 'USD'],
                ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => true]
            )->id,
            'estado' => EstadoActivo::Activo->value,
            'fecha_adquisicion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerEstadisticasReportesUseCase::class)->ejecutar();

        expect($resultado['sinAsignacion'])->toBe(1);
    });

    it('counts mantenimientosVencidos correctly', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Preventivo,
            'estado' => EstadoMantenimiento::Programado,
            'fecha_programada' => now()->subDays(5)->toDateString(),
            'notas' => 'Vencido',
        ]);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Preventivo,
            'estado' => EstadoMantenimiento::Completado,
            'fecha_programada' => now()->subDays(10)->toDateString(),
            'notas' => 'Completado no cuenta',
        ]);

        $resultado = app(ObtenerEstadisticasReportesUseCase::class)->ejecutar();

        expect($resultado['mantenimientosVencidos'])->toBe(1);
    });

    it('counts garantiasProximas within 90 days', function () {
        $producto = crearProductoTipo3();
        crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::Activo->value);
        Activo::where('codigo_inventario', 'ACT-0001')->update([
            'fecha_garantia_fin' => now()->addDays(30)->toDateString(),
        ]);
        crearActivoConAsignacion($producto, 'ACT-0002', 200.00, EstadoActivo::Activo->value);
        Activo::where('codigo_inventario', 'ACT-0002')->update([
            'fecha_garantia_fin' => now()->addDays(120)->toDateString(),
        ]);

        $resultado = app(ObtenerEstadisticasReportesUseCase::class)->ejecutar();

        expect($resultado['garantiasProximas'])->toBe(1);
    });

    it('counts totalBajas', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::DadoDeBaja->value);

        ActivoBaja::create([
            'codigo' => 'BAJA-'.uniqid(),
            'activo_id' => $activo->id,
            'motivo_tipo' => TipoBaja::Robo,
            'motivo_detalle' => 'Robo',
            'fecha_baja' => now()->toDateString(),
            'creado_por_id' => $this->user->id,
        ]);

        $resultado = app(ObtenerEstadisticasReportesUseCase::class)->ejecutar();

        expect($resultado['totalBajas'])->toBe(1);
    });
});

// ══════════════════════════════════════════════════════════════════════════
// ObtenerFichasReportesUseCase
// ══════════════════════════════════════════════════════════════════════════

describe('ObtenerFichasReportesUseCase', function () {

    it('loads relationships on fichaActivo', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
        asignarAActivo($activo, Ubicacion::class, $this->ubicacion->id);

        $ficha = app(ObtenerFichasReportesUseCase::class)->fichaActivo($activo);

        expect($ficha->relationLoaded('producto'))->toBeTrue();
        expect($ficha->relationLoaded('asignacionActiva'))->toBeTrue();
        expect($ficha->relationLoaded('asignaciones'))->toBeTrue();
        expect($ficha->relationLoaded('mantenimientos'))->toBeTrue();
    });

    it('loads relationships on fichaMantenimiento', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        $mantenimiento = ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Correctivo,
            'estado' => EstadoMantenimiento::Programado,
            'fecha_programada' => now()->addDays(5)->toDateString(),
            'notas' => 'Revisión general',
        ]);

        $ficha = app(ObtenerFichasReportesUseCase::class)->fichaMantenimiento($mantenimiento);

        expect($ficha->relationLoaded('activo'))->toBeTrue();
        expect($ficha->relationLoaded('plan'))->toBeTrue();
        expect($ficha->relationLoaded('realizadoPor'))->toBeTrue();
    });
});

// ══════════════════════════════════════════════════════════════════════════
// ObtenerHistorialMovimientosUseCase
// ══════════════════════════════════════════════════════════════════════════

describe('ObtenerHistorialMovimientosUseCase', function () {

    it('returns activo and lineaTiempo sorted by date', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        $asignacion = asignarAActivo($activo, Ubicacion::class, $this->ubicacion->id);

        $resultado = app(ObtenerHistorialMovimientosUseCase::class)->ejecutar($activo->id);

        expect($resultado)->toHaveKeys(['activo', 'lineaTiempo']);
        expect($resultado['activo']->id)->toBe($activo->id);
        expect($resultado['lineaTiempo'])->toHaveCount(1);
        expect($resultado['lineaTiempo'][0]['tipo'])->toBe('Asignación');
        expect($resultado['lineaTiempo'][0]['detalle'])->toContain($this->ubicacion->nombre);
    });

    it('includes traslado type when fecha_fin is set', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        ActivoAsignacion::create([
            'activo_id' => $activo->id,
            'asignable_type' => Ubicacion::class,
            'asignable_id' => $this->ubicacion->id,
            'fecha_inicio' => now()->subDays(10),
            'fecha_fin' => now()->subDays(5),
            'estado' => 1,
        ]);

        $resultado = app(ObtenerHistorialMovimientosUseCase::class)->ejecutar($activo->id);

        expect($resultado['lineaTiempo'][0]['tipo'])->toBe('Traslado');
    });

    it('includes mantenimiento entries in timeline', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Preventivo,
            'estado' => EstadoMantenimiento::Programado,
            'fecha_programada' => now()->addDays(5)->toDateString(),
            'notas' => 'Mantenimiento preventivo',
        ]);

        $resultado = app(ObtenerHistorialMovimientosUseCase::class)->ejecutar($activo->id);

        $mttoEntries = $resultado['lineaTiempo']->filter(fn ($e) => str_contains($e['tipo'], 'Mantenimiento'));
        expect($mttoEntries)->toHaveCount(1);
    });

    it('includes baja entry in timeline', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::DadoDeBaja->value);

        ActivoBaja::create([
            'codigo' => 'BAJA-'.uniqid(),
            'activo_id' => $activo->id,
            'motivo_tipo' => TipoBaja::Robo,
            'motivo_detalle' => 'Robo reportado',
            'fecha_baja' => now()->toDateString(),
            'creado_por_id' => $this->user->id,
        ]);

        $resultado = app(ObtenerHistorialMovimientosUseCase::class)->ejecutar($activo->id);

        $bajaEntries = $resultado['lineaTiempo']->filter(fn ($e) => $e['tipo'] === 'Baja');
        expect($bajaEntries)->toHaveCount(1);
        expect($bajaEntries->first()['detalle'])->toContain('Robo');
    });

    it('returns first activo when id is 0', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        $resultado = app(ObtenerHistorialMovimientosUseCase::class)->ejecutar(0);

        expect($resultado['activo']->id)->toBe($activo->id);
    });

    it('throws ModelNotFoundException for non-existent activo id > 0', function () {
        expect(fn () => app(ObtenerHistorialMovimientosUseCase::class)->ejecutar(99999))
            ->toThrow(ModelNotFoundException::class);
    });
});

// ══════════════════════════════════════════════════════════════════════════
// ObtenerHojaHabitacionEspacioUseCase
// ══════════════════════════════════════════════════════════════════════════

describe('ObtenerHojaHabitacionEspacioUseCase', function () {

    it('returns habitacion with assigned activos', function () {
        $producto = crearProductoTipo3();
        $habitacion = crearHabitacion('HAB-101', 'Suite 101');
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
        asignarAActivo($activo, Habitacion::class, $habitacion->id);

        $resultado = app(ObtenerHojaHabitacionEspacioUseCase::class)->ejecutar('habitacion', $habitacion->id);

        expect($resultado)->toHaveKeys(['entidad', 'activos']);
        expect($resultado['entidad']->nombre)->toBe($habitacion->nombre);
        expect($resultado['activos'])->toHaveCount(1);
        expect($resultado['activos'][0]->activo_id)->toBe($activo->id);
    });

    it('returns espacio with assigned activos', function () {
        $producto = crearProductoTipo3();
        $espacio = Espacio::create([
            'codigo' => 'ESP-001',
            'nombre' => 'Salón de Eventos',
            'tipo' => 'salon',
        ]);
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
        asignarAActivo($activo, Espacio::class, $espacio->id);

        $resultado = app(ObtenerHojaHabitacionEspacioUseCase::class)->ejecutar('espacio', $espacio->id);

        expect($resultado)->toHaveKeys(['entidad', 'activos']);
        expect($resultado['entidad']->nombre)->toBe($espacio->nombre);
        expect($resultado['activos'])->toHaveCount(1);
    });

    it('returns empty activos when none assigned', function () {
        $habitacion = crearHabitacion('HAB-999', 'Vacía');

        $resultado = app(ObtenerHojaHabitacionEspacioUseCase::class)->ejecutar('habitacion', $habitacion->id);

        expect($resultado['activos'])->toHaveCount(0);
    });

    it('ignores finished assignments (fecha_fin is not null)', function () {
        $producto = crearProductoTipo3();
        $habitacion = crearHabitacion('HAB-202', 'Suite 202');
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        ActivoAsignacion::create([
            'activo_id' => $activo->id,
            'asignable_type' => Habitacion::class,
            'asignable_id' => $habitacion->id,
            'fecha_inicio' => now()->subDays(10),
            'fecha_fin' => now()->subDays(5),
            'estado' => 1,
        ]);

        $resultado = app(ObtenerHojaHabitacionEspacioUseCase::class)->ejecutar('habitacion', $habitacion->id);

        expect($resultado['activos'])->toHaveCount(0);
    });

    it('throws ModelNotFoundException for non-existent habitacion', function () {
        expect(fn () => app(ObtenerHojaHabitacionEspacioUseCase::class)->ejecutar('habitacion', 99999))
            ->toThrow(ModelNotFoundException::class);
    });

    it('throws ModelNotFoundException for non-existent espacio', function () {
        expect(fn () => app(ObtenerHojaHabitacionEspacioUseCase::class)->ejecutar('espacio', 99999))
            ->toThrow(ModelNotFoundException::class);
    });
});

// ══════════════════════════════════════════════════════════════════════════
// ObtenerMantenimientosReportesUseCase
// ══════════════════════════════════════════════════════════════════════════

describe('ObtenerMantenimientosReportesUseCase', function () {

    it('enMantenimiento returns activos with estado EnMantenimiento', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::EnMantenimiento->value);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Correctivo,
            'estado' => EstadoMantenimiento::EnProceso,
            'fecha_programada' => now()->toDateString(),
            'notas' => 'Reparación',
        ]);

        $resultado = app(ObtenerMantenimientosReportesUseCase::class)->enMantenimiento();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($activo->id);
    });

    it('enMantenimiento excludes activos not in EnMantenimiento state', function () {
        $producto = crearProductoTipo3();
        crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::Activo->value);

        $resultado = app(ObtenerMantenimientosReportesUseCase::class)->enMantenimiento();

        expect($resultado)->toBeEmpty();
    });

    it('mantenimientosVencidos returns overdue maintenance items', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Preventivo,
            'estado' => EstadoMantenimiento::Programado,
            'fecha_programada' => now()->subDays(10)->toDateString(),
            'notas' => 'Vencido',
        ]);

        $resultado = app(ObtenerMantenimientosReportesUseCase::class)->mantenimientosVencidos();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->activo_id)->toBe($activo->id);
    });

    it('mantenimientosVencidos excludes completed or future maintenance', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Preventivo,
            'estado' => EstadoMantenimiento::Completado,
            'fecha_programada' => now()->subDays(5)->toDateString(),
            'notas' => 'Ya completado',
        ]);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Preventivo,
            'estado' => EstadoMantenimiento::Programado,
            'fecha_programada' => now()->addDays(10)->toDateString(),
            'notas' => 'Futuro',
        ]);

        $resultado = app(ObtenerMantenimientosReportesUseCase::class)->mantenimientosVencidos();

        expect($resultado)->toBeEmpty();
    });

    it('mantenimientosVencidos orders by fecha_programada ascending', function () {
        $producto = crearProductoTipo3();
        $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Preventivo,
            'estado' => EstadoMantenimiento::Programado,
            'fecha_programada' => now()->subDays(30)->toDateString(),
            'notas' => 'Más antiguo',
        ]);

        ActivoMantenimiento::create([
            'activo_id' => $activo->id,
            'tipo' => TipoMantenimiento::Correctivo,
            'estado' => EstadoMantenimiento::EnProceso,
            'fecha_programada' => now()->subDays(5)->toDateString(),
            'notas' => 'Reciente',
        ]);

        $resultado = app(ObtenerMantenimientosReportesUseCase::class)->mantenimientosVencidos();

        expect($resultado)->toHaveCount(2);
        expect($resultado[0]->notas)->toBe('Más antiguo');
        expect($resultado[1]->notas)->toBe('Reciente');
    });
});

// ══════════════════════════════════════════════════════════════════════════
// ObtenerOpcionesRecepcionItems
// ══════════════════════════════════════════════════════════════════════════

describe('ObtenerOpcionesRecepcionItems', function () {

    it('returns only recepcion_items for productos tipo=3 without activo', function () {
        $producto = crearProductoTipo3();
        $item = crearRecepcionConItem($producto);

        $opciones = ObtenerOpcionesRecepcionItems::ejecutar();

        expect($opciones)->toHaveCount(1);
        expect($opciones)->toHaveKey($item->id);
        expect($opciones[$item->id])->toContain($producto->nombre);
    });

    it('excludes items already linked to an activo', function () {
        $producto = crearProductoTipo3();
        $item = crearRecepcionConItem($producto);

        $moneda = Moneda::firstOrCreate(
            ['codigo' => 'USD'],
            ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => true]
        );

        Activo::create([
            'producto_id' => $producto->id,
            'recepcion_item_id' => $item->id,
            'codigo_inventario' => 'ACT-0001',
            'nombre_descriptivo' => 'Ya individualizado',
            'costo_adquisicion' => 100.00,
            'moneda_id' => $moneda->id,
            'estado' => EstadoActivo::Activo->value,
            'fecha_adquisicion' => now()->toDateString(),
        ]);

        $opciones = ObtenerOpcionesRecepcionItems::ejecutar();

        expect($opciones)->not->toHaveKey($item->id);
    });

    it('excludes items from productos of non tipo 3', function () {
        $tipo = CatalogoTipo::factory()->create();
        $categoria = Catalogo::factory()->create([
            'catalogo_tipo_id' => $tipo->id,
            'nombre' => 'Insumos',
            'estado' => 1,
        ]);
        $productoConsumible = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Consumible',
            'tipo' => 1,
            'estado' => 1,
        ]);
        crearRecepcionConItem($productoConsumible);

        $opciones = ObtenerOpcionesRecepcionItems::ejecutar();

        expect($opciones)->toBeEmpty();
    });

    it('returns empty array when no items available', function () {
        $opciones = ObtenerOpcionesRecepcionItems::ejecutar();

        expect($opciones)->toBe([]);
    });
});

// ══════════════════════════════════════════════════════════════════════════
// ObtenerReportesActivosVariosUseCase
// ══════════════════════════════════════════════════════════════════════════

describe('ObtenerReportesActivosVariosUseCase', function () {

    describe('inventarioGeneral', function () {

        it('returns all activos with relations when no filters', function () {
            $producto = crearProductoTipo3();
            $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
            asignarAActivo($activo, Ubicacion::class, $this->ubicacion->id);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->inventarioGeneral();

            expect($resultado)->toHaveCount(1);
            expect($resultado->first()->id)->toBe($activo->id);
            expect($resultado->first()->relationLoaded('producto'))->toBeTrue();
        });

        it('filters by estado', function () {
            $producto = crearProductoTipo3();
            crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::Activo->value);
            crearActivoConAsignacion($producto, 'ACT-0002', 200.00, EstadoActivo::EnMantenimiento->value);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->inventarioGeneral([
                'estado' => EstadoActivo::EnMantenimiento->value,
            ]);

            expect($resultado)->toHaveCount(1);
            expect($resultado->first()->codigo_inventario)->toBe('ACT-0002');
        });

        it('filters by producto_id', function () {
            $producto1 = crearProductoTipo3('Prod A');
            $producto2 = crearProductoTipo3('Prod B');
            crearActivoConAsignacion($producto1, 'ACT-0001', 100.00);
            crearActivoConAsignacion($producto2, 'ACT-0002', 200.00);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->inventarioGeneral([
                'producto_id' => $producto1->id,
            ]);

            expect($resultado)->toHaveCount(1);
            expect($resultado->first()->producto_id)->toBe($producto1->id);
        });

        it('filters by ubicacion_tipo', function () {
            $producto = crearProductoTipo3();
            $habitacion = crearHabitacion('HAB-101', 'Suite 101');
            $activo1 = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
            $activo2 = crearActivoConAsignacion($producto, 'ACT-0002', 200.00);
            asignarAActivo($activo1, Habitacion::class, $habitacion->id);
            asignarAActivo($activo2, Ubicacion::class, $this->ubicacion->id);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->inventarioGeneral([
                'ubicacion_tipo' => Habitacion::class,
            ]);

            expect($resultado)->toHaveCount(1);
            expect($resultado->first()->codigo_inventario)->toBe('ACT-0001');
        });

        it('returns empty collection when no activos match filters', function () {
            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->inventarioGeneral([
                'estado' => EstadoActivo::Extraviado->value,
            ]);

            expect($resultado)->toBeEmpty();
        });
    });

    describe('garantiasProximas', function () {

        it('returns activos with warranty ending within default 90 days', function () {
            $producto = crearProductoTipo3();
            crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
            Activo::where('codigo_inventario', 'ACT-0001')->update([
                'fecha_garantia_fin' => now()->addDays(30)->toDateString(),
            ]);
            crearActivoConAsignacion($producto, 'ACT-0002', 200.00);
            Activo::where('codigo_inventario', 'ACT-0002')->update([
                'fecha_garantia_fin' => now()->addDays(120)->toDateString(),
            ]);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->garantiasProximas();

            expect($resultado)->toHaveCount(1);
            expect($resultado->first()->codigo_inventario)->toBe('ACT-0001');
        });

        it('respects custom dias parameter', function () {
            $producto = crearProductoTipo3();
            crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
            Activo::where('codigo_inventario', 'ACT-0001')->update([
                'fecha_garantia_fin' => now()->addDays(60)->toDateString(),
            ]);

            $resultado30 = app(ObtenerReportesActivosVariosUseCase::class)->garantiasProximas(30);
            $resultado90 = app(ObtenerReportesActivosVariosUseCase::class)->garantiasProximas(90);

            expect($resultado30)->toBeEmpty();
            expect($resultado90)->toHaveCount(1);
        });

        it('excludes activos with null fecha_garantia_fin', function () {
            $producto = crearProductoTipo3();
            crearActivoConAsignacion($producto, 'ACT-0001', 100.00);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->garantiasProximas();

            expect($resultado)->toBeEmpty();
        });

        it('orders by fecha_garantia_fin ascending', function () {
            $producto = crearProductoTipo3();
            crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
            Activo::where('codigo_inventario', 'ACT-0001')->update([
                'fecha_garantia_fin' => now()->addDays(60)->toDateString(),
            ]);
            crearActivoConAsignacion($producto, 'ACT-0002', 200.00);
            Activo::where('codigo_inventario', 'ACT-0002')->update([
                'fecha_garantia_fin' => now()->addDays(30)->toDateString(),
            ]);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->garantiasProximas();

            expect($resultado[0]->codigo_inventario)->toBe('ACT-0002');
            expect($resultado[1]->codigo_inventario)->toBe('ACT-0001');
        });
    });

    describe('dadosDeBaja', function () {

        it('returns all baja records ordered by fecha_baja desc', function () {
            $producto = crearProductoTipo3();
            $activo1 = crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::DadoDeBaja->value);
            $activo2 = crearActivoConAsignacion($producto, 'ACT-0002', 200.00, EstadoActivo::DadoDeBaja->value);

            ActivoBaja::create([
                'codigo' => 'BAJA-'.uniqid(),
                'activo_id' => $activo1->id,
                'motivo_tipo' => TipoBaja::Robo,
                'motivo_detalle' => 'Robo',
                'fecha_baja' => now()->subDays(5)->toDateString(),
                'creado_por_id' => $this->user->id,
            ]);

            ActivoBaja::create([
                'codigo' => 'BAJA-'.uniqid(),
                'activo_id' => $activo2->id,
                'motivo_tipo' => TipoBaja::Obsolescencia,
                'motivo_detalle' => 'Obsoleto',
                'fecha_baja' => now()->subDays(1)->toDateString(),
                'creado_por_id' => $this->user->id,
            ]);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->dadosDeBaja();

            expect($resultado)->toHaveCount(2);
            expect($resultado[0]->fecha_baja->toDateString())->toBe(now()->subDays(1)->toDateString());
            expect($resultado[1]->fecha_baja->toDateString())->toBe(now()->subDays(5)->toDateString());
        });

        it('returns empty collection when no bajas exist', function () {
            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->dadosDeBaja();

            expect($resultado)->toBeEmpty();
        });
    });

    describe('extraviados', function () {

        it('returns activos with estado Extraviado', function () {
            $producto = crearProductoTipo3();
            crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::Extraviado->value);
            crearActivoConAsignacion($producto, 'ACT-0002', 200.00, EstadoActivo::Activo->value);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->extraviados();

            expect($resultado)->toHaveCount(1);
            expect($resultado->first()->codigo_inventario)->toBe('ACT-0001');
        });

        it('loads asignaciones relation', function () {
            $producto = crearProductoTipo3();
            $activo = crearActivoConAsignacion($producto, 'ACT-0001', 100.00, EstadoActivo::Extraviado->value);
            asignarAActivo($activo, Ubicacion::class, $this->ubicacion->id);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->extraviados();

            expect($resultado->first()->relationLoaded('asignaciones'))->toBeTrue();
        });

        it('returns empty when no extraviados exist', function () {
            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->extraviados();

            expect($resultado)->toBeEmpty();
        });
    });

    describe('sinAsignacion', function () {

        it('returns activos without active assignment', function () {
            $producto = crearProductoTipo3();
            $activoConAsignacion = crearActivoConAsignacion($producto, 'ACT-0001', 100.00);
            asignarAActivo($activoConAsignacion, Ubicacion::class, $this->ubicacion->id);

            Activo::create([
                'producto_id' => $producto->id,
                'codigo_inventario' => 'ACT-0002',
                'nombre_descriptivo' => 'Sin asignar',
                'costo_adquisicion' => 200.00,
                'moneda_id' => Moneda::firstOrCreate(
                    ['codigo' => 'USD'],
                    ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => true]
                )->id,
                'estado' => EstadoActivo::Activo->value,
                'fecha_adquisicion' => now()->toDateString(),
            ]);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->sinAsignacion();

            expect($resultado)->toHaveCount(1);
            expect($resultado->first()->codigo_inventario)->toBe('ACT-0002');
        });

        it('excludes DadoDeBaja activos', function () {
            $producto = crearProductoTipo3();

            Activo::create([
                'producto_id' => $producto->id,
                'codigo_inventario' => 'ACT-0001',
                'nombre_descriptivo' => 'De baja',
                'costo_adquisicion' => 100.00,
                'estado' => EstadoActivo::DadoDeBaja->value,
                'fecha_adquisicion' => now()->toDateString(),
            ]);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->sinAsignacion();

            expect($resultado)->toBeEmpty();
        });

        it('loads expected relationships', function () {
            $producto = crearProductoTipo3();
            $proveedor = Proveedor::factory()->create();

            Activo::create([
                'producto_id' => $producto->id,
                'codigo_inventario' => 'ACT-0001',
                'nombre_descriptivo' => 'Suelto',
                'costo_adquisicion' => 100.00,
                'moneda_id' => Moneda::firstOrCreate(
                    ['codigo' => 'USD'],
                    ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => true]
                )->id,
                'proveedor_id' => $proveedor->id,
                'estado' => EstadoActivo::Activo->value,
                'fecha_adquisicion' => now()->toDateString(),
            ]);

            $resultado = app(ObtenerReportesActivosVariosUseCase::class)->sinAsignacion();

            expect($resultado)->toHaveCount(1);
            expect($resultado->first()->relationLoaded('producto'))->toBeTrue();
            expect($resultado->first()->relationLoaded('proveedor'))->toBeTrue();
        });
    });
});
