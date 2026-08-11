<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Presenters\Landing\EspacioDetallePresenter;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Support\Str;

final class ObtenerEspacioDetalleLanding
{
    public function __construct(
        private readonly EspacioDetallePresenter $presenter,
    ) {}

    /**
     * @return array{space: array<string, mixed>, similarSpaces: array<int, array<string, mixed>>}
     */
    public function ejecutar(string|int $identificador): array
    {
        $espacio = $this->resolverEspacio($identificador);

        if (! $espacio instanceof Espacio) {
            abort(404, 'Espacio no encontrado.');
        }

        return [
            'space' => $this->presenter->detalle($espacio),
            'similarSpaces' => $this->presenter->similares($espacio),
        ];
    }

    private function resolverEspacio(string|int $identificador): ?Espacio
    {
        $identificadorStr = (string) $identificador;

        $query = Espacio::with([
            'ubicacion', 'imagenes', 'precios.moneda', 'politicas', 'servicioAsignaciones.servicio',
        ])->activosWeb();

        if (is_numeric($identificador)) {
            $espacio = (clone $query)->find((int) $identificador);
            if ($espacio !== null) {
                return $espacio;
            }
        }

        if (preg_match('/-(\d+)$/', $identificadorStr, $matches)) {
            $espacio = (clone $query)->find((int) $matches[1]);
            if ($espacio !== null) {
                return $espacio;
            }
        }

        $espacios = $query->get();

        return $espacios->first(function (Espacio $espacio) use ($identificadorStr): bool {
            if ($espacio->slug === $identificadorStr || $espacio->codigo === $identificadorStr) {
                return true;
            }

            return Str::slug($espacio->nombre) === $identificadorStr;
        });
    }
}
