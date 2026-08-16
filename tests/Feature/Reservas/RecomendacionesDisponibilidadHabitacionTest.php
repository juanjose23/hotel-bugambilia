<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\ObtenerDiasAgotadosHabitacionQuery;
use Carbon\CarbonImmutable;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;

beforeEach(function (): void {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);
});

test('recomienda rangos continuos cuando una categoria no tiene disponibilidad exacta', function (): void {
    $categoria = Catalogo::query()->firstOrFail();

    $habitaciones = Habitacion::factory()
        ->count(2)
        ->create([
            'categoria_id' => $categoria->id,
            'estado' => EstadoEspacio::Disponible,
        ]);

    crearDetalleHabitacionOcupada($habitaciones[0], '2026-09-01', '2026-10-01');
    crearDetalleHabitacionOcupada($habitaciones[1], '2026-09-18', '2026-09-21');

    $resultado = app(ObtenerDiasAgotadosHabitacionQuery::class)->recomendarPorCategoria(
        categoriaId: (int) $categoria->id,
        checkIn: CarbonImmutable::parse('2026-09-17'),
        checkOut: CarbonImmutable::parse('2026-09-22'),
        adultos: 1,
        ninos: 0,
        diasBusqueda: 10,
    );

    expect($resultado['disponible'])->toBeFalse()
        ->and($resultado['dias_sin_disponibilidad'])->toContain('2026-09-18')
        ->and($resultado['dias_sin_disponibilidad'])->toContain('2026-09-20')
        ->and($resultado['recomendaciones'])->not->toBeEmpty()
        ->and($resultado['recomendaciones'][0]['fecha_check_in'])->toBe('2026-09-13')
        ->and($resultado['recomendaciones'][0]['fecha_check_out'])->toBe('2026-09-18');
});

function crearDetalleHabitacionOcupada(Habitacion $habitacion, string $checkIn, string $checkOut): void
{
    $recurso = app(ReservaRepositorioInterface::class)->resolverRecurso(TipoReserva::HABITACION, (int) $habitacion->id);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-REC-'.str()->random(8),
        'nombre_cliente' => 'Cliente ocupado',
        'tipo_reserva' => TipoReserva::HABITACION,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => $checkIn,
        'fecha_check_out' => $checkOut,
        'adultos' => 1,
        'estado' => EstadoReserva::CONFIRMADA,
        'total' => 100,
    ]);

    $reserva->detalles()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoReservaDetalle::CONFIRMADO,
        'fecha_inicio' => $checkIn.' 00:00:00',
        'fecha_fin' => $checkOut.' 00:00:00',
        'cantidad' => 1,
        'precio_unitario' => 100,
        'subtotal' => 100,
    ]);
}
