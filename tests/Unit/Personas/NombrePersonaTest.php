<?php

declare(strict_types=1);

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('obtiene el nombre completo uniendo nombres y apellidos correctamente', function (): void {
    $persona = Persona::query()->create([
        'primer_nombre' => 'Juan',
        'segundo_nombre' => 'José',
        'tipo_persona' => 'natural',
    ]);

    PersonaNatural::query()->create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Ríos',
        'segundo_apellido' => 'González',
    ]);

    $persona->load('personaNatural');

    expect(ObtenerNombrePersona::desde($persona))->toBe('Juan José Ríos González')
        ->and($persona->personaNatural->full_name)->toBe('Juan José Ríos González');
});
