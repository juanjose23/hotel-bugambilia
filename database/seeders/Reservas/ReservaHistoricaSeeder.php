<?php

declare(strict_types=1);

namespace Database\Seeders\Reservas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\EstadoFolioFactura;
use App\Enums\Facturacion\TipoFactura;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoHuesped;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaDetalle;
use App\Repository\Models\Facturacion\FacturaFolio;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaBitacora;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\Reservas\ReservaServicio;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Sembrador de 10.000 reservas históricas distribuidas a lo largo de un año
 * completo, respetando la capacidad real de las habitaciones (sin doble reserva
 * sobre el mismo recurso) y materializando toda la cadena operativa:
 *
 * Reserva → Detalle/Recurso → Huésped → Historial → Estancia → Cuenta con
 * cargos de habitación (hospedaje, restaurante, minibar, lavandería, servicios)
 * → Pagos → Venta → Facturación DGI (serie A/B con folio y desglose fiscal).
 *
 * Un ~2% corresponde a cancelaciones y no-shows con su bitácora, replicando la
 * lógica de ReservaExcepcionSeeder dentro del año histórico.
 */
final class ReservaHistoricaSeeder extends Seeder
{
    private const TOTAL_RESERVAS = 10000;

    private const DIAS_HISTORICOS = 365;

    private const TASA_CAMBIO = 36.50;

    private const IVA = 0.15;

    private ?int $adminId = null;

    private ?FacturaSerie $serieA = null;

    private ?FacturaSerie $serieB = null;

    private ?FacturaAutorizacionDgi $authA = null;

    private ?FacturaAutorizacionDgi $authB = null;

    /** @var Collection<int, Cliente> */
    private Collection $clientes;

    /** @var Collection<int, Habitacion> */
    private Collection $habitaciones;

    /** @var Collection<int, Servicio> */
    private Collection $servicios;

    /** @var array<string, RecursoReservable> */
    private array $recursos = [];

    public function run(): void
    {
        mt_srand(8000286);

        $this->adminId = (User::query()->where('email', 'admin@hotel.com')->first() ?? User::query()->first())?->id;

        $monedaUsd = Moneda::query()->where('codigo', 'USD')->first()
            ?? Moneda::query()->where('codigo', 'NIO')->first()
            ?? Moneda::query()->first();

        $monedaNio = Moneda::query()->where('codigo', 'NIO')->first() ?? $monedaUsd;

        $this->clientes = Cliente::query()->with('persona')->get();
        $this->habitaciones = Habitacion::query()->where('estado', 1)->orderBy('numero')->get();
        $this->servicios = Servicio::query()->get();

        if (! $monedaUsd || ! $monedaNio || $this->clientes->isEmpty() || $this->habitaciones->isEmpty()) {
            return;
        }

        $this->serieA = FacturaSerie::query()->where('codigo', 'A')->first();
        $this->serieB = FacturaSerie::query()->where('codigo', 'B')->first() ?? $this->serieA;

        $this->authA = $this->serieA
            ? FacturaAutorizacionDgi::query()->where('factura_serie_id', $this->serieA->id)->first()
            : null;
        $this->authB = $this->serieB
            ? FacturaAutorizacionDgi::query()->where('factura_serie_id', $this->serieB->id)->first()
            : $this->authA;

        DB::disableQueryLog();

        $creadas = $this->programarAnioCompleto($monedaUsd, $monedaNio);

        $this->command->info("Ciclo histórico completo: {$creadas} reservas en el último año.");
    }

