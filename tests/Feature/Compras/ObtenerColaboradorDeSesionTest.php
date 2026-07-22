<?php

use App\Models\Colaboradores\Colaborador;
use App\Models\Personas\Persona;
use App\Models\User;
use App\Repository\Queries\Compras\Shared\ObtenerColaboradorDeSesion;

beforeEach(function () {
    $this->persona = Persona::factory()->create();
    $this->colaborador = Colaborador::factory()->create([
        'persona_id' => $this->persona->id,
    ]);
    $this->user = User::factory()->create([
        'persona_id' => $this->persona->id,
    ]);

    $this->actingAs($this->user);
});

describe('ObtenerColaboradorDeSesion', function () {
    it('retorna el colaborador asociado al usuario autenticado', function () {
        $colaborador = app(ObtenerColaboradorDeSesion::class)->ejecutar();

        expect($colaborador)->toBeInstanceOf(Colaborador::class);
        expect($colaborador->id)->toBe($this->colaborador->id);
        expect($colaborador->persona_id)->toBe($this->persona->id);
    });

    it('lanza excepcion cuando el usuario no tiene persona_id', function () {
        $userSinPersona = User::factory()->create(['persona_id' => null]);
        $this->actingAs($userSinPersona);

        app(ObtenerColaboradorDeSesion::class)->ejecutar();
    })->throws(RuntimeException::class, 'El usuario actual no tiene un colaborador asignado.');

    it('lanza excepcion cuando la persona no tiene colaborador', function () {
        $personaSinColaborador = Persona::factory()->create();
        $userSinColaborador = User::factory()->create([
            'persona_id' => $personaSinColaborador->id,
        ]);
        $this->actingAs($userSinColaborador);

        app(ObtenerColaboradorDeSesion::class)->ejecutar();
    })->throws(RuntimeException::class, 'El usuario actual no tiene un colaborador asignado.');
});
