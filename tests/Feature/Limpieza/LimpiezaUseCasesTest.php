<?php

declare(strict_types=1);

namespace Tests\Feature\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Colaboradores\Colaborador;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock as InventarioStock;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Limpieza\SolicitudLimpieza;
use App\Models\Limpieza\Turno;
use App\Models\Shared\Stock as SharedStock;
use App\Models\User;
use App\UseCases\Limpieza\Mutations\EnviarALavanderia;
use App\UseCases\Limpieza\Mutations\IniciarLimpieza;
use App\UseCases\Limpieza\Mutations\ReabastecerUbicacion;
use App\UseCases\Limpieza\Mutations\TerminarLimpieza;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Support\Facades\DB;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function crearHabitacionLimpieza(int $numero = 401, ?int $categoriaId = null, ?int $ubicacionId = null, EstadoHabitacion $estado = EstadoHabitacion::Activa): Habitacion
{
    return Habitacion::create([
        'codigo' => 'HAB-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT),
        'numero' => $numero,
        'slug' => "habitacion-{$numero}",
        'nombre' => "Habitación {$numero}",
        'categoria_id' => $categoriaId,
        'ubicacion_id' => $ubicacionId,
        'estado' => $estado,
    ]);
}

function crearEspacioLimpieza(int $ubicacionId): Espacio
{
    return Espacio::create([
        'codigo' => 'MESA-LIMP-001',
        'nombre' => 'Mesa Limpieza',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'ubicacion_id' => $ubicacionId,
        'estado' => EstadoEspacio::Disponible,
    ]);
}

function crearSolicitudLimpieza(Habitacion|Espacio $limpiable, EstadoLimpieza $estado = EstadoLimpieza::Pendiente): SolicitudLimpieza
{
    return SolicitudLimpieza::create([
        'limpiable_type' => $limpiable::class,
        'limpiable_id' => $limpiable->id,
        'prioridad' => 'normal',
        'estado' => $estado,
    ]);
}

