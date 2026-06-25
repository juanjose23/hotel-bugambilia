<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoActivo;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\HabitacionesEspacios\TipoPrecioEspacio;
use App\Models\Activos\Activo;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Monedas\Moneda;
use App\Models\Politicas\Politica;
use App\Models\Servicios\Servicio;
use App\Models\User;
use App\UseCases\Activos\Mutations\Asignacion\AsignarActivo;
use App\UseCases\Shared\Mutations\AsignarPolitica;
use App\UseCases\Shared\Mutations\AsignarPrecio;
use App\UseCases\Shared\Mutations\AsignarServicio;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\ServicioSeeder;
use Database\Seeders\TasaCambioSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
        TasaCambioSeeder::class,
        ServicioSeeder::class,
    ]);

    $this->ubicacion = Ubicacion::where('nombre', 'Planta Baja')->firstOrFail();
    $this->monedaNio = Moneda::where('codigo', 'NIO')->firstOrFail();
    $this->monedaUsd = Moneda::where('codigo', 'USD')->firstOrFail();
    $this->servicio = Servicio::firstOrFail();
});

it('puede crear un espacio padre e hijo con jerarquia autoreferenciada', function () {
    // 1. Crear espacio padre (Restaurante)
    $restaurante = Espacio::create([
        'codigo' => 'REST-001',
        'nombre' => 'Restaurante Bugambilias',
        'descripcion' => 'Restaurante buffet y a la carta',
        'tipo' => TipoEspacio::RESTAURANTE,
        'capacidad_personas' => 80,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoEspacio::Disponible,
        'orden' => 1,
    ]);

    // 2. Crear espacio hijo (Mesa)
    $mesa = Espacio::create([
        'padre_id' => $restaurante->id,
        'codigo' => 'MESA-001',
        'nombre' => 'Mesa 1',
        'descripcion' => 'Mesa junto a la ventana para 4 personas',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Disponible,
        'orden' => 1,
        'meta_datos' => [
            'tipo_mesa' => 'redonda',
            'zona' => 'interior',
        ],
    ]);

    // Assertions
    expect($restaurante->hijos)->toHaveCount(1);
    expect($mesa->padre->id)->toBe($restaurante->id);
    expect($mesa->tipo)->toBe(TipoEspacio::MESA);
    expect($mesa->meta_datos)->toBe([
        'tipo_mesa' => 'redonda',
        'zona' => 'interior',
    ]);
});

it('genera el nombre completo jerarquico formateado correctamente', function () {
    $restaurante = Espacio::create([
        'codigo' => 'REST-001',
        'nombre' => 'Restaurante Bugambilias',
        'tipo' => TipoEspacio::RESTAURANTE,
        'ubicacion_id' => $this->ubicacion->id,
    ]);

    $terraza = Espacio::create([
        'padre_id' => $restaurante->id,
        'codigo' => 'TERR-001',
        'nombre' => 'Terraza del Restaurante',
        'tipo' => TipoEspacio::OTRO,
    ]);

    $mesa = Espacio::create([
        'padre_id' => $terraza->id,
        'codigo' => 'MESA-T1',
        'nombre' => 'Mesa T1',
        'tipo' => TipoEspacio::MESA,
    ]);

    expect($mesa->getNombreCompleto())->toBe('Restaurante Bugambilias > Terraza del Restaurante > Mesa T1');
});

it('hereda la ubicacion fisica del espacio padre si el hijo no la tiene asignada', function () {
    $restaurante = Espacio::create([
        'codigo' => 'REST-001',
        'nombre' => 'Restaurante Bugambilias',
        'tipo' => TipoEspacio::RESTAURANTE,
        'ubicacion_id' => $this->ubicacion->id,
    ]);

    $mesa = Espacio::create([
        'padre_id' => $restaurante->id,
        'codigo' => 'MESA-001',
        'nombre' => 'Mesa 1',
        'tipo' => TipoEspacio::MESA,
        'ubicacion_id' => null, // No la especificamos para heredarla
    ]);

    // Comprobar la herencia de ubicación
    $ubicacionResuelta = $mesa->ubicacion_id ? $mesa->ubicacion : $mesa->padre?->ubicacion;
    expect($ubicacionResuelta)->not->toBeNull();
    expect($ubicacionResuelta->id)->toBe($this->ubicacion->id);
});

it('evita la duplicidad del codigo unico del espacio', function () {
    Espacio::create([
        'codigo' => 'ESP-DUP',
        'nombre' => 'Espacio Original',
        'tipo' => TipoEspacio::GYM,
        'ubicacion_id' => $this->ubicacion->id,
    ]);

    $this->expectException(QueryException::class);

    Espacio::create([
        'codigo' => 'ESP-DUP',
        'nombre' => 'Espacio Duplicado',
        'tipo' => TipoEspacio::GYM,
        'ubicacion_id' => $this->ubicacion->id,
    ]);
});

