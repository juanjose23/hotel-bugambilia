<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\Interactors\Activos\IndividualizarActivos;
use App\Repository\Models\Activos\RegistroIndividualizacion;

class IndividualizadorAutomaticoRecepcion
{
    public function __construct(
        private readonly IndividualizarActivos $individualizarActivos
    ) {}

    public function execute(RegistroIndividualizacion $registro, int $cantidad, ?int $creadoPorId): void
    {
        if ($cantidad <= 0) {
            return;
        }

        $items = array_fill(0, $cantidad, [
            'numero_serie' => null,
            'nombre_descriptivo' => $registro->producto->nombre ?? 'Activo Fijo',
            'notas' => null,
        ]);

        $this->individualizarActivos->ejecutar(
            registroId: $registro->id,
            items: $items,
            usuarioId: $creadoPorId ?? 1
        );
    }
}
