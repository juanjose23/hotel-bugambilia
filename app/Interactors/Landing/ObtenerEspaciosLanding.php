<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Presenters\Landing\EspacioTarjetaPresenter;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerEspaciosLanding
{
    public function __construct(
        private readonly EspacioTarjetaPresenter $espacioPresenter,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(?string $categoriaTipo = null): array
    {
        $query = Espacio::activosWeb()
            ->whereNull('padre_id')
            ->with(['imagenes', 'ubicacion', 'hijos'])
            ->orderBy('orden')
            ->orderBy('nombre');

        $this->aplicarFiltroCategoria($query, $categoriaTipo);

        return $query->get()
            ->map(fn (Espacio $espacio): array => $this->espacioPresenter->tarjeta($espacio))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{tipo: string, label: string}>
     */
    public function tiposDisponibles(): array
    {
        $tiposValores = Espacio::activosWeb()
            ->whereNull('padre_id')
            ->distinct()
            ->pluck('tipo');

        $tipos = [];
        foreach ($tiposValores as $tipoValor) {
            $tipo = $tipoValor instanceof TipoEspacio ? $tipoValor : (is_string($tipoValor) ? TipoEspacio::tryFrom($tipoValor) : null);

            if ($tipo === null) {
                continue;
            }

            $tipoStr = $tipo->value;

            if (! isset($tipos[$tipoStr])) {
                $tipos[$tipoStr] = ['tipo' => $tipoStr, 'label' => $tipo->getLabel()];
            }
        }

        return array_values($tipos);
    }

    /**
     * @param  Builder<Espacio>  $query
     */
    private function aplicarFiltroCategoria(Builder $query, ?string $categoriaTipo): void
    {
        if ($categoriaTipo !== null && trim($categoriaTipo) !== '' && strtoupper(trim($categoriaTipo)) !== 'TODOS') {
            $query->where('tipo', trim($categoriaTipo));
        }
    }
}
