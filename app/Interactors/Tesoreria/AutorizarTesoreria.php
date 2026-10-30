<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\Repository\Models\User;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;
use DomainException;
use Illuminate\Support\Facades\Gate;

final readonly class AutorizarTesoreria
{
    public function __construct(private CajaRepositorio $cajas) {}

    public function ejecutar(int $actor, string $permiso): User
    {
        if (! config('tesoreria.habilitado')) {
            throw new DomainException('Tesorería no está activada en este entorno.');
        }$user = $this->cajas->usuario($actor);
        Gate::forUser($user)->authorize($permiso);

        return $user;
    }
}
