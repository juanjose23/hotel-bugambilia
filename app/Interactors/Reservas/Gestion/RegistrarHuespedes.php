<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\BusinessLogic\Huespedes\ValidarHuespedes;
use App\Events\Reservas\HuespedModificado;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Persistencia\Reservas\DestinoHuespedRepositorio;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Illuminate\Support\Facades\DB;

final class RegistrarHuespedes
{
    public function __construct(
        private readonly ValidarHuespedes $validar,
        private readonly ReservaRepositorioInterface $reservas,
        private readonly DestinoHuespedRepositorio $destinos,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function agregar(Reserva $reserva, array $datos): ReservaHuesped
    {
        return DB::transaction(function () use ($reserva, $datos): ReservaHuesped {
            $detalle = $this->destinos->resolver($reserva, is_numeric($datos['reserva_detalle_id'] ?? null) ? (int) $datos['reserva_detalle_id'] : null);
            $datos['reserva_detalle_id'] = $detalle->id;
            $this->validar->validarCreacion($reserva, $datos);

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
        return DB::transaction(function () use ($reserva, $huesped, $datos): ReservaHuesped {
            $destino = $this->destinos->resolver($reserva, is_numeric($datos['reserva_detalle_id'] ?? null) ? (int) $datos['reserva_detalle_id'] : $huesped->reserva_detalle_id);
            $datos['reserva_detalle_id'] = $destino->id;
            $datos['tipo_huesped'] ??= $huesped->tipo_huesped;
            $datos['identificacion'] ??= $huesped->identificacion;
            $datos['es_titular'] ??= $huesped->es_titular;
            $this->validar->validarActualizacion($reserva, $datos, $huesped);

            $datosAnteriores = $huesped->toArray();

            $huespedActualizado = $this->reservas->actualizarHuesped($huesped, [
                'reserva_detalle_id' => $destino->id,
                'nombre' => $datos['nombre'] ?? $huesped->nombre,
                'tipo_identificacion' => $datos['tipo_identificacion'] ?? $huesped->tipo_identificacion,
                'identificacion' => $datos['identificacion'] ?? $huesped->identificacion,
                'tipo_huesped' => $datos['tipo_huesped'],
                'es_titular' => $datos['es_titular'],
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
