<?php

declare(strict_types=1);

test('la ruta /mis-reservas redirige al listado unificado de reservas del portal', function (): void {
    $this->get('/mis-reservas')
        ->assertRedirect(route('portal.reservas.index'));
});

test('la ruta /reservas/mis-reservas funciona como alias redirigiendo al portal', function (): void {
    $this->get('/reservas/mis-reservas')
        ->assertRedirect(route('portal.reservas.index'));
});

test('la ruta /mis-reservas con codigo redirige manteniendo los parametros al portal', function (): void {
    $this->get('/mis-reservas?codigo=RES-1234')
        ->assertRedirect(route('portal.reservas.index', ['codigo' => 'RES-1234']));
});
