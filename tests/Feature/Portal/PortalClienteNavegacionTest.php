<?php

declare(strict_types=1);

use App\Repository\Models\User;

test('la ruta de detalle de reserva en el portal responde correctamente', function (): void {
    $response = $this->get('/portal/reserva/1');

    $response->assertStatus(200);
});

test('la ruta de cuenta del cliente responde correctamente', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/portal/cuenta');

    $response->assertRedirect(route('mis-reservas'));
});
