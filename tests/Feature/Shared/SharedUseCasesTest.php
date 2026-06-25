<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Monedas\Moneda;
use App\Models\Personas\Persona;
use App\Models\Personas\PersonaJuridica;
use App\Models\Personas\PersonaNatural;
use App\Models\Servicios\Servicio;
use App\Models\Shared\Precio;
use App\Models\Shared\ServicioAsignacion;
use App\UseCases\Shared\Mutations\AsignarPrecio;
use App\UseCases\Shared\Mutations\AsignarServicio;
use App\UseCases\Shared\Queries\ObtenerNombrePersona;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\ServicioSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ─────────────────────────────────────────────────────────────────

function crearHabitacionPrecio(): Habitacion
{
    $categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();

    return Habitacion::create([
        'codigo' => 'HAB-PRECIO-01',
        'numero' => 5001,
        'slug' => 'habitacion-precio',
        'nombre' => 'Habitación Precio',
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);
}

function crearEspacioPrecio(): Espacio
{
    $ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();

    return Espacio::create([
        'codigo' => 'SALON-PRECIO',
        'nombre' => 'Salón de Eventos',
        'tipo' => TipoEspacio::SALON,
        'capacidad_personas' => 100,
        'ubicacion_id' => $ubicacion->id,
        'estado' => EstadoEspacio::Disponible,
    ]);
}

// ─── Setup ───────────────────────────────────────────────────────────────────

beforeEach(function (): void {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
        ServicioSeeder::class,
    ]);

    $this->moneda = Moneda::create([
        'codigo' => 'USD',
        'nombre' => 'Dólar Americano',
        'simbolo' => '$',
        'es_predeterminada' => true,
    ]);
});

// ─── AsignarPrecio ───────────────────────────────────────────────────────────

describe('AsignarPrecio', function () {

    it('asigna un precio a una habitación', function () {
        $habitacion = crearHabitacionPrecio();

        $precio = app(AsignarPrecio::class)->execute(
            priceableType: Habitacion::class,
            priceableId: $habitacion->id,
            monedaId: $this->moneda->id,
            precio: 150.00,
            fechaInicio: now()->toDateString(),
        );

        expect($precio)->toBeInstanceOf(Precio::class)
            ->and($precio->priceable_type)->toBe(Habitacion::class)
            ->and($precio->priceable_id)->toBe($habitacion->id)
            ->and((float) $precio->precio)->toBe(150.00)
            ->and($precio->estado)->toBe(1)
            ->and($precio->es_oferta)->toBeFalse();
    });

    it('asigna un precio a un espacio', function () {
        $espacio = crearEspacioPrecio();

        $precio = app(AsignarPrecio::class)->execute(
            priceableType: Espacio::class,
            priceableId: $espacio->id,
            monedaId: $this->moneda->id,
            precio: 500.00,
            fechaInicio: now()->toDateString(),
        );

        expect($precio->priceable_type)->toBe(Espacio::class)
            ->and($precio->priceable_id)->toBe($espacio->id);
    });

    it('desactiva precio activo anterior al crear uno nuevo del mismo tipo', function () {
        $habitacion = crearHabitacionPrecio();

        app(AsignarPrecio::class)->execute(
            priceableType: Habitacion::class,
            priceableId: $habitacion->id,
            monedaId: $this->moneda->id,
            precio: 100.00,
            fechaInicio: now()->subMonth()->toDateString(),
        );

        $segundo = app(AsignarPrecio::class)->execute(
            priceableType: Habitacion::class,
            priceableId: $habitacion->id,
            monedaId: $this->moneda->id,
            precio: 120.00,
            fechaInicio: now()->toDateString(),
        );

        $precios = Precio::where('priceable_type', Habitacion::class)
            ->where('priceable_id', $habitacion->id)
            ->where('moneda_id', $this->moneda->id)
            ->where('tipo_precio', 'base')
            ->get();

        expect($precios)->toHaveCount(2);

        $anterior = $precios->firstWhere('id', '!=', $segundo->id);
        expect($anterior->estado)->toBe(2);
    });

    it('lanza InvalidArgumentException si el precio es negativo', function () {
        $habitacion = crearHabitacionPrecio();

        expect(fn () => app(AsignarPrecio::class)->execute(
            priceableType: Habitacion::class,
            priceableId: $habitacion->id,
            monedaId: $this->moneda->id,
            precio: -10.00,
            fechaInicio: now()->toDateString(),
        ))->toThrow(\InvalidArgumentException::class, 'no puede ser negativo');
    });

    it('lanza ModelNotFoundException si el modelo priceable no existe', function () {
        expect(fn () => app(AsignarPrecio::class)->execute(
            priceableType: Habitacion::class,
            priceableId: 99999,
            monedaId: $this->moneda->id,
            precio: 100.00,
            fechaInicio: now()->toDateString(),
        ))->toThrow(ModelNotFoundException::class);
    });

    it('crea precio como oferta cuando es_oferta es true sin desactivar precio activo', function () {
        $habitacion = crearHabitacionPrecio();

        app(AsignarPrecio::class)->execute(
            priceableType: Habitacion::class,
            priceableId: $habitacion->id,
            monedaId: $this->moneda->id,
            precio: 100.00,
            fechaInicio: now()->toDateString(),
        );

        $oferta = app(AsignarPrecio::class)->execute(
            priceableType: Habitacion::class,
            priceableId: $habitacion->id,
            monedaId: $this->moneda->id,
            precio: 80.00,
            fechaInicio: now()->toDateString(),
            esOferta: true,
        );

        expect($oferta->es_oferta)->toBeTrue();

        $activos = Precio::where('priceable_type', Habitacion::class)
            ->where('priceable_id', $habitacion->id)
            ->where('estado', 1)
            ->get();
        expect($activos)->toHaveCount(2);
    });
});

