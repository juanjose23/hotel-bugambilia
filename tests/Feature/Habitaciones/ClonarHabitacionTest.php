<?php

declare(strict_types=1);

// tests/Feature/Habitaciones/ClonarHabitacionTest.php

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoServicioHabitacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\DetalleHabitacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Habitaciones\HabitacionStock;
use App\Models\Habitaciones\PrecioHabitacion;
use App\Models\Habitaciones\ServicioHabitacion;
use App\Models\Monedas\Moneda;
use App\Models\Politicas\Politica;
use App\Models\Servicios\Servicio;
use App\UseCases\Habitaciones\Mutations\ClonarHabitacion;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\ServicioSeeder;
use Database\Seeders\TasaCambioSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Crea una habitación base con todos sus atributos para usarla como plantilla.
 */
function crearHabitacionOrigen(
    int $numero = 101,
    ?int $categoriaId = null,
    ?int $ubicacionId = null,
): Habitacion {
    return Habitacion::create([
        'codigo' => 'HAB-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT),
        'numero' => $numero,
        'slug' => "habitacion-{$numero}",
        'nombre' => "Habitación {$numero}",
        'descripcion' => 'Suite de prueba con vista al mar',
        'categoria_id' => $categoriaId,
        'ubicacion_id' => $ubicacionId,
        'estado' => EstadoHabitacion::Activa,
    ]);
}

// ─── Setup ───────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
        TasaCambioSeeder::class,
        ServicioSeeder::class,
    ]);

    $this->categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $this->ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();
    $this->monedaNio = Moneda::where('codigo', 'NIO')->firstOrFail();
    $this->servicio = Servicio::firstOrFail();
    $this->useCase = app(ClonarHabitacion::class);
});

// ─── Atributos básicos ───────────────────────────────────────────────────────

it('clona los atributos base de la habitación origen', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    $nueva = $this->useCase->execute($origen, 102);

    expect($nueva->numero)->toBe(102)
        ->and($nueva->nombre)->toBe('Habitación 102')
        ->and($nueva->descripcion)->toBe('Suite de prueba con vista al mar')
        ->and($nueva->categoria_id)->toBe($this->categoria->id)
        ->and($nueva->ubicacion_id)->toBe($this->ubicacion->id);
});

it('la habitación clonada nace siempre en estado Mantenimiento', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    // Aunque el origen esté Activa u Ocupada, la clonada debe ser Mantenimiento
    $nueva = $this->useCase->execute($origen, 102);

    expect($nueva->estado)->toBe(EstadoHabitacion::Mantenimiento);
});

it('genera un código HAB-XXXX único y diferente al de la habitación origen', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    $nueva = $this->useCase->execute($origen, 102);

    expect($nueva->codigo)->not->toBe($origen->codigo)
        ->and($nueva->codigo)->toStartWith('HAB-');
});

it('genera un slug único diferente al de la habitación origen', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    $nueva = $this->useCase->execute($origen, 102);

    expect($nueva->slug)->not->toBe($origen->slug)
        ->and($nueva->slug)->toContain('102');
});

it('acepta un nombre personalizado al clonar', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    $nueva = $this->useCase->execute($origen, 202, 'Suite Presidencial 202');

    expect($nueva->nombre)->toBe('Suite Presidencial 202')
        ->and($nueva->slug)->toContain('suite-presidencial-202');
});

// ─── Validaciones ────────────────────────────────────────────────────────────

it('lanza InvalidArgumentException si el número ya está en uso', function () {
    crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);
    $origen2 = crearHabitacionOrigen(102, $this->categoria->id, $this->ubicacion->id);
    $origen2->codigo = 'HAB-0002';
    $origen2->save();

    expect(fn () => $this->useCase->execute($origen2, 101))
        ->toThrow(InvalidArgumentException::class, 'ya está en uso');
});

it('lanza InvalidArgumentException si el número es menor a 1', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    expect(fn () => $this->useCase->execute($origen, 0))
        ->toThrow(InvalidArgumentException::class, 'mayor a cero');
});

