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

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $tipoHuespedValor = $data['tipo_huesped'] ?? $data['tipo'] ?? null;
        $tipoHuesped = TipoHuesped::ADULTO;
        if ($tipoHuespedValor instanceof TipoHuesped) {
            $tipoHuesped = $tipoHuespedValor;
        } elseif (is_numeric($tipoHuespedValor)) {
            $tipoHuesped = TipoHuesped::tryFrom((int) $tipoHuespedValor) ?? TipoHuesped::ADULTO;
        } elseif (is_string($tipoHuespedValor)) {
            $nombreLimpio = strtoupper(trim($tipoHuespedValor));
            $tipoHuesped = match ($nombreLimpio) {
                'ADULTO', 'ADULT' => TipoHuesped::ADULTO,
                'NINO', 'NIÑO', 'CHILD' => TipoHuesped::NINO,
                'INFANTE', 'INFANT' => TipoHuesped::INFANTE,
                default => TipoHuesped::ADULTO,
            };
        }

        $nombre = isset($data['nombre']) && is_string($data['nombre']) ? trim($data['nombre']) : '';

        return new self(
            nombre: $nombre,
            apellido: isset($data['apellido']) && is_string($data['apellido']) ? trim($data['apellido']) : null,
            tipoDocumento: isset($data['tipo_documento']) && is_string($data['tipo_documento']) ? trim($data['tipo_documento']) : null,
            numeroDocumento: isset($data['numero_documento']) && is_string($data['numero_documento'])
                ? trim($data['numero_documento'])
                : (isset($data['identificacion']) && is_string($data['identificacion']) ? trim($data['identificacion']) : null),
            email: isset($data['email']) && is_string($data['email']) ? trim($data['email']) : null,
            telefono: isset($data['telefono']) && is_string($data['telefono']) ? trim($data['telefono']) : null,
            tipoHuesped: $tipoHuesped,
            esTitular: isset($data['es_titular']) ? (bool) $data['es_titular'] : false,
        );
    }
}
