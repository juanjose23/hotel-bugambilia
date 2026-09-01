<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Interactors\Clientes\ObtenerDashboardPortalCliente;
use App\Interactors\Clientes\ObtenerDetalleReservaPortal;
use App\Repository\Models\User;
use DomainException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PortalReservasController extends Controller
{
    public function __construct(
        private readonly ObtenerDashboardPortalCliente $obtenerDashboard,
        private readonly ObtenerDetalleReservaPortal $obtenerDetalle,
    ) {}

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $dashboardData = $this->obtenerDashboard->ejecutar($user);

        return Inertia::render('portal/MisReservas', [
            'reservas_activas' => $dashboardData['reservas_activas'],
            'historial_reservas' => $dashboardData['historial_reservas'],
            'cliente' => $dashboardData['cliente'],
        ]);
    }

    public function show(int $id, Request $request): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        $codigo = $request->string('codigo')->toString();

        try {
            $detalle = $this->obtenerDetalle->ejecutar($id, $user, $codigo !== '' ? $codigo : null);
        } catch (DomainException $exception) {
            abort(404, $exception->getMessage());
        }

        return Inertia::render('portal/ReservaDetalle', [
            'reserva' => $detalle,
        ]);
    }
}
