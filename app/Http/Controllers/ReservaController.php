<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Voucher\GenerarVoucherPDF;
use App\Enums\Reservas\EstadoReserva;
use App\Exceptions\StripeApiException;
use App\Http\Requests\Reservas\CrearReservaRequest;
use App\Interactors\Reservas\Gestion\CancelarReserva;
use App\Interactors\Reservas\Gestion\CrearReservaPublica;
use App\Repository\Models\Reservas\Reserva;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReservaController extends Controller
{
    public function __construct(
        private readonly CrearReservaPublica $crearReservaPublica,
        private readonly CancelarReserva $cancelarReserva,
    ) {}

    public function crear(CrearReservaRequest $request): JsonResponse|RedirectResponse
    {
        $datos = $request->validated();
        $servicios = is_array($datos['servicios_adicionales'] ?? null) ? $datos['servicios_adicionales'] : [];
        $espacios = is_array($datos['espacios_adicionales'] ?? null) ? $datos['espacios_adicionales'] : [];

        try {
            $resultado = $this->crearReservaPublica->ejecutar(
                datos: $datos,
                servicios: $servicios,
                espacios: $espacios,
                clienteId: $request->user()?->persona?->cliente?->id,
            );
            $reserva = $resultado['reserva'];

            if ($request->expectsJson()) {
                return $this->construirRespuestaJson($reserva, $resultado);
            }

            $destino = $this->resolverDestinoRedirect($resultado, $request->user());
            $codigo = $reserva->codigo_reserva;

            return redirect($destino)
                ->with('exito', "Reserva creada correctamente. Código de confirmación: {$codigo}.");
        } catch (StripeApiException $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No se pudo conectar con Stripe. No se creo la reserva.',
                    'error' => 'stripe_api_error',
                    'details' => app()->hasDebugModeEnabled() ? $exception->details() : null,
                ], 502);
            }

            return back()->withErrors(['error' => 'No se pudo procesar el pago con Stripe. Intente nuevamente.']);
        } catch (DomainException|InvalidArgumentException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Construye la respuesta JSON con los datos de la reserva y Stripe.
     *
     * @param  array{reserva: Reserva, requiere_pago_stripe: bool, stripe_pago: array<string, mixed>|null}  $resultado
     */
    private function construirRespuestaJson(Reserva $reserva, array $resultado): JsonResponse
    {
        $stripePago = $resultado['stripe_pago'];

        $stripeData = $stripePago !== null ? [
            'client_secret' => $stripePago['client_secret'],
            'publishable_key' => $stripePago['publishable_key'],
            'transaccion_id' => $stripePago['transaccion']->id, // @phpstan-ignore property.nonObject (Stripe object)
            'monto' => $stripePago['monto'],
            'moneda' => $stripePago['moneda'],
        ] : null;

        return response()->json([
            'message' => "Reserva creada correctamente. Código de confirmación: {$reserva->codigo_reserva}.",
            'reserva' => [
                'id' => $reserva->id,
                'codigo_reserva' => $reserva->codigo_reserva,
                'tipo_pago' => $reserva->tipo_pago->value,
            ],
            'requiere_pago_stripe' => $resultado['requiere_pago_stripe'],
            'stripe_pago' => $stripeData,
        ]);
    }

    /**
     * Resuelve la URL de destino para el redirect post-creación.
     *
     * @param  array{reserva: Reserva, requiere_pago_stripe: bool}  $resultado
     */
    private function resolverDestinoRedirect(array $resultado, ?object $usuario): string
    {
        $reserva = $resultado['reserva'];

        if ($resultado['requiere_pago_stripe'] === true) {
            return route('reservas.pago', ['reserva' => $reserva, 'codigo' => $reserva->codigo_reserva]);
        }

        if ($usuario !== null) {
            return route('mis-reservas', ['codigo' => $reserva->codigo_reserva]);
        }

        return route('home');
    }

    public function cancelar(Request $request, Reserva $reserva): RedirectResponse
    {
        $this->authorize('cancel', $reserva);

        try {
            $this->cancelarReserva->ejecutar($reserva, $request->user()?->id);
        } catch (DomainException $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        return back()->with('exito', 'La reserva ha sido cancelada correctamente.');
    }

    public function voucher(Request $request, Reserva $reserva, GenerarVoucherPDF $action): StreamedResponse
    {
        $codigoParam = $request->string('codigo')->toString();
        $esValidoPorCodigo = $codigoParam !== '' && $codigoParam === $reserva->codigo_reserva;
        $usuario = $request->user();
        $esAutorizadoUsuario = $usuario !== null && $usuario->can('view', $reserva);

        if (! $esValidoPorCodigo && ! $esAutorizadoUsuario) {
            $this->authorize('view', $reserva);
        }

        abort_if(in_array($reserva->estado, [
            EstadoReserva::CANCELADA,
            EstadoReserva::NO_SHOW,
        ], true), 404);

        return $action->ejecutar($reserva);
    }
}
