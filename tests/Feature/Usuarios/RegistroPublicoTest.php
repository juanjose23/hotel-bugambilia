<?php

declare(strict_types=1);

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

function crearCatalogoClienteRegular(): void
{
    $tipo = CatalogoTipo::create([
        'codigo' => 'tipo_cliente',
        'nombre' => 'Tipo de Cliente',
    ]);

    Catalogo::create([
        'codigo' => 'cliente_regular',
        'nombre' => 'Cliente Regular',
        'catalogo_tipo_id' => $tipo->id,
    ]);
}

function datosRegistroNatural(array $sobrescribir = []): array
{
    return array_merge([
        'tipo_persona' => 'natural',
        'primer_nombre' => 'Lionel',
        'primer_apellido' => 'Messi',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '001-010285-0001A',
        'email' => 'lionel@test.com',
        'phone' => '88888888',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $sobrescribir);
}

it('pide la identificación al cliente cuando selecciona un tipo de documento sin número', function () {
    $response = $this->post('/registro', datosRegistroNatural([
        'numero_identificacion' => '',
    ]));

    $response->assertSessionHasErrors('numero_identificacion');

    expect(Persona::count())->toBe(0);
    expect(Cliente::count())->toBe(0);
    expect(User::count())->toBe(0);
});

it('pide el tipo de identificación cuando el cliente ingresa un número sin tipo', function () {
    $response = $this->post('/registro', datosRegistroNatural([
        'tipo_identificacion' => '',
    ]));

    $response->assertSessionHasErrors('tipo_identificacion');

    expect(Persona::count())->toBe(0);
});

it('permite registrarse cuando la identificación está completa', function () {
    crearCatalogoClienteRegular();

    $response = $this->post('/registro', datosRegistroNatural());

    $response->assertRedirect(route('portal'));
    $response->assertSessionHas('exito');

    expect(Persona::count())->toBe(1);
    expect(Cliente::count())->toBe(1);
    expect(User::count())->toBe(1);

    $persona = Persona::firstOrFail();
    expect($persona->personaNatural->tipo_identificacion)->toBe('cedula');
    expect($persona->personaNatural->numero_identificacion)->toBe('001-010285-0001A');
});
