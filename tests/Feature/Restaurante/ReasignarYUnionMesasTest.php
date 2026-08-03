<?php

declare(strict_types=1);

use App\BusinessLogic\Restaurante\Mesas\ResolverUnionMesasAuto;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Restaurante\Mesas\ReasignarMesaReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;

test('resolver union auto calcula mesas secundarias necesarias para cubrir exceso de comensales', function (): void {
    $mesaPrincipal = Espacio::query()->create([
        'codigo' => 'M-PRINCIPAL-01',
        'nombre' => 'Mesa 1 (Cap 2)',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
        'meta_datos' => ['capacidad_personas' => 2],
    ]);

    $mesaSecundaria = Espacio::query()->create([
        'codigo' => 'M-SECUNDARIA-02',
        'nombre' => 'Mesa 2 (Cap 4)',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
        'meta_datos' => ['capacidad_personas' => 4],
    ]);

    $mesasDisponibles = collect([$mesaPrincipal, $mesaSecundaria]);

    $resolver = new ResolverUnionMesasAuto;
    $unirIds = $resolver->resolver($mesaPrincipal, 5, $mesasDisponibles);

    expect($unirIds)->toContain($mesaSecundaria->id);
});

test('reasignar mesa reserva transfiere la reserva a una nueva mesa libre y libera la anterior', function (): void {
    $mesaOriginal = Espacio::query()->create([
        'codigo' => 'M-ORIGINAL-01',
        'nombre' => 'Mesa Original',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Ocupado,
    ]);

    $nuevaMesa = Espacio::query()->create([
        'codigo' => 'M-NUEVA-02',
        'nombre' => 'Mesa Nueva',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Disponible,
        'meta_datos' => ['capacidad_personas' => 4],
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-REASIGNAR-001',
        'nombre_cliente' => 'Cliente Reasignado',
        'tipo_reserva' => TipoReserva::RESTAURANTE,
        'espacio_id' => $mesaOriginal->id,
        'fecha_check_in' => now()->toDateString(),
        'adultos' => 2,
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    $interactor = app(ReasignarMesaReserva::class);
    $mesaFinal = $interactor->ejecutar($reserva, $nuevaMesa->id);

    expect($mesaFinal->id)->toBe($nuevaMesa->id)
        ->and($reserva->fresh()?->espacio_id)->toBe($nuevaMesa->id)
        ->and($mesaOriginal->fresh()?->estado)->toBe(EstadoEspacio::Disponible);
});