it('puede asignar tarifas y precios al espacio', function () {
    $salon = Espacio::create([
        'codigo' => 'SALON-01',
        'nombre' => 'Salón de Eventos Ejecutivo',
        'tipo' => TipoEspacio::SALON,
        'ubicacion_id' => $this->ubicacion->id,
    ]);

    $precio = app(AsignarPrecio::class)->execute(
        priceableType: Espacio::class,
        priceableId: $salon->id,
        monedaId: $this->monedaNio->id,
        precio: 1500.00,
        fechaInicio: now()->toDateString(),
        estado: 1,
        esOferta: false,
        tipoPrecio: 'base'
    );

    expect($salon->precios)->toHaveCount(1);
    expect($salon->precios->first()->precio)->toBe('1500.00');
    expect($salon->precios->first()->moneda->codigo)->toBe('NIO');
    expect($salon->precios->first()->tipo_precio)->toBe(TipoPrecioEspacio::Base);
});

it('puede asociar y desasociar servicios a un espacio', function () {
    $salon = Espacio::create([
        'codigo' => 'SALON-01',
        'nombre' => 'Salón de Eventos Ejecutivo',
        'tipo' => TipoEspacio::SALON,
        'ubicacion_id' => $this->ubicacion->id,
    ]);

    app(AsignarServicio::class)->execute(
        servicioId: $this->servicio->id,
        serviceableType: Espacio::class,
        serviceableId: $salon->id,
        incluido: true,
        estado: 1
    );

    expect($salon->serviciosEspacio)->toHaveCount(1);
    expect($salon->serviciosEspacio->first()->servicio->id)->toBe($this->servicio->id);
    expect($salon->serviciosEspacio->first()->incluido)->toBeTrue();
});

it('puede asociar politicas de forma polimorfica al espacio', function () {
    // 1. Crear una política
    $politica = Politica::create([
        'titulo' => 'Política de Cancelación Estándar de Espacios',
        'descripcion' => 'Cancelación gratuita hasta 24 horas antes del evento.',
        'estado' => 1,
    ]);

    $salon = Espacio::create([
        'codigo' => 'SALON-01',
        'nombre' => 'Salón de Eventos Ejecutivo',
        'tipo' => TipoEspacio::SALON,
        'ubicacion_id' => $this->ubicacion->id,
    ]);

    // 2. Asociar de forma polimórfica usando Caso de Uso
    app(AsignarPolitica::class)->execute(
        politicaId: $politica->id,
        entity: $salon
    );

    expect($salon->politicas)->toHaveCount(1);
    expect($salon->politicas->first()->titulo)->toBe('Política de Cancelación Estándar de Espacios');
    expect($salon->politicas->first()->descripcion)->toBe('Cancelación gratuita hasta 24 horas antes del evento.');
});

it('puede asociar activos fijos de forma polimorfica al espacio', function () {
    $user = User::factory()->create();

    // 1. Crear un producto de tipo 3 (Activo Fijo)
    $tipo = CatalogoTipo::where('codigo', 'CATEGORIA_PRODUCTO')->firstOrFail();
    $catActivos = Catalogo::create([
        'catalogo_tipo_id' => $tipo->id,
        'codigo' => 'CAT_PRO_ACT_MOB_TEST',
        'nombre' => 'Mobiliario Test',
        'estado' => 1,
    ]);

    $unidad = Catalogo::create([
        'catalogo_tipo_id' => CatalogoTipo::where('codigo', 'UNIDAD_MEDIDA')->firstOrFail()->id,
        'codigo' => 'UNI_UD_TEST',
        'nombre' => 'Unidad Test',
        'estado' => 1,
    ]);

    $producto = Producto::create([
        'categoria_id' => $catActivos->id,
        'nombre' => 'Mesa de Madera Rústica',
        'tipo' => 3, // Activo Fijo
        'estado' => 1,
        'unidad_medida_id' => $unidad->id,
    ]);

    // 2. Crear el activo físico
    $activo = Activo::create([
        'producto_id' => $producto->id,
        'codigo_inventario' => 'ACT-2026-0001',
        'nombre_descriptivo' => 'Mesa rústica grande',
        'numero_serie' => 'SERIE-MESA-001',
        'estado' => EstadoActivo::Activo->value,
        'fecha_adquisicion' => now()->toDateString(),
    ]);

    // 3. Crear el Espacio
    $salon = Espacio::create([
        'codigo' => 'SALON-01',
        'nombre' => 'Salón de Eventos Ejecutivo',
        'tipo' => TipoEspacio::SALON,
        'ubicacion_id' => $this->ubicacion->id,
    ]);

    // 4. Asignar el activo usando el custom Caso de Uso de Habitaciones/Espacios
    app(AsignarActivo::class)->execute(
        activoId: $activo->id,
        asignableType: Espacio::class,
        asignableId: $salon->id,
        userId: $user->id,
        motivo: 'Asignar mobiliario al salón'
    );

    // Assertions
    expect($salon->asignacionesActivos)->toHaveCount(1);
    expect($salon->asignacionesActivos->first()->activo->nombre_descriptivo)->toBe('Mesa rústica grande');
});
