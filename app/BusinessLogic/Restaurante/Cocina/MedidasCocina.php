<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cocina;

final class MedidasCocina
{
    /** @return array{dimension: string, factor: float} */
    public function resolver(int $id, string $nombre, string $codigo, float $volumenMl = 0, float $pesoGramos = 0, float $unidadesPorEmpaque = 1): array
    {
        if ($unidadesPorEmpaque <= 0 || ! is_finite($unidadesPorEmpaque)) {
            throw new \DomainException('Configure el contenido del empaque.');
        }
        $nombre = mb_strtolower(trim($nombre));
        $codigo = mb_strtolower(trim($codigo));
        if (str_starts_with($nombre, 'kilogram') || in_array($codigo, ['kg', 'kilogramo'], true)) {
            return ['dimension' => 'masa', 'factor' => 1000.0];
        }
        if (str_starts_with($nombre, 'gram') || in_array($codigo, ['g', 'gr'], true)) {
            return ['dimension' => 'masa', 'factor' => 1.0];
        }
        if (str_starts_with($nombre, 'libra') || $codigo === 'lb') {
            return ['dimension' => 'masa', 'factor' => 453.59237];
        }
        if (str_starts_with($nombre, 'mililitro') || $codigo === 'ml') {
            return ['dimension' => 'volumen', 'factor' => 1.0];
        }
        if (str_starts_with($nombre, 'litro') || in_array($codigo, ['l', 'lt'], true)) {
            return ['dimension' => 'volumen', 'factor' => 1000.0];
        }
        if ($pesoGramos > 0) {
            return ['dimension' => 'masa', 'factor' => $pesoGramos * $unidadesPorEmpaque];
        }
        if ($volumenMl > 0) {
            return ['dimension' => 'volumen', 'factor' => $volumenMl * $unidadesPorEmpaque];
        }

        if (preg_match('/^(unidad|botella|lata|caja|paquete|empaque)/u', $nombre) === 1 || in_array($codigo, ['und', 'un', 'u'], true)) {
            return ['dimension' => 'unidades', 'factor' => $unidadesPorEmpaque];
        }

        return ['dimension' => 'unidad:'.$id, 'factor' => 1.0];
    }
}
