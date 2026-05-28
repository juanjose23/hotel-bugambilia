<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoIndividualizacion;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\EstadoPlanMantenimiento;
use App\Enums\Activos\TipoBaja;
use App\Enums\Activos\TipoMantenimiento;
use App\Enums\Activos\TipoPlanMantenimiento;
use App\Enums\Compras\EstadoRecepcion;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Activos\Activo;
use App\Models\Activos\ActPlanMantenimiento;
use App\Models\Activos\PrefijoCodigo;
use App\Models\Activos\RegistroIndividualizacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\Stock;
use App\Models\User;
use App\UseCases\Activos\Mutations\Asignacion\AsignarActivo;
use App\UseCases\Activos\Mutations\Gestion\DarDeBajaActivo;
use App\UseCases\Activos\Mutations\Gestion\IndividualizarActivos;
use App\UseCases\Activos\Mutations\Gestion\RegistrarActivoFijo;
use App\UseCases\Activos\Mutations\Mantenimiento\CompletarMantenimiento;
use App\UseCases\Activos\Mutations\Mantenimiento\EnviarAMantenimiento;
use App\UseCases\Inventario\Recepciones\Mutations\RegistrarEntradaRecepcion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed initial dependencies
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $tipo = CatalogoTipo::factory()->create();
    $this->categoria = Catalogo::factory()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => 'Tecnología',
        'estado' => 1,
    ]);

    // Create a generic active location (Almacén General)
    $this->ubicacionAlmacen = Ubicacion::create([
        'nombre' => 'Almacén General',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);

    // Seed prefijos
    PrefijoCodigo::create(['prefijo' => 'TV', 'ultimo_numero' => 0]);
    PrefijoCodigo::create(['prefijo' => 'ACT', 'ultimo_numero' => 0]);
});

function createValidRecepcionItem(Producto $producto, int $cantidad, ?string $loteProveedor = null): RecepcionItem
{
    $proveedor = Proveedor::factory()->create();
    $condicion = Catalogo::factory()->create();

    $orden = OrdenCompra::create([
        'codigo' => 'OC-'.uniqid(),
        'proveedor_id' => $proveedor->id,
        'fecha_orden' => now(),
        'condicion_pago_id' => $condicion->id,
        'estado' => 1,
        'subtotal' => 100,
        'total' => 115,
    ]);

    $ordenItem = $orden->items()->create([
        'producto_id' => $producto->id,
        'cantidad' => $cantidad,
        'precio_unitario' => 20,
        'subtotal' => 100,
    ]);

    $recepcion = RecepcionCompra::create([
        'codigo' => 'REC-'.uniqid(),
        'orden_compra_id' => $orden->id,
        'fecha_recepcion' => now(),
        'recibido_por_id' => auth()->id() ?? 1,
        'estado' => EstadoRecepcion::Completa->value,
    ]);

    return RecepcionItem::create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'producto_id' => $producto->id,
        'cantidad_recibida' => $cantidad,
        'cantidad_rechazada' => 0,
        'lote_proveedor' => $loteProveedor ?: 'LOTE-TEST',
    ]);
}

it('registra automaticamente los activos fijos al recepcionar un producto tipo 3', function () {
    // 1. Create a product of type 3 (Activo Fijo)
    $activoProducto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Televisor Samsung 55',
        'tipo' => 3, // Activo Fijo
        'estado' => 1,
    ]);

    // Mock a fake recepcion_item
    $recepcionItem = createValidRecepcionItem($activoProducto, 5, 'SERIE-XYZ');

    $items = [
        [
            'id' => $recepcionItem->id,
            'producto_id' => $activoProducto->id,
            'producto_variante_id' => null,
            'cantidad_recibida' => 5.0,
            'cantidad_rechazada' => 0.0,
            'lote_proveedor' => 'SERIE-XYZ',
            'fecha_vencimiento' => null,
        ],
    ];

    // Run RegistrarEntradaRecepcion Use Case
    app(RegistrarEntradaRecepcion::class)->execute(
        nuevoEstado: 'Completa',
        items: $items,
        proveedorId: null,
        creadoPorId: $this->user->id
    );

    // 2. Assert no inv_lote was created for this product
    $this->assertDatabaseMissing('inv_lotes', [
        'producto_id' => $activoProducto->id,
    ]);

    // 3. Assert inv_registro_individualizacion was created and completed automatically
    $this->assertDatabaseHas('inv_registro_individualizacion', [
        'recepcion_item_id' => $recepcionItem->id,
        'producto_id' => $activoProducto->id,
        'cantidad_total' => 5,
        'cantidad_registrada' => 5,
        'estado' => EstadoIndividualizacion::Completado->value,
    ]);

    $registro = RegistroIndividualizacion::where('recepcion_item_id', $recepcionItem->id)->first();
    expect($registro)->not->toBeNull();

    $activos = Activo::where('individualizacion_id', $registro->id)->get();
    expect($activos)->toHaveCount(5);
});