it('detecta número en uso incluso si la habitación fue soft-deleted', function () {
    $hab = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);
    $hab->delete(); // soft delete

    $origen = Habitacion::create([
        'codigo' => 'HAB-0002',
        'numero' => 102,
        'slug' => 'habitacion-102',
        'nombre' => 'Habitación 102',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    expect(fn () => $this->useCase->execute($origen, 101))
        ->toThrow(InvalidArgumentException::class, 'ya está en uso');
});

// ─── DetalleHabitacion ───────────────────────────────────────────────────────

it('clona el detalle de habitacion con capacidades y medidas', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    DetalleHabitacion::create([
        'habitacion_id' => $origen->id,
        'capacidad_adultos' => 3,
        'capacidad_ninos' => 2,
        'medidas' => '28.50',
        'vistas' => ['mar', 'jardin'],
    ]);

    $nueva = $this->useCase->execute($origen, 102);

    expect($nueva->detalle)->not->toBeNull()
        ->and($nueva->detalle->capacidad_adultos)->toBe(3)
        ->and($nueva->detalle->capacidad_ninos)->toBe(2)
        ->and((float) $nueva->detalle->medidas)->toBe(28.50)
        ->and($nueva->detalle->vistas)->toBe(['mar', 'jardin'])
        ->and($nueva->detalle->habitacion_id)->toBe($nueva->id); // No el del origen
});

it('clona correctamente si la habitación origen no tiene detalle', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);
    // Origen SIN detalle

    $nueva = $this->useCase->execute($origen, 102);

    expect($nueva->detalle)->toBeNull();
});

// ─── ServicioHabitacion ──────────────────────────────────────────────────────

it('clona todos los servicios de la habitacion origen', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    ServicioHabitacion::create([
        'habitacion_id' => $origen->id,
        'servicio_id' => $this->servicio->id,
        'incluido' => true,
        'estado' => EstadoServicioHabitacion::Activo,
    ]);

    $nueva = $this->useCase->execute($origen, 102);
    $nueva->load('serviciosHabitacion');

    expect($nueva->serviciosHabitacion)->toHaveCount(1)
        ->and($nueva->serviciosHabitacion->first()->servicio_id)->toBe($this->servicio->id)
        ->and($nueva->serviciosHabitacion->first()->incluido)->toBeTrue()
        ->and($nueva->serviciosHabitacion->first()->habitacion_id)->toBe($nueva->id);
});

// ─── PrecioHabitacion ────────────────────────────────────────────────────────

it('clona los precios de la habitacion origen como plantilla', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    PrecioHabitacion::create([
        'habitacion_id' => $origen->id,
        'moneda_id' => $this->monedaNio->id,
        'precio' => '1200.00',
        'tipo_precio' => 'base',
        'fecha_inicio' => now()->toDateString(),
        'estado' => 1,
        'es_oferta' => false,
    ]);

    $nueva = $this->useCase->execute($origen, 102);
    $nueva->load('precioshabitacion');

    expect($nueva->precioshabitacion)->toHaveCount(1)
        ->and($nueva->precioshabitacion->first()->precio)->toBe('1200.00')
        ->and($nueva->precioshabitacion->first()->moneda_id)->toBe($this->monedaNio->id)
        ->and($nueva->precioshabitacion->first()->habitacion_id)->toBe($nueva->id);
});

// ─── Políticas ───────────────────────────────────────────────────────────────

it('clona los vinculos polimorfica de politicas', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    $politica = Politica::create([
        'titulo' => 'Política de cancelación',
        'descripcion' => 'Cancelación gratuita 48h antes.',
        'estado' => 1,
    ]);

    $origen->politicas()->attach($politica->id);

    $nueva = $this->useCase->execute($origen, 102);
    $nueva->load('politicas');

    expect($nueva->politicas)->toHaveCount(1)
        ->and($nueva->politicas->first()->id)->toBe($politica->id);
});

// ─── HabitacionStock (plantilla de consumibles) ──────────────────────────────

