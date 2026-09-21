<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\Shared\Stock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface LimpiezaRepositorioInterface
{
    public function buscarEjecucionPorId(int $id): ?LimpiezaEjecucion;

    public function buscarEjecucionPorIdConLock(int $id): LimpiezaEjecucion;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarEjecucion(LimpiezaEjecucion $ejecucion, array $datos): void;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarSolicitud(SolicitudLimpieza $solicitud, array $datos): void;

    public function actualizarEstadoLimpiable(Model $limpiable, EstadoEspacio $estado): void;

    public function obtenerStockCarritoPorIdConLock(int $stockId, int $carritoId): InventarioStock;

    public function agregarStockACarrito(
        int $carritoId,
        int $productoId,
        float $cantidad,
        ?int $productoVarianteId = null,
        ?int $loteId = null
    ): void;

    public function actualizarConsumoEnEjecucion(int $ejecucionId, int $productoVarianteId, float $cantidad): void;

    public function descontarStockAmenity(int $stockId, float $cantidad): ?Stock;

    /** @param list<string> $relaciones */
    public function buscarSolicitudPorIdConRelaciones(int $id, array $relaciones = []): SolicitudLimpieza;

    public function buscarSolicitudActivaPorLimpiable(string $limpiableType, int $limpiableId): ?SolicitudLimpieza;

    /** @param array<string, mixed> $datos */
    public function crearSolicitud(array $datos): SolicitudLimpieza;

    /** @param array<string, mixed> $datos */
    public function crearEjecucion(array $datos): LimpiezaEjecucion;

    public function buscarEjecucionPorSolicitudId(int $solicitudId): ?LimpiezaEjecucion;

    public function buscarEjecucionPorColaboradorFecha(int $colaboradorId, string $fecha): ?LimpiezaEjecucion;

    public function buscarEjecucionPorCarritoFecha(int $carritoId, string $fecha): ?LimpiezaEjecucion;

    public function liberarCarritoDeColaborador(int $colaboradorId, string $fecha): bool;

    public function crearCarrito(string $nombre, ?string $descripcion = null): Ubicacion;

    public function asignarCarritoAColaborador(int $colaboradorId, int $carritoId, string $fecha): LimpiezaEjecucion;

    public function obtenerStockDisponibleEnCarrito(int $carritoId, int $varianteId): float;

    public function registrarSustitucionStock(int $ejecucionId, int $productoId, int $sustitutoProductoId, int $varianteId, int $sustitutoVarianteId, float $cantidad): void;

    public function incrementarSharedStock(string $stockableType, int $stockableId, int $varianteId, float $cantidad): void;

    public function crearSharedStockSiNoExiste(string $stockableType, int $stockableId, int $varianteId): void;

    public function descontarSharedStockConLock(int $stockId, float $cantidad): ?Stock;

    /** @return Collection<int, LimpiezaHorario> */
    public function obtenerHorariosActivosParaMaterializar(string $diaSemanaActual): Collection;

    /** @return Collection<string, int> */
    public function obtenerEjecucionesExistentesKeys(string $fecha): Collection;

    /** @param list<array<string, mixed>> $nuevasEjecuciones */
    public function insertarEjecucionesMasivas(array $nuevasEjecuciones): void;

    /** @param list<int> $turnoIds
     * @return Collection<int, Turno>
     */
    public function obtenerTurnosConPersonasPorIds(array $turnoIds): Collection;

    /** @return Collection<int, LimpiezaEjecucion> */
    public function obtenerEjecucionesPendientesParaRecordatorio(Carbon $ahora, string $horaActual): Collection;

    public function buscarColaboradorPorUserId(int $userId): ?Colaborador;

    public function buscarTurnoRestaurante(): Turno;

    public function buscarTurnoPorUbicacion(int $ubicacionId): ?Turno;

    public function buscarTurnoDefault(): Turno;

    public function buscarLimpiablePorTipoYId(string $modelClass, int $modelId): Model;

    public function tieneDiscrepanciasSharedStock(string $stockableType, int $stockableId): bool;
}
