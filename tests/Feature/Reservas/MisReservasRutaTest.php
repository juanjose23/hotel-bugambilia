<?php

declare(strict_types=1);

use App\Repository\Models\User;

test('la ruta /mis-reservas es accesible para usuarios no autenticados', function (): void {
    $this->get('/mis-reservas')
        ->assertStatus(200);
});

test('la ruta /reservas/mis-reservas funciona como alias accesible para guests', function (): void {
    $this->get('/reservas/mis-reservas')
        ->assertStatus(200);
});

test('la ruta /mis-reservas es accesible para usuarios autenticados', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/mis-reservas')
        ->assertStatus(200);
});

test('el portal responde en la raiz del subdominio dedicado portal.localhost', function (): void {
    $this->get('http://portal.localhost/')
        ->assertStatus(200);
});

test('el portal responde en /mis-reservas en el subdominio dedicado portal.localhost', function (): void {
    $this->get('http://portal.localhost/mis-reservas')
        ->assertStatus(200);
});
