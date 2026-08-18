<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\LeerDatoReserva;
use App\BusinessLogic\Reservas\ResolverIdEntidadPrincipal;
use App\Enums\Reservas\TipoReserva;

test('resuelve habitacion_id para tipo HABITACION', function (): void {
    $resolver = new ResolverIdEntidadPrincipal(new LeerDatoReserva);

    $id = $resolver->resolver(TipoReserva::HABITACION, ['habitacion_id' => 15]);

    expect($id)->toBe(15);
});

test('resuelve espacio_id para tipo RESTAURANTE', function (): void {
    $resolver = new ResolverIdEntidadPrincipal(new LeerDatoReserva);

    $id = $resolver->resolver(TipoReserva::RESTAURANTE, ['espacio_id' => 8]);

    expect($id)->toBe(8);
});

test('resuelve servicio_id para tipo SERVICIO', function (): void {
    $resolver = new ResolverIdEntidadPrincipal(new LeerDatoReserva);

    $id = $resolver->resolver(TipoReserva::SERVICIO, ['servicio_id' => 4]);

    expect($id)->toBe(4);
});

test('resuelve entidad principal para PAQUETE priorizando habitacion sobre espacio y servicio', function (): void {
    $resolver = new ResolverIdEntidadPrincipal(new LeerDatoReserva);

    $idHab = $resolver->resolver(TipoReserva::PAQUETE, ['habitacion_id' => 10, 'espacio_id' => 20]);
    expect($idHab)->toBe(10);

    $idEsp = $resolver->resolver(TipoReserva::PAQUETE, ['espacio_id' => 20]);
    expect($idEsp)->toBe(20);

    $idServ = $resolver->resolver(TipoReserva::PAQUETE, ['servicio_id' => 30]);
    expect($idServ)->toBe(30);

    $idPaq = $resolver->resolver(TipoReserva::PAQUETE, ['paquete_id' => 99]);
    expect($idPaq)->toBe(99);

    $idDefault = $resolver->resolver(TipoReserva::PAQUETE, []);
    expect($idDefault)->toBe(0);
});
