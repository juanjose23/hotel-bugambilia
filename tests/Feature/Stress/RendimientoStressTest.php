<?php

declare(strict_types=1);

use function Pest\Stressless\stress;

test('el portal publico cumple con umbrales de rendimiento bajo carga con k6', function () {
    $baseUrl = config('app.url', 'http://localhost');

    $result = stress($baseUrl)
        ->concurrently(5)
        ->for(5)
        ->dump();

    expect($result->requests()->failed->count())
        ->toBe(0)
        ->and($result->requests()->duration->med)
        ->toBeLessThan(1500);
})->skip(fn () => @file_get_contents(config('app.url', 'http://localhost')) === false, 'El servidor web local (Herd) no está activo.');
