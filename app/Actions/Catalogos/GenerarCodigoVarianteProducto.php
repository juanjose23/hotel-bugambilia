<?php

declare(strict_types=1);

namespace App\Actions\Catalogos;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use Illuminate\Support\Str;

final class GenerarCodigoVarianteProducto
{
    private const int MAXIMO_INTENTOS = 100000;

    public function ejecutar(Producto $producto): string
    {
        $usadosGlobales = ProductoVariante::withTrashed()->pluck('codigo');
        $base = $this->resolverBase($producto);

        for ($indice = 0; $indice < self::MAXIMO_INTENTOS; $indice++) {
            $codigo = $base.'-'.self::sufijo($indice);

            if (! $usadosGlobales->contains($codigo)) {
                return $codigo;
            }
        }

        return $base.'-'.Str::upper(Str::random(8));
    }

    private function resolverBase(Producto $producto): string
    {
        $codigoProducto = Str::upper(trim((string) $producto->codigo));

        if ($codigoProducto !== '') {
            return $codigoProducto;
        }

        $codigosProducto = $producto->variantes()->withTrashed()->pluck('codigo');

        $bases = $codigosProducto
            ->map(fn (mixed $codigo): ?string => is_string($codigo) && preg_match('/^(.*)-([A-Z]+)$/', $codigo, $m) === 1 ? (string) $m[1] : null)
            ->filter()
            ->countBy();

        foreach ($bases as $base => $count) {
            if ($count >= 2) {
                return (string) $base;
            }
        }

        return $this->abreviaturaNombre($producto->nombre).'-'.$producto->id;
    }

    private function abreviaturaNombre(?string $nombre): string
    {
        $limpio = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'],
            (string) $nombre
        );

        return Str::upper(trim(mb_substr($limpio, 0, 3)));
    }

    private static function sufijo(int $indice): string
    {
        $letras = '';

        while ($indice >= 0) {
            $letras = chr(65 + ($indice % 26)).$letras;
            $indice = intdiv($indice, 26) - 1;
        }

        return $letras;
    }
}
