<?php

declare(strict_types=1);

use App\BusinessLogic\Usuarios\CompararDatosPersona;
use App\BusinessLogic\Usuarios\ResolverIdentidadPersona;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Enums\Usuarios\TipoResolucionIdentidad;
use App\Exceptions\YaTieneCuentaException;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Queries\Usuarios\BuscarPersonaIdentidadQuery;
use App\Repository\Queries\Usuarios\BuscarUsuarioCuentaQuery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->comparador = new CompararDatosPersona;
    $this->personas = new BuscarPersonaIdentidadQuery(new BuscarUsuarioCuentaQuery);
    $this->resolver = new ResolverIdentidadPersona($this->comparador, $this->personas);
});

function crearPersonaConCedula(
    string $cedula = '123456789',
    string $primerNombre = 'Juan',
    string $primerApellido = 'Pérez',
    ?string $telefono = null,
    ?string $direccion = null,
): Persona {
    $persona = Persona::create([
        'primer_nombre' => $primerNombre,
        'tipo_persona' => 'natural',
        'telefono' => $telefono,
        'direccion' => $direccion,
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => $primerApellido,
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => $cedula,
    ]);

    return $persona;
}

function datosRegistro(string $cedula = '123456789', array $sobrescribir = []): array
{
    return array_merge([
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => $cedula,
        'primer_nombre' => 'Juan',
        'primer_apellido' => 'Pérez',
        'telefono' => '88888888',
        'direccion' => 'Managua',
    ], $sobrescribir);
}

function crearCatalogoCliente(): Catalogo
{
    $tipo = CatalogoTipo::firstOrCreate(
        ['codigo' => 'tipo_cliente'],
        ['nombre' => 'Tipo de Cliente'],
    );

    return Catalogo::firstOrCreate(
        ['codigo' => 'cliente_regular'],
        [
            'nombre' => 'Cliente Regular',
            'catalogo_tipo_id' => $tipo->id,
        ],
    );
}

it('retorna crear_nueva cuando no existe persona con esa identificación', function () {
    $resultado = $this->resolver->resolver(datosRegistro('000000000'));

    expect($resultado['tipo'])->toBe(TipoResolucionIdentidad::CrearNueva);
    expect($resultado['persona'])->toBeNull();
    expect($resultado['tipo_conflicto'])->toBeNull();
});

it('retorna crear_nueva cuando no se provee identificación', function () {
    $datos = [
        'primer_nombre' => 'Juan',
        'primer_apellido' => 'Pérez',
    ];

    $resultado = $this->resolver->resolver($datos);

    expect($resultado['tipo'])->toBe(TipoResolucionIdentidad::CrearNueva);
});

it('lanza excepción cuando la persona ya tiene user', function () {
    $persona = crearPersonaConCedula();
    $persona->user()->create([
        'name' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'password' => 'password',
    ]);

    expect(fn () => $this->resolver->resolver(datosRegistro()))
        ->toThrow(YaTieneCuentaException::class);
});

it('lanza excepción cuando el email pertenece a una cuenta existente', function () {
    $persona = crearPersonaConCedula('999999999', 'María', 'González');
    $persona->user()->create([
        'name' => 'María González',
        'email' => 'maria@test.com',
        'password' => 'password',
    ]);

    $datos = datosRegistro('111111111', [
        'primer_nombre' => 'Laura',
        'primer_apellido' => 'Ruiz',
        'email' => 'maria@test.com',
    ]);

    expect(fn () => $this->resolver->resolver($datos))
        ->toThrow(YaTieneCuentaException::class);
});

it('retorna vincular_directo cuando datos coinciden exactamente', function () {
    crearPersonaConCedula('123456789', 'Juan', 'Pérez', '88888888', 'Managua');

    $resultado = $this->resolver->resolver(datosRegistro());

    expect($resultado['tipo'])->toBe(TipoResolucionIdentidad::VincularDirecto);
    expect($resultado['persona'])->toBeInstanceOf(Persona::class);
});

it('retorna actualizar_contacto cuando solo difieren teléfono o dirección', function () {
    crearPersonaConCedula('123456789', 'Juan', 'Pérez', '88888888', 'Managua');

    $resultado = $this->resolver->resolver(datosRegistro('123456789', [
        'telefono' => '99999999',
        'direccion' => 'León',
    ]));

    expect($resultado['tipo'])->toBe(TipoResolucionIdentidad::ActualizarContacto);
    expect($resultado['persona'])->toBeInstanceOf(Persona::class);
});

it('retorna conflicto_identidad cuando difieren nombre o apellidos', function () {
    crearPersonaConCedula();

    $resultado = $this->resolver->resolver(datosRegistro('123456789', [
        'primer_nombre' => 'Carlos',
        'primer_apellido' => 'López',
    ]));

    expect($resultado['tipo'])->toBe(TipoResolucionIdentidad::ConflictoIdentidad);
    expect($resultado['tipo_conflicto'])->toBe(TipoConflictoIdentidad::Homonimia);
});

it('retorna vincular_directo cuando la persona es cliente sin usuario', function () {
    $persona = crearPersonaConCedula();
    Cliente::create([
        'persona_id' => $persona->id,
        'catalogo_id' => crearCatalogoCliente()->id,
        'estado' => 1,
    ]);

    $resultado = $this->resolver->resolver(datosRegistro('123456789', [
        'telefono' => null,
        'direccion' => null,
    ]));

    expect($resultado['tipo'])->toBe(TipoResolucionIdentidad::VincularDirecto);
    expect($resultado['persona']->id)->toBe($persona->id);
});

it('retorna vincular_directo cuando la persona es colaborador sin usuario', function () {
    $persona = crearPersonaConCedula();
    Colaborador::create([
        'persona_id' => $persona->id,
        'codigo' => 'EMP-001',
    ]);

    $resultado = $this->resolver->resolver(datosRegistro('123456789', [
        'telefono' => null,
        'direccion' => null,
    ]));

    expect($resultado['tipo'])->toBe(TipoResolucionIdentidad::VincularDirecto);
    expect($resultado['persona']->id)->toBe($persona->id);
});

it('retorna vincular_directo cuando la persona es proveedor sin usuario', function () {
    $persona = crearPersonaConCedula();
    Proveedor::create([
        'persona_id' => $persona->id,
        'codigo' => 'PROV-001',
    ]);

    $resultado = $this->resolver->resolver(datosRegistro('123456789', [
        'telefono' => null,
        'direccion' => null,
    ]));

    expect($resultado['tipo'])->toBe(TipoResolucionIdentidad::VincularDirecto);
    expect($resultado['persona']->id)->toBe($persona->id);
});

it('lanza excepción cuando un colaborador con usuario intenta registrarse', function () {
    $persona = crearPersonaConCedula();
    Colaborador::create([
        'persona_id' => $persona->id,
        'codigo' => 'EMP-001',
    ]);
    $persona->user()->create([
        'name' => 'Juan Pérez',
        'email' => 'juan@colaborador.com',
        'password' => 'password',
    ]);

    expect(fn () => $this->resolver->resolver(datosRegistro()))
        ->toThrow(YaTieneCuentaException::class);
});
