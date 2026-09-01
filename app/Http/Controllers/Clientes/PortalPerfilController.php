<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Interactors\Clientes\ActualizarPerfilClientePortal;
use App\Interactors\Clientes\ObtenerDashboardPortalCliente;
use App\Repository\Models\User;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PortalPerfilController extends Controller
{
    public function __construct(
        private readonly ObtenerDashboardPortalCliente $obtenerDashboard,
        private readonly ActualizarPerfilClientePortal $actualizarPerfil,
    ) {}

    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $dashboardData = $this->obtenerDashboard->ejecutar($user);

        return Inertia::render('portal/Perfil', [
            'cliente' => $dashboardData['cliente'],
            'estadisticas' => $dashboardData['estadisticas'],
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'telefono' => ['nullable', 'string', 'max:30'],
            'identificacion' => ['nullable', 'string', 'max:50'],
            'tipo_identificacion' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $resultado = $this->actualizarPerfil->ejecutar($user, $datos);
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
                'message' => 'Perfil actualizado exitosamente.',
            ]);
        }

        return redirect()->route('portal.perfil')
            ->with('success', 'Perfil actualizado exitosamente.');
    }
}
