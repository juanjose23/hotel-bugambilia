<?php

declare(strict_types=1);

use Database\Seeders\Colaboradores\ColaboradorBaseSeeder;
use Database\Seeders\Configuracion\CatalogoSeeder;
use Database\Seeders\Configuracion\CatalogoTipoSeeder;
use Database\Seeders\Configuracion\MonedaSeeder;
use Database\Seeders\Configuracion\PaisSeeder;
use Database\Seeders\Configuracion\UbicacionSeeder;
use Database\Seeders\EspacioSeeder;
use Database\Seeders\Restaurante\Menu\MenuRestauranteSeeder;
use Database\Seeders\TasaCambioSeeder;
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
