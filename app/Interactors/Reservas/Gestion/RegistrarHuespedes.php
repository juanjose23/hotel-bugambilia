<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\BusinessLogic\Huespedes\ValidarHuespedes;
use App\Events\Reservas\HuespedModificado;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class RegistrarHuespedes
{
    public function __construct(
        private readonly ValidarHuespedes $validar,
        private readonly ReservaRepositorioInterface $reservas,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function agregar(Reserva $reserva, array $datos): ReservaHuesped
    {
        $this->validar->validarCreacion($reserva, $datos);

        return DB::transaction(function () use ($reserva, $datos): ReservaHuesped {
            $detalle = $this->reservas->detallePrincipalDe($reserva);

            $huesped = $this->reservas->crearHuesped($detalle, [
                'nombre' => $datos['nombre'],
                'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
                'identificacion' => $datos['identificacion'] ?? null,
                'tipo_huesped' => $datos['tipo_huesped'],
                'es_titular' => $datos['es_titular'] ?? false,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'email' => $datos['email'] ?? null,
            ]);

            HuespedModificado::dispatch($huesped, 'creado');

            return $huesped;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Reserva $reserva, ReservaHuesped $huesped, array $datos): ReservaHuesped
    {
        $this->validar->validarActualizacion($reserva, $datos, $huesped);

        return DB::transaction(function () use ($huesped, $datos): ReservaHuesped {
            $datosAnteriores = $huesped->toArray();

            $huespedActualizado = $this->reservas->actualizarHuesped($huesped, [
                'nombre' => $datos['nombre'] ?? $huesped->nombre,
                'tipo_identificacion' => $datos['tipo_identificacion'] ?? $huesped->tipo_identificacion,
                'identificacion' => $datos['identificacion'] ?? $huesped->identificacion,
                'tipo_huesped' => $datos['tipo_huesped'] ?? $huesped->tipo_huesped,
                'es_titular' => $datos['es_titular'] ?? $huesped->es_titular,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? $huesped->fecha_nacimiento,
                'telefono' => $datos['telefono'] ?? $huesped->telefono,
                'email' => $datos['email'] ?? $huesped->email,
            ]);

            HuespedModificado::dispatch($huespedActualizado, 'actualizado', $datosAnteriores);

            return $huespedActualizado;
        });
    }

    public function eliminar(Reserva $reserva, ReservaHuesped $huesped): void
    {
        $this->validar->validarEliminacion($reserva, $huesped);

        DB::transaction(function () use ($huesped): void {
            HuespedModificado::dispatch($huesped, 'eliminado');
            $this->reservas->eliminarHuesped($huesped);
        });
    }
}
