<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Enums\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Catalogo;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerCatalogoClienteRegularQuery
{
    public function obtener(): ?Catalogo
    {
        $tipos = [CatalogoTipo::TIPO_CLIENTE->value, mb_strtolower(CatalogoTipo::TIPO_CLIENTE->value)];

        $catalogo = Catalogo::query()
            ->whereHas('catalogoTipo', fn (Builder $query): Builder => $query->whereIn('codigo', $tipos))
            ->whereIn('codigo', ['CLI_REGULAR', 'cliente_regular'])
            ->first();

        if ($catalogo instanceof Catalogo) {
            return $catalogo;
        }

        return Catalogo::query()
            ->whereHas('catalogoTipo', fn (Builder $query): Builder => $query->whereIn('codigo', $tipos))
            ->orderBy('orden')
            ->first();
    }
}
