<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Facturacion;

use App\Enums\Facturacion\PasarelaCodigo;
use App\Repository\Models\Facturacion\PasarelaPago;

final readonly class PasarelaPagoPersistencia
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarOCrearPorCodigo(PasarelaCodigo $codigo, array $datos): PasarelaPago
    {
        /** @var PasarelaPago $pasarela */
        $pasarela = PasarelaPago::query()->updateOrCreate(
            ['codigo' => $codigo->value],
            $datos,
        );

        return $pasarela;
    }
}
