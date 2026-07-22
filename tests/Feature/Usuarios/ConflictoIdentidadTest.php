<?php

declare(strict_types=1);

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Events\Usuarios\ClienteRegistrado;
use App\Interactors\Usuarios\ResolverConflictoIdentidad;
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

function crearConflicto(): ConflictoIdentidad
{
    $persona = Persona::create([
        'primer_nombre' => 'Juan',
        'tipo_persona' => 'natural',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Pérez',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '111222333',
    ]);

    return ConflictoIdentidad::create([
        'persona_id' => $persona->id,
        'tipo_conflicto' => TipoConflictoIdentidad::Homonimia,
        'datos_providos' => ['nombre' => 'Carlos'],
        'datos_existentes' => ['nombre' => 'Juan'],
        'estado' => EstadoConflictoIdentidad::Pendiente,
    ]);
}

function crearCatalogo(): Catalogo
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

it('vincula persona y crea cliente al resolver conflicto', function () {
    Event::fake([ClienteRegistrado::class]);

    $conflicto = crearConflicto();
    $catalogo = crearCatalogo();

    $interactor = app(ResolverConflictoIdentidad::class);
    $resultado = $interactor->vincular($conflicto, [
        'catalogo_id' => $catalogo->id,
        'email' => 'juan@test.com',
        'password' => 'password123',
    ]);

    expect($resultado['cliente'])->toBeInstanceOf(Cliente::class);
    expect($resultado['user'])->toBeInstanceOf(User::class);

    $conflicto->refresh();
    expect($conflicto->estado)->toBe(EstadoConflictoIdentidad::Resuelto);
    expect($conflicto->resuelto_en)->not->toBeNull();

    Event::assertDispatched(ClienteRegistrado::class);
});

it('rechaza conflicto y actualiza estado', function () {
    $conflicto = crearConflicto();

    $interactor = app(ResolverConflictoIdentidad::class);
    $interactor->rechazar($conflicto, 'Datos incorrectos, no se puede verificar identidad.');

    $conflicto->refresh();
    expect($conflicto->estado)->toBe(EstadoConflictoIdentidad::Rechazado);
    expect($conflicto->notas)->toBe('Datos incorrectos, no se puede verificar identidad.');
    expect($conflicto->resuelto_en)->not->toBeNull();
});
