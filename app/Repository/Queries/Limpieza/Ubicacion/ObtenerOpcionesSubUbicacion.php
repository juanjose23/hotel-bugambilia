<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ubicacion;

use App\Repository\Models\Catalogos\Ubicacion;

class ObtenerOpcionesSubUbicacion
{
    /**
     * @return array<int, string>
     */
    public function execute(int $ubicacionPadreId): array
    {
        /** @var array<int, string> */
        return Ubicacion::whereIn('tipo', ['estante', 'nivel', 'posicion'])
            ->where('estado', 1)
            ->where(function ($q) use ($ubicacionPadreId) {
                $q->whereIn('padre_id', function ($sub) use ($ubicacionPadreId) {
                    $sub->select('id')
                        ->from((new Ubicacion)->getTable())
                        ->where('padre_id', $ubicacionPadreId)
                        ->whereIn('tipo', ['estante'])
                        ->where('estado', 1);
                })->orWhere(function ($sub) use ($ubicacionPadreId) {
                    $sub->where('padre_id', $ubicacionPadreId)
                        ->where('tipo', 'estante')
                        ->where('estado', 1);
                });
            })
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn (Ubicacion $u) => [$u->id => $u->nombre.' ('.ucfirst($u->tipo).')'])
            ->toArray();
    }
}
