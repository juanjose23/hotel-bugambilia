<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Eloquent\Model;

final class ObtenerTarifasReservaQuery
{
    public function habitacion(int $id): float
    {
        return $this->precioVigente(Habitacion::class, $id, 'base');
    }

    public function espacio(int $id): float
    {
        $precioHora = $this->precioVigente(Espacio::class, $id, 'por_hora');
        if ($precioHora > 0) {
            return $precioHora;
        }

        return $this->precioVigente(Espacio::class, $id, 'base');
    }

    public function espacioEsPorHora(int $id): bool
    {
        return $this->precioVigente(Espacio::class, $id, 'por_hora') > 0;
    }

    public function servicio(int $id): float
    {
        return $this->precioVigente(Servicio::class, $id, 'base');
    }

    /** @param class-string<Model> $tipo */
    private function precioVigente(string $tipo, int $id, ?string $tipoPrecio = null): float
    {
        $query = Precio::query()
            ->where('priceable_type', $tipo)
            ->where('priceable_id', $id)
            ->where('estado', EstadoGeneral::Activo)
            ->whereDate('fecha_inicio', '<=', today())
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', today()))
            ->whereHas('moneda', fn ($q) => $q->where('es_predeterminada', true));

        if ($tipoPrecio !== null) {
            $query->where('tipo_precio', $tipoPrecio);
        }

        $precio = $query->orderByDesc('es_oferta')
            ->orderByDesc('fecha_inicio')
            ->value('precio');

        if (! is_numeric($precio)) {
            $fallbackQuery = Precio::query()
                ->where('priceable_type', $tipo)
                ->where('priceable_id', $id)
                ->where('estado', EstadoGeneral::Activo);

            if ($tipoPrecio !== null) {
                $fallbackQuery->where('tipo_precio', $tipoPrecio);
            }

            $fallback = $fallbackQuery->orderByDesc('es_oferta')
                ->orderByDesc('fecha_inicio')
                ->value('precio');

            if (is_numeric($fallback)) {
                return (float) $fallback;
            }

            return 0.0;
        }

        return (float) $precio;
    }
}
