<?php

declare(strict_types=1);

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    if (Moneda::query()->where('codigo', 'NIO')->doesntExist()) {
        Moneda::query()->create([
            'codigo' => 'NIO',
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]);
    }
});

function obtenerUsuarioLimpiezaAdmin(): User
{
    $admin = User::query()->where('email', 'admin@bugambilia.test')->first();

    if ($admin === null) {
        $admin = User::factory()->create([
            'email' => 'admin@bugambilia.test',
            'is_admin' => true,
        ]);
    }

    $persona = Persona::query()->create([
        'primer_nombre' => 'Admin Limpieza Dusk',
        'tipo_persona' => 'natural',
    ]);

    $admin->update(['persona_id' => $persona->id]);

    Colaborador::query()->firstOrCreate([
        'id' => $admin->id,
    ], [
        'codigo' => 'COL-LIM-'.$admin->id,
        'persona_id' => $persona->id,
        'fecha_ingreso' => now(),
        'estado' => EstadoGeneral::Activo,
    ]);

    $rol = Role::firstOrCreate([
        'name' => config('filament-shield.super_admin.name', 'super_admin'),
        'guard_name' => 'web',
    ]);

    if (! $admin->hasRole($rol)) {
        $admin->assignRole($rol);
    }

    return $admin;
}

test('modulo de limpieza: tablero interactivo de operaciones y estado de habitaciones', function (): void {
    $user = obtenerUsuarioLimpiezaAdmin();

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/admin/tablero-limpieza')
            ->waitForText('Tablero de Control de Limpieza')
            ->assertSee('Tablero de Control de Limpieza')
            ->pause(1000);
    });
});

test('modulo de limpieza: solicitudes y planificacion de horarios', function (): void {
    $user = obtenerUsuarioLimpiezaAdmin();

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/admin/limpieza/solicitudes')
            ->waitForText('Solicitudes de Limpieza')
            ->assertSee('Solicitudes de Limpieza')
            ->pause(1000);

        $browser->visit('/admin/limpieza/horarios')
            ->waitForText('Horarios Planificados')
            ->assertSee('Horarios Planificados')
            ->pause(1000);
    });
});

test('modulo de limpieza: gestion de turnos y ejecuciones', function (): void {
    $user = obtenerUsuarioLimpiezaAdmin();

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/admin/limpieza/turnos')
            ->waitForText('Turnos')
            ->assertSee('Turnos')
            ->pause(1000);

        $browser->visit('/admin/limpieza/ejecuciones')
            ->waitForText('Ejecuciones')
            ->assertSee('Ejecuciones')
            ->pause(1000);
    });
});

test('modulo de limpieza: abastecimiento de carritos y control de lavanderia', function (): void {
    $user = obtenerUsuarioLimpiezaAdmin();

    $this->browse(function (Browser $browser) use ($user): void {
        $browser->loginAs($user)
            ->visit('/admin/limpieza/abastecer-carrito')
            ->waitForText('Administración de Carritos de Limpieza')
            ->assertSee('Administración de Carritos de Limpieza')
            ->pause(1000);

        $browser->visit('/admin/limpieza/control-lavanderia')
            ->waitForText('Control de Inventario de Lavandería')
            ->assertSee('Control de Inventario de Lavandería')
            ->pause(1000);
    });
});
