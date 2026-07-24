<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Reservas\ReservaHuesped;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class HuespedModificado implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ReservaHuesped $huesped,
        public readonly string $accion,
        /** @var array<string, mixed>|null */
        public readonly ?array $datosAnteriores = null,
    ) {}
}
