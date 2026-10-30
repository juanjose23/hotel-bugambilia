<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Habitaciones;

use App\Actions\Shared\GenerarCorrelativoCodigoAction;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Shared\Stock;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class HabitacionRepositorio implements HabitacionRepositorioInterface
{
    public function __construct(
        private readonly GenerarCorrelativoCodigoAction $generadorCodigo,
        private readonly VistasHabitacionRepositorio $vistas,
    ) {}

    public function existePorSlug(string $slug, ?int $idAIgnorar = null): bool
    {
        $consulta = Habitacion::withTrashed()->where('slug', $slug);

        if ($idAIgnorar !== null) {
            $consulta->where('id', '!=', $idAIgnorar);
        }

        return $consulta->exists();
    }

    public function existePorNumero(int $numero, ?int $idAIgnorar = null): bool
    {
        $consulta = Habitacion::withTrashed()->where('numero', $numero);

        if ($idAIgnorar !== null) {
            $consulta->where('id', '!=', $idAIgnorar);
        }

        return $consulta->exists();
    }

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Habitacion
    {
        return Habitacion::create($datos);
    }

    public function buscarPorId(int $id): ?Habitacion
    {
        return Habitacion::find($id);
    }

    public function buscarPorIdConLock(int $id): Habitacion
    {
        /** @var Habitacion $habitacion */
        $habitacion = Habitacion::query()->where('id', $id)->lockForUpdate()->firstOrFail();

        return $habitacion;
    }

    public function buscarPorRecursoReservableId(int $recursoReservableId): ?Habitacion
    {
        /** @var Habitacion|null $habitacion */
        $habitacion = Habitacion::query()
            ->where('reservable_id', $recursoReservableId)
            ->with('detalle')
            ->first();

        return $habitacion;
    }

    public function buscarPorRecursoReservableIdConLock(int $recursoReservableId): ?Habitacion
    {
        /** @var Habitacion|null $habitacion */
        $habitacion = Habitacion::query()
            ->where('reservable_id', $recursoReservableId)
            ->lockForUpdate()
            ->first();

        return $habitacion;
    }

    public function actualizarEstado(Habitacion $habitacion, EstadoEspacio $estado): void
    {
        $habitacion->update(['estado' => $estado]);
    }

    /** @param array<array-key, mixed> $imagenes */
    public function sincronizarImagenes(Habitacion $habitacion, array $imagenes): void
    {
        $rutas = [];
        foreach ($imagenes as $path) {
            if (! is_string($path) || trim($path) === '') {
                throw ValidationException::withMessages(['imagenes' => 'Seleccione imagenes validas para la galeria.']);
            }
            $rutas[] = $path;
        }
        $actuales = $habitacion->imagenes()->get()->keyBy('url');
        $conservadas = [];
        foreach (array_values(array_unique($rutas)) as $index => $path) {
            $imagen = $actuales->get($path);
            if ($imagen === null) {
                $imagen = $habitacion->imagenes()->create(['url' => $path, 'orden' => $index + 1]);
            } elseif ($imagen->orden !== $index + 1) {
                $imagen->update(['orden' => $index + 1]);
            }
            $conservadas[] = $imagen->id;
        }
        $habitacion->imagenes()->whereNotIn('id', $conservadas)->delete();
        $habitacion->unsetRelation('imagenes');
    }

    public function clonar(
        Habitacion $origen,
        int $nuevoNumero,
        ?string $nuevoNombre = null,
        ?string $nuevoSlug = null,
        ?string $nuevoCodigo = null,
    ): Habitacion {
        $nombre = $nuevoNombre ?? (string) preg_replace(
            '/\d+/',
            (string) $nuevoNumero,
            $origen->nombre ?? '',
            1
        );

        $slug = $nuevoSlug ?? Str::slug($nombre);
        $codigo = $nuevoCodigo ?? 'HAB-'.str_pad((string) $nuevoNumero, 4, '0', STR_PAD_LEFT);

        $nueva = Habitacion::create([
            'codigo' => $codigo,
            'numero' => $nuevoNumero,
            'slug' => $slug,
            'nombre' => $nombre,
            'descripcion' => $origen->descripcion,
            'categoria_id' => $origen->categoria_id,
            'ubicacion_id' => $origen->ubicacion_id,
            'estado' => EstadoEspacio::Mantenimiento,
        ]);

        if ($origen->detalle) {
            $nueva->detalle()->create($origen->detalle->replicate(['id', 'habitacion_id', 'vistas_legacy', 'created_at', 'updated_at'])->toArray());
        }

        $this->vistas->sincronizar($nueva, $origen->vistas->modelKeys());

        foreach ($origen->servicioAsignaciones as $servicio) {
            $nueva->servicioAsignaciones()->create([
                'servicio_id' => $servicio->servicio_id,
                'incluido' => $servicio->incluido,
            ]);
        }

        foreach ($origen->precios as $precio) {
            $nueva->precios()->create($precio->replicate(['id', 'priceable_type', 'priceable_id', 'created_at', 'updated_at'])->toArray());
        }

        if ($origen->politicas->isNotEmpty()) {
            $nueva->politicas()->sync(
                $origen->politicas->pluck('id')->all()
            );
        }

        $origen->loadMissing('stocks');

        foreach ($origen->stocks as $stock) {
            Stock::create([
                'stockable_type' => Habitacion::class,
                'stockable_id' => $nueva->id,
                'producto_variante_id' => $stock->producto_variante_id,
                'lote_id' => null,
                'cantidad_ideal' => $stock->cantidad_ideal,
                'cantidad_actual' => '0.0000',
            ]);
        }

        return $nueva;
    }

    public function generarCodigo(): string
    {
        return $this->generadorCodigo->ejecutar('HAB', Habitacion::class, 'codigo');
    }
}
