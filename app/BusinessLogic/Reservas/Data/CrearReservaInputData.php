<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final readonly class CrearReservaInputData
{
    /**
     * @param  list<ServicioAdicionalItemData>  $serviciosAdicionales
     * @param  list<EspacioAdicionalItemData>  $espaciosAdicionales
     * @param  list<HabitacionAdicionalItemData>  $habitacionesAdicionales
     * @param  list<PreordenItemData>  $itemsPreorden
     * @param  list<RegistrarHuespedData>  $huespedes
     * @param  list<int>  $cargosFacturacionIds
     * @param  array<string, mixed>  $datosOriginales
     */
    public function __construct(
        public TipoReserva $tipo,
        public string $nombreCliente,
        public DateTimeImmutable $checkIn,
        public int $entidadPrincipalId,
        public ?DateTimeImmutable $checkOut = null,
        public ?string $horaReserva = null,
        public int $duracionHoras = 1,
        public int $adultos = 1,
        public int $ninos = 0,
        public ?string $emailCliente = null,
        public ?string $telefonoCliente = null,
        public ?int $clienteId = null,
        public ?int $usuarioId = null,
        public ?int $monedaId = null,
        public ?int $categoriaHabitacionId = null,
        public TipoPagoReserva $tipoPago = TipoPagoReserva::SIN_PAGO,
        public ?string $canalPago = null,
        public ?int $metodoPago = null,
        public ?float $montoPago = null,
        public ?string $referenciaPago = null,
        public string $origenPago = 'admin',
        public bool $cobrarTarifaMesa = false,
        public array $serviciosAdicionales = [],
        public array $espaciosAdicionales = [],
        public array $habitacionesAdicionales = [],
        public array $itemsPreorden = [],
        public array $huespedes = [],
        public array $cargosFacturacionIds = [],
        public ?string $notas = null,
        public array $datosOriginales = [],
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $servicios
     * @param  array<int, mixed>  $espacios
     * @param  array<int, mixed>  $habitaciones
     */
    public static function fromArray(
        array $datos,
        array $servicios = [],
        array $espacios = [],
        array $habitaciones = [],
        ?int $clienteId = null,
        ?int $usuarioId = null,
    ): self {
        $tipoValor = $datos['tipo_reserva'] ?? $datos['tipo'] ?? null;
        $tipo = null;
        if ($tipoValor instanceof TipoReserva) {
            $tipo = $tipoValor;
        } elseif (is_numeric($tipoValor)) {
            $tipo = TipoReserva::tryFrom((int) $tipoValor);
        } elseif (is_string($tipoValor)) {
            $tipo = TipoReserva::tryFrom($tipoValor);
        }

        if ($tipo === null) {
            throw new InvalidArgumentException('El tipo de reserva no es válido.');
        }

        $nombreRaw = $datos['nombre_cliente'] ?? $datos['cliente_nombre'] ?? 'Cliente General';
        $nombreCliente = is_string($nombreRaw) && trim($nombreRaw) !== '' ? trim($nombreRaw) : 'Cliente General';

        $checkInRaw = $datos['fecha_check_in'] ?? $datos['check_in'] ?? null;
        if ($checkInRaw instanceof DateTimeImmutable) {
            $checkIn = $checkInRaw;
        } elseif ($checkInRaw instanceof DateTimeInterface) {
            $checkIn = DateTimeImmutable::createFromInterface($checkInRaw);
        } elseif (is_string($checkInRaw) && trim($checkInRaw) !== '') {
            $checkIn = new DateTimeImmutable(trim($checkInRaw));
        } else {
            throw new InvalidArgumentException('La fecha de check-in es obligatoria.');
        }

        $checkOutRaw = $datos['fecha_check_out'] ?? $datos['check_out'] ?? null;
        $checkOut = null;
        if ($checkOutRaw instanceof DateTimeImmutable) {
            $checkOut = $checkOutRaw;
        } elseif ($checkOutRaw instanceof DateTimeInterface) {
            $checkOut = DateTimeImmutable::createFromInterface($checkOutRaw);
        } elseif (is_string($checkOutRaw) && trim($checkOutRaw) !== '') {
            $checkOut = new DateTimeImmutable(trim($checkOutRaw));
        }

        $horaReserva = isset($datos['hora_reserva']) && is_string($datos['hora_reserva']) && trim($datos['hora_reserva']) !== ''
            ? trim($datos['hora_reserva'])
            : null;

        $duracionHoras = isset($datos['duracion_horas']) && is_numeric($datos['duracion_horas'])
            ? max(1, (int) $datos['duracion_horas'])
            : 1;

        $adultos = isset($datos['adultos']) && is_numeric($datos['adultos'])
            ? max(1, (int) $datos['adultos'])
            : 1;

        $ninos = isset($datos['ninos']) && is_numeric($datos['ninos'])
            ? max(0, (int) $datos['ninos'])
            : 0;

        $habitacionVal = $datos['habitacion_id'] ?? $datos['recurso_id'] ?? null;
        $espacioVal = $datos['espacio_id'] ?? $datos['mesa_id'] ?? null;
        $servicioVal = $datos['servicio_id'] ?? null;
        $paqueteVal = $datos['paquete_id'] ?? null;

        $entidadPrincipalId = match ($tipo) {
            TipoReserva::HABITACION => is_numeric($habitacionVal) ? (int) $habitacionVal : 0,
            TipoReserva::RESTAURANTE => is_numeric($espacioVal) ? (int) $espacioVal : 0,
            TipoReserva::SERVICIO => is_numeric($servicioVal) ? (int) $servicioVal : 0,
            TipoReserva::PAQUETE => is_numeric($habitacionVal)
                ? (int) $habitacionVal
                : (is_numeric($espacioVal)
                    ? (int) $espacioVal
                    : (is_numeric($servicioVal)
                        ? (int) $servicioVal
                        : (is_numeric($paqueteVal) ? (int) $paqueteVal : 0))),
        };

        $categoriaHabitacionId = isset($datos['categoria_habitacion_id']) && is_numeric($datos['categoria_habitacion_id'])
            ? (int) $datos['categoria_habitacion_id']
            : null;

        $tipoPagoValor = $datos['tipo_pago_reserva'] ?? $datos['tipo_pago'] ?? null;
        $tipoPago = $tipoPagoValor instanceof TipoPagoReserva
            ? $tipoPagoValor
            : (is_string($tipoPagoValor) ? TipoPagoReserva::tryFrom($tipoPagoValor) ?? TipoPagoReserva::SIN_PAGO : TipoPagoReserva::SIN_PAGO);

        $canalPago = isset($datos['canal_pago_reserva']) && is_string($datos['canal_pago_reserva'])
            ? trim($datos['canal_pago_reserva'])
            : null;

        $metodoPagoRaw = $datos['metodo_pago_reserva'] ?? $datos['metodo_pago_abono'] ?? $datos['metodo_pago'] ?? null;
        $metodoPago = null;
        if ($metodoPagoRaw instanceof MetodoPago) {
            $metodoPago = $metodoPagoRaw->value;
        } elseif (is_numeric($metodoPagoRaw)) {
            $metodoPago = (int) $metodoPagoRaw;
        }

        $montoPago = isset($datos['monto_pago_reserva']) && is_numeric($datos['monto_pago_reserva'])
            ? (float) $datos['monto_pago_reserva']
            : null;

        $referenciaPago = isset($datos['referencia_pago']) && is_string($datos['referencia_pago']) && trim($datos['referencia_pago']) !== ''
            ? trim($datos['referencia_pago'])
            : null;

        $origenPago = isset($datos['origen_pago_reserva']) && is_string($datos['origen_pago_reserva'])
            ? trim($datos['origen_pago_reserva'])
            : 'admin';

        $cobrarTarifaMesa = isset($datos['cobrar_tarifa_mesa']) ? (bool) $datos['cobrar_tarifa_mesa'] : true;

        // Parsear arrays de adicionales
        $serviciosRaw = $servicios !== [] ? $servicios : (is_array($datos['servicios_adicionales'] ?? null) ? $datos['servicios_adicionales'] : []);
        $espaciosRaw = $espacios !== [] ? $espacios : (is_array($datos['espacios_adicionales'] ?? null) ? $datos['espacios_adicionales'] : []);
        $habitacionesRaw = $habitaciones !== [] ? $habitaciones : (is_array($datos['habitaciones_adicionales'] ?? null) ? $datos['habitaciones_adicionales'] : []);
        $itemsPreordenRaw = is_array($datos['items_preorden'] ?? null) ? $datos['items_preorden'] : [];

        $serviciosAdicionales = [];
        foreach ($serviciosRaw as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $serviciosAdicionales[] = ServicioAdicionalItemData::fromArray($item);
            }
        }

        $espaciosAdicionales = [];
        foreach ($espaciosRaw as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $espaciosAdicionales[] = EspacioAdicionalItemData::fromArray($item);
            }
        }

        $habitacionesAdicionales = [];
        foreach ($habitacionesRaw as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $habitacionesAdicionales[] = HabitacionAdicionalItemData::fromArray($item);
            }
        }

        $itemsPreorden = [];
        foreach ($itemsPreordenRaw as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $itemsPreorden[] = PreordenItemData::fromArray($item);
            }
        }

        $huespedesRaw = is_array($datos['huespedes'] ?? null) ? $datos['huespedes'] : [];
        $huespedes = [];
        foreach ($huespedesRaw as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $huespedes[] = RegistrarHuespedData::fromArray($item);
            }
        }

        /** @var list<int> $cargosFacturacionIds */
        $cargosFacturacionIds = [];
        if (is_array($datos['cargos_facturacion_ids'] ?? null)) {
            foreach ($datos['cargos_facturacion_ids'] as $cargoId) {
                if (is_numeric($cargoId)) {
                    $cargosFacturacionIds[] = (int) $cargoId;
                }
            }
        }

        return new self(
            tipo: $tipo,
            nombreCliente: $nombreCliente,
            checkIn: $checkIn,
            entidadPrincipalId: $entidadPrincipalId,
            checkOut: $checkOut,
            horaReserva: $horaReserva,
            duracionHoras: $duracionHoras,
            adultos: $adultos,
            ninos: $ninos,
            emailCliente: isset($datos['email_cliente']) && is_string($datos['email_cliente']) ? trim($datos['email_cliente']) : null,
            telefonoCliente: isset($datos['telefono_cliente']) && is_string($datos['telefono_cliente']) ? trim($datos['telefono_cliente']) : null,
            clienteId: $clienteId ?? (isset($datos['cliente_id']) && is_numeric($datos['cliente_id']) ? (int) $datos['cliente_id'] : null),
            usuarioId: $usuarioId ?? (isset($datos['usuario_id']) && is_numeric($datos['usuario_id']) ? (int) $datos['usuario_id'] : (auth()->id() !== null ? (int) auth()->id() : null)),
            monedaId: isset($datos['moneda_id']) && is_numeric($datos['moneda_id']) ? (int) $datos['moneda_id'] : null,
            categoriaHabitacionId: $categoriaHabitacionId,
            tipoPago: $tipoPago,
            canalPago: $canalPago,
            metodoPago: $metodoPago,
            montoPago: $montoPago,
            referenciaPago: $referenciaPago,
            origenPago: $origenPago,
            cobrarTarifaMesa: $cobrarTarifaMesa,
            serviciosAdicionales: $serviciosAdicionales,
            espaciosAdicionales: $espaciosAdicionales,
            habitacionesAdicionales: $habitacionesAdicionales,
            itemsPreorden: $itemsPreorden,
            huespedes: $huespedes,
            cargosFacturacionIds: $cargosFacturacionIds,
            notas: isset($datos['notas']) && is_string($datos['notas']) ? trim($datos['notas']) : null,
            datosOriginales: $datos,
        );
    }
}
