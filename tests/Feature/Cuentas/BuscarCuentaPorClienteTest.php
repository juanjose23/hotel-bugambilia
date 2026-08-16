<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;

test('busca cuentas por nombres y apellidos almacenados en las tablas reales', function (): void {
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_BUSQUEDA', 'nombre' => 'Tipo Búsqueda', 'estado' => 1]);
    $catalogo = Catalogo::query()->create(['codigo' => 'CAT_BUSQUEDA', 'nombre' => 'Cat Búsqueda', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);

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
    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'estado' => 1,
    ]);
    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-BUSQUEDA-001',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'cliente_id' => $cliente->id,
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
        ->whereHas('cliente.persona', fn ($query) => $query->conNombre('jua'))
        ->get();
    $porNombreCompleto = Cuenta::query()
        ->whereHas('cliente.persona', fn ($query) => $query->conNombre('Juan Pérez'))
        ->get();

    expect($porNombre)->toHaveCount(1)
        ->and($porNombre->first()?->is($cuenta))->toBeTrue()
        ->and($porNombreCompleto)->toHaveCount(1)
        ->and($porNombreCompleto->first()?->is($cuenta))->toBeTrue();
});
