<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Delivery;

use DomainException;

final readonly class ValidarDepartamentoDelivery
{
    public function __construct(
        private ObtenerDepartamentosDelivery $obtenerDepartamentos,
    ) {}

    /**
     * Valida que el departamento exista y esté activo para entregas,
     * y retorna la información configurada del departamento.
     *
     * @return array{
     *     codigo: string,
     *     nombre: string,
     *     activo: bool,
     *     costo_envio: float,
     *     municipios: list<string>
     * }
     *
     * @throws DomainException
     */
    public function ejecutar(string $departamento, ?string $municipio = null): array
    {
        $departamentos = $this->obtenerDepartamentos->ejecutar(soloActivos: false);

        $depNormalizado = mb_strtolower(trim($departamento));

        $coincidencia = null;
        foreach ($departamentos as $item) {
            if (mb_strtolower($item['codigo']) === $depNormalizado || mb_strtolower($item['nombre']) === $depNormalizado) {
                $coincidencia = $item;
                break;
            }
        }

        if ($coincidencia === null) {
            throw new DomainException("El departamento '{$departamento}' no está registrado para entregas de Restaurante Bugambilias.");
        }

        if (! $coincidencia['activo']) {
            throw new DomainException("Actualmente el servicio de delivery no está disponible para el departamento de {$coincidencia['nombre']}. Por el momento solo atendemos en zonas autorizadas.");
        }

        if ($municipio !== null && trim($municipio) !== '') {
            $munNormalizado = mb_strtolower(trim($municipio));
            $municipiosList = array_map('mb_strtolower', $coincidencia['municipios']);

            if (! in_array($munNormalizado, $municipiosList, true)) {
                $municipiosValidos = implode(', ', $coincidencia['municipios']);
                throw new DomainException("El municipio '{$municipio}' no está dentro de la cobertura para {$coincidencia['nombre']}. Municipios disponibles: {$municipiosValidos}.");
            }
        }

        return $coincidencia;
    }
}
