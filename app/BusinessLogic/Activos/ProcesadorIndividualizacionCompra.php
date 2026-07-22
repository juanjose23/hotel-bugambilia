<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Enums\Activos\EstadoIndividualizacion;
use App\Repository\Persistencia\Activos\RegistroIndividualizacionRepositorioInterface;

class ProcesadorIndividualizacionCompra
{
    public function __construct(
        private readonly RegistroIndividualizacionRepositorioInterface $registroRepositorio,
    ) {}

    public function procesar(int $recepcionItemId, int $productoId, ?int $productoVarianteId, float $cantidadRecibida, int $userId): ?int
    {
        $registro = $this->registroRepositorio->buscarOrCreate(
            recepcionItemId: $recepcionItemId,
            datosDefault: [
                'producto_id' => $productoId,
                'producto_variante_id' => $productoVarianteId,
                'cantidad_total' => (int) $cantidadRecibida,
                'cantidad_registrada' => 0,
                'estado' => EstadoIndividualizacion::Pendiente,
                'registrado_por_id' => $userId,
            ]
        );

        $registro->cantidad_registrada++;
        if ($registro->cantidad_registrada >= $registro->cantidad_total) {
            $registro->estado = EstadoIndividualizacion::Completado;
            $registro->fecha_completado = now();
        } else {
            $registro->estado = EstadoIndividualizacion::EnProceso;
        }
        $this->registroRepositorio->guardar($registro);

        return $registro->id;
    }
}