it('successfully individualizes assets generating sequential codes, stock count and movements', function () {
    $activoProducto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Televisor LG 43',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $recepcionItem = createValidRecepcionItem($activoProducto, 3);

    $registro = RegistroIndividualizacion::create([
        'recepcion_item_id' => $recepcionItem->id,
        'producto_id' => $activoProducto->id,
        'cantidad_total' => 3,
        'cantidad_registrada' => 0,
        'estado' => EstadoIndividualizacion::Pendiente,
    ]);

    $unidades = [
        ['numero_serie' => 'LG-SER-001', 'nombre_descriptivo' => 'TV Suite 101', 'notas' => 'Impecable'],
        ['numero_serie' => 'LG-SER-002', 'nombre_descriptivo' => 'TV Suite 102', 'notas' => 'Pequeño raspón caja'],
        ['numero_serie' => 'LG-SER-003', 'nombre_descriptivo' => 'TV Lobby', 'notas' => 'Impecable'],
    ];

    // Execute Use Case
    app(IndividualizarActivos::class)->execute(
        registroId: $registro->id,
        items: $unidades,
        userId: $this->user->id
    );

    // 1. Verify RegistroIndividualizacion is completed
    $registro->refresh();
    expect($registro->estado)->toBe(EstadoIndividualizacion::Completado);
    expect($registro->cantidad_registrada)->toBe(3);

    // 2. Verify 3 Activo records were created with sequential TV-YYYY-000X codes
    $activos = Activo::where('producto_id', $activoProducto->id)->get();
    expect($activos)->toHaveCount(3);
    expect($activos[0]->codigo_inventario)->toBe('TV-'.now()->format('Y').'-0001');
    expect($activos[1]->codigo_inventario)->toBe('TV-'.now()->format('Y').'-0002');
    expect($activos[2]->codigo_inventario)->toBe('TV-'.now()->format('Y').'-0003');

    // 3. Verify Active Assignments pointing to Almacén General
    foreach ($activos as $activo) {
        $this->assertDatabaseHas('inv_activo_asignaciones', [
            'activo_id' => $activo->id,
            'asignable_type' => Ubicacion::class,
            'asignable_id' => $this->ubicacionAlmacen->id,
            'fecha_fin' => null,
            'estado' => EstadoAsignacion::Vigente->value,
        ]);
    }

    // 4. Verify Stock Count is increased by 3 in Almacén General
    $stock = Stock::where([
        'producto_id' => $activoProducto->id,
        'ubicacion_id' => $this->ubicacionAlmacen->id,
    ])->first();
    expect($stock->cantidad)->toBe(3.0);

    // 5. Verify movement logs
    $this->assertDatabaseHas('inv_movimientos', [
        'producto_id' => $activoProducto->id,
        'cantidad' => 1.0,
        'tipo' => 'MOV_ENTRADA',
        'documento_tipo' => 'inv_activos',
    ]);
});

