<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\BusinessLogic\Limpieza\Data\EnviarALavanderiaData;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Limpieza\LavanderiaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class EnviarALavanderia
{
    public function __construct(
        private LavanderiaRepositorioInterface $lavanderiaRepositorio,
    ) {}

    public function execute(EnviarALavanderiaData $dto): void
    {
        $this->ejecutar($dto);
    }

    public function ejecutar(EnviarALavanderiaData $dto): void
    {
        if (empty($dto->items)) {
            throw new InvalidArgumentException('Debe seleccionar al menos un item para enviar a lavandería.');
        }

        $stockableTypeMap = [
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
        ];

        DB::transaction(function () use ($dto, $stockableTypeMap): void {
            foreach ($dto->items as $item) {
                if (! array_key_exists($item->tipo, $stockableTypeMap)) {
                    throw new InvalidArgumentException("Tipo de stock inválido: {$item->tipo}");
                }

                $this->lavanderiaRepositorio->enviarALavanderia(
                    stockId: $item->stockId,
                    lavanderiaId: $dto->ubicacionLavanderiaId,
                    cantidad: (float) $item->cantidad,
                    tipo: $item->tipo,
                    creadoPorId: $dto->creadoPorId,
                    notas: $dto->notas,
                );
            }
        });
    }
}
