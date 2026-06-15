<?php

declare(strict_types=1);

namespace Tests\Feature\Colaboradores;

use App\Models\Colaboradores\Colaborador;
use App\Models\Personas\Persona;
use App\Models\Personas\PersonaNatural;
use App\UseCases\Colaboradores\Queries\ObtenerDatosCarnet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->useCase = app(ObtenerDatosCarnet::class);
});

it('obtiene el nombre completo de una persona natural', function () {
    $persona = Persona::factory()->create([
        'primer_nombre' => 'Juan',
        'segundo_nombre' => 'Carlos',
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Perez',
        'segundo_apellido' => 'Lopez',
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '12345678',
        'sexo' => 'M',
        'fecha_nacimiento' => '1990-01-01',
    ]);

    $nombre = $this->useCase->obtenerNombreCompleto($persona);
    expect($nombre)->toBe('Juan Carlos Perez Lopez');
});

it('obtiene el codigo del colaborador o un fallback', function () {
    $personaConColaborador = Persona::factory()->create();
    $colaborador = Colaborador::factory()->create([
        'persona_id' => $personaConColaborador->id,
        'codigo' => 'COL-1234',
    ]);

    $codigo1 = $this->useCase->obtenerCodigo($personaConColaborador);
    expect($codigo1)->toBe('COL-1234');

    $personaSinColaborador = Persona::factory()->create();
    $codigo2 = $this->useCase->obtenerCodigo($personaSinColaborador);
    expect($codigo2)->toBe('SIN-CODIGO');
});

it('obtiene la direccion o un fallback', function () {
    $personaConDir = Persona::factory()->create(['direccion' => 'Calle Falsa 123']);
    expect($this->useCase->obtenerDireccion($personaConDir))->toBe('Calle Falsa 123');

    $personaSinDir = Persona::factory()->create(['direccion' => null]);
    expect($this->useCase->obtenerDireccion($personaSinDir))->toBe('Sin dirección registrada');
});

it('obtiene el tipo de sangre formateado o fallback', function () {
    $persona = Persona::factory()->create();
    $colaborador = Colaborador::factory()->create(['persona_id' => $persona->id]);

    // Scenario 1: No definido
    expect($this->useCase->obtenerTipoSangre($persona))->toBe('No definido');

    // Scenario 2: Definido
    $colaborador->datosMedicos()->create([
        'tipo_sangre' => 'O+',
        'alergias' => 'Ninguna',
        'padecimientos' => 'Ninguno',
        'estado' => 1,
    ]);

    $persona->load('colaborador.datosMedicos');
    expect($this->useCase->obtenerTipoSangre($persona))->toBe('O+'); // Assuming O_POSITIVO maps to O+ in TipoSangre
});

it('genera el SVG del codigo de barras', function () {
    $svg = $this->useCase->obtenerSvgCodigoBarras('COL-1234');
    expect($svg)->toBeString()
        ->and($svg)->toContain('<svg');
});
