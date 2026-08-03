<?php

declare(strict_types=1);

use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\ColaboradorBaseSeeder;
use Database\Seeders\EspacioSeeder;
use Database\Seeders\MenuRestauranteSeeder;
use Database\Seeders\MonedaSeeder;
use Database\Seeders\PaisSeeder;
use Database\Seeders\RestauranteSeeder;
use Database\Seeders\TasaCambioSeeder;
use Database\Seeders\UbicacionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('la página del restaurante responde exitosamente y pasa los datos de restaurante, ambientes y menú a Inertia', function () {
    $this->seed([
        PaisSeeder::class,
        MonedaSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        TasaCambioSeeder::class,
        ColaboradorBaseSeeder::class,
        UbicacionSeeder::class,
        EspacioSeeder::class,
        RestauranteSeeder::class,
        MenuRestauranteSeeder::class,
    ]);

    $response = $this->get(route('restaurante'));

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('restaurante/Restaurante', false)
            ->has('restaurante')
            ->has('ambientes')
            ->has('mesas')
            ->has('menu')
            ->where('restaurante.nombre', 'Restaurante Bugambilias')
        );
});
