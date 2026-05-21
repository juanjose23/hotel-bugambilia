<?php

use App\Jobs\Inventario\VerificarCaducidadesJob;
use App\UseCases\Inventario\Lotes\Mutations\VerificarCaducidades;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('puede despachar y ejecutar VerificarCaducidadesJob llamando al caso de uso', function () {
    $mockUseCase = mock(VerificarCaducidades::class);
    $mockUseCase->shouldReceive('execute')
        ->once();

    $this->app->instance(VerificarCaducidades::class, $mockUseCase);

    // Despachar el Job de forma síncrona
    VerificarCaducidadesJob::dispatchSync();
});
