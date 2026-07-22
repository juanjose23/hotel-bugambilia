<?php

declare(strict_types=1);

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Events\Usuarios\ClienteRegistrado;
use App\Events\Usuarios\PersonaConflictoIdentidad;
use App\Exceptions\YaTieneCuentaException;
use App\Interactors\Usuarios\RegistrarCliente;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\User;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(LazilyRefreshDatabase::class);

function crearCatalogoTipoCliente(): Catalogo
{
    $tipo = CatalogoTipo::create([
        'codigo' => 'tipo_cliente',
        'nombre' => 'Tipo de Cliente',
    ]);

    return Catalogo::create([
        'codigo' => 'cliente_regular',
        'nombre' => 'Cliente Regular',
        'catalogo_tipo_id' => $tipo->id,
    ]);
}

function datosClienteBase(): array
{
    return [
        'primer_nombre' => 'María',
        'primer_apellido' => 'González',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '987654321',
        'email' => 'maria@test.com',
        'password' => 'password123',
        'telefono' => '88888888',
        'direccion' => 'Managua',
        'catalogo_id' => crearCatalogoTipoCliente()->id,
    ];
}

it('crea nuevo cliente cuando no existe persona', function () {
    Event::fake([ClienteRegistrado::class]);

    $datos = datosClienteBase();
    $interactor = app(RegistrarCliente::class);
    $resultado = $interactor->ejecutar($datos);

    expect($resultado['es_nuevo'])->toBeTrue();
    expect($resultado['cliente'])->toBeInstanceOf(Cliente::class);
    expect($resultado['persona'])->toBeInstanceOf(Persona::class);
    expect($resultado['user'])->toBeInstanceOf(User::class);

    expect($resultado['persona']->primer_nombre)->toBe('María');
    expect($resultado['persona']->personaNatural->numero_identificacion)->toBe('987654321');
    expect($resultado['user']->email)->toBe('maria@test.com');

    Event::assertDispatched(ClienteRegistrado::class, function ($event) {
        return $event->esNuevo === true;
    });
});

it('vincula persona existente cuando datos coinciden', function () {
    Event::fake([ClienteRegistrado::class]);

    $persona = Persona::create([
        'primer_nombre' => 'María',
        'tipo_persona' => 'natural',
        'telefono' => '88888888',
        'direccion' => 'Managua',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'González',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '987654321',
    ]);

    $datos = datosClienteBase();
    $interactor = app(RegistrarCliente::class);
    $resultado = $interactor->ejecutar($datos);

    expect($resultado['es_nuevo'])->toBeFalse();
    expect($resultado['persona']->id)->toBe($persona->id);
    expect($resultado['user']->persona_id)->toBe($persona->id);

    Event::assertDispatched(ClienteRegistrado::class, function ($event) {
        return $event->esNuevo === false;
    });
});

it('actualiza contacto y vincula cuando solo difieren teléfono/dirección', function () {
    Event::fake([ClienteRegistrado::class]);

    $persona = Persona::create([
        'primer_nombre' => 'María',
        'tipo_persona' => 'natural',
        'telefono' => '11111111',
        'direccion' => 'León',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'González',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '987654321',
    ]);

    $datos = datosClienteBase();
    $interactor = app(RegistrarCliente::class);
    $resultado = $interactor->ejecutar($datos);

    expect($resultado['es_nuevo'])->toBeFalse();
    expect($resultado['persona']->telefono)->toBe('88888888');
    expect($resultado['persona']->direccion)->toBe('Managua');
});

it('lanza excepción cuando persona ya tiene user', function () {
    $persona = Persona::create([
        'primer_nombre' => 'María',
        'tipo_persona' => 'natural',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'González',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '987654321',
    ]);

    User::create([
        'persona_id' => $persona->id,
        'name' => 'María González',
        'email' => 'maria@existente.com',
        'password' => 'password',
    ]);

    $datos = datosClienteBase();
    $interactor = app(RegistrarCliente::class);

    expect(fn () => $interactor->ejecutar($datos))
        ->toThrow(YaTieneCuentaException::class);
});

it('crea conflicto de identidad cuando difieren nombres', function () {
    Event::fake([PersonaConflictoIdentidad::class]);

    $persona = Persona::create([
        'primer_nombre' => 'María',
        'tipo_persona' => 'natural',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'González',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '987654321',
    ]);

    $datos = datosClienteBase();
    $datos['primer_nombre'] = 'Carlos';
    $datos['primer_apellido'] = 'López';

    $interactor = app(RegistrarCliente::class);

    expect(fn () => $interactor->ejecutar($datos))
        ->toThrow(RuntimeException::class);

    expect(ConflictoIdentidad::count())->toBe(1);

    $conflicto = ConflictoIdentidad::first();
    expect($conflicto->estado)->toBe(EstadoConflictoIdentidad::Pendiente);
    expect($conflicto->persona_id)->toBe($persona->id);

    Event::assertDispatched(PersonaConflictoIdentidad::class);
});

it('crea cliente correctamente sin correo electrónico', function () {
    Event::fake([ClienteRegistrado::class]);

    $datos = datosClienteBase();
    unset($datos['email']);

    $interactor = app(RegistrarCliente::class);
    $resultado = $interactor->ejecutar($datos);

    expect($resultado['es_nuevo'])->toBeTrue();
    expect($resultado['user']->email)->toBeNull();
    expect($resultado['cliente'])->toBeInstanceOf(Cliente::class);
});
