<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

use App\Enums\Reservas\TipoPagoReserva;
use Carbon\CarbonInterface;

final readonly class CrearReservaHabitacionData
{
    /**
     * @param  array<int, int>  $recursosReservablesIds  List of RecursoReservable IDs to reserve
     * @param  array<int, array<int, RegistrarHuespedData>>  $huespedesPorHabitacion  Keyed by index or recurso_id
     */
    public function __construct(
        public string $nombreCliente,
        public CarbonInterface $fechaCheckIn,
        public CarbonInterface $fechaCheckOut,
        public array $recursosReservablesIds,
        public ?int $clienteId = null,
        public ?string $telefonoCliente = null,
        public ?string $emailCliente = null,
        public ?int $monedaId = null,
        public int $adultos = 1,
        public int $ninos = 0,
        public array $huespedesPorHabitacion = [],
        public ?string $notas = null,
        public TipoPagoReserva $tipoPago = TipoPagoReserva::SIN_PAGO,
        public bool $solicitaCuenta = false,
        public ?float $limiteCuentaSolicitado = null,
        public ?int $holdExpiresMinutes = null,
    ) {}
}
