<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Builders;

use App\Actions\Reservas\GenerarCodigoReserva;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

final class ReservaBuilder
{
    private ?TipoReserva $tipo = null;

    private ?int $clienteId = null;

    private string $nombreCliente = '';

    private ?string $telefonoCliente = null;

    private ?string $emailCliente = null;

    private ?DateTimeImmutable $checkIn = null;

    private ?DateTimeImmutable $checkOut = null;

    private ?string $horaReserva = null;

    private int $adultos = 1;

    private int $ninos = 0;

    private ?int $habitacionId = null;

    private ?int $espacioId = null;

    private ?int $servicioId = null;

    private ?int $entidadPrincipalId = null;

    private float $precioPrincipal = 0.0;

    private ?DateTimeImmutable $inicioPeriodo = null;

    private ?DateTimeImmutable $finPeriodo = null;

    private int $unidades = 1;

    private ?int $monedaId = null;

    private ?int $promocionId = null;

    private ?string $notas = null;

    private mixed $acompanantes = null;

    /** @var array<int, mixed> */
    private array $huespedes = [];

    /** @var array<int, array{servicio_id: int, cantidad: int, precio: float}> */
    private array $serviciosAdicionales = [];

    /** @var array<int, array{espacio_id: int, cantidad: int, precio: float}> */
    private array $espaciosAdicionales = [];

    /** @var array<int, array{habitacion_id: int, cantidad: int, precio: float}> */
    private array $habitacionesAdicionales = [];

    /** @var array<string, mixed>|null */
    private ?array $resumenRestaurante = null;

    private float $subtotal = 0.0;

    private float $descuento = 0.0;

    private float $total = 0.0;

    /** @var array<string, mixed> */
    private array $datosOriginales = [];

    public static function nuevo(): self
    {
        return new self;
    }

