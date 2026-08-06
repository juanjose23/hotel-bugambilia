<?php

declare(strict_types=1);

use App\Repository\Models\User;
use Laravel\Dusk\Browser;

test('usuario sin permisos de restaurante no puede acceder al modulo de mesas', function (): void {
    $userSinPermiso = User::factory()->create([
        'email' => 'sinpermiso-'.uniqid().'@bugambilia.test',
        'is_admin' => false,
    ]);

    $this->browse(function (Browser $browser) use ($userSinPermiso): void {
        $browser->loginAs($userSinPermiso)
            ->visit('/admin/restaurante/mesas')
            ->pause(1000)
            ->assertDontSee('Gestión de Mesas');
    });
});