function crearStockEnBodega(int $varianteId, int $bodegaId, float $cantidad = 50.0): void
{
    $variant = ProductoVariante::find($varianteId);
    $productoId = $variant ? $variant->producto_id : 0;

    // Ensure product exists
    if ($productoId !== 0 && ! DB::table('productos')->where('id', $productoId)->exists()) {
        $cat = Catalogo::first();
        DB::table('productos')->insert([
            'id' => $productoId,
            'categoria_id' => $cat?->id ?? 1,
            'marca_id' => $cat?->id ?? 1,
            'unidad_medida_id' => $cat?->id ?? 1,
            'nombre' => 'Dummy Product For Test',
            'tipo' => 1,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } elseif ($productoId === 0 && ! DB::table('productos')->where('id', 0)->exists()) {
        $cat = Catalogo::first();
        DB::table('productos')->insert([
            'id' => 0,
            'categoria_id' => $cat?->id ?? 1,
            'marca_id' => $cat?->id ?? 1,
            'unidad_medida_id' => $cat?->id ?? 1,
            'nombre' => 'Dummy Reabastecimiento',
            'tipo' => 1,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    InventarioStock::create([
        'producto_id' => $productoId,
        'ubicacion_id' => $bodegaId,
        'producto_variante_id' => $varianteId,
        'cantidad' => $cantidad,
    ]);
}

// ─── Setup ───────────────────────────────────────────────────────────────────

beforeEach(function (): void {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $this->ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();
});

function crearTurno(): Turno
{
    return Turno::create([
        'nombre' => 'Turno Test',
        'lider_id' => Colaborador::factory()->create()->id,
        'hora_inicio' => '07:00:00',
        'hora_fin' => '15:00:00',
    ]);
}

function crearEjecucion(Habitacion|Espacio $limpiable, Turno $turno, EstadoLimpieza $estado = EstadoLimpieza::Pendiente): LimpiezaEjecucion
{
    return LimpiezaEjecucion::create([
        'limpiable_type' => $limpiable::class,
        'limpiable_id' => $limpiable->id,
        'turno_id' => $turno->id,
        'fecha' => now()->toDateString(),
        'estado' => $estado,
    ]);
}

// ─── IniciarLimpieza ─────────────────────────────────────────────────────────

describe('IniciarLimpieza', function () {

    it('inicia limpieza de habitación y cambia estado a En Limpieza', function () {
        $habitacion = crearHabitacionLimpieza(401, $this->categoria->id, $this->ubicacion->id, EstadoHabitacion::Sucia);
        $turno = crearTurno();
        $ejecucion = crearEjecucion($habitacion, $turno, EstadoLimpieza::Pendiente);

        app(IniciarLimpieza::class)->execute($ejecucion, $this->user->id);

        expect($ejecucion->fresh()->estado)->toBe(EstadoLimpieza::EnProgreso)
            ->and($ejecucion->fresh()->colaborador_id)->toBe($this->user->id)
            ->and($habitacion->fresh()->estado)->toBe(EstadoHabitacion::EN_LIMPIEZA);
    });

    it('inicia limpieza de espacio y cambia estado a Limpieza', function () {
        $espacio = crearEspacioLimpieza($this->ubicacion->id);
        $espacio->update(['estado' => EstadoEspacio::Limpieza]);
        $turno = crearTurno();
        $ejecucion = crearEjecucion($espacio, $turno, EstadoLimpieza::Pendiente);

        app(IniciarLimpieza::class)->execute($ejecucion, $this->user->id);

        expect($ejecucion->fresh()->estado)->toBe(EstadoLimpieza::EnProgreso)
            ->and($espacio->fresh()->estado)->toBe(EstadoEspacio::Limpieza);
    });

    it('asigna auth()->id() cuando no se provee personal_id', function () {
        $habitacion = crearHabitacionLimpieza(402, $this->categoria->id, $this->ubicacion->id, EstadoHabitacion::Sucia);
        $turno = crearTurno();
        $ejecucion = crearEjecucion($habitacion, $turno, EstadoLimpieza::Pendiente);

        app(IniciarLimpieza::class)->execute($ejecucion);

        expect($ejecucion->fresh()->colaborador_id)->toBe($this->user->id);
    });
});

// ─── TerminarLimpieza ────────────────────────────────────────────────────────

describe('TerminarLimpieza', function () {

    it('completa limpieza de habitación y cambia estado a Disponible', function () {
        $habitacion = crearHabitacionLimpieza(403, $this->categoria->id, $this->ubicacion->id, EstadoHabitacion::Limpieza);
        $turno = crearTurno();
        $ejecucion = crearEjecucion($habitacion, $turno, EstadoLimpieza::EnProgreso);

        app(TerminarLimpieza::class)->execute($ejecucion);

        expect($ejecucion->fresh()->estado)->toBe(EstadoLimpieza::Completada)
            ->and($habitacion->fresh()->estado)->toBe(EstadoHabitacion::DISPONIBLE);
    });

    it('completa limpieza de espacio y cambia estado a Disponible', function () {
        $espacio = crearEspacioLimpieza($this->ubicacion->id);
        $espacio->update(['estado' => EstadoEspacio::Limpieza]);
        $turno = crearTurno();
        $ejecucion = crearEjecucion($espacio, $turno, EstadoLimpieza::EnProgreso);

        app(TerminarLimpieza::class)->execute($ejecucion);

        expect($ejecucion->fresh()->estado)->toBe(EstadoLimpieza::Completada)
            ->and($espacio->fresh()->estado)->toBe(EstadoEspacio::Disponible);
    });
});

// ─── EnviarALavanderia ───────────────────────────────────────────────────────

describe('EnviarALavanderia', function () {

    beforeEach(function (): void {
        $this->producto = Producto::factory()->create();
        $this->variante = ProductoVariante::create([
            'producto_id' => $this->producto->id,
            'codigo' => 'VAR-LAV-001',
            'nombre_variante' => 'Toalla de baño',
            'precio_compra' => 5.0,
            'precio_venta' => 10.0,
            'estado' => 1,
        ]);
        $this->ubicacionLavanderia = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();
    });

    it('envía items desde habitación a lavandería y descuenta stock', function () {
        $habitacion = crearHabitacionLimpieza(404, $this->categoria->id, $this->ubicacion->id);

        $sharedStock = SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 10.0,
            'cantidad_actual' => 10.0,
        ]);

        app(EnviarALavanderia::class)->execute(
            items: [
                ['stock_id' => $sharedStock->id, 'tipo' => 'habitacion', 'cantidad' => 4.0],
            ],
            ubicacionLavanderiaId: $this->ubicacionLavanderia->id,
            creadoPorId: $this->user->id,
        );

        expect((float) $sharedStock->fresh()->cantidad_actual)->toBe(6.0);

        $movimiento = MovimientoStock::where('tipo', 'TRASLADO_LAVANDERIA')->first();
        expect($movimiento)->not->toBeNull()
            ->and((float) $movimiento->cantidad)->toBe(-4.0);
    });

    it('envía items desde espacio a lavandería', function () {
        $espacio = crearEspacioLimpieza($this->ubicacion->id);

        $sharedStock = SharedStock::create([
            'stockable_type' => Espacio::class,
            'stockable_id' => $espacio->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 20.0,
            'cantidad_actual' => 20.0,
        ]);

        app(EnviarALavanderia::class)->execute(
            items: [
                ['stock_id' => $sharedStock->id, 'tipo' => 'espacio', 'cantidad' => 5.0],
            ],
            ubicacionLavanderiaId: $this->ubicacionLavanderia->id,
        );

        expect((float) $sharedStock->fresh()->cantidad_actual)->toBe(15.0);
    });

    it('lanza InvalidArgumentException si items está vacío', function () {
        expect(fn () => app(EnviarALavanderia::class)->execute(
            items: [],
            ubicacionLavanderiaId: 1,
        ))->toThrow(\InvalidArgumentException::class, 'al menos un item');
    });

    it('lanza InvalidArgumentException si el tipo de stock es inválido', function () {
        expect(fn () => app(EnviarALavanderia::class)->execute(
            items: [['stock_id' => 1, 'tipo' => 'invalido', 'cantidad' => 1.0]],
            ubicacionLavanderiaId: 1,
        ))->toThrow(\InvalidArgumentException::class, 'Tipo de stock inválido');
    });

    it('usa min(cantidad_actual, cantidad_solicitada) cuando la cantidad excede el stock', function () {
        $habitacion = crearHabitacionLimpieza(405, $this->categoria->id, $this->ubicacion->id);

        $sharedStock = SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 3.0,
            'cantidad_actual' => 3.0,
        ]);

        app(EnviarALavanderia::class)->execute(
            items: [
                ['stock_id' => $sharedStock->id, 'tipo' => 'habitacion', 'cantidad' => 100.0],
            ],
            ubicacionLavanderiaId: $this->ubicacionLavanderia->id,
        );

        expect((float) $sharedStock->fresh()->cantidad_actual)->toBe(0.0);
    });

    it('salta items con cantidad_actual igual a cero', function () {
        $habitacion = crearHabitacionLimpieza(406, $this->categoria->id, $this->ubicacion->id);

        $sharedStock = SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 0.0,
            'cantidad_actual' => 0.0,
        ]);

        app(EnviarALavanderia::class)->execute(
            items: [
                ['stock_id' => $sharedStock->id, 'tipo' => 'habitacion', 'cantidad' => 0.0],
            ],
            ubicacionLavanderiaId: $this->ubicacionLavanderia->id,
        );

        expect(MovimientoStock::count())->toBe(0);
    });
});