    public function paraTipo(TipoReserva $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function conCliente(?int $clienteId, string $nombre, ?string $telefono = null, ?string $email = null): self
    {
        $this->clienteId = $clienteId !== null && $clienteId > 0 ? $clienteId : null;
        $this->nombreCliente = trim($nombre);
        $this->telefonoCliente = $telefono !== null && trim($telefono) !== '' ? trim($telefono) : null;
        $this->emailCliente = $email !== null && trim($email) !== '' ? trim($email) : null;

        return $this;
    }

    public function conFechas(DateTimeImmutable $checkIn, ?DateTimeImmutable $checkOut = null, ?string $horaReserva = null): self
    {
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
        $this->horaReserva = $horaReserva !== null && trim($horaReserva) !== '' ? trim($horaReserva) : null;

        return $this;
    }

    public function conPeriodoCalculado(DateTimeImmutable $inicio, DateTimeImmutable $fin, int $unidades = 1): self
    {
        $this->inicioPeriodo = $inicio;
        $this->finPeriodo = $fin;
        $this->unidades = max(1, $unidades);

        return $this;
    }

    public function conCapacidad(int $adultos = 1, int $ninos = 0): self
    {
        $this->adultos = max(1, $adultos);
        $this->ninos = max(0, $ninos);

        return $this;
    }

    public function conRecursoPrincipal(
        int $entidadId,
        float $precioPrincipal = 0.0,
        ?int $habitacionId = null,
        ?int $espacioId = null,
        ?int $servicioId = null,
    ): self {
        $this->entidadPrincipalId = $entidadId;
        $this->precioPrincipal = max(0.0, $precioPrincipal);
        $this->habitacionId = $habitacionId ?? ($this->tipo === TipoReserva::HABITACION ? $entidadId : null);
        $this->espacioId = $espacioId ?? ($this->tipo === TipoReserva::RESTAURANTE ? $entidadId : null);
        $this->servicioId = $servicioId ?? ($this->tipo === TipoReserva::SERVICIO ? $entidadId : null);

        return $this;
    }

    public function conMoneda(?int $monedaId): self
    {
        $this->monedaId = $monedaId !== null && $monedaId > 0 ? $monedaId : null;

        return $this;
    }

    public function conPromocion(?int $promocionId): self
    {
        $this->promocionId = $promocionId !== null && $promocionId > 0 ? $promocionId : null;

        return $this;
    }

    public function conNotas(?string $notas): self
    {
        $this->notas = $notas !== null && trim($notas) !== '' ? trim($notas) : null;

        return $this;
    }

    /** @param array<int, mixed> $huespedes */
    public function conHuespedes(array $huespedes): self
    {
        $this->huespedes = $huespedes;

        return $this;
    }

    public function conAcompanantes(mixed $acompanantes): self
    {
        $this->acompanantes = $acompanantes;

        return $this;
    }

    /** @param array<int, array{servicio_id: int, cantidad: int, precio: float}> $servicios */
    public function conServiciosAdicionales(array $servicios): self
    {
        $this->serviciosAdicionales = $servicios;

        return $this;
    }

    /** @param array<int, array{espacio_id: int, cantidad: int, precio: float}> $espacios */
    public function conEspaciosAdicionales(array $espacios): self
    {
        $this->espaciosAdicionales = $espacios;

        return $this;
    }

    /** @param array<int, array{habitacion_id: int, cantidad: int, precio: float}> $habitaciones */
    public function conHabitacionesAdicionales(array $habitaciones): self
    {
        $this->habitacionesAdicionales = $habitaciones;

        return $this;
    }

    /** @param array<string, mixed>|null $resumen */
    public function conResumenRestaurante(?array $resumen): self
    {
        $this->resumenRestaurante = $resumen;

        return $this;
    }

    public function conTotales(float $subtotal, float $descuento, float $total): self
    {
        $this->subtotal = round($subtotal, 2);
        $this->descuento = round($descuento, 2);
        $this->total = round($total, 2);

        return $this;
    }

    /** @param array<string, mixed> $datos */
    public function conDatosOriginales(array $datos): self
    {
        $this->datosOriginales = $datos;

        return $this;
    }

    public function obtenerTipo(): TipoReserva
    {
        if ($this->tipo === null) {
            throw new DomainException('El tipo de reserva no ha sido definido en el Builder.');
        }

        return $this->tipo;
    }

    public function obtenerEntidadPrincipalId(): int
    {
        if ($this->entidadPrincipalId === null) {
            throw new DomainException('La entidad principal no ha sido definida en el Builder.');
        }

        return $this->entidadPrincipalId;
    }

    public function obtenerPrecioPrincipal(): float
    {
        return $this->precioPrincipal;
    }

    public function obtenerCheckIn(): DateTimeImmutable
    {
        if ($this->checkIn === null) {
            throw new DomainException('La fecha de check-in no ha sido definida en el Builder.');
        }

        return $this->checkIn;
    }

    public function obtenerCheckOut(): ?DateTimeImmutable
    {
        return $this->checkOut;
    }

    public function obtenerHoraReserva(): ?string
    {
        return $this->horaReserva;
    }

    public function obtenerInicioPeriodo(): DateTimeImmutable
    {
        return $this->inicioPeriodo ?? $this->obtenerCheckIn();
    }

    public function obtenerFinPeriodo(): DateTimeImmutable
    {
        return $this->finPeriodo ?? ($this->checkOut ?? $this->obtenerCheckIn());
    }

    public function obtenerUnidades(): int
    {
        return $this->unidades;
    }

    public function obtenerSubtotal(): float
    {
        return $this->subtotal;
    }

    public function obtenerDescuento(): float
    {
        return $this->descuento;
    }

    public function obtenerTotal(): float
    {
        return $this->total;
    }

    /** @return array<int, array{servicio_id: int, cantidad: int, precio: float}> */
    public function obtenerServiciosAdicionales(): array
    {
        return $this->serviciosAdicionales;
    }

    /** @return array<int, array{espacio_id: int, cantidad: int, precio: float}> */
    public function obtenerEspaciosAdicionales(): array
    {
        return $this->espaciosAdicionales;
    }

    /** @return array<int, array{habitacion_id: int, cantidad: int, precio: float}> */
    public function obtenerHabitacionesAdicionales(): array
    {
        return $this->habitacionesAdicionales;
    }

    /** @return array<string, mixed>|null */
    public function obtenerResumenRestaurante(): ?array
    {
        return $this->resumenRestaurante;
    }

    /** @return array<string, mixed> */
    public function obtenerDatosOriginales(): array
    {
        return $this->datosOriginales;
    }

    /** @return array<int, mixed> */
    public function obtenerHuespedes(): array
    {
        return $this->huespedes;
    }

    public function obtenerAcompanantes(): mixed
    {
        return $this->acompanantes;
    }

    /**
     * Construye y valida los atributos finales requeridos para persistir el modelo Reserva.
     *
     * @return array<string, mixed>
     */
    public function construirAtributos(
        GenerarCodigoReserva $generarCodigo,
        ObtenerMonedaPredeterminadaQuery $obtenerMoneda,
    ): array {
        if ($this->tipo === null) {
            throw new DomainException('No se puede construir la reserva: tipo de reserva no definido.');
        }

        if (trim($this->nombreCliente) === '') {
            throw new InvalidArgumentException('El nombre del cliente es obligatorio para registrar la reserva.');
        }

        if ($this->checkIn === null) {
            throw new DomainException('No se puede construir la reserva: fecha de check-in requerida.');
        }

        $monedaIdFinal = $this->monedaId ?? $obtenerMoneda->ejecutar()?->id;

        return [
            'codigo_reserva' => $generarCodigo->ejecutar(),
            'cliente_id' => $this->clienteId,
            'nombre_cliente' => $this->nombreCliente,
            'telefono_cliente' => $this->telefonoCliente,
            'email_cliente' => $this->emailCliente,
            'tipo_reserva' => $this->tipo,
            'habitacion_id' => $this->habitacionId,
            'espacio_id' => $this->espacioId,
            'servicio_id' => $this->servicioId,
            'promocion_id' => $this->promocionId,
            'moneda_id' => $monedaIdFinal,
            'fecha_check_in' => $this->checkIn->format('Y-m-d'),
            'fecha_check_out' => $this->checkOut?->format('Y-m-d'),
            'hora_reserva' => $this->horaReserva,
            'adultos' => $this->adultos,
            'ninos' => $this->ninos,
            'subtotal' => $this->subtotal,
            'descuento' => $this->descuento,
            'total' => $this->total,
            'total_pagado' => 0,
            'saldo' => $this->total,
            'estado' => EstadoReserva::CONFIRMADA,
            'notas' => $this->notas,
            'acompanantes' => $this->acompanantes,
        ];
    }
}