it('can assign and transfer assets physically to a room', function () {
    $activoProducto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Televisor Samsung 55',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $recepcionItem = createValidRecepcionItem($activoProducto, 1);

    $registro = RegistroIndividualizacion::create([
        'recepcion_item_id' => $recepcionItem->id,
        'producto_id' => $activoProducto->id,
        'cantidad_total' => 1,
        'cantidad_registrada' => 0,
        'estado' => EstadoIndividualizacion::Pendiente,
    ]);

    app(IndividualizarActivos::class)->execute(
        registroId: $registro->id,
        items: [['numero_serie' => 'SAM-111', 'nombre_descriptivo' => 'TV Test', 'notas' => '']],
        userId: $this->user->id
    );

    $activo = Activo::where('producto_id', $activoProducto->id)->firstOrFail();

    // Create a Room (Habitacion)
    $habitacion = Habitacion::create([
        'codigo' => 'HAB-101',
        'nombre' => 'Suite Nupcial 101',
        'estado' => EstadoHabitacion::Activa,
        'categoria_id' => Catalogo::factory()->create()->id,
        'ubicacion_id' => $this->ubicacionAlmacen->id,
    ]);

    // Assign asset to room
    app(AsignarActivo::class)->execute(
        activoId: $activo->id,
        asignableType: Habitacion::class,
        asignableId: $habitacion->id,
        userId: $this->user->id,
        motivo: 'Colocación en habitación'
    );

    // 1. Verify previous assignment is closed
    $this->assertDatabaseHas('inv_activo_asignaciones', [
        'activo_id' => $activo->id,
        'asignable_type' => Ubicacion::class,
        'asignable_id' => $this->ubicacionAlmacen->id,
        'estado' => EstadoAsignacion::Cerrada->value,
    ]);

    // 2. Verify new active assignment is open for the room
    $this->assertDatabaseHas('inv_activo_asignaciones', [
        'activo_id' => $activo->id,
        'asignable_type' => Habitacion::class,
        'asignable_id' => $habitacion->id,
        'fecha_fin' => null,
        'estado' => EstadoAsignacion::Vigente->value,
    ]);
});

