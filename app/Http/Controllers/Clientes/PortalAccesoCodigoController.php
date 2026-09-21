<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clientes;

use App\Http\Controllers\Controller;
use App\Interactors\Clientes\AutenticarClientePorCodigoReserva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class PortalAccesoCodigoController extends Controller
{
    public function __construct(
        private readonly AutenticarClientePorCodigoReserva $autenticarPorCodigo,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $validado = $request->validate([
            'codigo' => ['required', 'string', 'max:50'],
        ], [
            'codigo.required' => 'Debes ingresar tu código de reservación.',
        ]);

        $codigo = trim((string) $validado['codigo']);
        $resultado = $this->autenticarPorCodigo->ejecutar($codigo);

        if ($resultado === null) {
            throw ValidationException::withMessages([
                'codigo' => ['No encontramos ninguna reservación válida con el código ingresado.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->route('portal.reservas.show', ['id' => $resultado['reserva']->id])
            ->with('exito', "¡Bienvenido! Has accedido a tu reservación #{$resultado['reserva']->codigo_reserva}.");
    }
}
