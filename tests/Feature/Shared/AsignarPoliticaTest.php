<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Politicas\Politica;
use App\Models\User;
use App\UseCases\Shared\Mutations\AsignarPolitica;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);
});

it('puede asignar una politica a una habitacion', function () {
    $categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();

    $habitacion = Habitacion::create([
        'codigo' => 'HAB-0301',
        'numero' => 301,
        'slug' => 'habitacion-301',
        'nombre' => 'Habitación 301',
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    $politica = Politica::create([
        'titulo' => 'Política de No Fumar',
        'descripcion' => 'No se permite fumar en la habitación',
        'estado' => 1,
    ]);

    $useCase = app(AsignarPolitica::class);
    $useCase->execute($politica->id, $habitacion);

    expect($habitacion->politicas()->where('politica_id', $politica->id)->exists())->toBeTrue();
});

it('no duplica la asignacion de politica si ya existe', function () {
    $categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();

    $habitacion = Habitacion::create([
        'codigo' => 'HAB-0301',
        'numero' => 301,
        'slug' => 'habitacion-301',
        'nombre' => 'Habitación 301',
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);

    $politica = Politica::create([
        'titulo' => 'Política de Cancelación',
        'descripcion' => 'Cancelación gratuita hasta 24h antes',
        'estado' => 1,
    ]);

    $habitacion->politicas()->attach($politica->id);

    $useCase = app(AsignarPolitica::class);
    $useCase->execute($politica->id, $habitacion);

    // Verify it is only attached once
    $count = DB::table('politicaables')
        ->where('politicaable_type', Habitacion::class)
        ->where('politicaable_id', $habitacion->id)
        ->where('politica_id', $politica->id)
        ->count();

    expect($count)->toBe(1);
});

it('lanza excepcion si el modelo no soporta politicas', function () {
    $user = User::factory()->create();

    $politica = Politica::create([
        'titulo' => 'Cualquier política',
        'descripcion' => 'Cualquier descripción',
        'estado' => 1,
    ]);

    $useCase = app(AsignarPolitica::class);

    expect(fn () => $useCase->execute($politica->id, $user))->toThrow(\InvalidArgumentException::class);
});
