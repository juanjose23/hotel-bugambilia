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
use App\Models\User;
use App\UseCases\Shared\Mutations\RegistrarSolicitudLimpieza;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

    $this->habitacion = Habitacion::create([
        'codigo' => 'HAB-0301',
        'numero' => 301,
        'slug' => 'habitacion-301',
        'nombre' => 'Habitación 301',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    $this->espacio = Espacio::create([
        'codigo' => 'ESP-MESA-001',
        'nombre' => 'Mesa Terraza 1',
        'descripcion' => 'Mesa de prueba',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoEspacio::Disponible,
    ]);
});

it('registra solicitud de limpieza pasando modelo habitacion y cambia estado a Sucia', function () {
    $useCase = app(RegistrarSolicitudLimpieza::class);
    $solicitud = $useCase->execute($this->habitacion, null, 'alta', 'Limpieza post-checkout');

    expect($solicitud->limpiable_type)->toBe(Habitacion::class)
        ->and($solicitud->limpiable_id)->toBe($this->habitacion->id)
        ->and($solicitud->prioridad)->toBe('alta')
        ->and($solicitud->notas)->toBe('Limpieza post-checkout');

    expect($this->habitacion->fresh()->estado)->toBe(EstadoHabitacion::Sucia);
});

it('registra solicitud de limpieza pasando modelo espacio y cambia estado a Limpieza', function () {
    $useCase = app(RegistrarSolicitudLimpieza::class);
    $solicitud = $useCase->execute($this->espacio, null, 'normal', 'Mesa sucia');

    expect($solicitud->limpiable_type)->toBe(Espacio::class)
        ->and($solicitud->limpiable_id)->toBe($this->espacio->id)
        ->and($solicitud->prioridad)->toBe('normal')
        ->and($solicitud->notas)->toBe('Mesa sucia');

    expect($this->espacio->fresh()->estado)->toBe(EstadoEspacio::Limpieza);
});

it('registra solicitud de limpieza pasando nombre de clase y ID', function () {
    $useCase = app(RegistrarSolicitudLimpieza::class);
    $solicitud = $useCase->execute(Habitacion::class, $this->habitacion->id, 'baja', 'Notas de prueba');

    expect($solicitud->limpiable_type)->toBe(Habitacion::class)
        ->and($solicitud->limpiable_id)->toBe($this->habitacion->id)
        ->and($solicitud->prioridad)->toBe('baja')
        ->and($solicitud->notas)->toBe('Notas de prueba');

    expect($this->habitacion->fresh()->estado)->toBe(EstadoHabitacion::Sucia);
});

it('registra solicitud de limpieza pasando solo ID (asume habitacion)', function () {
    $useCase = app(RegistrarSolicitudLimpieza::class);
    $solicitud = $useCase->execute($this->habitacion->id, null, 'normal', 'Solo ID');

    expect($solicitud->limpiable_type)->toBe(Habitacion::class)
        ->and($solicitud->limpiable_id)->toBe($this->habitacion->id)
        ->and($solicitud->prioridad)->toBe('normal')
        ->and($solicitud->notas)->toBe('Solo ID');

    expect($this->habitacion->fresh()->estado)->toBe(EstadoHabitacion::Sucia);
});
