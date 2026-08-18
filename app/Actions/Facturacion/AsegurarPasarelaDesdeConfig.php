<?php

declare(strict_types=1);

namespace App\Actions\Facturacion;

use App\Enums\Facturacion\PasarelaCodigo;
use App\Repository\Models\Facturacion\PasarelaPago;
use App\Repository\Persistencia\Facturacion\PasarelaPagoPersistencia;
use DomainException;

final readonly class AsegurarPasarelaDesdeConfig
{
    public function __construct(
        private PasarelaPagoPersistencia $pasarelaPagoPersistencia,
    ) {}

    /**
     * Provisiona o sincroniza la PasarelaPago a partir del bloque config/services.php.
     *
     * @param  bool  $exigirActiva  si true, lanza una excepcion cuando la pasarela esta desactivada.
     */
    public function ejecutar(PasarelaCodigo $codigo, bool $exigirActiva = true): PasarelaPago
    {
        $config = config("services.{$codigo->value}");

        if (! is_array($config)) {
            throw new DomainException("La pasarela {$codigo->value} no esta configurada en config/services.php.");
        }

        $activa = (bool) ($config['enabled'] ?? true);

        if ($exigirActiva && ! $activa) {
            throw new DomainException("La pasarela {$codigo->value} esta desactivada en la configuracion del sistema.");
        }

        $faltantes = [];

        foreach ($codigo->clavesRequeridas() as $clave) {
            $valor = $config[$clave] ?? null;

            if (! is_string($valor) || trim($valor) === '') {
                $faltantes[] = $clave;
            }
        }

        if ($faltantes !== []) {
            throw new DomainException(
                "La pasarela {$codigo->value} no tiene configurado: ".implode(', ', $faltantes).'.',
            );
        }

        $mode = $config['mode'] ?? 'test';
        $modoPrueba = ! (is_string($mode) && trim($mode) === 'live');

        return $this->pasarelaPagoPersistencia->actualizarOCrearPorCodigo($codigo, [
            'nombre' => $codigo->getLabel(),
            'activa' => $activa,
            'modo_prueba' => $modoPrueba,
            'configuracion' => [
                'origen' => 'config/services.php',
                'enabled_config_key' => "services.{$codigo->value}.enabled",
                'mode_config_key' => "services.{$codigo->value}.mode",
                'claves_config' => array_map(
                    fn (string $clave): string => "services.{$codigo->value}.{$clave}",
                    $codigo->clavesRequeridas(),
                ),
            ],
            'proveedor' => $codigo->value,
            'gestion' => 'sistema',
        ]);
    }
}
