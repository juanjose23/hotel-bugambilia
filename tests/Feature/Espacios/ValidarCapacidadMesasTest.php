<?php

declare(strict_types=1);

// tests/Feature/Espacios/ValidarCapacidadMesasTest.php

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\UseCases\Espacios\Mutations\ValidarCapacidadMesas;
use App\UseCases\Espacios\Queries\ConsultarCapacidadMesas;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ────────────────────────────────────────────────────────────────

/**
 * Crea un restaurante con la capacidad de mesas configurada en meta_datos.
 *
 * @param  int|null  $capacidadMesas  null = sin límite configurado
 */
function crearRestaurante(?int $capacidadMesas = null, ?int $ubicacionId = null): Espacio
{
    return Espacio::create([
        'codigo' => 'REST-'.uniqid(),
        'nombre' => 'Restaurante Bugambilias',
        'tipo' => TipoEspacio::RESTAURANTE,
        'capacidad_personas' => 80,
        'estado' => EstadoEspacio::Disponible,
        'ubicacion_id' => $ubicacionId,
        'meta_datos' => $capacidadMesas !== null
            ? ['capacidad_mesas' => $capacidadMesas]
            : null,
    ]);
}

/**
 * Crea una mesa hija bajo un restaurante dado.
 */
function crearMesa(Espacio $restaurante, string $sufijo = ''): Espacio
{
    return Espacio::create([
        'padre_id' => $restaurante->id,
        'codigo' => 'MESA-'.uniqid().$sufijo,
        'nombre' => 'Mesa '.$sufijo,
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Disponible,
    ]);
}

// ─── Setup ───────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);

    $this->ubicacion = Ubicacion::where('nombre', 'Planta Baja')->firstOrFail();
});

// ─── ConsultarCapacidadMesas ─────────────────────────────────────────────────

describe('ConsultarCapacidadMesas', function () {

    it('retorna sin límite cuando el restaurante no tiene capacidad_mesas configurada', function () {
        $restaurante = crearRestaurante(capacidadMesas: null, ubicacionId: $this->ubicacion->id);

        $resultado = app(ConsultarCapacidadMesas::class)->execute($restaurante->id);

        expect($resultado['capacidad_configurada'])->toBeNull()
            ->and($resultado['mesas_activas'])->toBe(0)
            ->and($resultado['mesas_disponibles'])->toBeNull()
            ->and($resultado['puede_agregar'])->toBeTrue();
    });

    it('cuenta correctamente las mesas activas dentro del restaurante', function () {
        $restaurante = crearRestaurante(capacidadMesas: 5, ubicacionId: $this->ubicacion->id);

        crearMesa($restaurante, 'A');
        crearMesa($restaurante, 'B');
        crearMesa($restaurante, 'C');

        $resultado = app(ConsultarCapacidadMesas::class)->execute($restaurante->id);

        expect($resultado['mesas_activas'])->toBe(3)
            ->and($resultado['capacidad_configurada'])->toBe(5)
            ->and($resultado['mesas_disponibles'])->toBe(2)
            ->and($resultado['puede_agregar'])->toBeTrue();
    });

    it('reporta puede_agregar=false cuando se alcanza el límite exacto', function () {
        $restaurante = crearRestaurante(capacidadMesas: 2, ubicacionId: $this->ubicacion->id);

        crearMesa($restaurante, 'X');
        crearMesa($restaurante, 'Y');

        $resultado = app(ConsultarCapacidadMesas::class)->execute($restaurante->id);

        expect($resultado['puede_agregar'])->toBeFalse()
            ->and($resultado['mesas_disponibles'])->toBe(0)
            ->and($resultado['mesas_activas'])->toBe(2);
    });

    it('no cuenta mesas con soft-delete en el total de activas', function () {
        $restaurante = crearRestaurante(capacidadMesas: 3, ubicacionId: $this->ubicacion->id);

        $mesa1 = crearMesa($restaurante, 'Z1');
        crearMesa($restaurante, 'Z2');
        $mesa1->delete(); // Soft-delete

        $resultado = app(ConsultarCapacidadMesas::class)->execute($restaurante->id);

        expect($resultado['mesas_activas'])->toBe(1)
            ->and($resultado['mesas_disponibles'])->toBe(2);
    });

    it('no cuenta sub-espacios de tipo distinto a MESA', function () {
        $restaurante = crearRestaurante(capacidadMesas: 5, ubicacionId: $this->ubicacion->id);

        // Sub-espacio tipo OTRO (barra, almacén, etc.)
        Espacio::create([
            'padre_id' => $restaurante->id,
            'codigo' => 'BARRA-001',
            'nombre' => 'Barra de Tragos',
            'tipo' => TipoEspacio::OTRO,
            'estado' => EstadoEspacio::Disponible,
        ]);

        $resultado = app(ConsultarCapacidadMesas::class)->execute($restaurante->id);

        // La barra NO debe contarse como mesa
        expect($resultado['mesas_activas'])->toBe(0)
            ->and($resultado['puede_agregar'])->toBeTrue();
    });

    it('lanza InvalidArgumentException si el espacio no es de tipo RESTAURANTE', function () {
        $salon = Espacio::create([
            'codigo' => 'SALON-001',
            'nombre' => 'Salón de Eventos',
            'tipo' => TipoEspacio::SALON,
        ]);

        expect(fn () => app(ConsultarCapacidadMesas::class)->execute($salon->id))
            ->toThrow(InvalidArgumentException::class, 'no es de tipo RESTAURANTE');
    });

    it('incluye el nombre y el id del restaurante en el resultado', function () {
        $restaurante = crearRestaurante(capacidadMesas: 10, ubicacionId: $this->ubicacion->id);

        $resultado = app(ConsultarCapacidadMesas::class)->execute($restaurante->id);

        expect($resultado['restaurante_id'])->toBe($restaurante->id)
            ->and($resultado['restaurante_nombre'])->toBe('Restaurante Bugambilias');
    });

    it('devuelve null en capacidad_configurada si meta_datos.capacidad_mesas tiene valor no numérico', function () {
        $restaurante = Espacio::create([
            'codigo' => 'REST-INVALID',
            'nombre' => 'Restaurante Roto',
            'tipo' => TipoEspacio::RESTAURANTE,
            'meta_datos' => ['capacidad_mesas' => 'sin_limite'],
        ]);

        $resultado = app(ConsultarCapacidadMesas::class)->execute($restaurante->id);

        expect($resultado['capacidad_configurada'])->toBeNull()
            ->and($resultado['puede_agregar'])->toBeTrue();
    });
});

