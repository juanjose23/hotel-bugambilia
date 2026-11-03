<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Mesas;

use App\Actions\Restaurante\ConstruirUrlWhatsapp;
use App\BusinessLogic\Restaurante\Mesas\ResolverUnionMesasAuto;
use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\Facturacion\PasarelaCodigo;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Interactors\Restaurante\Pedidos\RegistrarClienteRapido;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Restaurante\Mesas\ObtenerMesaQuery;
use App\Repository\Queries\Restaurante\Mesas\ObtenerMesasActivasQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPreordenPublicaQuery;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class CrearReservaMesaPublica
{
    public function __construct(
        private CrearReserva $crearReserva,
        private RegistrarClienteRapido $registrarClienteRapido,
        private ObtenerMesasDisponibles $obtenerMesasDisponibles,
        private ObtenerMesaQuery $obtenerMesa,
        private ConstruirUrlWhatsapp $construirUrlWhatsapp,
        private ObtenerPreordenPublicaQuery $preordenPublica,
        private ObtenerMesasActivasQuery $mesasActivas,
        private ResolverUnionMesasAuto $unionMesas,
        private VerificarRestauranteActivo $restauranteActivo,
    ) {}

    /**
     * @param array{
     *     nombre_cliente: string,
     *     telefono_cliente: string,
     *     email_cliente?: string|null,
     *     fecha: string,
     *     hora: string,
     *     duracion_horas?: int|null,
     *     adultos: int,
     *     espacio_id?: int|null,
     *     tipo_pago_reserva?: string|null,
     *     canal_pago_reserva?: string|null,
     *     promocion_id?: int|null,
     *     items_preorden?: array<int, array{plato_id: int, cantidad: int, observaciones?: ?string}>,
     *     notas?: string|null
     * } $datos
     * @return array{
     *     success: bool,
     *     reserva_id: int,
     *     codigo_reserva: string,
     *     fecha: string,
     *     hora: string,
     *     mesa_nombre: string,
     *     mesas_unidas: array<int, string>,
     *     requiere_pago_stripe: bool,
     *     stripe_pago: ?array<string, mixed>,
     *     whatsapp_url: string,
     *     resumen: ?array<string, mixed>,
     *     url_detalle: string,
     *     mensaje: string
     * }
     */
    public function ejecutar(array $datos): array
    {
        if (! $this->restauranteActivo->estaHabilitadoWeb()) {
            throw new DomainException('El restaurante no está disponible para reservaciones.');
        }
        $comensales = max(1, (int) $datos['adultos']);
        $duracion = max(1, (int) ($datos['duracion_horas'] ?? 1));
        $fecha = $datos['fecha'];
        $hora = $datos['hora'];

        return DB::transaction(function () use ($datos, $comensales, $duracion, $fecha, $hora): array {
            // 1. Resolver o registrar el cliente
            $cliente = $this->resolverCliente($datos['nombre_cliente'], $datos['telefono_cliente']);

            // 2. Consultar mesas disponibles en ese horario
            $disponibilidad = $this->obtenerMesasDisponibles->ejecutar(
                fecha: $fecha,
                hora: $hora,
                duracionHoras: $duracion,
                comensales: $comensales,
            );

            if ($disponibilidad['mesas_disponibles'] === []) {
                throw new DomainException("No hay mesas disponibles en Restaurante Bugambilias para el día {$fecha} a las {$hora}. Por favor selecciona otro horario.");
            }

            // 3. Determinar mesa principal y mesas adicionales para unir
            $mesaPrincipalId = $datos['espacio_id'] ?? ($disponibilidad['mesa_asignada']['id'] ?? $disponibilidad['mesas_disponibles'][0]['id']);
            $mesa = $this->obtenerMesa->porId((int) $mesaPrincipalId);

            if ($mesa === null) {
                throw new ModelNotFoundException('Mesa no encontrada para la reserva.');
            }

            $idsDisponibles = array_column($disponibilidad['mesas_disponibles'], 'id');
            if (! in_array($mesa->id, $idsDisponibles, true)) {
                throw new DomainException('La mesa seleccionada ya no está disponible. Elige otra mesa u horario.');
            }
            $libres = $this->mesasActivas->ejecutar()->whereIn('id', $idsDisponibles);
            $adicionales = $this->unionMesas->resolver($mesa, $comensales, $libres);
            $capacidad = (int) $mesa->capacidad_personas + $libres->whereIn('id', $adicionales)->sum(fn (Espacio $secundaria): int => (int) $secundaria->capacidad_personas);
            if ($capacidad < $comensales) {
                throw new DomainException('No hay capacidad suficiente en mesas compatibles para este grupo. Selecciona otro horario.');
            }
            $espaciosAdicionales = array_map(static fn (int $id): array => ['espacio_id' => $id, 'cantidad' => 1], $adicionales);
            $nombresMesasUnidas = [(string) $mesa->nombre];
            foreach ($libres->whereIn('id', $adicionales) as $adicional) {
                $nombresMesasUnidas[] = (string) $adicional->nombre;
            }
            $preorden = $this->preordenPublica->ejecutar($datos['items_preorden'] ?? []);

            $tipoPago = $datos['tipo_pago_reserva'] ?? TipoPagoReserva::SIN_PAGO->value;

            // 4. Invocar el interactor CrearReserva
            $resultado = $this->crearReserva->ejecutarConPasarela(
                datos: [
                    'nombre_cliente' => $datos['nombre_cliente'],
                    'telefono_cliente' => $datos['telefono_cliente'],
                    'email_cliente' => $datos['email_cliente'] ?? null,
                    'tipo_reserva' => TipoReserva::RESTAURANTE->value,
                    'origen_pago_reserva' => 'publico',
                    'espacio_id' => $mesa->id,
                    'fecha_check_in' => $fecha,
                    'fecha_check_out' => $fecha,
                    'hora_reserva' => $hora,
                    'duracion_horas' => $duracion,
                    'adultos' => $comensales,
                    'ninos' => 0,
                    'tipo_pago_reserva' => $tipoPago,
                    'canal_pago_reserva' => $datos['canal_pago_reserva'] ?? ($tipoPago === TipoPagoReserva::SIN_PAGO->value ? null : PasarelaCodigo::Stripe->value),
                    'items_preorden' => $preorden,
                    'promocion_id' => $datos['promocion_id'] ?? null,
                    'notas' => $datos['notas'] ?? "Reserva de mesa para {$comensales} personas.",
                ],
                serviciosAdicionales: [],
                espaciosAdicionales: $espaciosAdicionales,
                clienteId: (int) $cliente->id,
            );

            /** @var Reserva $reserva */
            $reserva = $resultado->reserva;

            // 5. Construir mensaje de WhatsApp
            $whatsappUrl = $this->construirUrlWhatsappReserva(
                reserva: $reserva,
                mesas: $nombresMesasUnidas,
                fecha: $fecha,
                hora: $hora,
                comensales: $comensales,
                nombre: $datos['nombre_cliente'],
            );

            return [
                'success' => true,
                'reserva_id' => (int) $reserva->id,
                'codigo_reserva' => (string) $reserva->codigo_reserva,
                'fecha' => $fecha,
                'hora' => $hora,
                'mesa_nombre' => (string) $mesa->nombre,
                'mesas_unidas' => $nombresMesasUnidas,
                'requiere_pago_stripe' => $resultado->requierePagoStripe,
                'stripe_pago' => $resultado->stripePago,
                'whatsapp_url' => $whatsappUrl,
                'resumen' => $reserva->ultimaEntradaBitacora('resumen_restaurante'),
                'url_detalle' => route('portal.reservas.show', ['id' => $reserva->id, 'codigo' => $reserva->codigo_reserva]),
                'mensaje' => "¡Reserva #{$reserva->codigo_reserva} creada exitosamente para {$comensales} personas!",
            ];
        });
    }

    private function resolverCliente(string $nombre, string $telefono): Cliente
    {
        $usuario = Auth::user();
        $clienteAuth = $usuario?->persona?->cliente;

        if ($clienteAuth instanceof Cliente) {
            return $clienteAuth;
        }

        $partes = explode(' ', trim($nombre), 2);
        $primerNombre = $partes[0];
        $primerApellido = $partes[1] ?? '';

        return $this->registrarClienteRapido->ejecutar([
            'primer_nombre' => $primerNombre,
            'primer_apellido' => $primerApellido,
            'telefono' => $telefono,
            'tipo_persona' => 'natural',
        ]);
    }

    /**
     * @param  array<int, string>  $mesas
     */
    private function construirUrlWhatsappReserva(
        Reserva $reserva,
        array $mesas,
        string $fecha,
        string $hora,
        int $comensales,
        string $nombre,
    ): string {
        $mesasTexto = implode(' + ', $mesas);

        $lineas = [
            '*¡Nueva Reservación de Mesa — Hotel Bugambilias!* 🍷',
            "🔖 *Código de Reserva:* #{$reserva->codigo_reserva}",
            '',
            "👤 *Cliente:* {$nombre}",
            "📅 *Fecha:* {$fecha}",
            "⏰ *Hora:* {$hora}",
            "👥 *Comensales:* {$comensales} personas",
            "🪑 *Mesa(s) Asignada(s):* {$mesasTexto}",
            '',
            'Por favor confirmen la disponibilidad y preparación de nuestra mesa. ¡Muchas gracias!',
        ];

        return $this->construirUrlWhatsapp->ejecutar($lineas);
    }
}
