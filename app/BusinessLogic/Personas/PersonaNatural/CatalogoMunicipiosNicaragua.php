<?php

declare(strict_types=1);

namespace App\BusinessLogic\Personas\PersonaNatural;

final class CatalogoMunicipiosNicaragua
{
    /**
     * @var list<array{departamento: string, codigo_departamento: string, municipios: list<array{codigo: string, nombre: string}>}>|null
     */
    private static ?array $catalogo = null;

    /**
     * @var array<string, array{codigo: string, nombre: string, departamento: string}>|null
     */
    private static ?array $municipiosPorCodigo = null;

    /**
     * @return list<array{departamento: string, codigo_departamento: string, municipios: list<array{codigo: string, nombre: string}>}>
     */
    public static function obtenerCatalogo(): array
    {
        if (self::$catalogo !== null) {
            return self::$catalogo;
        }

        $path = __DIR__.'/../../../../database/data/nicaragua_municipios.json';

        if (! file_exists($path) && function_exists('database_path')) {
            try {
                $path = database_path('data/nicaragua_municipios.json');
            } catch (\Throwable) {
                // Ignore if container is not fully bootstrapped in unit tests
            }
        }

        if (! file_exists($path)) {
            return [];
        }

        $contenido = (string) file_get_contents($path);
        /** @var list<array{departamento: string, codigo_departamento: string, municipios: list<array{codigo: string, nombre: string}>}> $data */
        $data = json_decode($contenido, true) ?? [];

        self::$catalogo = $data;

        return self::$catalogo;
    }

    /**
     * Verifica si un código de municipio de 3 dígitos es válido según el catálogo nacional.
     */
    public static function esCodigoMunicipioValido(string $codigo): bool
    {
        $codigoNormalizado = str_pad(trim($codigo), 3, '0', STR_PAD_LEFT);
        $index = self::obtenerIndicePorCodigo();

        return isset($index[$codigoNormalizado]);
    }

    /**
     * Obtiene los datos de un municipio según su código de 3 dígitos de cédula.
     *
     * @return array{codigo: string, nombre: string, departamento: string}|null
     */
    public static function obtenerPorCodigo(string $codigo): ?array
    {
        $codigoNormalizado = str_pad(trim($codigo), 3, '0', STR_PAD_LEFT);
        $index = self::obtenerIndicePorCodigo();

        return $index[$codigoNormalizado] ?? null;
    }

    /**
     * Obtiene la lista de nombres de municipios de un departamento dado.
     *
     * @return list<string>
     */
    public static function obtenerNombresMunicipiosPorDepartamento(string $departamento): array
    {
        $catalogo = self::obtenerCatalogo();
        $depBuscado = mb_strtolower(trim($departamento));

        foreach ($catalogo as $dep) {
            if (mb_strtolower($dep['departamento']) === $depBuscado) {
                return array_map(fn (array $m): string => $m['nombre'], $dep['municipios']);
            }
        }

        return [];
    }

    /**
     * @return array<string, array{codigo: string, nombre: string, departamento: string}>
     */
    private static function obtenerIndicePorCodigo(): array
    {
        if (self::$municipiosPorCodigo !== null) {
            return self::$municipiosPorCodigo;
        }

        $catalogo = self::obtenerCatalogo();
        $index = [];

        foreach ($catalogo as $dep) {
            $nombreDep = $dep['departamento'];
            foreach ($dep['municipios'] as $mun) {
                $codigo = str_pad($mun['codigo'], 3, '0', STR_PAD_LEFT);
                $index[$codigo] = [
                    'codigo' => $codigo,
                    'nombre' => $mun['nombre'],
                    'departamento' => $nombreDep,
                ];
            }
        }

        self::$municipiosPorCodigo = $index;

        return self::$municipiosPorCodigo;
    }
}