// ─── ValidarCapacidadMesas ───────────────────────────────────────────────────

describe('ValidarCapacidadMesas', function () {

    it('pasa la validación cuando hay espacio disponible (modo solo validación)', function () {
        $restaurante = crearRestaurante(capacidadMesas: 10, ubicacionId: $this->ubicacion->id);
        crearMesa($restaurante, '1');

        // No debe lanzar excepción
        $resultado = app(ValidarCapacidadMesas::class)->execute(
            restauranteId: $restaurante->id,
            crearSiValida: false,
        );

        expect($resultado)->toBeNull(); // Solo validación, no crea
    });

    it('crea la mesa cuando hay capacidad disponible y crearSiValida=true', function () {
        $restaurante = crearRestaurante(capacidadMesas: 5, ubicacionId: $this->ubicacion->id);

        $mesa = app(ValidarCapacidadMesas::class)->execute(
            restauranteId: $restaurante->id,
            crearSiValida: true,
            datosMesa: [
                'codigo' => 'MESA-NW-001',
                'nombre' => 'Mesa Nueva 1',
                'capacidad_personas' => 6,
                'estado' => EstadoEspacio::Disponible->value,
            ],
        );

        expect($mesa)->toBeInstanceOf(Espacio::class)
            ->and($mesa->tipo)->toBe(TipoEspacio::MESA)
            ->and($mesa->padre_id)->toBe($restaurante->id)
            ->and($mesa->nombre)->toBe('Mesa Nueva 1');
    });

    it('lanza OverflowException cuando la capacidad máxima ha sido alcanzada', function () {
        $restaurante = crearRestaurante(capacidadMesas: 2, ubicacionId: $this->ubicacion->id);
        crearMesa($restaurante, 'A');
        crearMesa($restaurante, 'B');

        expect(fn () => app(ValidarCapacidadMesas::class)->execute(
            restauranteId: $restaurante->id,
        ))->toThrow(OverflowException::class, 'ha alcanzado su capacidad máxima');
    });

    it('el mensaje del OverflowException incluye el nombre del restaurante y los números reales', function () {
        $restaurante = crearRestaurante(capacidadMesas: 3, ubicacionId: $this->ubicacion->id);
        crearMesa($restaurante, 'A');
        crearMesa($restaurante, 'B');
        crearMesa($restaurante, 'C');

        $excepcion = null;

        try {
            app(ValidarCapacidadMesas::class)->execute(restauranteId: $restaurante->id);
        } catch (OverflowException $e) {
            $excepcion = $e;
        }

        expect($excepcion)->not->toBeNull()
            ->and($excepcion->getMessage())->toContain('Restaurante Bugambilias')
            ->and($excepcion->getMessage())->toContain('3 mesas')
            ->and($excepcion->getMessage())->toContain('3 mesas registradas');
    });

    it('lanza InvalidArgumentException si el padre no es RESTAURANTE', function () {
        $gym = Espacio::create([
            'codigo' => 'GYM-001',
            'nombre' => 'Gimnasio',
            'tipo' => TipoEspacio::GYM,
        ]);

        expect(fn () => app(ValidarCapacidadMesas::class)->execute(
            restauranteId: $gym->id,
        ))->toThrow(InvalidArgumentException::class);
    });

    it('lanza InvalidArgumentException si se intenta crear un sub-espacio que no es MESA', function () {
        $restaurante = crearRestaurante(capacidadMesas: 10, ubicacionId: $this->ubicacion->id);

        expect(fn () => app(ValidarCapacidadMesas::class)->execute(
            restauranteId: $restaurante->id,
            crearSiValida: true,
            datosMesa: [
                'codigo' => 'SALON-ERR-001',
                'nombre' => 'Salón dentro del restaurante',
                'tipo' => TipoEspacio::SALON->value,
            ],
        ))->toThrow(InvalidArgumentException::class, 'debe ser MESA');
    });

    it('permite crear mesas sin límite cuando capacidad_mesas no está configurada', function () {
        $restaurante = crearRestaurante(capacidadMesas: null, ubicacionId: $this->ubicacion->id);

        // Crear 50 mesas sin límite — no debe lanzar excepción
        for ($i = 1; $i <= 50; $i++) {
            app(ValidarCapacidadMesas::class)->execute(
                restauranteId: $restaurante->id,
                crearSiValida: true,
                datosMesa: [
                    'codigo' => "MESA-ILIM-{$i}",
                    'nombre' => "Mesa Ilimitada {$i}",
                    'capacidad_personas' => 4,
                ],
            );
        }

        expect($restaurante->hijos()->count())->toBe(50);
    });

    it('fuerza el tipo MESA y el padre_id aunque no se pasen en datosMesa', function () {
        $restaurante = crearRestaurante(capacidadMesas: 5, ubicacionId: $this->ubicacion->id);

        $mesa = app(ValidarCapacidadMesas::class)->execute(
            restauranteId: $restaurante->id,
            crearSiValida: true,
            datosMesa: [
                'codigo' => 'MESA-FORZADA-01',
                'nombre' => 'Mesa Forzada',
                // Intencionalmente omitimos tipo y padre_id
            ],
        );

        expect($mesa->tipo)->toBe(TipoEspacio::MESA)
            ->and($mesa->padre_id)->toBe($restaurante->id);
    });

    it('restaurantes de distintos restaurantes no comparten el contador de capacidad', function () {
        $rest1 = crearRestaurante(capacidadMesas: 1, ubicacionId: $this->ubicacion->id);
        $rest2 = crearRestaurante(capacidadMesas: 1, ubicacionId: $this->ubicacion->id);

        // Llenar rest1
        crearMesa($rest1, 'R1-A');

        // rest1 debe bloquear
        expect(fn () => app(ValidarCapacidadMesas::class)->execute(restauranteId: $rest1->id))
            ->toThrow(OverflowException::class);

        // rest2 aún tiene cupo
        $mesa = app(ValidarCapacidadMesas::class)->execute(
            restauranteId: $rest2->id,
            crearSiValida: true,
            datosMesa: ['codigo' => 'MESA-R2-A', 'nombre' => 'Mesa R2-A'],
        );

        expect($mesa)->toBeInstanceOf(Espacio::class);
    });
});
