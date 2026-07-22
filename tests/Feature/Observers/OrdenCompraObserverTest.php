<?php

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\User;
use Database\Seeders\TasaCambioSeeder;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $seeder = new TasaCambioSeeder;
    $seeder->run();
});

it('registra log al crear una orden de compra', function () {
    Log::spy();

    $orden = OrdenCompra::factory()->create();

    Log::shouldHaveReceived('info')
        ->with("OrdenCompra {$orden->codigo} registrada con éxito.");
});

it('registra log al cambiar el estado de una orden de compra', function () {
    Log::spy();

    $orden = OrdenCompra::factory()->create([
        'estado' => EstadoOrdenCompra::Emitida,
    ]);

    $orden->update(['estado' => EstadoOrdenCompra::Recibida]);

    Log::shouldHaveReceived('info')
        ->with("OrdenCompra {$orden->codigo} cambió su estado a: ".EstadoOrdenCompra::Recibida->value);
});