// ─── AsignarServicio ─────────────────────────────────────────────────────────

describe('AsignarServicio', function () {

    beforeEach(function (): void {
        $this->servicio = Servicio::create([
            'codigo' => 'SRV-WIFI-001',
            'nombre' => 'WiFi Premium',
            'descripcion' => 'Internet de alta velocidad',
            'categoria_id' => Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail()->id,
            'estado' => 1,
        ]);
    });

    it('asigna un servicio a una habitación', function () {
        $habitacion = crearHabitacionPrecio();

        $asignacion = app(AsignarServicio::class)->execute(
            servicioId: $this->servicio->id,
            serviceableType: Habitacion::class,
            serviceableId: $habitacion->id,
            incluido: true,
        );

        expect($asignacion)->toBeInstanceOf(ServicioAsignacion::class)
            ->and($asignacion->servicio_id)->toBe($this->servicio->id)
            ->and($asignacion->serviceable_type)->toBe(Habitacion::class)
            ->and($asignacion->serviceable_id)->toBe($habitacion->id)
            ->and($asignacion->incluido)->toBeTrue();
    });

    it('asigna un servicio a un espacio', function () {
        $espacio = crearEspacioPrecio();

        $asignacion = app(AsignarServicio::class)->execute(
            servicioId: $this->servicio->id,
            serviceableType: Espacio::class,
            serviceableId: $espacio->id,
            incluido: false,
        );

        expect($asignacion->serviceable_type)->toBe(Espacio::class)
            ->and($asignacion->serviceable_id)->toBe($espacio->id);
    });

    it('actualiza asignación existente si ya existe', function () {
        $habitacion = crearHabitacionPrecio();

        app(AsignarServicio::class)->execute(
            servicioId: $this->servicio->id,
            serviceableType: Habitacion::class,
            serviceableId: $habitacion->id,
            incluido: false,
        );

        app(AsignarServicio::class)->execute(
            servicioId: $this->servicio->id,
            serviceableType: Habitacion::class,
            serviceableId: $habitacion->id,
            incluido: true,
            estado: 1,
        );

        $asignaciones = ServicioAsignacion::where('servicio_id', $this->servicio->id)
            ->where('serviceable_type', Habitacion::class)
            ->where('serviceable_id', $habitacion->id)
            ->get();

        expect($asignaciones)->toHaveCount(1);
        expect($asignaciones->first()->incluido)->toBeTrue();
    });

    it('restaura asignación soft-deleted al re-asignar', function () {
        $habitacion = crearHabitacionPrecio();

        $asignacion = app(AsignarServicio::class)->execute(
            servicioId: $this->servicio->id,
            serviceableType: Habitacion::class,
            serviceableId: $habitacion->id,
        );

        $asignacion->delete();

        expect(ServicioAsignacion::withTrashed()->count())->toBe(1);

        $restaurada = app(AsignarServicio::class)->execute(
            servicioId: $this->servicio->id,
            serviceableType: Habitacion::class,
            serviceableId: $habitacion->id,
            incluido: true,
        );

        expect($restaurada->trashed())->toBeFalse()
            ->and($restaurada->incluido)->toBeTrue();
    });

    it('lanza ModelNotFoundException si el servicio no existe', function () {
        $habitacion = crearHabitacionPrecio();

        expect(fn () => app(AsignarServicio::class)->execute(
            servicioId: 99999,
            serviceableType: Habitacion::class,
            serviceableId: $habitacion->id,
        ))->toThrow(ModelNotFoundException::class);
    });

    it('lanza ModelNotFoundException si el serviceable no existe', function () {
        expect(fn () => app(AsignarServicio::class)->execute(
            servicioId: $this->servicio->id,
            serviceableType: Habitacion::class,
            serviceableId: 99999,
        ))->toThrow(ModelNotFoundException::class);
    });
});

