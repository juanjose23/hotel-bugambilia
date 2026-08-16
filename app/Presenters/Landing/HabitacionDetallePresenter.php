<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Actions\Landing\ResolverUrlImagen;
use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Support\Str;

final class HabitacionDetallePresenter
{
    public function __construct(
        private readonly ResolverUrlImagen $resolverUrlImagen,
        private readonly ServicioAsignacionPresenter $serviciosPresenter,
        private readonly PoliticaPresenter $politicasPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function detalle(Habitacion $habitacion): array
    {
        $precioObj = $habitacion->precios->first();
        $nombre = $habitacion->nombre ?? "Habitación $habitacion->numero";
        $capacidades = $this->capacidades($habitacion);

        return [
            'id' => $habitacion->id,
            'categoria_id' => $habitacion->categoria_id,
            'ubicacion_id' => $habitacion->ubicacion_id,
            'codigo' => $habitacion->codigo,
            'numero' => $habitacion->numero,
            'slug' => Str::slug($nombre).'-'.$habitacion->id,
            'nombre' => $nombre,
            'descripcion' => $habitacion->descripcion ?? 'Ambiente confortable con acabados de primera calidad, pensado para su descanso en Estelí.',
            'categoria' => $habitacion->categoria->nombre ?? 'Suite Elegante',
            'ubicacion' => $habitacion->ubicacion->nombre ?? 'Piso Principal',
            'precio' => $precioObj ? (float) $precioObj->precio : 45.0,
            'moneda' => $precioObj->moneda->simbolo ?? '$',
            'capacidad' => $capacidades['total'],
            'adultos' => $capacidades['adultos'],
            'ninos' => $capacidades['ninos'],
            'medidas' => $habitacion->detalle?->medidas ? $habitacion->detalle->medidas.' m²' : '32 m²',
            'vistas' => $this->vistas($habitacion),
            'camas' => '1 Cama King Size',
            'imagenes' => $this->resolverUrlImagen->deHabitacion($habitacion),
            'serviciosIncluidos' => $this->serviciosPresenter->lista($habitacion->servicioAsignaciones),
            'politicas' => $this->politicasPresenter->lista($habitacion->politicas),
            'equipamiento' => $this->equipamiento($habitacion),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function similares(Habitacion $habitacion): array
    {
        return Habitacion::with(['categoria', 'imagenes', 'precios.moneda'])
            ->activas()
            ->where('id', '!=', $habitacion->id)
            ->take(3)
            ->get()
            ->map(fn (Habitacion $h): array => $this->similar($h))
            ->values()
            ->all();
    }

    /**
     * @return array{adultos: int, ninos: int, total: int}
     */
    private function capacidades(Habitacion $habitacion): array
    {
        $detalle = $habitacion->detalle;
        $adultos = $detalle ? (int) $detalle->capacidad_adultos : 2;
        $ninos = $detalle ? (int) $detalle->capacidad_ninos : 0;

        return [
            'adultos' => $adultos,
            'ninos' => $ninos,
            'total' => $adultos + $ninos,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function vistas(Habitacion $habitacion): array
    {
        $detalle = $habitacion->detalle;

        if ($detalle !== null && is_array($detalle->vistas) && $detalle->vistas !== []) {
            return $detalle->vistas;
        }

        return ['Vista al Jardín / Terraza'];
    }

    /**
     * @return array<int, string>
     */
    private function equipamiento(Habitacion $habitacion): array
    {
        $equipamiento = [];

        foreach ($habitacion->inventarioFijo as $asignacion) {
            $activo = $asignacion->activo;
            if ($activo !== null && property_exists($activo, 'nombre') && is_string($activo->nombre) && $activo->nombre !== '') {
                $equipamiento[] = $activo->nombre;
            }
        }

        return $equipamiento;
    }

    /**
     * @return array<string, mixed>
     */
    private function similar(Habitacion $h): array
    {
        $p = $h->precios->first();
        $nombre = $h->nombre ?? "Habitación $h->numero";
        $img = $h->imagenes->first();

        return [
            'id' => $h->id,
            'slug' => Str::slug($nombre).'-'.$h->id,
            'nombre' => $nombre,
            'categoria' => $h->categoria->nombre ?? 'Suite',
            'precio' => $p ? (float) $p->precio : 45.0,
            'moneda' => $p->moneda->simbolo ?? '$',
            'imagen' => $img !== null ? ($this->resolverUrlImagen->ejecutar($img->url) ?? '/images/main-room.jpg') : '/images/main-room.jpg',
        ];
    }
}
