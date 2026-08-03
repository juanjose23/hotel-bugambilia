<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class ObtenerDestinatariosRecordatorioReservaQuery
{
    /** @return Collection<int, User> */
    public function ejecutar(Reserva $reserva): Collection
    {
        $generalesConfig = config('hotel.reservas.permisos_generales', []);
        $mesasConfig = $reserva->tipo_reserva === TipoReserva::RESTAURANTE
            ? config('hotel.reservas.permisos_mesas', [])
            : [];
        $generales = is_array($generalesConfig) ? $generalesConfig : [];
        $mesas = is_array($mesasConfig) ? $mesasConfig : [];
        $permisos = array_values(array_unique(array_filter([...$generales, ...$mesas], 'is_string')));

        return User::query()
            ->where(function ($query) use ($permisos): void {
                $query->where('is_admin', true)
                    ->orWhereHas('permissions', fn ($permissions) => $permissions->whereIn('name', $permisos))
                    ->orWhereHas('roles.permissions', fn ($permissions) => $permissions->whereIn('name', $permisos));
            })
            ->get();
    }
}