    private function programarAnioCompleto(Moneda $monedaUsd, Moneda $monedaNio): int
    {
        $tamanoBloque = 500;

        DB::beginTransaction();

        $inicio = Carbon::today()->subDays(self::DIAS_HISTORICOS - 1)->startOfDay();

        $habitaciones = $this->habitaciones->values();
        $habitacionesCount = $habitaciones->count();

        $porHabitacion = intdiv(self::TOTAL_RESERVAS, $habitacionesCount);
        $sobrante = self::TOTAL_RESERVAS - ($porHabitacion * $habitacionesCount);

        $secuencia = 0;
        $pendientesEnBloque = 0;

        foreach ($habitaciones as $habitacionIndex => $habitacion) {
            if ($secuencia >= self::TOTAL_RESERVAS) {
                break;
            }

            $estadias = $porHabitacion + ($habitacionIndex < $sobrante ? 1 : 0);
            $margenInicio = $habitacionIndex % 25;

            $longitudes = $this->longitudesPara($estadias);
            $diasUtiles = self::DIAS_HISTORICOS - 1 - $margenInicio;
            $diasLibres = $diasUtiles - array_sum($longitudes);

            // Garantiza huecos mínimos de limpieza entre estadías.
            while ($diasLibres < max(0, $estadias - 1)) {
                $maxIndex = null;
                $maxLongitud = -1;
                foreach ($longitudes as $candIndex => $cand) {
                    if ($cand > $maxLongitud) {
                        $maxLongitud = $cand;
                        $maxIndex = $candIndex;
                    }
                }

                if ($maxIndex === null || $longitudes[$maxIndex] <= 1) {
                    break;
                }
                $longitudes[$maxIndex] -= 1;
                $diasLibres += 1;
            }

            $gaps = $this->repartirGaps($diasLibres, max(0, $estadias - 1));

            $cursor = $inicio->copy()->addDays($margenInicio);

            foreach ($longitudes as $index => $noches) {
                if ($secuencia >= self::TOTAL_RESERVAS) {
                    break 2;
                }

                $entrada = $cursor->copy();
                $salida = $entrada->copy()->addDays($noches);

                if ($salida->greaterThan(Carbon::today())) {
                    break;
                }

                $secuencia++;
                $cliente = $this->clientes->get(($secuencia * 7 + $habitacionIndex * 3) % $this->clientes->count());
                if ($cliente === null) {
                    break;
                }

                $escenario = $this->escenario();
                if ($escenario === 'CANCELADA' || $escenario === 'NO_SHOW') {
                    $this->crearExcepcion($secuencia, $habitacion, $cliente, $entrada, $noches, $escenario, $monedaUsd);
                } else {
                    $this->crearEstadiaCompleta($secuencia, $habitacion, $cliente, $entrada, $noches, $monedaUsd, $monedaNio);
                }

                $pendientesEnBloque++;
                if ($pendientesEnBloque >= $tamanoBloque) {
                    DB::commit();
                    $this->command->info("  bloque confirmado: {$secuencia} / ".self::TOTAL_RESERVAS);
                    DB::beginTransaction();
                    $pendientesEnBloque = 0;
                }

                if ($secuencia % 1000 === 0) {
                    $this->command->info("  progreso: {$secuencia} / ".self::TOTAL_RESERVAS);
                }

                $cursor = $salida;
                if ($index < count($gaps)) {
                    $cursor = $cursor->addDays($gaps[$index]);
                }
            }
        }

        if (DB::transactionLevel() > 0) {
            DB::commit();
        }

        return $secuencia;
    }

    /**
     * @return array<int, int>
     */
    private function longitudesPara(int $cantidad): array
    {
        $longitudes = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $longitudes[] = $this->longitudPorPeso();
        }

