<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promociones\ReservarPromocionRequest;
use App\Interactors\Promociones\ReservarPromocionPublica;
use App\Presenters\Promociones\PromocionPresenter;
use App\Repository\Queries\Promociones\ObtenerOpcionesReservaPromocionQuery;
use App\Repository\Queries\Reservas\ObtenerPromocionReservaQuery;
use DomainException;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class ReservarPromocionController extends Controller
{
    public function show(int $promocion, ObtenerPromocionReservaQuery $promociones,
        ObtenerOpcionesReservaPromocionQuery $opciones, PromocionPresenter $presenter): Response
    {
        try {
            $oferta = $promociones->vigente($promocion, publica: true);
        } catch (InvalidArgumentException) {
            abort(404, 'La promoción ya no está disponible.');
        }
        $oferta->loadMissing('imagenes', 'beneficios', 'tipo', 'precios.moneda');

        return Inertia::render('promociones/ReservarPromocion', ['promocion' => $presenter->presentar($oferta),
            'opciones' => $opciones->ejecutar($oferta), 'tarjeta_disponible' => filled(config('services.stripe.key')) && filled(config('services.stripe.secret')) && filled(config('services.stripe.webhook_secret'))]);
    }

    public function store(ReservarPromocionRequest $request, int $promocion, ReservarPromocionPublica $reservar): JsonResponse
    {
        try {
            /** @var array{opcion: string, nombre_cliente: string, telefono_cliente: string, email_cliente: string, fecha_check_in: string, fecha_check_out?: ?string, hora_reserva: string, adultos: int, duracion_horas: int, tipo_pago_reserva: string, notas?: ?string} $datos */
            $datos = $request->validated();
            $resultado = $reservar->ejecutar($promocion, $datos);
            if (! isset($resultado['url_pago'])) {
                $resultado['url_pago'] = route('reservas.pago', ['reserva' => $resultado['reserva_id'], 'codigo' => $resultado['codigo_reserva']]);
            }
            unset($resultado['stripe_pago']);

            return response()->json($resultado, 201);
        } catch (DomainException|InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
