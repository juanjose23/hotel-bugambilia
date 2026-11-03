<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Promociones\Promocion;
use InvalidArgumentException;

final readonly class ObtenerPromocionReservaQuery
{
    public function vigente(int $id, bool $publica = false): Promocion
    {
        $promocion = Promocion::query()->vigentes()->when($publica, fn ($query) => $query->where('web', true))->with('items.item')->find($id);

        return $promocion ?? throw new InvalidArgumentException('La promoción seleccionada ya no está disponible.');
    }

    public function padreDeEspacio(int $id): ?int
    {
        $padre = Espacio::query()->whereKey($id)->value('padre_id');

        return is_numeric($padre) ? (int) $padre : null;
    }
}
