<?php

declare(strict_types=1);

namespace App\Support\Pdf;

final class TipoPaginaResolver
{
    /**
     * @return array{0: TamanoPapel, 1: Orientacion}
     */
    public function resolver(mixed $tipoPagina): array
    {
        if ($tipoPagina instanceof FormatoPagina) {
            return $tipoPagina->resolver();
        }

        $formato = is_string($tipoPagina) ? FormatoPagina::tryFrom($tipoPagina) : null;
        if ($formato !== null) {
            return $formato->resolver();
        }

        return [TamanoPapel::A4, Orientacion::Vertical];
    }
}
