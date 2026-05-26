<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Queries;

use App\Enums\Activos\EstadoActivo;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoBaja;
use Illuminate\Database\Eloquent\Collection;

class ObtenerReportesActivosVariosUseCase
{
    /**
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, Activo>
     */
    public function inventarioGeneral(array $filtros = []): Collection
    {
        $query = Activo::with([
            'producto',
            'variante',
            'asignacionActiva.asignable',
            'proveedor.persona',
            'moneda',
        ]);

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (! empty($filtros['producto_id'])) {
            $query->where('producto_id', $filtros['producto_id']);
        }
        if (! empty($filtros['ubicacion_tipo'])) {
            $type = $filtros['ubicacion_tipo'];
            $query->whereHas('asignacionActiva', function ($q) use ($type) {
                $q->where('asignable_type', $type);
            });
        }

        return $query->get();
    }

    /**
     * @return Collection<int, Activo>
     */
    public function garantiasProximas(int $dias = 90): Collection
    {
        return Activo::with([
            'producto',
            'proveedor.persona',
            'moneda',
        ])
            ->whereNotNull('fecha_garantia_fin')
            ->where('fecha_garantia_fin', '<=', now()->addDays($dias))
            ->orderBy('fecha_garantia_fin')
            ->get();
    }

    /**
     * @return Collection<int, ActivoBaja>
     */
    public function dadosDeBaja(): Collection
    {
        return ActivoBaja::with([
            'activo',
            'creadoPor',
        ])
            ->orderBy('fecha_baja', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, Activo>
     */
    public function extraviados(): Collection
    {
        return Activo::with([
            'producto',
            'moneda',
            'asignaciones' => fn ($q) => $q->with('asignable')->latest('fecha_inicio'),
        ])
            ->where('estado', EstadoActivo::Extraviado->value)
            ->get();
    }

    /**
     * @return Collection<int, Activo>
     */
    public function sinAsignacion(): Collection
    {
        return Activo::with([
            'producto',
            'moneda',
            'proveedor.persona',
        ])
            ->whereDoesntHave('asignacionActiva')
            ->where('estado', '!=', EstadoActivo::DadoDeBaja->value)
            ->get();
    }
}
