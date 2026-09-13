<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Delivery;

use App\BusinessLogic\Restaurante\ConfiguracionRestaurante;

final class CalcularCostoYDetalleDelivery
{
    public const string MARCA_DELIVERY = 'PEDIDO A DOMICILIO';

    public function __construct(
        private readonly ConfiguracionRestaurante $configuracion,
    ) {}

    /**
     * @return array{costo_envio: float, es_delivery: bool}
     */
    public function ejecutar(?string $notas): array
    {
        $esDelivery = is_string($notas) && str_contains($notas, self::MARCA_DELIVERY);

        if (! $esDelivery) {
            return ['costo_envio' => 0.0, 'es_delivery' => false];
        }

        return [
            'costo_envio' => $this->configuracion->costoEnvioBase(),
            'es_delivery' => true,
        ];
    }
}
