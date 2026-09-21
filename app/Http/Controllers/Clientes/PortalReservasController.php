<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Interactors\Clientes\AutenticarClientePorCodigoReserva;
use App\Interactors\Clientes\ObtenerDashboardPortalCliente;
use App\Interactors\Clientes\ObtenerDetalleReservaPortal;
use App\Repository\Models\User;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class PortalReservasController extends Controller
{
    public function __construct(
        private readonly ObtenerDashboardPortalCliente $obtenerDashboard,
        private readonly ObtenerDetalleReservaPortal $obtenerDetalle,
        private readonly AutenticarClientePorCodigoReserva $autenticarPorCodigo,
    ) {}

    public function index(Request $request): Response
    {
        $codigo = $request->string('codigo')->toString();

        if (Auth::guest() && $codigo !== '') {
            $resultado = $this->autenticarPorCodigo->ejecutar($codigo);
            if ($resultado !== null) {
                $request->session()->regenerate();
            }
        }

        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return Inertia::render('portal/MisReservas', [
                'reservas_activas' => [],
                'historial_reservas' => [],
                'cliente' => null,
                'codigoBusqueda' => $codigo,
            ]);
        }

        $dashboardData = $this->obtenerDashboard->ejecutar($user);

        return Inertia::render('portal/MisReservas', [
            'reservas_activas' => $dashboardData['reservas_activas'],
            'historial_reservas' => $dashboardData['historial_reservas'],
            'cliente' => $dashboardData['cliente'],
            'codigoBusqueda' => $codigo,
        ]);
    }

    public function show(int $id, Request $request): Response
    {
        $codigo = $request->string('codigo')->toString();

        if (Auth::guest() && $codigo !== '') {
            $resultado = $this->autenticarPorCodigo->ejecutar($codigo);
            if ($resultado !== null) {
                $request->session()->regenerate();
            }
        }

        /** @var User|null $user */
        $user = $request->user();

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
