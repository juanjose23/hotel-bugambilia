<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\Habitacion;
use App\UseCases\Habitaciones\Mutations\GenerarCodigoHabitacion;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);

    $this->categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $this->ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();
    $this->generador = app(GenerarCodigoHabitacion::class);
});

it('genera el codigo HAB-0001 cuando no existen habitaciones', function () {
    $codigo = $this->generador->execute();
    expect($codigo)->toBe('HAB-0001');
});

it('incrementa el codigo secuencialmente segun la ultima habitacion', function () {
    Habitacion::create([
        'codigo' => 'HAB-0001',
        'numero' => 101,
        'slug' => 'habitacion-101',
        'nombre' => 'Habitación 101',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    $codigo = $this->generador->execute();
    expect($codigo)->toBe('HAB-0002');
});

it('incluye habitaciones soft-deleted para la generacion del codigo', function () {
    $habitacion = Habitacion::create([
        'codigo' => 'HAB-0005',
        'numero' => 105,
        'slug' => 'habitacion-105',
        'nombre' => 'Habitación 105',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    $habitacion->delete(); // Soft delete

    $codigo = $this->generador->execute();
    expect($codigo)->toBe('HAB-0006');
});
