<?php

declare(strict_types=1);

namespace App\Interactors\Promociones;

use App\BusinessLogic\Promociones\ValidarPeriodoPromocion;
use App\BusinessLogic\Promociones\ValidarPromocionReserva;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Interactors\Restaurante\Mesas\CrearReservaMesaPublica;
use App\Interactors\Restaurante\Pedidos\RegistrarClienteRapido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Promociones\ObtenerOpcionesReservaPromocionQuery;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class ReservarPromocionPublica
{
    public function __construct(
        private ValidarPromocionReserva $promociones,
        private ObtenerOpcionesReservaPromocionQuery $opciones,
        private CrearReserva $crearReserva,
        private CrearReservaMesaPublica $crearMesa,
        private RegistrarClienteRapido $crearCliente,
        private ValidarPeriodoPromocion $validarPeriodo,
    ) {}

    /**
     * @param  array{opcion: string, nombre_cliente: string, telefono_cliente: string, email_cliente: string, fecha_check_in: string, fecha_check_out?: ?string, hora_reserva: string, adultos: int, duracion_horas: int, tipo_pago_reserva: string, notas?: ?string}  $datos
     * @return array<string, mixed>
     */
    public function ejecutar(int $promocionId, array $datos): array
    {
        return DB::transaction(function () use ($promocionId, $datos): array {
            $promocion = $this->promociones->vigente($promocionId, new DateTimeImmutable((string) $datos['fecha_check_in']), publica: true);
            $opcion = collect($this->opciones->ejecutar($promocion))->firstWhere('key', $datos['opcion']);
            if ($opcion === null) {
                throw new DomainException('La selección ya no está disponible para esta promoción.');
            }
            if ($opcion['necesita_salida'] && empty($datos['fecha_check_out'])) {
                throw new DomainException('Selecciona la fecha de salida de la habitación.');
            }
            $this->validarPeriodo->ejecutar(
                new DateTimeImmutable((string) $datos['fecha_check_in'].' '.(string) $datos['hora_reserva']),
                ! empty($datos['fecha_check_out']) ? new DateTimeImmutable((string) $datos['fecha_check_out']) : null,
                (int) $datos['adultos'],
                is_numeric($promocion->getAttribute('noches_incluidas')) ? (int) $promocion->getAttribute('noches_incluidas') : null,
                is_numeric($promocion->getAttribute('personas_incluidas')) ? (int) $promocion->getAttribute('personas_incluidas') : null,
                $promocion->anticipacion_horas, new DateTimeImmutable(now()->toDateTimeString()),
                $opcion['necesita_salida'] && (float) $promocion->precio_paquete > 0,
            );
            $incluidos = $promocion->items->filter(fn ($i): bool => (bool) ($i->getAttribute('incluido') ?? true));
            $principal = $incluidos->first(fn ($i): bool => (int) $i->item_id === $opcion['recurso_id']
                && (($opcion['tipo'] === 'habitacion' && $i->item instanceof Habitacion)
                    || ($opcion['tipo'] === 'servicio' && $i->item instanceof Servicio)
                    || ($opcion['tipo'] === 'restaurante' && $i->item instanceof Espacio)));
            $esMesa = $opcion['tipo'] === 'restaurante' && ($principal === null
                || ($principal->item instanceof Espacio && in_array($principal->item->tipo, [TipoEspacio::MESA, TipoEspacio::RESTAURANTE], true)));
            if ($esMesa) {
                return $this->crearMesa->ejecutar([
                    'nombre_cliente' => (string) $datos['nombre_cliente'], 'telefono_cliente' => (string) $datos['telefono_cliente'],
                    'email_cliente' => (string) $datos['email_cliente'], 'fecha' => (string) $datos['fecha_check_in'],
                    'hora' => (string) $datos['hora_reserva'], 'duracion_horas' => (int) $datos['duracion_horas'],
                    'adultos' => (int) $datos['adultos'], 'promocion_id' => $promocionId,
                    'espacio_id' => $principal?->item instanceof Espacio && $principal->item->tipo === TipoEspacio::MESA ? $principal->item_id : null,
                    'items_preorden' => $incluidos->filter(fn ($i): bool => $i->item instanceof Plato)
                        ->map(fn ($i): array => ['plato_id' => (int) $i->item_id, 'cantidad' => 1])->values()->all(),
                    'tipo_pago_reserva' => (string) $datos['tipo_pago_reserva'],
                    'canal_pago_reserva' => $datos['tipo_pago_reserva'] === 'sin_pago' ? null : 'stripe',
                    'notas' => is_string($datos['notas'] ?? null) ? $datos['notas'] : null,
                ]);
            }
            $entrada = array_merge($datos, [
                'tipo_reserva' => $opcion['tipo'], 'promocion_id' => $promocionId,
                $opcion['tipo'] === 'habitacion' ? 'habitacion_id' : ($opcion['tipo'] === 'servicio' ? 'servicio_id' : 'espacio_id') => $opcion['recurso_id'],
                'origen_pago_reserva' => 'publico', 'canal_pago_reserva' => $datos['tipo_pago_reserva'] === 'sin_pago' ? 'sin_pago' : 'stripe',
            ]);
            $servicios = [];
            $espacios = [];
            $habitaciones = [];
            foreach ($incluidos as $item) {
                if ($principal !== null && $item->id === $principal->id) {
                    continue;
                }
                if ($item->item instanceof Servicio) {
                    $servicios[] = ['servicio_id' => $item->item_id, 'cantidad' => 1];
                } elseif ($item->item instanceof Espacio) {
                    $espacios[] = ['espacio_id' => $item->item_id, 'cantidad' => 1];
                } elseif ($item->item instanceof Habitacion) {
                    $habitaciones[] = ['habitacion_id' => $item->item_id, 'cantidad' => 1];
                }
            }
            $entrada['servicios_adicionales'] = $servicios;
            $entrada['espacios_adicionales'] = $espacios;
            $entrada['habitaciones_adicionales'] = $habitaciones;
            $entrada['items_preorden'] = $incluidos->filter(fn ($i): bool => $i->item instanceof Plato)
                ->map(fn ($i): array => ['plato_id' => (int) $i->item_id, 'cantidad' => 1])->values()->all();
            $cliente = Auth::user()?->persona?->cliente;
            if ($cliente === null) {
                $partes = explode(' ', trim((string) $datos['nombre_cliente']), 2);
                $cliente = $this->crearCliente->ejecutar(['primer_nombre' => $partes[0], 'primer_apellido' => $partes[1] ?? '',
                    'telefono' => (string) $datos['telefono_cliente']]);
            }
            $resultado = $this->crearReserva->ejecutarConPasarela($entrada, $servicios, $espacios, $habitaciones, $cliente->id);
            $reserva = $resultado->reserva;

            return ['success' => true, 'reserva_id' => $reserva->id, 'codigo_reserva' => $reserva->codigo_reserva,
                'requiere_pago_stripe' => $resultado->requierePagoStripe,
                'url_detalle' => route('portal.reservas.show', ['id' => $reserva->id, 'codigo' => $reserva->codigo_reserva]),
                'url_pago' => route('reservas.pago', ['reserva' => $reserva->id, 'codigo' => $reserva->codigo_reserva]),
                'total' => (float) $reserva->total];
        });
    }
}
