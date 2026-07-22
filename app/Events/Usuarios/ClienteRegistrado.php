<?php

declare(strict_types=1);

namespace App\Events\Usuarios;

use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ClienteRegistrado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Cliente $cliente,
        public Persona $persona,
        public bool $esNuevo = true,
    ) {}
}
