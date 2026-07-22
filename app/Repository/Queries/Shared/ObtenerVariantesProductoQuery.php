<?php

declare(strict_types=1);

namespace App\Repository\Queries\Shared;

use App\Repository\Models\Catalogos\ProductoVariante;

final class ObtenerVariantesProductoQuery
{
    /** @return array<int, string> */
    public function ejecutar(?int $productoId): array
    {
        if ($productoId === null) {
            return [];
        }

        $variantes = ProductoVariante::where('producto_id', $productoId)->get();

        $result = [];
        foreach ($variantes as $v) {
            $info = strval($v->codigo);

            if ($v->atributos) {
                $attrParts = [];
                foreach ((array) $v->atributos as $attrKey => $attrVal) {
                    $keyStr = (string) $attrKey;
                    $valStr = is_scalar($attrVal) ? (string) $attrVal : '';
                    $attrParts[] = $keyStr.': '.$valStr;
                }
                $info .= ' | '.implode(', ', $attrParts);
            }

            if ($v->unidadMedida) {
                $info .= ' ('.$v->unidadMedida->nombre.')';
            }

            $result[(int) $v->id] = $info;
        }

        return $result;
    }
}
