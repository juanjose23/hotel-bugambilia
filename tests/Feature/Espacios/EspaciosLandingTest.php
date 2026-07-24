<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\HabitacionSeeder;
use Database\Seeders\PaisSeeder;
use Database\Seeders\RestauranteSeeder;
use Database\Seeders\TasaCambioSeeder;
use Database\Seeders\UbicacionSeeder;

test('la ruta publica /espacios carga correctamente y lista solo espacios activos con web igual a true', function () {
    $this->seed([
        PaisSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        RestauranteSeeder::class,
    ]);

    // Crear un espacio oculto en la web
    Espacio::create([
        'codigo' => 'ESP-OCULTO',
        'nombre' => 'Bodega Oculta',
        'tipo' => TipoEspacio::OTRO->value,
        'estado' => EstadoEspacio::Disponible->value,
        'capacidad_personas' => 2,
        'web' => false,
        'reservable' => false,
    ]);

    $response = $this->get(route('espacios'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('espacios/Espacios', false)
            ->has('espacios')
            ->where('espacios', function ($espacios) {
                $nombres = collect($espacios)->pluck('nombre')->toArray();

                return ! in_array('Bodega Oculta', $nombres) && count($espacios) > 0;
            })
        );
});

test('se puede guardar acompañantes en una reserva de habitacion', function () {
    $this->seed([
        PaisSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
        TasaCambioSeeder::class,
        HabitacionSeeder::class,
    ]);

    $habitacion = Habitacion::query()->firstOrFail();

    $response = $this->post(route('reservas.crear'), [
        'nombre_cliente' => 'Carlos Mendoza',
        'telefono_cliente' => '+505 8888 1111',
        'email_cliente' => 'carlos@ejemplo.com',
        'tipo_reserva' => 'habitacion',
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-05',
        'adultos' => 2,
        'acompanantes' => [
            ['nombre' => 'Ana Mendoza', 'identificacion' => '001-020295-0002B', 'tipo' => 'adulto'],
        ],
        'total' => 350.00,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('reservas', [
        'nombre_cliente' => 'Carlos Mendoza',
        'email_cliente' => 'carlos@ejemplo.com',
    ]);

    $reserva = Reserva::where('email_cliente', 'carlos@ejemplo.com')->first();
    expect($reserva)->not->toBeNull()
        ->and($reserva?->acompanantes)->toBeArray()
        ->and($reserva?->acompanantes[0]['nombre'])->toBe('Ana Mendoza');
});