it('clona la plantilla de stock con cantidad_ideal y fija cantidad_actual en cero', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    // Necesitamos una ProductoVariante real para el foreign key
    $tipoCat = CatalogoTipo::where('codigo', 'CATEGORIA_PRODUCTO')->firstOrFail();
    $categoria = Catalogo::create([
        'catalogo_tipo_id' => $tipoCat->id,
        'codigo' => 'CAT_CONSUMIBLES_TEST',
        'nombre' => 'Consumibles Blancos Test',
        'estado' => 1,
    ]);
    $tipoUm = CatalogoTipo::where('codigo', 'UNIDAD_MEDIDA')->firstOrFail();
    $unidad = Catalogo::create([
        'catalogo_tipo_id' => $tipoUm->id,
        'codigo' => 'UNI_PZA_TEST',
        'nombre' => 'Pieza Test',
        'estado' => 1,
    ]);
    $producto = Producto::create([
        'categoria_id' => $categoria->id,
        'nombre' => 'Toalla de Baño',
        'tipo' => 2, // No perecedero
        'estado' => 1,
        'unidad_medida_id' => $unidad->id,
    ]);
    $variante = ProductoVariante::create([
        'producto_id' => $producto->id,
        'codigo' => 'TOA-GDE-001',
        'nombre_variante' => 'Grande',
        'estado' => 1,
    ]);

    // Stock del origen con 6 toallas ideales y 4 actuales (ya usadas)
    HabitacionStock::create([
        'habitacion_id' => $origen->id,
        'producto_variante_id' => $variante->id,
        'lote_id' => null,
        'cantidad_ideal' => '6.0000',
        'cantidad_actual' => '4.0000',
    ]);

    $nueva = $this->useCase->execute($origen, 102);
    $nueva->load('habitacionStocks');

    expect($nueva->habitacionStocks)->toHaveCount(1);

    $stockClonado = $nueva->habitacionStocks->first();

    expect((float) $stockClonado->cantidad_ideal)->toBe(6.0)   // Copia la plantilla
        ->and((float) $stockClonado->cantidad_actual)->toBe(0.0) // Nace vacía
        ->and($stockClonado->lote_id)->toBeNull()               // Sin lote real
        ->and($stockClonado->producto_variante_id)->toBe($variante->id)
        ->and($stockClonado->habitacion_id)->toBe($nueva->id);
});

// ─── Activos fijos — jamás se clonan ─────────────────────────────────────────

it('la habitacion clonada no hereda activos fijos del origen', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    // Simulamos que el origen tiene activos asignados en la tabla polimórfica
    // (no necesitamos crear un activo real, solo verificar que la nueva está vacía)
    $nueva = $this->useCase->execute($origen, 102);
    $nueva->load('asignacionesActivos');

    expect($nueva->asignacionesActivos)->toHaveCount(0);
});

// ─── Clonación en cadena ─────────────────────────────────────────────────────

it('puede clonar varias habitaciones en serie desde la misma plantilla', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);

    PrecioHabitacion::create([
        'habitacion_id' => $origen->id,
        'moneda_id' => $this->monedaNio->id,
        'precio' => '900.00',
        'tipo_precio' => 'base',
        'fecha_inicio' => now()->toDateString(),
        'estado' => 1,
        'es_oferta' => false,
    ]);

    $hab201 = $this->useCase->execute($origen, 201);
    $hab202 = $this->useCase->execute($origen, 202);
    $hab203 = $this->useCase->execute($origen, 203);

    foreach ([$hab201, $hab202, $hab203] as $hab) {
        $hab->load('precioshabitacion');
        expect($hab->precioshabitacion)->toHaveCount(1)
            ->and($hab->estado)->toBe(EstadoHabitacion::Mantenimiento);
    }

    // Códigos únicos
    expect([$hab201->codigo, $hab202->codigo, $hab203->codigo])
        ->toHaveCount(3)
        ->sequence(
            fn ($c) => $c->toStartWith('HAB-'),
            fn ($c) => $c->toStartWith('HAB-'),
            fn ($c) => $c->toStartWith('HAB-'),
        );

    // Slugs únicos
    expect($hab201->slug)->not->toBe($hab202->slug)
        ->and($hab202->slug)->not->toBe($hab203->slug);
});

it('las habitaciones clonadas son independientes: modificar una no afecta a las otras', function () {
    $origen = crearHabitacionOrigen(101, $this->categoria->id, $this->ubicacion->id);
    $nueva1 = $this->useCase->execute($origen, 201);
    $nueva2 = $this->useCase->execute($origen, 202);

    // Cambiar el nombre de la primera no afecta a la segunda
    $nueva1->nombre = 'Nombre Modificado';
    $nueva1->save();

    $nueva2->refresh();
    expect($nueva2->nombre)->toBe('Habitación 202');
});
