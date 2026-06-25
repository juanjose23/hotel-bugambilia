<?php

declare(strict_types=1);

use App\Models\Personas\Persona;
use App\Models\Personas\PersonaNatural;
use App\Models\User;
use App\UseCases\Usuarios\Mutations\GenerarCredencialesUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->useCase = app(GenerarCredencialesUsuario::class);
    $this->domain = config('app.email_domain', 'hotel.com');
});

it('genera credenciales con nombre de usuario y email', function () {
    $persona = Persona::factory()->create([
        'primer_nombre' => 'Juan',
        'segundo_nombre' => null,
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Perez',
        'segundo_apellido' => null,
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '12345678',
        'sexo' => 'M',
        'fecha_nacimiento' => '1990-01-01',
    ]);

    $credenciales = $this->useCase->execute($persona);

    expect($credenciales)->toHaveKey('name')
        ->and($credenciales)->toHaveKey('email')
        ->and($credenciales['name'])->toBe('juan.perez')
        ->and($credenciales['email'])->toBe('juan.perez@'.$this->domain);
});

it('limpia acentos y caracteres especiales en las credenciales', function () {
    $persona = Persona::factory()->create([
        'primer_nombre' => 'María José',
        'segundo_nombre' => null,
    ]);

    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'González',
        'segundo_apellido' => null,
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '87654321',
        'sexo' => 'F',
        'fecha_nacimiento' => '1992-05-15',
    ]);

    $credenciales = $this->useCase->execute($persona);

    expect($credenciales['name'])->toBe('maria jose.gonzalez');
    expect($credenciales['email'])->toBe('maria jose.gonzalez@'.$this->domain);
});

it('incrementa el nombre de usuario si ya existe uno con el mismo base', function () {
    $persona1 = Persona::factory()->create(['primer_nombre' => 'Carlos']);
    PersonaNatural::create([
        'persona_id' => $persona1->id,
        'primer_apellido' => 'Lopez',
        'segundo_apellido' => null,
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '11111111',
        'sexo' => 'M',
        'fecha_nacimiento' => '1985-03-10',
    ]);

    $credenciales1 = $this->useCase->execute($persona1);
    expect($credenciales1['name'])->toBe('carlos.lopez');

    // Create a user with the same base name so it's already taken
    User::create([
        'name' => $credenciales1['name'],
        'email' => $credenciales1['email'],
        'password' => bcrypt('password'),
    ]);

    $persona2 = Persona::factory()->create(['primer_nombre' => 'Carlos']);
    PersonaNatural::create([
        'persona_id' => $persona2->id,
        'primer_apellido' => 'Lopez',
        'segundo_apellido' => null,
        'tipo_identificacion' => 'cedula',
        'numero_identificacion' => '22222222',
        'sexo' => 'M',
        'fecha_nacimiento' => '1990-07-22',
    ]);

    $credenciales2 = $this->useCase->execute($persona2);

    expect($credenciales2['name'])->toBe('carlos.lopez1');
    expect($credenciales2['email'])->toBe('carlos.lopez1@'.$this->domain);
});

it('usa X como apellido por defecto si la persona no tiene personaNatural', function () {
    $persona = Persona::factory()->create([
        'primer_nombre' => 'Test',
    ]);

    $credenciales = $this->useCase->execute($persona);

    expect($credenciales['name'])->toBe('test.x');
    expect($credenciales['email'])->toBe('test.x@'.$this->domain);
});
