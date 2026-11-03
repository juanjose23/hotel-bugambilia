<?php

declare(strict_types=1);

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Interactors\Restaurante\Mesas\ObtenerMesasDisponibles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ConsultarMesasDisponiblesController extends Controller
{
    public function __invoke(
        Request $request,
        ObtenerMesasDisponibles $interactor,
    ): JsonResponse {
        $datos = $request->validate([
            'fecha' => ['sometimes', 'required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora' => ['sometimes', 'required', 'date_format:H:i'],
            'duracion_horas' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comensales' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ]);
        $fecha = (string) ($datos['fecha'] ?? today()->toDateString());
        $hora = (string) ($datos['hora'] ?? '13:00');
        $duracion = (int) ($datos['duracion_horas'] ?? 1);
        $comensales = (int) ($datos['comensales'] ?? 2);

        $resultado = $interactor->ejecutar(
            fecha: $fecha,
            hora: $hora,
            duracionHoras: $duracion,
            comensales: $comensales,
        );

        return response()->json($resultado);
    }
}
