<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

test('los errores de servidor devuelven la página 500 amigable en web', function (): void {
    Route::get('/_test-error-500', fn () => throw new RuntimeException('Error simulado'));

    $this->get('/_test-error-500')
        ->assertStatus(500)
        ->assertSee('Algo no salió como esperábamos')
        ->assertSee('Reintentar');
});

test('los errores 404 devuelven la página amigable en web', function (): void {
    $this->get('/_ruta_inexistente_de_prueba')
        ->assertStatus(404)
        ->assertSee('Página no encontrada')
        ->assertSee('Ir al inicio');
});

test('las peticiones Inertia devuelven el componente de React Error en pantalla completa', function (): void {
    Route::get('/_test-error-inertia', fn () => abort(404, 'Recurso no hallado'));

    $response = $this->withHeaders(['X-Inertia' => 'true'])
        ->get('/_test-error-inertia');

    $response->assertStatus(404);
    $response->assertHeader('X-Inertia', 'true');
    expect($response->json('component'))->toBe('Error')
        ->and($response->json('props.status'))->toBe(404);
});

test('las peticiones JSON conservan el formato JSON de error', function (): void {
    Route::get('/_test-error-json', fn () => throw new RuntimeException('Error simulado'));

    $this->getJson('/_test-error-json')
        ->assertStatus(500)
        ->assertJsonStructure(['message']);
});
