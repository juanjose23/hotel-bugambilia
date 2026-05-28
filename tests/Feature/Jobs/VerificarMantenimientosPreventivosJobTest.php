<?php

declare(strict_types=1);

use App\Jobs\Activos\VerificarMantenimientosPreventivosJob;
use App\UseCases\Activos\Mutations\Mantenimiento\DetectarMantenimientosPreventivos;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('puede despachar y ejecutar VerificarMantenimientosPreventivosJob llamando al caso de uso', function () {
    $mockUseCase = mock(DetectarMantenimientosPreventivos::class);
    $mockUseCase->shouldReceive('execute')
        ->once()
        ->andReturn(0);

    $this->app->instance(DetectarMantenimientosPreventivos::class, $mockUseCase);

    VerificarMantenimientosPreventivosJob::dispatchSync();
});
