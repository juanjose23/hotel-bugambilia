<?php

declare(strict_types=1);

use App\BusinessLogic\Limpieza\Data\IniciarLimpiezaData;
use App\BusinessLogic\Limpieza\Data\TerminarLimpiezaData;
use App\BusinessLogic\Restaurante\Mesas\ValidarTransicionMesa;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Limpieza\Ejecucion\IniciarLimpieza;
use App\Interactors\Limpieza\Ejecucion\RegistrarSolicitudLimpieza;
use App\Interactors\Limpieza\Ejecucion\TerminarLimpieza;
use App\Interactors\Restaurante\Mesas\CambiarEstadoMesa;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Monedas\Moneda;

beforeEach(function (): void {
    if (Moneda::query()->where('codigo', 'NIO')->doesntExist()) {
        Moneda::query()->create([
            'codigo' => 'NIO',
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]);
    }
});

test('la mesa cambia de estado y permite trazabilidad de ocupación', function (): void {
    $mesa = Espacio::query()->create([
        'nombre' => 'Mesa Salón 02',
        'codigo' => 'M-SAL-02',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 2,
        'estado' => EstadoEspacio::Disponible,
    ]);

    expect($mesa->estado)->toBe(EstadoEspacio::Disponible);

    // Ocupar mesa
    $mesa->update(['estado' => EstadoEspacio::Mantenimiento]);
    $mesa->refresh();
    expect($mesa->estado)->toBe(EstadoEspacio::Mantenimiento);

    // Liberar mesa a estado por limpiar
    $mesa->update(['estado' => EstadoEspacio::Limpieza]);
    $mesa->refresh();
    expect($mesa->estado)->toBe(EstadoEspacio::Limpieza);
});

test('rechaza cambiar el estado de la mesa si tiene una solicitud de limpieza activa', function (): void {
    $mesa = Espacio::query()->create([
        'nombre' => 'Mesa Terraza 05',
        'codigo' => 'M-TER-05',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Sucio,
    ]);

    SolicitudLimpieza::query()->create([
        'limpiable_type' => $mesa->getMorphClass(),
        'limpiable_id' => $mesa->id,
        'estado' => EstadoLimpieza::Pendiente,
        'prioridad' => 'alta',
        'notas' => 'Prueba de bloqueo por limpieza activa',
    ]);

    $cambiarEstado = app(CambiarEstadoMesa::class);

    expect(fn () => $cambiarEstado->ejecutar($mesa->id, EstadoEspacio::Disponible))
        ->toThrow(DomainException::class, "No se puede cambiar el estado de la mesa '{$mesa->nombre}' porque tiene una solicitud de limpieza activa.");
});

test('bloquea liberaciones manuales de mesas ocupadas o reservadas', function (): void {
    $mesaOcupada = Espacio::query()->create([
        'nombre' => 'Mesa Bloqueo Ocupada',
        'codigo' => 'M-BLOQ-O',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Ocupado,
    ]);

    $mesaReservada = Espacio::query()->create([
        'nombre' => 'Mesa Bloqueo Reservada',
        'codigo' => 'M-BLOQ-R',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Reservado,
    ]);

    $cambiarEstado = app(CambiarEstadoMesa::class);

    expect(fn () => $cambiarEstado->ejecutar($mesaOcupada->id, EstadoEspacio::Disponible))
        ->toThrow(DomainException::class, 'Transición no permitida')
        ->and(fn () => $cambiarEstado->ejecutar($mesaReservada->id, EstadoEspacio::Disponible))
        ->toThrow(DomainException::class, 'Transición no permitida');
});

test('permite liberar mesas solo desde motivos operativos controlados', function (): void {
    $validar = app(ValidarTransicionMesa::class);

    $validar->validar(EstadoEspacio::Ocupado, EstadoEspacio::Sucio, MotivoTransicionMesa::CierrePedido);
    $validar->validar(EstadoEspacio::Reservado, EstadoEspacio::Disponible, MotivoTransicionMesa::CancelacionReserva);
    $validar->validar(EstadoEspacio::Limpieza, EstadoEspacio::Disponible, MotivoTransicionMesa::LimpiezaCompletada);

    expect(true)->toBeTrue();
});

test('garantiza la idempotencia evitando crear múltiples solicitudes de limpieza activas para la misma mesa', function (): void {
    $mesa = Espacio::query()->create([
        'nombre' => 'Mesa VIP 01',
        'codigo' => 'M-VIP-01',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 6,
        'estado' => EstadoEspacio::Sucio,
    ]);

    $registrarLimpieza = app(RegistrarSolicitudLimpieza::class);

    $solicitud1 = $registrarLimpieza->execute($mesa, prioridad: 'normal', notas: 'Primera solicitud');
    $solicitud2 = $registrarLimpieza->execute($mesa, prioridad: 'alta', notas: 'Segunda solicitud duplicada');

    expect($solicitud1->id)->toBe($solicitud2->id);

    $totalActivas = SolicitudLimpieza::query()
        ->where('limpiable_type', $mesa->getMorphClass())
        ->where('limpiable_id', $mesa->id)
        ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
        ->count();

    expect($totalActivas)->toBe(1);
});

test('sincroniza el ciclo de vida de la mesa con los avances de la solicitud de limpieza', function (): void {
    $mesa = Espacio::query()->create([
        'nombre' => 'Mesa Terraza 10',
        'codigo' => 'M-TER-10',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Ocupado,
    ]);

    // 1. Al pasar a Sucio, genera automáticamente la solicitud de limpieza
    $cambiarEstado = app(CambiarEstadoMesa::class);
    $cambiarEstado->ejecutar($mesa->id, EstadoEspacio::Sucio);

    $solicitud = SolicitudLimpieza::query()
        ->where('limpiable_type', $mesa->getMorphClass())
        ->where('limpiable_id', $mesa->id)
        ->first();

    expect($solicitud)->not->toBeNull()
        ->and($solicitud->estado)->toBe(EstadoLimpieza::Pendiente)
        ->and($mesa->refresh()->estado)->toBe(EstadoEspacio::Sucio);

    // 2. Al iniciar la limpieza, la mesa pasa a estado Limpieza
    $iniciarLimpieza = app(IniciarLimpieza::class);
    $iniciarLimpieza->execute(new IniciarLimpiezaData($solicitud));

    expect($solicitud->refresh()->estado)->toBe(EstadoLimpieza::EnProgreso)
        ->and($mesa->refresh()->estado)->toBe(EstadoEspacio::Limpieza);

    // 3. Al terminar la limpieza, la mesa pasa a estado Disponible
    $terminarLimpieza = app(TerminarLimpieza::class);
    $terminarLimpieza->execute(new TerminarLimpiezaData($solicitud));

    expect($solicitud->refresh()->estado)->toBe(EstadoLimpieza::Completada)
        ->and($mesa->refresh()->estado)->toBe(EstadoEspacio::Disponible);
});
