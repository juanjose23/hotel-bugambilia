<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\Habitacion;
use App\UseCases\Habitaciones\Mutations\GenerarSlugHabitacion;
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
    $this->generador = app(GenerarSlugHabitacion::class);
});

it('genera un slug valido basado en el nombre de la habitacion', function () {
    $slug = $this->generador->execute('Habitación Presidencial Especial');
    expect($slug)->toBe('habitacion-presidencial-especial');
});

it('retorna habitacion si el slug es vacio', function () {
    $slug = $this->generador->execute('   ');
    expect($slug)->toBe('habitacion');
});

it('evita colisiones agregando un sufijo incremental', function () {
    // Crear primera habitación
    Habitacion::create([
        'codigo' => 'HAB-0001',
        'numero' => 101,
        'slug' => 'habitacion-101',
        'nombre' => 'Habitación 101',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    // Generar slug para una nueva habitación con el mismo nombre
    $slug = $this->generador->execute('Habitación 101');
    expect($slug)->toBe('habitacion-101-1');

    // Crear la segunda habitación con ese slug colisionado
    Habitacion::create([
        'codigo' => 'HAB-0002',
        'numero' => 102,
        'slug' => 'habitacion-101-1',
        'nombre' => 'Habitación 101',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    // Generar slug para una tercera
    $slug = $this->generador->execute('Habitación 101');
    expect($slug)->toBe('habitacion-101-2');
});

it('ignora colision con el ID especificado al actualizar', function () {
    $habitacion = Habitacion::create([
        'codigo' => 'HAB-0001',
        'numero' => 101,
        'slug' => 'habitacion-101',
        'nombre' => 'Habitación 101',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    // Al actualizar la misma habitación, no debe colisionar consigo misma
    $slug = $this->generador->execute('Habitación 101', $habitacion->id);
    expect($slug)->toBe('habitacion-101');
});

it('detecta colisiones incluso con habitaciones soft-deleted', function () {
    $habitacion = Habitacion::create([
        'codigo' => 'HAB-0001',
        'numero' => 101,
        'slug' => 'habitacion-101',
        'nombre' => 'Habitación 101',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    $habitacion->delete(); // Soft delete

    // Debe detectar la colisión con la habitación eliminada
    $slug = $this->generador->execute('Habitación 101');
    expect($slug)->toBe('habitacion-101-1');
});
