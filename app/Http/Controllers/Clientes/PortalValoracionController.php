<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\RegistrarValoracionEstanciaRequest;
use App\Interactors\Clientes\RegistrarValoracionEstancia;
use App\Repository\Models\User;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class PortalValoracionController extends Controller
{
    public function __construct(
        private readonly RegistrarValoracionEstancia $registrarValoracion,
    ) {}

    public function store(int $id, RegistrarValoracionEstanciaRequest $request): RedirectResponse|JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        try {
            $resultado = $this->registrarValoracion->ejecutar(
                reservaId: $id,
                estrellas: $request->estrellas(),
                comentario: $request->comentario(),
                user: $user,
                codigo: $request->codigo(),
            );
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => '¡Muchas gracias por tu valoración!',
            ]);
        }

        return back()->with('exito', '¡Muchas gracias por tu valoración!');
    }
}
