<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::get('/deployment-proxy-check', fn () => response()->json([
        'secure' => request()->isSecure(),
        'url' => url('/admin/login'),
    ]));
});

test('el proxy configurado transmite HTTPS y el dominio público', function (): void {
    config()->set('trustedproxy.proxies', ['127.0.0.1']);

    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->get('/deployment-proxy-check', [
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'hotel.example.invalid',
            'X-Forwarded-Port' => '443',
        ])->assertOk()->assertExactJson([
            'secure' => true,
            'url' => 'https://hotel.example.invalid/admin/login',
        ]);
});

test('un emisor ajeno al proxy no puede falsificar HTTPS ni el dominio', function (): void {
    config()->set('trustedproxy.proxies', ['10.123.0.1']);

    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->get('/deployment-proxy-check', [
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'hotel.example.invalid',
        ])->assertOk()->assertExactJson([
            'secure' => false,
            'url' => 'http://localhost/admin/login',
        ]);
});

test('la ejecución local conserva HTTP cuando no hay proxy configurado', function (): void {
    config()->set('trustedproxy.proxies', []);

    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->get('/deployment-proxy-check', ['X-Forwarded-Proto' => 'https'])
        ->assertOk()->assertJsonPath('secure', false);
});
