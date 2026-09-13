<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Interactors\Clientes\ObtenerDashboardPortalCliente;
use App\Repository\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PortalPedidosController extends Controller
{
    public function __construct(
        private readonly ObtenerDashboardPortalCliente $obtenerDashboard,
    ) {}

    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $dashboardData = $this->obtenerDashboard->ejecutar($user);

        return Inertia::render('portal/MisPedidos', [
            'pedidos_activos' => $dashboardData['pedidos_activos'],
            'historial_pedidos' => $dashboardData['historial_pedidos'],
            'cliente' => $dashboardData['cliente'],
            'estadisticas' => $dashboardData['estadisticas'],
        ]);
    }
}
