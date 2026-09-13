<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Delivery;

use App\Repository\Models\Restaurante\ZonaDelivery;
use App\Repository\Queries\Restaurante\Delivery\ObtenerZonasDeliveryQuery;
use Throwable;

final class ObtenerDepartamentosDelivery
{
    public function __construct(
        private readonly ObtenerZonasDeliveryQuery $obtenerZonas = new ObtenerZonasDeliveryQuery,
    ) {}

    /**
     * @return list<array{
     *     codigo: string,
     *     nombre: string,
     *     activo: bool,
     *     costo_envio: float,
     *     municipios: list<string>
     * }>
     */
    public function ejecutar(bool $soloActivos = false): array
    {
        try {
            $zonasDb = $this->obtenerZonas->ejecutar($soloActivos);

            if ($zonasDb->isNotEmpty()) {
                /** @var list<array{codigo: string, nombre: string, activo: bool, costo_envio: float, municipios: list<string>}> $resultado */
                $resultado = $zonasDb->map(function (ZonaDelivery $zona): array {
                    return [
                        'codigo' => (string) $zona->codigo,
                        'nombre' => (string) $zona->nombre,
                        'activo' => (bool) $zona->activo,
                        'costo_envio' => (float) $zona->costo_envio,
                        'municipios' => $zona->municipios,
                    ];
                })->values()->all();

                return $resultado;
            }
        } catch (Throwable) {
            // Fallback a configuración en caso de migración pendiente o error de conexión
        }

        /** @var list<array{codigo: string, nombre: string, activo: bool, costo_envio: float, municipios: list<string>}> $departamentos */
        $departamentos = config('restaurante.delivery.departamentos_permitidos', [
            [
                'codigo' => 'EST',
                'nombre' => 'Estelí',
                'activo' => true,
                'costo_envio' => 50.0,
                'municipios' => [
                    'Estelí',
                    'La Trinidad',
                    'Condega',
                    'Pueblo Nuevo',
                    'San Juan de Limay',
                    'San Nicolás',
                ],
            ],
        ]);

        if ($soloActivos) {
            return array_values(array_filter($departamentos, fn (array $d): bool => (bool) $d['activo']));
        }

        return $departamentos;
    }
}
