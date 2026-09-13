<?php

declare(strict_types=1);

namespace App\Repository\Queries\Clientes;

use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ObtenerReservasPortalClienteQuery
{
    /**
     * @return Collection<int, Reserva>
     */
    public function ejecutar(?int $clienteId, ?string $email): Collection
    {
        /** @var Builder<Reserva> $query */
        $query = Reserva::with([
            'habitacion.categoria',
            'habitacion.imagenes',
            'espacio',
            'moneda',
            'cuentas.detalles',
            'cuentas.pagos',
        ])->orderBy('id', 'desc');

        return $query->where(function (Builder $q) use ($clienteId, $email): void {
            if ($clienteId !== null) {
                $q->where('cliente_id', $clienteId);
            }
            if (is_string($email) && trim($email) !== '') {
                if ($clienteId !== null) {
                    $q->orWhere('email_cliente', trim($email));
                } else {
                    $q->where('email_cliente', trim($email));
                }
            }
        })->get();
    }
}
