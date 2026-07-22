<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Reservas\EstadoReserva;
use App\Interactors\Reservas\CrearReserva;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ReservaController extends Controller
{
    public function crear(Request $request, CrearReserva $interactor): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_cliente' => 'required|string|max:150',
            'telefono_cliente' => 'nullable|string|max:50',
            'email_cliente' => 'nullable|email|max:150',
            'tipo_reserva' => 'required|string',
            'habitacion_id' => 'nullable|integer|exists:habitaciones,id',
            'espacio_id' => 'nullable|integer|exists:espacios,id',
            'servicio_id' => 'nullable|integer|exists:servicios,id',
            'fecha_check_in' => 'required|date',
            'fecha_check_out' => 'nullable|date|after_or_equal:fecha_check_in',
            'hora_reserva' => 'nullable|string|max:50',
            'adultos' => 'nullable|integer|min:1',
            'ninos' => 'nullable|integer|min:0',
            'notas' => 'nullable|string',
            'total' => 'nullable|numeric|min:0',
            'acompanantes' => 'nullable|array',
            'servicios_adicionales' => 'nullable|array',
        ]);

        if (auth()->check()) {
            $validated['cliente_id'] = auth()->id();
        }

        try {
            $reserva = $interactor->ejecutar(
                datos: $validated,
                serviciosAdicionales: $validated['servicios_adicionales'] ?? []
            );

            return redirect()->route('mis-reservas', ['codigo' => $reserva->codigo_reserva])
                ->with('exito', "¡Reserva realizada con éxito! Su código de reserva es: {$reserva->codigo_reserva}");
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancelar(int $id): RedirectResponse
    {
        $reserva = Reserva::findOrFail($id);

        if (auth()->check() && $reserva->cliente_id !== auth()->id()) {
            abort(403, 'No tiene permisos para cancelar esta reserva.');
        }

        $reserva->estado = EstadoReserva::CANCELADA;
        $reserva->save();

        return back()->with('exito', 'La reserva ha sido cancelada correctamente.');
    }
}