// ─── ObtenerNombrePersona ────────────────────────────────────────────────────

describe('ObtenerNombrePersona', function () {

    it('retorna apellidos de persona natural desde accessor full_name', function () {
        $persona = Persona::factory()->create([
            'tipo_persona' => 'natural',
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
        ]);

        PersonaNatural::create([
            'persona_id' => $persona->id,
            'primer_apellido' => 'Pérez',
            'segundo_apellido' => 'García',
        ]);

        $nombre = app(ObtenerNombrePersona::class)->execute($persona->id);

        expect($nombre)->toContain('Pérez')
            ->and($nombre)->toContain('García');
    });

    it('retorna razon_social de persona jurídica', function () {
        $persona = Persona::factory()->create([
            'tipo_persona' => 'juridica',
        ]);

        PersonaJuridica::create([
            'persona_id' => $persona->id,
            'razon_social' => 'Hotel Bugambilia S.A. de C.V.',
        ]);

        $nombre = app(ObtenerNombrePersona::class)->execute($persona->id);

        expect($nombre)->toBe('Hotel Bugambilia S.A. de C.V.');
    });

    it('retorna "Persona #ID" cuando la persona no existe', function () {
        $nombre = app(ObtenerNombrePersona::class)->execute(99999);

        expect($nombre)->toBe('Persona #99999');
    });

    it('retorna nombre desde tabla personas como fallback cuando no hay registros relacionados', function () {
        $persona = Persona::factory()->create([
            'tipo_persona' => 'natural',
            'primer_nombre' => 'María',
            'segundo_nombre' => null,
        ]);

        $nombre = app(ObtenerNombrePersona::class)->execute($persona->id);

        expect($nombre)->toContain('María');
    });

    it('usa el método ejecutar con instancia de Persona', function () {
        $persona = Persona::factory()->create([
            'tipo_persona' => 'natural',
            'primer_nombre' => 'Ana',
        ]);

        PersonaNatural::create([
            'persona_id' => $persona->id,
            'primer_apellido' => 'López',
        ]);

        $uc = app(ObtenerNombrePersona::class);
        $nombre = $uc->ejecutar($persona);

        expect($nombre)->toContain('López');
    });

    it('usa el método estático desde con instancia de Persona', function () {
        $persona = Persona::factory()->create([
            'tipo_persona' => 'juridica',
        ]);

        PersonaJuridica::create([
            'persona_id' => $persona->id,
            'razon_social' => 'Corporación Turística S.A.',
        ]);

        $nombre = ObtenerNombrePersona::desde($persona);

        expect($nombre)->toBe('Corporación Turística S.A.');
    });
});
