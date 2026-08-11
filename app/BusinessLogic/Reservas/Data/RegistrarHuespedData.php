<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

use App\Enums\Reservas\TipoHuesped;

final readonly class RegistrarHuespedData
{
    public function __construct(
        public string $nombre,
        public ?string $apellido = null,
        public ?string $tipoDocumento = null,
        public ?string $numeroDocumento = null,
        public ?string $email = null,
        public ?string $telefono = null,
        public TipoHuesped $tipoHuesped = TipoHuesped::ADULTO,
        public bool $esTitular = false,
    ) {}
}
