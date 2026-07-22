<?php

declare(strict_types=1);

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Queries\Colaboradores\ObtenerDatosCarnet;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

test('obtiene datos de carnet sin error aunque no tenga foto de perfil o icono de hotel', function () {
    $persona = Persona::create([
        'primer_nombre' => 'Juan',
        'segundo_nombre' => 'Carlos',
        'tipo_persona' => 'natural',
        'direccion' => 'Managua',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Pérez',
        'segundo_apellido' => 'López',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '001-010102-1234G',
    ]);

    Colaborador::create([
        'persona_id' => $persona->id,
        'codigo' => 'COL-0001',
        'estado' => 1,
    ]);

    config(['hotel.icon' => '']);

    $query = new ObtenerDatosCarnet;
    $datos = $query->ejecutar($persona);

    expect($datos['nombre_completo'])->toBe('Juan Carlos Pérez López');
    expect($datos['codigo'])->toBe('COL-0001');
    expect($datos['foto_base64'])->toBe('');
});
