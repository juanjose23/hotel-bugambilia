<?php

declare(strict_types=1);

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User;

uses(LazilyRefreshDatabase::class);

test('la ruta auth.google.redirect redirige hacia Google OAuth', function () {
    Config::set('services.google.client_id', 'test-client-id');
    Config::set('services.google.client_secret', 'test-client-secret');
    Config::set('services.google.redirect', 'http://localhost:8000/auth/google/callback');

    $response = $this->get('/auth/google/redirect');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('accounts.google.com');
});

test('el callback de google crea nuevo usuario y autentica correctamente', function () {
    $tipo = CatalogoTipo::create([
        'codigo' => 'tipo_cliente',
        'nombre' => 'Tipo de Cliente',
    ]);

    Catalogo::create([
        'codigo' => 'cliente_regular',
        'nombre' => 'Cliente Regular',
        'catalogo_tipo_id' => $tipo->id,
    ]);

    Config::set('services.google.client_id', 'test-client-id');
    Config::set('services.google.client_secret', 'test-client-secret');
    Config::set('services.google.redirect', 'http://localhost:8000/auth/google/callback');

    $abstractUser = Mockery::mock(User::class);
    $abstractUser->shouldReceive('getId')->andReturn('google-123456');
    $abstractUser->shouldReceive('getEmail')->andReturn('huesped.prueba@gmail.com');
    $abstractUser->shouldReceive('getName')->andReturn('Juan Perez');
    $abstractUser->shouldReceive('getNickname')->andReturn('juanp');
    $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($abstractUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('home'));
    $this->assertAuthenticated();
});