// ─── ReabastecerUbicacion ────────────────────────────────────────────────────

describe('ReabastecerUbicacion', function () {

    beforeEach(function (): void {
        $this->producto = Producto::factory()->create();
        $this->variante = ProductoVariante::create([
            'producto_id' => $this->producto->id,
            'codigo' => 'VAR-REAB-001',
            'nombre_variante' => 'Jabón líquido',
            'precio_compra' => 3.0,
            'precio_venta' => 6.0,
            'estado' => 1,
        ]);
        $this->bodega = Ubicacion::where('nombre', 'Almacén General')->firstOrFail();
    });

    it('reabastece una habitación desde la bodega', function () {
        $habitacion = crearHabitacionLimpieza(407, $this->categoria->id, $this->ubicacion->id);

        crearStockEnBodega($this->variante->id, $this->bodega->id, 50.0);

        app(ReabastecerUbicacion::class)->execute(
            tipoDestino: 'habitacion',
            destinoId: $habitacion->id,
            items: [
                ['producto_variante_id' => $this->variante->id, 'cantidad' => 10.0],
            ],
            bodegaOrigenId: $this->bodega->id,
            creadoPorId: $this->user->id,
        );

        $shared = SharedStock::where('stockable_type', Habitacion::class)
            ->where('stockable_id', $habitacion->id)
            ->where('producto_variante_id', $this->variante->id)
            ->first();

        expect($shared)->not->toBeNull();
        expect((float) $shared->cantidad_actual)->toBe(10.0);
        expect((float) $shared->cantidad_ideal)->toBe(10.0);
    });

    it('reabastece un espacio desde la bodega', function () {
        $espacio = crearEspacioLimpieza($this->ubicacion->id);

        crearStockEnBodega($this->variante->id, $this->bodega->id, 30.0);

        app(ReabastecerUbicacion::class)->execute(
            tipoDestino: 'espacio',
            destinoId: $espacio->id,
            items: [
                ['producto_variante_id' => $this->variante->id, 'cantidad' => 8.0],
            ],
            bodegaOrigenId: $this->bodega->id,
        );

        $shared = SharedStock::where('stockable_type', Espacio::class)
            ->where('stockable_id', $espacio->id)
            ->where('producto_variante_id', $this->variante->id)
            ->first();

        expect($shared)->not->toBeNull();
        expect((float) $shared->cantidad_actual)->toBe(8.0);
    });

    it('lanza InvalidArgumentException si tipoDestino es inválido', function () {
        expect(fn () => app(ReabastecerUbicacion::class)->execute(
            tipoDestino: 'vehiculo',
            destinoId: 1,
            items: [['producto_variante_id' => 1, 'cantidad' => 5.0]],
            bodegaOrigenId: 1,
        ))->toThrow(\InvalidArgumentException::class, 'Tipo de destino inválido');
    });

    it('acumula cantidad cuando ya existe SharedStock previo', function () {
        $habitacion = crearHabitacionLimpieza(408, $this->categoria->id, $this->ubicacion->id);

        SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 5.0,
            'cantidad_actual' => 5.0,
        ]);

        crearStockEnBodega($this->variante->id, $this->bodega->id, 50.0);

        app(ReabastecerUbicacion::class)->execute(
            tipoDestino: 'habitacion',
            destinoId: $habitacion->id,
            items: [
                ['producto_variante_id' => $this->variante->id, 'cantidad' => 3.0],
            ],
            bodegaOrigenId: $this->bodega->id,
        );

        $shared = SharedStock::where('stockable_type', Habitacion::class)
            ->where('stockable_id', $habitacion->id)
            ->where('producto_variante_id', $this->variante->id)
            ->first();

        expect((float) $shared->cantidad_actual)->toBe(8.0);
        expect((float) $shared->cantidad_ideal)->toBe(5.0);
    });

    it('lanza RuntimeException si no hay stock suficiente en la bodega', function () {
        $habitacion = crearHabitacionLimpieza(409, $this->categoria->id, $this->ubicacion->id);

        expect(fn () => app(ReabastecerUbicacion::class)->execute(
            tipoDestino: 'habitacion',
            destinoId: $habitacion->id,
            items: [
                ['producto_variante_id' => $this->variante->id, 'cantidad' => 99.0],
            ],
            bodegaOrigenId: $this->bodega->id,
        ))->toThrow(\RuntimeException::class, 'Stock insuficiente');
    });
});

