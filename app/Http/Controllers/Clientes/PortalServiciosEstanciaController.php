<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Interactors\Clientes\ObtenerCatalogoServiciosEstancia;
use App\Interactors\Clientes\ObtenerDetalleReservaPortal;
use App\Interactors\Clientes\SolicitarServicioEstancia;
use App\Repository\Models\User;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PortalServiciosEstanciaController extends Controller
{
    public function __construct(
        private readonly ObtenerDetalleReservaPortal $obtenerDetalle,
        private readonly ObtenerCatalogoServiciosEstancia $obtenerCatalogo,
        private readonly SolicitarServicioEstancia $solicitarServicio,
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

        $servicios = $this->obtenerCatalogo->ejecutar();

        return Inertia::render('portal/SolicitarServicios', [
            'reserva' => $reserva,
            'servicios' => $servicios,
        ]);
    }

    public function store(int $id, Request $request): RedirectResponse|JsonResponse
    {
        $datos = $request->validate([
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'cantidad' => ['required', 'numeric', 'min:1', 'max:50'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var User|null $user */
        $user = $request->user();

        try {
            $resultado = $this->solicitarServicio->ejecutar($id, [
                'servicio_id' => (int) $datos['servicio_id'],
                'cantidad' => (float) $datos['cantidad'],
                'notas' => $datos['notas'] ?? null,
            ], $user);
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
                'message' => 'Servicio añadido con éxito a la estancia.',
            ]);
        }

        $nombreServicio = is_string($resultado['servicio'] ?? null) ? $resultado['servicio'] : 'solicitado';

        return redirect()->route('portal.reservas.show', ['id' => $id])
            ->with('success', "Servicio {$nombreServicio} cargado a tu estancia.");
    }
}
