<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;

test('busca cuentas por nombres y apellidos almacenados en las tablas reales', function (): void {
    $persona = Persona::query()->create([
        'primer_nombre' => 'Juan',
        'segundo_nombre' => 'Carlos',
        'tipo_persona' => 'natural',
    ]);
    PersonaNatural::query()->create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Pérez',
        'segundo_apellido' => 'López',
    ]);
    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-BUSQUEDA-001',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'cliente_id' => $persona->id,
        'subtotal' => 0,
        'descuento_total' => 0,
        'impuesto_total' => 0,
        'cargo_servicio_total' => 0,
        'propina_total' => 0,
        'recargo_total' => 0,
        'total' => 0,
        'total_pagado' => 0,
        'saldo' => 0,
        'abierta_at' => now(),
    ]);

    $porNombre = Cuenta::query()
        ->whereHas('cliente', fn ($query) => $query->conNombre('jua'))
        ->get();
    $porNombreCompleto = Cuenta::query()
        ->whereHas('cliente', fn ($query) => $query->conNombre('Juan Pérez'))
        ->get();

    expect($porNombre)->toHaveCount(1)
        ->and($porNombre->first()?->is($cuenta))->toBeTrue()
        ->and($porNombreCompleto)->toHaveCount(1)
        ->and($porNombreCompleto->first()?->is($cuenta))->toBeTrue();
});
