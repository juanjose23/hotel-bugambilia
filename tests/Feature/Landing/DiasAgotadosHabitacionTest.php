<?php

declare(strict_types=1);

use App\Repository\Models\Habitaciones\Habitacion;

test('el endpoint de dias agotados devuelve la estructura json esperada', function () {
    $habitacion = Habitacion::query()->first();

    if (! $habitacion) {
        $habitacion = Habitacion::factory()->create();
    }

    $response = $this->getJson(route('habitaciones.dias-agotados', [
        'slug' => $habitacion->slug,
        'meses' => 6,
    ]));

    $response->assertOk()
        ->assertJsonStructure([
            'total_habitaciones',
            'dias_agotados',
            'ocupacion_por_dia',
            'calendario',
        ]);

    expect($response->json('dias_agotados'))->toBeArray();
});
