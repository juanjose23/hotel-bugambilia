<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurante\CrearReservaMesaPublicaRequest;
use App\Interactors\Restaurante\Mesas\CrearReservaMesaPublica;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class CrearReservaMesaPublicaController extends Controller
{
    public function __invoke(
        CrearReservaMesaPublicaRequest $request,
        CrearReservaMesaPublica $interactor,
    ): JsonResponse {
        $validated = $request->validated();

        try {
            /** @var array{nombre_cliente: string, telefono_cliente: string, email_cliente?: ?string, fecha: string, hora: string, duracion_horas?: ?int, adultos: int, espacio_id?: ?int, tipo_pago_reserva?: ?string, canal_pago_reserva?: ?string, notas?: ?string} $validated */
            $resultado = $interactor->ejecutar($validated);

            return response()->json($resultado, 201);
        } catch (DomainException $e) {
            throw ValidationException::withMessages([
                'fecha' => [$e->getMessage()],
            ]);
        }
    }
}
