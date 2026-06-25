<?php

declare(strict_types=1);

namespace Tests\Feature\Colaboradores;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Models\Colaboradores\Colaborador;
use App\Models\Colaboradores\ColaboradorSalario;
use App\Models\Personas\Persona;
use App\Models\Personas\PersonaNatural;
use App\UseCases\Colaboradores\Mutations\CrearNuevoSalario;
use App\UseCases\Colaboradores\Mutations\GenerarCodigo;
use App\UseCases\Colaboradores\Queries\ObtenerNombreCompleto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── CrearNuevoSalario ──────────────────────────────────────────────────

it('crea un nuevo salario activo para el colaborador', function () {
    $colaborador = Colaborador::factory()->create();

    $salario = app(CrearNuevoSalario::class)->__invoke(
        colaboradorId: $colaborador->id,
        data: [
            'salario' => 15000.00,
            'fecha_inicio' => now()->toDateString(),
            'estado' => EstadoCatalogo::Activo->value,
        ]
    );

    expect($salario)->toBeInstanceOf(ColaboradorSalario::class)
        ->and($salario->colaborador_id)->toBe($colaborador->id)
        ->and($salario->salario)->toBe(15000.00)
        ->and($salario->estado)->toBe(EstadoCatalogo::Activo->value);
});

it('desactiva salarios previos activos al crear uno nuevo activo', function () {
    $colaborador = Colaborador::factory()->create();

    ColaboradorSalario::create([
        'colaborador_id' => $colaborador->id,
        'salario' => 12000.00,
        'fecha_inicio' => now()->subMonth()->toDateString(),
        'estado' => EstadoCatalogo::Activo->value,
    ]);

    app(CrearNuevoSalario::class)->__invoke(
        colaboradorId: $colaborador->id,
        data: [
            'salario' => 15000.00,
            'fecha_inicio' => now()->toDateString(),
            'estado' => EstadoCatalogo::Activo->value,
        ]
    );

    $salariosActivos = ColaboradorSalario::where('colaborador_id', $colaborador->id)
        ->where('estado', EstadoCatalogo::Activo->value)
        ->get();

    expect($salariosActivos)->toHaveCount(1);
    expect((float) $salariosActivos->first()->salario)->toBe(15000.00);
});

it('crea salario inactivo sin afectar salarios activos existentes', function () {
    $colaborador = Colaborador::factory()->create();

    ColaboradorSalario::create([
        'colaborador_id' => $colaborador->id,
        'salario' => 12000.00,
        'fecha_inicio' => now()->subMonth()->toDateString(),
        'estado' => EstadoCatalogo::Activo->value,
    ]);

    app(CrearNuevoSalario::class)->__invoke(
        colaboradorId: $colaborador->id,
        data: [
            'salario' => 15000.00,
            'fecha_inicio' => now()->toDateString(),
            'estado' => EstadoCatalogo::Inactivo->value,
        ]
    );

    $salariosActivos = ColaboradorSalario::where('colaborador_id', $colaborador->id)
        ->where('estado', EstadoCatalogo::Activo->value)
        ->get();

    expect($salariosActivos)->toHaveCount(1);
    expect((float) $salariosActivos->first()->salario)->toBe(12000.00);
});

// ─── GenerarCodigo ──────────────────────────────────────────────────────

it('genera el codigo COL-0001 cuando no existen colaboradores', function () {
    // Ensure no colaboradores exist
    Colaborador::query()->forceDelete();

    $codigo = app(GenerarCodigo::class)->generarCodigo();

    expect($codigo)->toBe('COL-0001');
});

it('incrementa el codigo secuencialmente segun el ultimo colaborador', function () {
    Colaborador::factory()->create(['codigo' => 'COL-0005']);

    $codigo = app(GenerarCodigo::class)->generarCodigo();

    expect($codigo)->toBe('COL-0006');
});

it('incluye colaboradores soft-deleted para la generacion del codigo', function () {
    $colaborador = Colaborador::factory()->create(['codigo' => 'COL-0010']);
    $colaborador->delete();

    $codigo = app(GenerarCodigo::class)->generarCodigo();

    expect($codigo)->toBe('COL-0011');
});

// ─── ObtenerNombreCompleto ──────────────────────────────────────────────

beforeEach(function (): void {
    $this->useCase = app(ObtenerNombreCompleto::class);
});

it('obtiene el nombre completo con todos los componentes', function () {
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

    // Reload to ensure relationships are loaded
    $colaborador = Colaborador::factory()->create(['persona_id' => $persona->id]);
    $colaborador->load('persona.personaNatural');

    $nombre = $this->useCase->obtenerNombreCompleto($colaborador);

    expect($nombre)->toBe('Juan Carlos Perez Lopez');
});

it('maneja nombres sin segundo nombre ni segundo apellido', function () {
    $persona = Persona::factory()->create([
        'primer_nombre' => 'Ana',
        'segundo_nombre' => null,
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Martinez',
        'segundo_apellido' => null,
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '87654321',
        'sexo' => 'F',
        'fecha_nacimiento' => '1992-05-15',
    ]);

    // The use case adds a space between each component even if null,
    // resulting in extra spaces that trim() condenses
    $colaborador = Colaborador::factory()->create(['persona_id' => $persona->id]);
    $colaborador->load('persona.personaNatural');

    $nombre = $this->useCase->obtenerNombreCompleto($colaborador);

    // The use case concatenates: 'Ana' . ' ' . '' . ' ' . 'Martinez' . ' ' . ''
    // = 'Ana  Martinez ' -> trim() = 'Ana  Martinez'
    expect($nombre)->toBe('Ana  Martinez');
});

it('retorna el nombre completo con el codigo del colaborador', function () {
    $persona = Persona::factory()->create([
        'primer_nombre' => 'Maria',
        'segundo_nombre' => null,
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Garcia',
        'segundo_apellido' => null,
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '11223344',
        'sexo' => 'F',
        'fecha_nacimiento' => '1988-08-20',
    ]);

    $colaborador = Colaborador::factory()->create([
        'persona_id' => $persona->id,
        'codigo' => 'COL-0042',
    ]);
    $colaborador->load('persona.personaNatural');

    $nombre = $this->useCase->nombreCompletoConCodigo($colaborador);

    expect($nombre)->toBe('COL-0042 - Maria  Garcia');
});