        return $longitudes;
    }

    private function longitudPorPeso(): int
    {
        $peso = mt_rand(1, 100);

        if ($peso <= 28) {
            return 1;
        }
        if ($peso <= 60) {
            return 2;
        }
        if ($peso <= 82) {
            return 3;
        }
        if ($peso <= 92) {
            return 4;
        }
        if ($peso <= 97) {
            return 5;
        }
        if ($peso <= 99) {
            return 6;
        }

        return 7;
    }

    /**
     * @return array<int, int>
     */
    private function repartirGaps(int $diasLibres, int $cantidad): array
    {
        if ($cantidad <= 0) {
            return [];
        }

        $base = intdiv($diasLibres, $cantidad);
        $resto = $diasLibres - ($base * $cantidad);

        $gaps = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $gaps[] = $base + ($i < $resto ? 1 : 0);
        }

        return $gaps;
    }

    private function escenario(): string
    {
        $peso = mt_rand(1, 100);

        if ($peso <= 97) {
            return 'COMPLETADA';
        }
        if ($peso <= 98) {
            return 'CANCELADA';
        }

        return 'NO_SHOW';
    }

    private function crearEstadiaCompleta(
        int $secuencia,
        Habitacion $habitacion,
        Cliente $cliente,
        Carbon $entrada,
        int $noches,
        Moneda $monedaUsd,
        Moneda $monedaNio,
    ): void {
        $salida = $entrada->copy()->addDays($noches);

        $tarifaNoche = (float) ($habitacion->precio_base ?? 85.00);
        if ($tarifaNoche <= 0) {
            $tarifaNoche = 85.00;
        }

        $adultos = mt_rand(1, 2);
        $ninos = mt_rand(1, 5) === 1 ? mt_rand(1, 2) : 0;

        $subtotalHospedaje = round($tarifaNoche * $noches, 2);

        $esVip = $cliente->catalogo_id !== 1;
        $montoDescuento = ($esVip || $secuencia % 4 === 0) ? round($subtotalHospedaje * 0.10, 2) : 0.00;

        // Cargos de habitación: restaurante, minibar y lavandería según consumo
        $cargos = $this->cargosDeHabitacion($secuencia, $habitacion, $subtotalHospedaje);

        // Servicio adicional ligado a la reserva (~30%)
        $servicioExtra = ($secuencia % 3 === 0 && $this->servicios->isNotEmpty())
            ? $this->servicios->get($secuencia % $this->servicios->count())
            : null;
        if ($servicioExtra) {
            $costoServicio = (float) ($servicioExtra->precio_base ?? 25.00);
            if ($costoServicio <= 0) {
                $costoServicio = 25.00;
            }
            $cargos[] = [
                'concepto' => $servicioExtra->nombre,
                'cantidad' => 1,
                'precio_unitario' => $costoServicio,
                'subtotal' => $costoServicio,
                'servicio_id' => $servicioExtra->id,
            ];
        }

        $subtotalCargos = array_sum(array_column($cargos, 'subtotal'));
        $subtotalTotal = round($subtotalHospedaje + $subtotalCargos, 2);
        $baseGravable = max(0, $subtotalTotal - $montoDescuento);
        $iva = round($baseGravable * self::IVA, 2);
        $totalReserva = round($baseGravable + $iva, 2);

        $codigoReserva = 'RES-HIST-'.str_pad((string) $secuencia, 7, '0', STR_PAD_LEFT);
        $diasAnticipacion = mt_rand(2, 45);
        $fechaReservaAt = $entrada->copy()->subDays($diasAnticipacion);

        $reserva = Reserva::query()->updateOrCreate(
            ['codigo_reserva' => $codigoReserva],
            [
                'cliente_id' => $cliente->id,
                'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Cliente Hotel Bugambilias',
                'telefono_cliente' => $cliente->persona->telefono ?? '+505 8888-0000',
                'email_cliente' => "cliente_{$cliente->id}@hotel.com",
                'tipo_reserva' => TipoReserva::HABITACION,
                'habitacion_id' => $habitacion->id,
                'fecha_check_in' => $entrada->toDateString(),
                'fecha_check_out' => $salida->toDateString(),
                'hora_reserva' => '14:00',
                'adultos' => $adultos,
                'ninos' => $ninos,
                'solicita_cuenta' => true,
                'limite_cuenta_solicitado' => 600.00,
                'estado' => EstadoReserva::CHECKED_OUT,
                'subtotal' => $subtotalTotal,
                'descuento' => $montoDescuento,
                'total' => $totalReserva,
                'moneda_id' => $monedaUsd->id,
                'tipo_pago' => TipoPagoReserva::PAGO_COMPLETO,
                'total_pagado' => $totalReserva,
                'saldo' => 0.00,
                'notas' => 'Estadía histórica completada y facturada con cargos de habitación.',
                'created_at' => $fechaReservaAt,
                'updated_at' => $fechaReservaAt,
            ]
        );

        if (! $reserva->wasRecentlyCreated) {
            return;
        }

        $recurso = $this->recursoDeHabitacion($habitacion);
        $detalle = $this->crearDetalleHospedaje($reserva, $recurso, $cliente, $entrada, $salida, $tarifaNoche, $subtotalHospedaje, $adultos, $ninos);
        $this->crearHuespedTitular($reserva, $detalle, $cliente);

        if ($servicioExtra) {
            ReservaServicio::query()->create([
                'reserva_id' => $reserva->id,
                'servicio_id' => $servicioExtra->id,
                'cantidad' => 1,
                'precio' => $costoServicio,
            ]);
        }

        ReservaEstadoHistorial::query()->create([
            'reserva_id' => $reserva->id,
            'estado_anterior' => EstadoReserva::CONFIRMADA,
            'estado_nuevo' => EstadoReserva::CHECKED_OUT,
            'usuario_id' => $this->adminId,
            'motivo' => 'Check-out completado y folio liquidado en caja.',
            'created_at' => $salida->copy()->setTime(12, 0),
        ]);

        $estancia = Estancia::query()->create([
            'reserva_id' => $reserva->id,
            'reserva_detalle_id' => $detalle->id,
            'habitacion_id' => $habitacion->id,
            'usuario_check_in_id' => $this->adminId,
            'usuario_check_out_id' => $this->adminId,
            'check_in_at' => $entrada->copy()->setTime(14, 0),
            'check_out_at' => $salida->copy()->setTime(11, 30),
            'fecha_entrada_programada' => $entrada->copy()->setTime(14, 0),
            'fecha_salida_programada' => $salida->copy()->setTime(12, 0),
            'fecha_check_in_real' => $entrada->copy()->setTime(14, 15),
            'fecha_check_out_real' => $salida->copy()->setTime(11, 30),
            'cantidad_llaves' => 2,
            'estado' => EstadoEstancia::FINALIZADA,
            'observaciones_entrada' => 'Check-in regular realizado con entrega de 2 llaves.',
            'observaciones_salida' => 'Check-out realizado satisfactoriamente. Habitación entregada en orden.',
            'created_at' => $fechaReservaAt,
            'updated_at' => $salida->copy()->setTime(11, 30),
        ]);

        $numeroCuenta = 'CTA-H-'.str_pad((string) $secuencia, 7, '0', STR_PAD_LEFT);
        $cuenta = Cuenta::query()->create([
            'numero_cuenta' => $numeroCuenta,
            'tipo_cuenta' => TipoCuenta::ESTANCIA,
            'estado' => EstadoCuenta::CERRADA,
            'cliente_id' => $cliente->id,
            'estancia_id' => $estancia->id,
            'reserva_id' => $reserva->id,
            'moneda_id' => $monedaUsd->id,
            'limite_autorizado' => 600.00,
            'subtotal' => $subtotalTotal,
            'descuento_total' => $montoDescuento,
            'impuesto_total' => $iva,
            'cargo_servicio_total' => 0.00,
            'propina_total' => 0.00,
            'recargo_total' => 0.00,
            'total' => $totalReserva,
            'total_pagado' => $totalReserva,
            'saldo' => 0.00,
            'abierta_at' => $entrada->copy()->setTime(14, 15),
            'cerrada_at' => $salida->copy()->setTime(11, 30),
            'abierta_por' => $this->adminId,
            'cerrada_por' => $this->adminId,
        ]);

        $this->crearRenglonesCuenta($cuenta, $estancia, $cargos, $subtotalHospedaje, $tarifaNoche, $noches, $habitacion);
        $this->crearPagos($cuenta, $codigoReserva, $totalReserva, $fechaReservaAt, $salida, $secuencia, $monedaUsd);
        $this->crearVentaYFactura($secuencia, $cliente, $reserva, $estancia, $cuenta, $cargos, $subtotalHospedaje, $tarifaNoche, $noches, $habitacion, $subtotalTotal, $montoDescuento, $iva, $totalReserva, $salida, $fechaReservaAt, $monedaUsd, $monedaNio);
    }

    /**
     * @return array<int, array{
     *     concepto: string,
     *     cantidad: int,
     *     precio_unitario: float,
     *     subtotal: float,
     *     servicio_id: int|null,
     * }>
     */
    private function cargosDeHabitacion(int $secuencia, Habitacion $habitacion, float $subtotalHospedaje): array
    {
        $cargos = [];

        // Consumo de Restaurante / Room Service cargado a la habitación (~55%)
        if ($secuencia % 2 === 1 || $secuencia % 7 === 0) {
            $monto = round(25.00 + (($secuencia * 3) % 4500) / 100, 2);
            $cargos[] = [
                'concepto' => "Cargo de Restaurante / Room Service - Hab. {$habitacion->numero}",
                'cantidad' => 1,
                'precio_unitario' => $monto,
                'subtotal' => $monto,
                'servicio_id' => null,
            ];
        }

        // Consumo de Minibar (~20%)
        if ($secuencia % 5 === 0) {
            $items = mt_rand(1, 4);
            $monto = round(8.00 + (($secuencia * 7) % 900) / 100, 2);
            $cargos[] = [
                'concepto' => "Consumo de Minibar ({$items} ítems) - Hab. {$habitacion->numero}",
                'cantidad' => $items,
                'precio_unitario' => round($monto / $items, 2),
                'subtotal' => $monto,
                'servicio_id' => null,
            ];
        }

        // Servicio de Lavandería (~15%)
        if ($secuencia % 6 === 0) {
            $prendas = mt_rand(2, 5);
            $monto = round(6.00 + (($secuencia * 11) % 600) / 100, 2);
            $cargos[] = [
                'concepto' => "Servicio de Lavandería ({$prendas} prendas) - Hab. {$habitacion->numero}",
                'cantidad' => $prendas,
                'precio_unitario' => round($monto / $prendas, 2),
                'subtotal' => $monto,
                'servicio_id' => null,
            ];
        }

        return $cargos;
    }

    private function recursoDeHabitacion(Habitacion $habitacion): RecursoReservable
    {
        $clave = 'HAB-'.$habitacion->id;

        if (! isset($this->recursos[$clave])) {
            $recurso = RecursoReservable::query()->firstOrCreate(
                ['nombre' => "Habitación {$habitacion->numero}", 'tipo' => TipoRecursoReservable::HABITACION],
                [
                    'capacidad' => 3,
                    'control_disponibilidad' => ControlDisponibilidad::FECHAS,
                    'duracion_minutos' => 1440,
                    'estado' => EstadoRecursoReservable::ACTIVO,
                ],
            );

            $this->recursos[$clave] = $recurso;
        }

        if (! $habitacion->reservable_id) {
            $habitacion->updateQuietly(['reservable_id' => $this->recursos[$clave]->id]);
        }

        return $this->recursos[$clave];
    }

    private function crearDetalleHospedaje(
        Reserva $reserva,
        RecursoReservable $recurso,
        Cliente $cliente,
        Carbon $entrada,
        Carbon $salida,
        float $tarifaNoche,
        float $subtotalHospedaje,
        int $adultos,
        int $ninos,
    ): ReservaDetalle {
        return ReservaDetalle::query()->create([
            'reserva_id' => $reserva->id,
            'reservable_id' => $recurso->id,
            'fecha_inicio' => $entrada->toDateString().' 14:00:00',
            'fecha_fin' => $salida->toDateString().' 11:00:00',
            'cantidad' => 1,
            'adultos' => $adultos,
            'ninos' => $ninos,
            'precio_unitario' => $tarifaNoche,
            'subtotal' => $subtotalHospedaje,
            'descuento' => 0.00,
            'impuestos' => round($subtotalHospedaje * self::IVA, 2),
            'estado' => EstadoReservaDetalle::COMPLETADO,
        ]);
    }

    private function crearHuespedTitular(Reserva $reserva, ReservaDetalle $detalle, Cliente $cliente): void
    {
        ReservaHuesped::query()->create([
            'reserva_detalle_id' => $detalle->id,
            'es_titular' => true,
            'nombre' => $reserva->nombre_cliente,
            'identificacion' => $cliente->persona->numero_identificacion ?? '001-010190-0001A',
            'tipo_huesped' => TipoHuesped::ADULTO,
            'email' => $reserva->email_cliente,
            'telefono' => $reserva->telefono_cliente,
        ]);
    }

    /**
     * @param  array<int, array{
     *     concepto: string,
     *     cantidad: int,
     *     precio_unitario: float,
     *     subtotal: float,
     *     servicio_id: int|null,
     * }>  $cargos
     */
    private function crearRenglonesCuenta(
        Cuenta $cuenta,
        Estancia $estancia,
        array $cargos,
        float $subtotalHospedaje,
        float $tarifaNoche,
        int $noches,
        Habitacion $habitacion,
    ): void {
        CuentaDetalle::query()->create([
            'cuenta_id' => $cuenta->id,
            'concepto' => "Hospedaje {$noches} noches - Hab. {$habitacion->numero}",
            'origen_type' => Estancia::class,
            'origen_id' => $estancia->id,
            'cantidad' => $noches,
            'precio_unitario' => $tarifaNoche,
            'subtotal' => $subtotalHospedaje,
            'total' => $subtotalHospedaje,
            'estado' => 1,
            'creador_id' => $this->adminId,
        ]);

        foreach ($cargos as $cargo) {
            CuentaDetalle::query()->create([
                'cuenta_id' => $cuenta->id,
                'concepto' => $cargo['concepto'],
                'origen_type' => $cargo['servicio_id'] !== null ? Servicio::class : Estancia::class,
                'origen_id' => $cargo['servicio_id'] ?? $estancia->id,
                'cantidad' => $cargo['cantidad'],
                'precio_unitario' => $cargo['precio_unitario'],
                'subtotal' => $cargo['subtotal'],
                'total' => $cargo['subtotal'],
                'estado' => 1,
                'creador_id' => $this->adminId,
            ]);
        }
    }

    private function crearPagos(
        Cuenta $cuenta,
        string $codigoReserva,
        float $totalReserva,
        Carbon $fechaReservaAt,
        Carbon $salida,
        int $secuencia,
        Moneda $monedaUsd,
    ): void {
        $anticipo = round($totalReserva / 2, 2);
        $liquidacion = round($totalReserva - $anticipo, 2);

        PagoCuenta::query()->create([
            'cuenta_id' => $cuenta->id,
            'forma_pago' => ($secuencia % 2 === 0) ? MetodoPago::TARJETA_CREDITO : MetodoPago::TRANSFERENCIA,
            'moneda_id' => $monedaUsd->id,
            'estado' => EstadoPago::APLICADO,
            'monto' => $anticipo,
            'propina' => 0.00,
            'referencia_transaccion' => "DEP-{$codigoReserva}",
            'observaciones' => 'Anticipo 50% de garantía de reserva.',
            'usuario_id' => $this->adminId,
            'created_at' => $fechaReservaAt,
        ]);

        PagoCuenta::query()->create([
            'cuenta_id' => $cuenta->id,
            'forma_pago' => ($secuencia % 3 === 0) ? MetodoPago::EFECTIVO : MetodoPago::TARJETA_CREDITO,
            'moneda_id' => $monedaUsd->id,
            'estado' => EstadoPago::APLICADO,
            'monto' => $liquidacion,
            'propina' => 0.00,
            'referencia_transaccion' => "LIQ-{$codigoReserva}",
            'observaciones' => 'Liquidación de saldo total al check-out.',
            'usuario_id' => $this->adminId,
            'created_at' => $salida->copy()->setTime(11, 25),
        ]);
    }

    /**
     * @param  array<int, array{
     *     concepto: string,
     *     cantidad: int,
     *     precio_unitario: float,
     *     subtotal: float,
     *     servicio_id: int|null,
     * }>  $cargos
     */
    private function crearVentaYFactura(
        int $secuencia,
        Cliente $cliente,
        Reserva $reserva,
        Estancia $estancia,
        Cuenta $cuenta,
        array $cargos,
        float $subtotalHospedaje,
        float $tarifaNoche,
        int $noches,
        Habitacion $habitacion,
        float $subtotalTotal,
        float $montoDescuento,
        float $iva,
        float $totalReserva,
        Carbon $salida,
        Carbon $fechaReservaAt,
        Moneda $monedaUsd,
        Moneda $monedaNio,
    ): void {
        $venta = Venta::query()->create([
            'numero_venta' => 'VNT-H-'.str_pad((string) $secuencia, 7, '0', STR_PAD_LEFT),
            'cuenta_id' => $cuenta->id,
            'cliente_id' => $cliente->id,
            'moneda_id' => $monedaUsd->id,
            'subtotal' => $subtotalTotal,
            'descuento_total' => $montoDescuento,
            'impuesto_total' => $iva,
            'servicio_total' => 0.00,
            'propina_total' => 0.00,
            'recargo_total' => 0.00,
            'total' => $totalReserva,
            'estado' => EstadoVenta::Emitida,
            'datos_fiscales' => [
                'ruc' => $cliente->persona->numero_identificacion ?? 'J031000000000',
                'razon_social' => $reserva->nombre_cliente,
                'tipo_comprobante' => 'factura',
            ],
            'creada_por' => $this->adminId,
            'created_at' => $salida->copy()->setTime(11, 30),
        ]);

        $conceptoHospedaje = "Hospedaje {$noches} noches - Hab. {$habitacion->numero}";
        $ventaDetalle = VentaDetalle::query()->create([
            'venta_id' => $venta->id,
            'concepto' => $conceptoHospedaje,
            'cantidad' => $noches,
            'precio_unitario' => $tarifaNoche,
            'subtotal' => $subtotalHospedaje,
            'descuento' => $montoDescuento,
            'impuesto' => round($subtotalHospedaje * self::IVA, 2),
            'total_linea' => $subtotalHospedaje,
            'origen_type' => Estancia::class,
            'origen_id' => $estancia->id,
        ]);

        $lineasVentaFactura = [[
            'concepto' => $conceptoHospedaje,
            'venta_detalle_id' => $ventaDetalle->id,
            'cantidad' => $noches,
            'precio_unitario' => $tarifaNoche,
            'subtotal' => $subtotalHospedaje,
            'descuento' => $montoDescuento,
        ]];

        foreach ($cargos as $cargo) {
            $detalleVenta = VentaDetalle::query()->create([
                'venta_id' => $venta->id,
                'concepto' => $cargo['concepto'],
                'cantidad' => $cargo['cantidad'],
                'precio_unitario' => $cargo['precio_unitario'],
                'subtotal' => $cargo['subtotal'],
                'descuento' => 0.00,
                'impuesto' => round($cargo['subtotal'] * self::IVA, 2),
                'total_linea' => $cargo['subtotal'],
                'origen_type' => Estancia::class,
                'origen_id' => $estancia->id,
            ]);

            $lineasVentaFactura[] = [
                'concepto' => $cargo['concepto'],
                'venta_detalle_id' => $detalleVenta->id,
                'cantidad' => $cargo['cantidad'],
                'precio_unitario' => $cargo['precio_unitario'],
                'subtotal' => $cargo['subtotal'],
                'descuento' => 0.00,
            ];
        }

        $serie = ($secuencia % 2 === 0) ? $this->serieA : $this->serieB;
        $autorizacion = ($secuencia % 2 === 0) ? $this->authA : $this->authB;

        if ($serie === null || $autorizacion === null) {
            return;
        }

        $correlativo = (int) $serie->siguiente_numero;
        $rangoHasta = (int) $autorizacion->rango_hasta;
        if ($correlativo > $rangoHasta) {
            return;
        }

        $numeroFactura = $serie->codigo.'-'.str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT);
        $emitidoAt = $salida->copy()->setTime(11, 35);

        $folio = FacturaFolio::query()->create([
            'factura_serie_id' => $serie->id,
            'factura_autorizacion_dgi_id' => $autorizacion->id,
            'numero_correlativo' => $correlativo,
            'numero' => $numeroFactura,
            'estado' => EstadoFolioFactura::Emitido,
            'emitido_at' => $emitidoAt,
            'created_at' => $emitidoAt,
        ]);

        $serie->update(['siguiente_numero' => $correlativo + 1]);

        $factura = Factura::query()->create([
            'venta_id' => $venta->id,
            'factura_serie_id' => $serie->id,
            'factura_autorizacion_dgi_id' => $autorizacion->id,
            'cuenta_id' => $cuenta->id,
            'cliente_id' => $cliente->id,
            'tipo' => TipoFactura::Contado,
            'estado' => EstadoFactura::Emitida,
            'numero' => $numeroFactura,
            'numero_correlativo' => $correlativo,
            'fecha_emision' => $emitidoAt,
            'moneda_id' => $monedaUsd->id,
            'moneda_base_id' => $monedaNio->id,
            'tasa_cambio' => self::TASA_CAMBIO,
            'subtotal' => $subtotalTotal,
            'descuento_total' => $montoDescuento,
            'iva_total' => $iva,
            'servicio_total' => 0.00,
            'propina_total' => 0.00,
            'recargo_total' => 0.00,
            'total' => $totalReserva,
            'subtotal_base' => round($subtotalTotal * self::TASA_CAMBIO, 2),
            'iva_total_base' => round($iva * self::TASA_CAMBIO, 2),
            'total_base' => round($totalReserva * self::TASA_CAMBIO, 2),
            'datos_receptor' => [
                'nombre' => $reserva->nombre_cliente,
                'identificacion' => $cliente->persona->numero_identificacion ?? '001-010190-0001A',
                'email' => $reserva->email_cliente,
                'direccion' => 'Nicaragua',
            ],
            'numero_autorizacion_dgi' => $autorizacion->numero_autorizacion,
            'rango_autorizado_desde' => (int) $autorizacion->rango_desde,
            'rango_autorizado_hasta' => $rangoHasta,
            'hash_documento' => hash('sha256', $numeroFactura.'|'.$venta->id.'|'.$emitidoAt->toISOString()),
            'emitida_por' => $this->adminId,
            'created_at' => $emitidoAt,
        ]);

        $folio->update(['factura_id' => $factura->id, 'updated_at' => $emitidoAt]);

        foreach ($lineasVentaFactura as $linea) {
            FacturaDetalle::query()->create([
                'factura_id' => $factura->id,
                'concepto' => $linea['concepto'],
                'venta_detalle_id' => $linea['venta_detalle_id'],
                'cantidad' => $linea['cantidad'],
                'precio_unitario' => $linea['precio_unitario'],
                'subtotal' => $linea['subtotal'],
                'descuento' => $linea['descuento'],
                'iva_porcentaje' => 15.00,
                'iva' => round($linea['subtotal'] * self::IVA, 2),
                'total_linea' => $linea['subtotal'],
            ]);
        }
    }

    private function crearExcepcion(
        int $secuencia,
        Habitacion $habitacion,
        Cliente $cliente,
        Carbon $entrada,
        int $noches,
        string $tipo,
        Moneda $monedaUsd,
    ): void {
        $salida = $entrada->copy()->addDays($noches);
        $tarifa = (float) ($habitacion->precio_base ?? 85.00);
        if ($tarifa <= 0) {
            $tarifa = 85.00;
        }
        $subtotal = round($tarifa * $noches, 2);
        $total = round($subtotal * 1.15, 2);

        $esNoShow = $tipo === 'NO_SHOW';
        $estado = $esNoShow ? EstadoReserva::NO_SHOW : EstadoReserva::CANCELADA;
        $anticipoRetenido = $esNoShow ? round($total * 0.50, 2) : 0.00;

        $codigoReserva = 'RES-HIST-'.str_pad((string) $secuencia, 7, '0', STR_PAD_LEFT);

        $reserva = Reserva::query()->updateOrCreate(
            ['codigo_reserva' => $codigoReserva],
            [
                'cliente_id' => $cliente->id,
                'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Cliente Hotel Bugambilias',
                'telefono_cliente' => $cliente->persona->telefono ?? '+505 8888-0000',
                'email_cliente' => "cliente_{$cliente->id}@hotel.com",
                'tipo_reserva' => TipoReserva::HABITACION,
                'habitacion_id' => $habitacion->id,
                'fecha_check_in' => $entrada->toDateString(),
                'fecha_check_out' => $salida->toDateString(),
                'hora_reserva' => '14:00',
                'adultos' => 1,
                'ninos' => 0,
                'solicita_cuenta' => false,
                'limite_cuenta_solicitado' => 0.00,
                'estado' => $estado,
                'subtotal' => $subtotal,
                'descuento' => 0.00,
                'total' => $total,
                'moneda_id' => $monedaUsd->id,
                'tipo_pago' => TipoPagoReserva::SIN_PAGO,
                'total_pagado' => $anticipoRetenido,
                'saldo' => 0.00,
                'notas' => $esNoShow
                    ? 'Huésped no se presentó en la fecha convenida. Depósito de 1 noche retenido.'
                    : 'Reserva cancelada por el cliente conforme a la política de cancelación.',
            ]
        );

        if (! $reserva->wasRecentlyCreated) {
            return;
        }

        $recurso = $this->recursoDeHabitacion($habitacion);

        $detalle = ReservaDetalle::query()->create([
            'reserva_id' => $reserva->id,
            'reservable_id' => $recurso->id,
            'fecha_inicio' => $entrada->toDateString().' 15:00:00',
            'fecha_fin' => $salida->toDateString().' 12:00:00',
            'cantidad' => 1,
            'adultos' => 1,
            'ninos' => 0,
            'precio_unitario' => $tarifa,
            'subtotal' => $subtotal,
            'descuento' => 0.00,
            'impuestos' => round($subtotal * self::IVA, 2),
            'estado' => EstadoReservaDetalle::CANCELADO,
        ]);

        $this->crearHuespedTitular($reserva, $detalle, $cliente);

        ReservaEstadoHistorial::query()->create([
            'reserva_id' => $reserva->id,
            'estado_anterior' => EstadoReserva::CONFIRMADA,
            'estado_nuevo' => $estado,
            'usuario_id' => $this->adminId,
            'motivo' => $esNoShow
                ? 'Cierre nocturno automático: Huésped no se presentó.'
                : 'Cancelación voluntaria conforme a política flexible.',
            'created_at' => $esNoShow ? $entrada->copy()->setTime(23, 59) : $entrada->copy()->subDays(3),
        ]);

        ReservaBitacora::query()->create([
            'reserva_id' => $reserva->id,
            'tipo' => $esNoShow ? 'NO_SHOW_DECLARADO' : 'CANCELACION_PROCESADA',
            'datos' => [
                'descripcion' => $esNoShow
                    ? "No-show declarado tras finalizar el día {$entrada->toDateString()}."
                    : 'Cancelación procesada sin penalidad según política del hotel.',
                'usuario_id' => $this->adminId,
                'ip' => '127.0.0.1',
            ],
        ]);
    }
}
