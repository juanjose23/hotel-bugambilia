<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Limpieza;

use App\Enums\Catalogos\TipoUbicacion;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Limpieza\SustitucionStock;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\Shared\Stock as SharedStock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class LimpiezaRepositorio implements LimpiezaRepositorioInterface
{
    public function buscarEjecucionPorId(int $id): ?LimpiezaEjecucion
    {
        return LimpiezaEjecucion::find($id);
    }

    public function buscarEjecucionPorIdConLock(int $id): LimpiezaEjecucion
    {
        /** @var LimpiezaEjecucion $ejecucion */
        $ejecucion = LimpiezaEjecucion::query()
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();

        return $ejecucion;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarEjecucion(LimpiezaEjecucion $ejecucion, array $datos): void
    {
        $ejecucion->update($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarSolicitud(SolicitudLimpieza $solicitud, array $datos): void
    {
        $solicitud->update($datos);
    }

    public function actualizarEstadoLimpiable(Model $limpiable, EstadoEspacio $estado): void
    {
        $limpiable->update(['estado' => $estado]);
    }

    public function obtenerStockCarritoPorIdConLock(int $stockId, int $carritoId): InventarioStock
    {
        /** @var InventarioStock $stock */
        $stock = InventarioStock::query()
            ->whereKey($stockId)
            ->where('ubicacion_id', $carritoId)
            ->lockForUpdate()
            ->firstOrFail();

        return $stock;
    }

    public function agregarStockACarrito(
        int $carritoId,
        int $productoId,
        float $cantidad,
        ?int $productoVarianteId = null,
        ?int $loteId = null
    ): void {
        $stock = InventarioStock::where([
            'ubicacion_id' => $carritoId,
            'producto_id' => $productoId,
            'producto_variante_id' => $productoVarianteId,
            'lote_id' => $loteId,
        ])->first();

        if ($stock) {
            $stock->cantidad += $cantidad;
            $stock->save();
        } else {
            InventarioStock::create([
                'ubicacion_id' => $carritoId,
                'producto_id' => $productoId,
                'producto_variante_id' => $productoVarianteId,
                'lote_id' => $loteId,
                'cantidad' => $cantidad,
            ]);
        }
    }

    public function actualizarConsumoEnEjecucion(int $ejecucionId, int $productoVarianteId, float $cantidad): void
    {
        $ejecucion = LimpiezaEjecucion::query()
            ->whereKey($ejecucionId)
            ->lockForUpdate()
            ->first();

        if (! $ejecucion instanceof LimpiezaEjecucion) {
            return;
        }

        $consumos = is_array($ejecucion->consumos) ? $ejecucion->consumos : [];
        $actual = isset($consumos[$productoVarianteId]) && is_numeric($consumos[$productoVarianteId])
            ? (float) $consumos[$productoVarianteId]
            : 0.0;

        $consumos[$productoVarianteId] = $actual + $cantidad;
        $ejecucion->consumos = $consumos;
        $ejecucion->save();
    }

    public function descontarStockAmenity(int $stockId, float $cantidad): ?SharedStock
    {
        /** @var SharedStock|null $sharedStock */
        $sharedStock = SharedStock::where('id', $stockId)->lockForUpdate()->first();
        if (! $sharedStock) {
            return null;
        }

        $sharedStock->cantidad_actual = max(0.0, (float) $sharedStock->cantidad_actual - $cantidad);
        $sharedStock->save();

        return $sharedStock;
    }

    /**
     * @param  list<string>  $relaciones
     */
    public function buscarSolicitudPorIdConRelaciones(int $id, array $relaciones = []): SolicitudLimpieza
    {
        /** @var SolicitudLimpieza $solicitud */
        $solicitud = SolicitudLimpieza::with($relaciones)->findOrFail($id);

        return $solicitud;
    }

    public function buscarSolicitudActivaPorLimpiable(string $limpiableType, int $limpiableId): ?SolicitudLimpieza
    {
        return SolicitudLimpieza::where('limpiable_type', $limpiableType)
            ->where('limpiable_id', $limpiableId)
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->first();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearSolicitud(array $datos): SolicitudLimpieza
    {
        return SolicitudLimpieza::create($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearEjecucion(array $datos): LimpiezaEjecucion
    {
        return LimpiezaEjecucion::create($datos);
    }

    public function buscarEjecucionPorSolicitudId(int $solicitudId): ?LimpiezaEjecucion
    {
        return LimpiezaEjecucion::where('solicitud_id', $solicitudId)->first();
    }

    public function buscarEjecucionPorColaboradorFecha(int $colaboradorId, string $fecha): ?LimpiezaEjecucion
    {
        return LimpiezaEjecucion::where('colaborador_id', $colaboradorId)
            ->whereDate('fecha', $fecha)
            ->first();
    }

    public function buscarEjecucionPorCarritoFecha(int $carritoId, string $fecha): ?LimpiezaEjecucion
    {
        return LimpiezaEjecucion::where('carrito_id', $carritoId)
            ->whereDate('fecha', $fecha)
            ->first();
    }

    public function liberarCarritoDeColaborador(int $colaboradorId, string $fecha): bool
    {
        $ejecuciones = LimpiezaEjecucion::where('colaborador_id', $colaboradorId)
            ->whereDate('fecha', $fecha)
            ->whereNotNull('carrito_id')
            ->get();

        if ($ejecuciones->isEmpty()) {
            return false;
        }

        foreach ($ejecuciones as $ejecucion) {
            $ejecucion->carrito_id = null;
            $ejecucion->save();
        }

        return true;
    }

    public function crearCarrito(string $nombre, ?string $descripcion = null): Ubicacion
    {
        return Ubicacion::create([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'tipo' => TipoUbicacion::CARRITO->value,
            'activo' => true,
        ]);
    }

    public function asignarCarritoAColaborador(int $colaboradorId, int $carritoId, string $fecha): LimpiezaEjecucion
    {
        /** @var LimpiezaEjecucion $ejecucion */
        $ejecucion = LimpiezaEjecucion::firstOrNew([
            'colaborador_id' => $colaboradorId,
            'fecha' => $fecha,
        ]);

        if ($carritoId >= 0) {
            $ejecucion->carrito_id = $carritoId;
        }
        $ejecucion->save();

        return $ejecucion;
    }

    public function obtenerStockDisponibleEnCarrito(int $carritoId, int $varianteId): float
    {
        return (float) InventarioStock::where('ubicacion_id', $carritoId)
            ->where('producto_variante_id', $varianteId)
            ->sum('cantidad');
    }

    public function registrarSustitucionStock(
        int $ejecucionId,
        int $productoId,
        int $sustitutoProductoId,
        int $varianteId,
        int $sustitutoVarianteId,
        float $cantidad
    ): void {
        SustitucionStock::create([
            'ejecucion_id' => $ejecucionId,
            'producto_id' => $productoId,
            'sustituto_producto_id' => $sustitutoProductoId,
            'producto_variante_id' => $varianteId,
            'sustituto_variante_id' => $sustitutoVarianteId,
            'cantidad' => $cantidad,
        ]);
    }

    public function incrementarSharedStock(string $stockableType, int $stockableId, int $varianteId, float $cantidad): void
    {
        $stock = SharedStock::where('stockable_type', $stockableType)
            ->where('stockable_id', $stockableId)
            ->where('producto_variante_id', $varianteId)
            ->first();

        if ($stock) {
            $stock->cantidad_actual += $cantidad;
            $stock->save();
        } else {
            SharedStock::create([
                'stockable_type' => $stockableType,
                'stockable_id' => $stockableId,
                'producto_variante_id' => $varianteId,
                'cantidad_actual' => $cantidad,
                'cantidad_minima' => 0,
            ]);
        }
    }

    public function crearSharedStockSiNoExiste(string $stockableType, int $stockableId, int $varianteId): void
    {
        SharedStock::firstOrCreate([
            'stockable_type' => $stockableType,
            'stockable_id' => $stockableId,
            'producto_variante_id' => $varianteId,
        ], [
            'cantidad_actual' => 0,
            'cantidad_minima' => 0,
        ]);
    }

    public function descontarSharedStockConLock(int $stockId, float $cantidad): ?SharedStock
    {
        /** @var SharedStock|null $stock */
        $stock = SharedStock::where('id', $stockId)->lockForUpdate()->first();
        if ($stock) {
            $stock->cantidad_actual = max(0.0, (float) $stock->cantidad_actual - $cantidad);
            $stock->save();
        }

        return $stock;
    }

    /**
     * @return Collection<int, LimpiezaHorario>
     */
    public function obtenerHorariosActivosParaMaterializar(string $diaSemanaActual): Collection
    {
        /** @var Collection<int, LimpiezaHorario> $result */
        $result = LimpiezaHorario::query()
            ->where('activo', EstadoGeneral::Activo->value)
            ->whereNotNull('turno_id')
            ->where(function (Builder $query) use ($diaSemanaActual): void {
                $query->where('frecuencia', 'diaria')
                    ->orWhere(function (Builder $query) use ($diaSemanaActual): void {
                        $query->where('frecuencia', 'semanal')
                            ->where('dia_semana', $diaSemanaActual);
                    });
            })
            ->with(['turno', 'detalles'])
            ->get();

        return $result;
    }

    /**
     * @return Collection<string, int>
     */
    public function obtenerEjecucionesExistentesKeys(string $fecha): Collection
    {
        /** @var Collection<string, int> $result */
        $result = LimpiezaEjecucion::whereDate('fecha', $fecha)
            ->get(['limpiable_type', 'limpiable_id', 'turno_id', 'id'])
            ->mapWithKeys(fn (LimpiezaEjecucion $e): array => [
                strval($e->limpiable_type.'-'.$e->limpiable_id.'-'.$e->turno_id) => (int) $e->id,
            ]);

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $nuevasEjecuciones
     */
    public function insertarEjecucionesMasivas(array $nuevasEjecuciones): void
    {
        LimpiezaEjecucion::insert($nuevasEjecuciones);
    }

    /**
     * @param  list<int>  $turnoIds
     * @return Collection<int, Turno>
     */
    public function obtenerTurnosConPersonasPorIds(array $turnoIds): Collection
    {
        /** @var Collection<int, Turno> $result */
        $result = Turno::with(['lider.persona', 'apoyo.persona'])->whereIn('id', $turnoIds)->get();

        return $result;
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    public function obtenerEjecucionesPendientesParaRecordatorio(Carbon $ahora, string $horaActual): Collection
    {
        /** @var Collection<int, LimpiezaEjecucion> $result */
        $result = LimpiezaEjecucion::query()
            ->whereDate('fecha', '<=', $ahora->toDateString())
            ->where('estado', EstadoLimpieza::Pendiente)
            ->whereNull('recordatorio_enviado_at')
            ->whereHas('horario', function (Builder $query) use ($horaActual): void {
                $query->where('hora_estimada', '<=', $horaActual);
            })
            ->with([
                'turno.lider.persona',
                'turno.apoyo.persona',
                'colaborador.persona',
                'horario',
                'limpiable',
            ])
            ->get();

        return $result;
    }

    public function buscarColaboradorPorUserId(int $userId): ?Colaborador
    {
        return Colaborador::whereHas('persona.user', function (Builder $query) use ($userId): void {
            $query->where('id', $userId);
        })->first();
    }

    public function buscarTurnoRestaurante(): Turno
    {
        /** @var Turno|null $turno */
        $turno = Turno::where('estado', EstadoGeneral::Activo->value)
            ->where(function (Builder $q): void {
                $q->where('nombre', 'like', '%restaurante%')
                    ->orWhere('nombre', 'like', '%comedor%');
            })
            ->first();

        return $turno ?? $this->buscarTurnoDefault();
    }

    public function buscarTurnoPorUbicacion(int $ubicacionId): ?Turno
    {
        return Turno::where('estado', EstadoGeneral::Activo->value)
            ->whereHas('carritos', fn (Builder $q) => $q->where('ubicacion_id', $ubicacionId))
            ->first();
    }

    public function buscarTurnoDefault(): Turno
    {
        return Turno::where('estado', EstadoGeneral::Activo->value)->first()
            ?? Turno::first()
            ?? Turno::create([
                'nombre' => 'Turno General',
                'hora_inicio' => '07:00:00',
                'hora_fin' => '15:00:00',
                'estado' => EstadoGeneral::Activo,
            ]);
    }

    public function buscarLimpiablePorTipoYId(string $modelClass, int $modelId): Model
    {
        /** @var Model $instance */
        $instance = $modelClass::query()->findOrFail($modelId);

        return $instance;
    }

    public function tieneDiscrepanciasSharedStock(string $stockableType, int $stockableId): bool
    {
        /** @var Collection<int, Stock> $stocks */
        $stocks = Stock::query()
            ->where('stockable_type', $stockableType)
            ->where('stockable_id', $stockableId)
            ->get();

        foreach ($stocks as $st) {
            if ((float) $st->cantidad_actual < (float) $st->cantidad_ideal) {
                return true;
            }
        }

        return false;
    }
}