it('can send asset to technical maintenance and repair', function () {
    $activoProducto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Aire Acondicionado Split',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $recepcionItem = createValidRecepcionItem($activoProducto, 1);

    $registro = RegistroIndividualizacion::create([
        'recepcion_item_id' => $recepcionItem->id,
        'producto_id' => $activoProducto->id,
        'cantidad_total' => 1,
        'cantidad_registrada' => 0,
        'estado' => EstadoIndividualizacion::Pendiente,
    ]);

    app(IndividualizarActivos::class)->execute(
        registroId: $registro->id,
        items: [['numero_serie' => 'AC-SPLIT-99', 'nombre_descriptivo' => 'AC Suite', 'notas' => '']],
        userId: $this->user->id
    );

    $activo = Activo::where('producto_id', $activoProducto->id)->firstOrFail();

    // Create a special maintenance/workshop location
    $taller = Ubicacion::create([
        'nombre' => 'Taller de Mantenimiento',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);

    $proveedor = Proveedor::factory()->create();

    // Send split AC to maintenance
    app(EnviarAMantenimiento::class)->execute(
        activoId: $activo->id,
        tipo: TipoMantenimiento::Correctivo,
        descripcion: 'Compresor quemado no enciende',
        userId: $this->user->id,
        costo: 120.00,
        monedaId: null,
        proveedorId: $proveedor->id
    );

    // 1. Verify asset status is EnMantenimiento
    $activo->refresh();
    expect($activo->estado)->toBe(EstadoActivo::EnMantenimiento);

    // 2. Verify new active assignment is pointing to workshop (Taller)
    $this->assertDatabaseHas('inv_activo_asignaciones', [
        'activo_id' => $activo->id,
        'asignable_type' => Ubicacion::class,
        'asignable_id' => $taller->id,
        'fecha_fin' => null,
        'estado' => EstadoAsignacion::Vigente->value,
    ]);

    // 3. Verify maintenance ticket was registered in process
    $this->assertDatabaseHas('inv_mantenimientos', [
        'activo_id' => $activo->id,
        'estado' => EstadoMantenimiento::EnProceso->value,
    ]);
});

it('can retire and decommission assets cleanly, updating stock count and bitacora logs', function () {
    $activoProducto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Proyector Epson',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $recepcionItem = createValidRecepcionItem($activoProducto, 1);

    $registro = RegistroIndividualizacion::create([
        'recepcion_item_id' => $recepcionItem->id,
        'producto_id' => $activoProducto->id,
        'cantidad_total' => 1,
        'cantidad_registrada' => 0,
        'estado' => EstadoIndividualizacion::Pendiente,
    ]);

    app(IndividualizarActivos::class)->execute(
        registroId: $registro->id,
        items: [['numero_serie' => 'EPSON-1234', 'nombre_descriptivo' => 'Proyector Eventos', 'notas' => '']],
        userId: $this->user->id
    );

    $activo = Activo::where('producto_id', $activoProducto->id)->firstOrFail();

    // Verify stock is 1
    $stock = Stock::where([
        'producto_id' => $activoProducto->id,
        'ubicacion_id' => $this->ubicacionAlmacen->id,
    ])->first();
    expect($stock->cantidad)->toBe(1.0);

    // Decommission the asset (Dar de baja)
    app(DarDeBajaActivo::class)->execute(
        activoId: $activo->id,
        motivoTipo: TipoBaja::Robo,
        motivoDetalle: 'Robo reportado de sala de conferencias',
        userId: $this->user->id,
        valorResidual: 0.00,
        aprobadoPorId: null
    );

    // 1. Verify asset is DadoDeBaja
    $activo->refresh();
    expect($activo->estado)->toBe(EstadoActivo::DadoDeBaja);

    // 2. Verify previous assignment is closed
    $this->assertDatabaseHas('inv_activo_asignaciones', [
        'activo_id' => $activo->id,
        'asignable_type' => Ubicacion::class,
        'asignable_id' => $this->ubicacionAlmacen->id,
        'estado' => EstadoAsignacion::Cerrada->value,
    ]);

    // 3. Verify Baja register was created
    $this->assertDatabaseHas('inv_activo_bajas', [
        'activo_id' => $activo->id,
        'motivo_tipo' => TipoBaja::Robo->value,
    ]);

    // 4. Verify Stock Count is decremented to 0
    $stock->refresh();
    expect($stock->cantidad)->toBe(0.0);

    // 5. Verify movements bitacora output log
    $this->assertDatabaseHas('inv_movimientos', [
        'producto_id' => $activoProducto->id,
        'cantidad' => 1.0,
        'tipo' => 'MOV_SALIDA',
        'documento_tipo' => 'inv_activo_bajas',
    ]);
});

it('can register a fixed asset manually or from a purchase and extracts purchase costs correctly', function () {
    $activoProducto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Televisor Panasonic 50',
        'tipo' => 3,
        'estado' => 1,
    ]);

    // Test 1: Manual Registration
    $activoManual = app(RegistrarActivoFijo::class)->execute(
        recepcionItemId: null,
        productoId: $activoProducto->id,
        productoVarianteId: null,
        nombreDescriptivo: 'TV Manual',
        numeroSerie: 'PANA-MAN-001',
        costoAdquisicion: 350.00,
        monedaId: null,
        proveedorId: null,
        fechaAdquisicion: now()->toDateString(),
        userId: $this->user->id,
        asignacionType: Ubicacion::class,
        asignableId: $this->ubicacionAlmacen->id,
        asignacionMotivo: 'Registro manual inicial'
    );

    expect($activoManual->codigo_inventario)->toBe('TV-'.now()->format('Y').'-0001');
    expect((float) $activoManual->costo_adquisicion)->toBe(350.00);
    $this->assertDatabaseHas('inv_activo_asignaciones', [
        'activo_id' => $activoManual->id,
        'asignable_type' => Ubicacion::class,
        'asignable_id' => $this->ubicacionAlmacen->id,
        'estado' => EstadoAsignacion::Vigente->value,
    ]);

    // Test 2: Purchase-associated Registration
    $recepcionItem = createValidRecepcionItem($activoProducto, 2); // 2 units received, unit price is 20 in createValidRecepcionItem

    $activoCompra = app(RegistrarActivoFijo::class)->execute(
        recepcionItemId: $recepcionItem->id,
        productoId: $activoProducto->id,
        productoVarianteId: null,
        nombreDescriptivo: 'TV Compra',
        numeroSerie: 'PANA-COM-002',
        costoAdquisicion: null, // Should be auto-extracted from Purchase (20.00)
        monedaId: null, // Should be auto-extracted
        proveedorId: null, // Should be auto-extracted
        fechaAdquisicion: now()->toDateString(),
        userId: $this->user->id,
        asignacionType: Ubicacion::class,
        asignableId: $this->ubicacionAlmacen->id,
        asignacionMotivo: 'Registro desde compra'
    );

    expect($activoCompra->codigo_inventario)->toBe('TV-'.now()->format('Y').'-0002');
    expect((float) $activoCompra->costo_adquisicion)->toBe(20.00); // 20.00 is defined in createValidRecepcionItem
    expect($activoCompra->proveedor_id)->toBe($recepcionItem->recepcion->ordenCompra->proveedor_id);

    // Verify individualization bridge state is updated to EnProceso (since quantity_total is 2 and we only registered 1)
    $registro = RegistroIndividualizacion::where('recepcion_item_id', $recepcionItem->id)->firstOrFail();
    expect($registro->estado)->toBe(EstadoIndividualizacion::EnProceso);
    expect($registro->cantidad_registrada)->toBe(1);
});

