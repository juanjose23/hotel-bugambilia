<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante;

final class ConfiguracionRestaurante
{
    public function costoEnvioBase(): float
    {
        $config = config('restaurante.delivery.costo_base', 50.0);

        return is_numeric($config) ? (float) $config : 50.0;
    }

    public function pedidoMinimo(): float
    {
        $config = config('restaurante.delivery.pedido_minimo', 0.0);

        return is_numeric($config) ? (float) $config : 0.0;
    }

    public function deliveryHabilitado(): bool
    {
        return (bool) config('restaurante.delivery.habilitado', true);
    }

    public function whatsapp(): string
    {
        $config = config('hotel.whatsapp');

        return is_string($config) ? $config : '+50588888888';
    }

    public function telefono(): string
    {
        $config = config('hotel.telefono');

        return is_string($config) ? $config : '+505 8713 6805';
    }
}
