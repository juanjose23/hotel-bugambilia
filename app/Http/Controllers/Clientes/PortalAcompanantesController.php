<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Interactors\Clientes\GestionarAcompanantesReserva;
use App\Interactors\Clientes\ObtenerDetalleReservaPortal;
use App\Repository\Models\User;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PortalAcompanantesController extends Controller
{
    public function __construct(
        private readonly ObtenerDetalleReservaPortal $obtenerDetalle,
        private readonly GestionarAcompanantesReserva $gestionarAcompanantes,
    ) {}

    public function create(int $id, Request $request): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        $codigo = $request->string('codigo')->toString();

        try {
            $reserva = $this->obtenerDetalle->ejecutar($id, $user, $codigo !== '' ? $codigo : null);
        } catch (DomainException $exception) {
            abort(404, $exception->getMessage());
        }

        return Inertia::render('portal/GestionAcompanantes', [
            'reserva' => $reserva,
        ]);
    }

    public function store(int $id, Request $request): RedirectResponse|JsonResponse
    {
        $datos = $request->validate([
            'acompanantes' => ['required', 'array'],
            'acompanantes.*.nombre' => ['required', 'string', 'max:150'],
            'acompanantes.*.identificacion' => ['nullable', 'string', 'max:50'],
            'acompanantes.*.tipo' => ['nullable', 'string', 'in:adulto,nino,bebe'],
        ]);

        /** @var User|null $user */
        $user = $request->user();

        try {
            /** @var array<int, array{nombre: string, identificacion?: string|null, tipo?: string|null}> $listaAcompanantes */
            $listaAcompanantes = $datos['acompanantes'];
            $resultado = $this->gestionarAcompanantes->ejecutar($id, $listaAcompanantes, $user);
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
                'message' => 'Huéspedes acompañantes actualizados con éxito.',
            ]);
        }

        return redirect()->route('portal.reservas.show', ['id' => $id])
            ->with('success', 'Acompañantes registrados exitosamente.');
    }
}
