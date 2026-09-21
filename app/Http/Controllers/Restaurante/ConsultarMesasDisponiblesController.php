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
        $fecha = (string) $request->query('fecha', date('Y-m-d'));
        $hora = (string) $request->query('hora', '13:00');
        $duracion = max(1, (int) $request->query('duracion_horas', 1));
        $comensales = max(1, (int) $request->query('comensales', 2));

        $resultado = $interactor->ejecutar(
            fecha: $fecha,
            hora: $hora,
            duracionHoras: $duracion,
            comensales: $comensales,
        );

        return response()->json($resultado);
    }
}