it('propaga fecha_ultimo y fecha_proximo en el plan al completar un mantenimiento', function () {
    $activoProducto = Producto::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Televisor Plan',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $recepcionItem = createValidRecepcionItem($activoProducto, 1);
    $registro = RegistroIndividualizacion::create([
        'recepcion_item_id' => $recepcionItem->id,
        'producto_id' => $activoProducto->id,
        'cantidad_total' => 1,
        'cantidad_registrada' => 0,
        'estado' => EstadoIndividualizacion::Pendiente,
    ]);

    app(IndividualizarActivos::class)->execute(
        registroId: $registro->id,
        items: [['numero_serie' => 'PLAN-TV-01', 'nombre_descriptivo' => 'TV Plan Test', 'notas' => '']],
        userId: $this->user->id
    );

    $activo = Activo::where('producto_id', $activoProducto->id)->firstOrFail();

    $plan = ActPlanMantenimiento::create([
        'nombre' => 'Plan Preven TV',
        'tipo' => TipoPlanMantenimiento::Preventivo,
        'frecuencia_dias' => 90,
        'fecha_inicio' => now()->subDays(30)->toDateString(),
        'estado' => EstadoPlanMantenimiento::Activo,
    ]);

    $plan->activos()->attach($activo->id);

    $taller = Ubicacion::create([
        'nombre' => 'Taller Test',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);

    $proveedor = Proveedor::factory()->create();

    app(EnviarAMantenimiento::class)->execute(
        activoId: $activo->id,
        tipo: TipoMantenimiento::Correctivo,
        descripcion: 'Fallo en pantalla',
        userId: $this->user->id,
        costo: 200.00,
        monedaId: null,
        proveedorId: $proveedor->id,
    );

    $mantenimiento = $activo->mantenimientos()->where('estado', EstadoMantenimiento::EnProceso)->firstOrFail();
    $mantenimiento->plan_id = $plan->id;
    $mantenimiento->save();

    $fechaRealizada = now()->toDateString();

    app(CompletarMantenimiento::class)->execute(
        mantenimiento: $mantenimiento,
        fechaRealizada: $fechaRealizada,
        costoReal: 180.00,
        notas: 'Reparación exitosa',
        usuarioId: $this->user->id
    );

    $plan->refresh();
    expect($plan->fecha_ultimo_mantenimiento->toDateString())->toBe($fechaRealizada);
    expect($plan->fecha_proximo_mantenimiento->toDateString())->toBe(
        now()->addDays($plan->frecuencia_dias)->toDateString()
    );
});