describe('IniciarLimpieza y TerminarLimpieza con Carrito y Consumos', function () {
    beforeEach(function (): void {
        $colab1 = Colaborador::factory()->create();
        $colab2 = Colaborador::factory()->create();

        $carritos = Ubicacion::whereIn('tipo', ['almacen', 'bodega', 'zona'])->limit(2)->get();
        $this->carrito1Id = $carritos->first()?->id ?? 1;
        $this->carrito2Id = $carritos->last()?->id ?? 2;

        $this->turno = Turno::create([
            'nombre' => 'Turno Prueba Carrito',
            'lider_id' => $colab1->id,
            'apoyo_id' => null,
            'carritos_ids' => [$this->carrito1Id, $this->carrito2Id],
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
        ]);
        $this->habitacion = crearHabitacionLimpieza(550, $this->categoria->id, $this->ubicacion->id, EstadoHabitacion::Sucia);
        $this->ejecucion = LimpiezaEjecucion::create([
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => $this->habitacion->id,
            'turno_id' => $this->turno->id,
            'colaborador_id' => $colab1->id,
            'fecha' => now()->toDateString(),
            'estado' => EstadoLimpieza::Pendiente,
        ]);

        $this->producto = Producto::factory()->create();

        // Insert product 0 to avoid foreign key violations when using ReabastecerUbicacion
        $cat = Catalogo::first();
        DB::table('productos')->insertOrIgnore([
            'id' => 0,
            'categoria_id' => $cat?->id ?? 1,
            'marca_id' => $cat?->id ?? 1,
            'unidad_medida_id' => $cat?->id ?? 1,
            'nombre' => 'Dummy Product',
            'tipo' => 1,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->variante = ProductoVariante::create([
            'producto_id' => 0, // set variant to refer to product 0 too!
            'codigo' => 'VAR-TEST-CARRITO',
            'nombre_variante' => 'Toalla',
        ]);
        $this->colab1Id = $colab1->id;
        $this->colab2Id = $colab2->id;
    });

    it('inicia limpieza sin carrito cuando no se provee uno', function () {
        app(IniciarLimpieza::class)->execute($this->ejecucion, $this->colab1Id);

        expect($this->ejecucion->fresh()->estado)->toBe(EstadoLimpieza::EnProgreso)
            ->and($this->ejecucion->fresh()->carrito_id)->toBeNull();
    });

    it('inicia correctamente cuando el carrito esta libre y registra consumos al terminar', function () {
        // Prepare cart physical stock (lote + inv_stock)
        $lote = Lote::create([
            'codigo_lote' => 'LOTE-TEST-C',
            'producto_id' => 0,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->carrito1Id,
            'fecha_recepcion' => now()->toDateString(),
        ]);
        InventarioStock::create([
            'producto_id' => 0,
            'lote_id' => $lote->id,
            'ubicacion_id' => $this->carrito1Id, // carrito 1
            'producto_variante_id' => $this->variante->id,
            'cantidad' => 10.0,
        ]);

        // Room stock
        $roomStock = SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 4.0,
            'cantidad_actual' => 1.0,
        ]);

        // Start cleaning
        app(IniciarLimpieza::class)->execute($this->ejecucion, $this->colab1Id, $this->carrito1Id);
        expect($this->ejecucion->fresh()->estado)->toBe(EstadoLimpieza::EnProgreso)
            ->and($this->ejecucion->fresh()->carrito_id)->toBe($this->carrito1Id);

        // Terminate cleaning with 2 towels consumed
        app(TerminarLimpieza::class)->execute($this->ejecucion, [], 'Todo limpio', [
            $this->variante->id => 2.0,
        ]);

        // Physical stock on cart should be reduced from 10 to 8
        $cartStock = InventarioStock::where('ubicacion_id', $this->carrito1Id)
            ->where('producto_variante_id', $this->variante->id)
            ->first();
        expect((float) $cartStock->cantidad)->toBe(8.0);

        // Room stock should increase by 2 (from 1 to 3)
        expect((float) $roomStock->fresh()->cantidad_actual)->toBe(3.0);
    });
});
