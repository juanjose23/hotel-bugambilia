<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Actions\Landing\ResolverUrlImagen;
use App\Repository\Models\Catalogos\Catalogo;
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
        $precio = $precioObj !== null ? (float) $precioObj->precio : 0.0;
        $moneda = $precioObj !== null ? ($precioObj->moneda->simbolo ?? '$') : '$';

        return [
            'id' => $habitacion->id,
            'categoria_id' => $habitacion->categoria_id,
            'ubicacion_id' => $habitacion->ubicacion_id,
            'codigo' => $habitacion->codigo,
            'numero' => $habitacion->numero,
            'slug' => Str::slug($nombre).'-'.$habitacion->id,
            'nombre' => $nombre,
            'descripcion' => $habitacion->descripcion ?? '',
            'categoria' => $habitacion->categoria->nombre ?? '',
            'ubicacion' => $habitacion->ubicacion->nombre ?? '',
            'precio' => $precio,
            'moneda' => $moneda,
            'capacidad' => $capacidades['total'],
            'adultos' => $capacidades['adultos'],
            'ninos' => $capacidades['ninos'],
            'medidas' => $habitacion->detalle?->medidas ? $habitacion->detalle->medidas.' m²' : null,
            'vistas' => $this->vistas($habitacion),
            'camas' => $this->resolverCamas($habitacion),
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

    private function resolverCamas(Habitacion $habitacion): ?string
    {
        $camas = [];

        foreach ($habitacion->inventarioFijo as $asignacion) {
            $activo = $asignacion->activo;
            if ($activo !== null) {
                $nombre = (string) ($activo->producto->nombre ?? $activo->nombre_descriptivo ?? '');
                if (stripos($nombre, 'cama') !== false) {
                    $camas[] = trim(explode(' - ', $nombre)[0]);
                }
            }
        }

        if ($camas !== []) {
            $conteo = array_count_values($camas);
            $partes = [];
            foreach ($conteo as $nombreCama => $cantidad) {
                $partes[] = "{$cantidad} {$nombreCama}";
            }

            return implode(' + ', $partes);
        }

        $texto = $habitacion->nombre.' '.($habitacion->categoria->nombre ?? '');
        if (stripos($texto, 'matrimonial') !== false) {
            return '1 Cama Matrimonial';
        }
        if (stripos($texto, 'king') !== false) {
            return '1 Cama King Size';
        }
        if (stripos($texto, 'queen') !== false) {
            return '1 Cama Queen Size';
        }
        if (stripos($texto, 'doble') !== false) {
            return '2 Camas Dobles';
        }
        if (stripos($texto, 'sencilla') !== false || stripos($texto, 'individual') !== false) {
            return '1 Cama Individual';
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function vistas(Habitacion $habitacion): array
    {
        $detalle = $habitacion->detalle;

        if ($detalle === null || ! is_array($detalle->vistas) || $detalle->vistas === []) {
            return [];
        }

        $vistaIds = array_filter($detalle->vistas, fn ($v) => is_numeric($v));
        if ($vistaIds !== []) {
            return Catalogo::whereIn('id', $vistaIds)
                ->pluck('nombre')
                ->filter()
                ->map(fn (mixed $v): string => is_string($v) ? $v : '')
                ->values()
                ->all();
        }

        return array_values(array_filter($detalle->vistas, fn ($v) => is_string($v) && $v !== ''));
    }

    /**
     * @return array<int, array{nombre: string, categoria: string, cantidad: int}>
     */
    private function equipamiento(Habitacion $habitacion): array
    {
        $equipamientoMap = [];

        foreach ($habitacion->inventarioFijo as $asignacion) {
            $activo = $asignacion->activo;
            if ($activo !== null) {
                // Obtener nombre limpio del producto sin códigos o etiquetas internas de inventario
                $nombre = (string) ($activo->producto->nombre ?? '');

                if ($nombre === '' && ! empty($activo->nombre_descriptivo)) {
                    $nombre = trim(explode(' - ', (string) $activo->nombre_descriptivo)[0]);
                }

                if ($nombre !== '') {
                    $categoria = (string) ($activo->producto->categoria->nombre ?? 'Mobiliario & Confort');

                    if (! isset($equipamientoMap[$nombre])) {
                        $equipamientoMap[$nombre] = [
                            'nombre' => $nombre,
                            'categoria' => $categoria,
                            'cantidad' => 0,
                        ];
                    }
                    $equipamientoMap[$nombre]['cantidad']++;
                }
            }
        }

        return array_values($equipamientoMap);
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
            'precio' => $p !== null ? (float) $p->precio : 0.0,
            'moneda' => $p !== null ? ($p->moneda->simbolo ?? '$') : '$',
            'imagen' => $img !== null ? ($this->resolverUrlImagen->ejecutar($img->url) ?? '/images/main-room.jpg') : '/images/main-room.jpg',
        ];
    }
}
