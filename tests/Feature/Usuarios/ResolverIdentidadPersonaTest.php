<?php

declare(strict_types=1);

use App\BusinessLogic\Usuarios\CompararDatosPersona;
use App\BusinessLogic\Usuarios\ResolverIdentidadPersona;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Exceptions\YaTieneCuentaException;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->comparador = new CompararDatosPersona;
    $this->resolver = new ResolverIdentidadPersona($this->comparador);
});

it('retorna crear_nueva cuando no existe persona con esa identificación', function () {
    $datos = [
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
        'primer_nombre' => 'Juan',
        'primer_apellido' => 'Pérez',
    ];

    $resultado = $this->resolver->resolver($datos);

    expect($resultado['tipo'])->toBe('crear_nueva');
    expect($resultado['persona'])->toBeNull();
    expect($resultado['tipo_conflicto'])->toBeNull();
});

it('retorna crear_nueva cuando no se provee identificación', function () {
    $datos = [
        'primer_nombre' => 'Juan',
        'primer_apellido' => 'Pérez',
    ];

    $resultado = $this->resolver->resolver($datos);

    expect($resultado['tipo'])->toBe('crear_nueva');
});

it('lanza excepción cuando la persona ya tiene user', function () {
    $persona = Persona::create([
        'primer_nombre' => 'Juan',
        'tipo_persona' => 'natural',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Pérez',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
    ]);

    User::create([
        'persona_id' => $persona->id,
        'name' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'password' => 'password',
    ]);

    $datos = [
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
        'primer_nombre' => 'Juan',
        'primer_apellido' => 'Pérez',
    ];

    expect(fn () => $this->resolver->resolver($datos))
        ->toThrow(YaTieneCuentaException::class);
});

it('retorna vincular_directo cuando datos coinciden exactamente', function () {
    $persona = Persona::create([
        'primer_nombre' => 'Juan',
        'tipo_persona' => 'natural',
        'telefono' => '88888888',
        'direccion' => 'Managua',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Pérez',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
    ]);

    $datos = [
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
        'primer_nombre' => 'Juan',
        'primer_apellido' => 'Pérez',
        'telefono' => '88888888',
        'direccion' => 'Managua',
    ];

    $resultado = $this->resolver->resolver($datos);

    expect($resultado['tipo'])->toBe('vincular_directo');
    expect($resultado['persona']->id)->toBe($persona->id);
});

it('retorna actualizar_contacto cuando solo difieren teléfono o dirección', function () {
    $persona = Persona::create([
        'primer_nombre' => 'Juan',
        'tipo_persona' => 'natural',
        'telefono' => '88888888',
        'direccion' => 'Managua',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Pérez',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
    ]);

    $datos = [
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
        'primer_nombre' => 'Juan',
        'primer_apellido' => 'Pérez',
        'telefono' => '99999999',
        'direccion' => 'León',
    ];

    $resultado = $this->resolver->resolver($datos);

    expect($resultado['tipo'])->toBe('actualizar_contacto');
    expect($resultado['persona']->id)->toBe($persona->id);
});

it('retorna conflicto_identidad cuando difieren nombre o apellidos', function () {
    $persona = Persona::create([
        'primer_nombre' => 'Juan',
        'tipo_persona' => 'natural',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Pérez',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
    ]);

    $datos = [
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '123456789',
        'primer_nombre' => 'Carlos',
        'primer_apellido' => 'López',
    ];

    $resultado = $this->resolver->resolver($datos);

    expect($resultado['tipo'])->toBe('conflicto_identidad');
    expect($resultado['tipo_conflicto'])->toBe(TipoConflictoIdentidad::Homonimia);
});
