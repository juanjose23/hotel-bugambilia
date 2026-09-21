<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Interactors\Servicios\ObtenerAforoServicio;
use App\Interactors\Servicios\ValidarCupoServicio;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Validation\ValidationException;

function crearServicioConFlota(int $cantidadActivos): Servicio
{
    $servicio = Servicio::create([
        'codigo' => 'SRV-CUPO-'.str()->random(6),
        'nombre' => 'Recorrido Laguna de Apoyo',
        'estado' => 1,
    ]);

    $producto = Producto::factory()->create();

    for ($i = 0; $i < $cantidadActivos; $i++) {
        $activo = Activo::create([
            'codigo_inventario' => 'ACT-CUPO-'.str()->random(8),
            'nombre_descriptivo' => "Vehículo {$i}",
            'producto_id' => $producto->id,
            'fecha_adquisicion' => now()->toDateString(),
            'costo_adquisicion' => 2500.00,
            'estado' => EstadoActivo::Activo,
        ]);

        ActivoAsignacion::create([
            'activo_id' => $activo->id,
            'asignable_type' => Servicio::class,
            'asignable_id' => $servicio->id,
            'fecha_inicio' => now()->toDateString(),
            'estado' => EstadoAsignacion::Vigente,
        ]);
    }

    return $servicio;
}

test('el aforo coincide con la flota fija vigente del servicio', function (): void {
    $servicio = crearServicioConFlota(3);

    expect(app(ObtenerAforoServicio::class)->ejecutar((int) $servicio->id))->toBe(3);
});

test('permite reservar la salida cuando los participantes no exceden el aforo', function (): void {
    $servicio = crearServicioConFlota(2);

    app(ValidarCupoServicio::class)->ejecutar((int) $servicio->id, 2);

    expect(app(ObtenerAforoServicio::class)->ejecutar((int) $servicio->id))->toBe(2);
});

test('rechaza la salida cuando los participantes exceden el aforo del servicio', function (): void {
    $servicio = crearServicioConFlota(2);

    app(ValidarCupoServicio::class)->ejecutar((int) $servicio->id, 3);
})->throws(ValidationException::class, 'cupo del recorrido');

test('bloquea crear una reserva de servicio por encima de su cupo sin persistirla', function (): void {
    $servicio = crearServicioConFlota(2);
    $reservasPrevias = Reserva::query()->count();

    try {
        app(CrearReserva::class)->ejecutar([
            'nombre_cliente' => 'Grupo Turístico',
            'tipo_reserva' => TipoReserva::SERVICIO->value,
            'servicio_id' => $servicio->id,
            'fecha_check_in' => now()->addDay()->toDateString(),
            'adultos' => 5,
        ]);

        $this->fail('Debería haberse lanzado ValidationException por cupo insuficiente.');
    } catch (ValidationException $exception) {
        expect($exception->getMessage())->toContain('cupo del recorrido')
            ->and($exception->getMessage())->toContain('aforo: 2')
            ->and(Reserva::query()->count())->toBe($reservasPrevias);
    }
});
