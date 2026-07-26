<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\AuditoriaRestaurante;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Stock;
use Illuminate\Support\Collection;

final class RestauranteRepositorio implements RestauranteRepositorioInterface
{
    // ============================================================
    // Lectura - Mesas / Espacios
    // ============================================================

    public function obtenerMesaPorId(int $id): ?Espacio
    {
        /** @var Espacio|null $mesa */
        $mesa = Espacio::query()->find($id);

        return $mesa;
    }

    public function obtenerEspacioPorId(int $id): ?Espacio
    {
        /** @var Espacio|null $espacio */
        $espacio = Espacio::query()->find($id);

        return $espacio;
    }

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, Espacio>
     */
    public function obtenerEspaciosPorIds(array $ids): Collection
    {
        return Espacio::query()->whereIn('id', $ids)->get();
    }

    public function obtenerRestaurantePrincipal(): ?Espacio
    {
        /** @var Espacio|null $restaurante */
        $restaurante = Espacio::query()->where('tipo', 'restaurante')->first();

        return $restaurante;
    }

    // ============================================================
    // Lectura - Pedidos
    // ============================================================

    public function obtenerPedidoPorId(int $id): ?Pedido
    {
        /** @var Pedido|null $pedido */
        $pedido = Pedido::query()->find($id);

        return $pedido;
    }

    /** @return Collection<int, Pedido> */
    public function obtenerPedidosActivosDeMesa(int $mesaId): Collection
    {
        return Pedido::query()
            ->where('mesa_id', $mesaId)
            ->whereIn('estado', ['abierto', 'en_preparacion', 'servido'])
            ->get();
    }

    // ============================================================
    // Lectura - Reservas
    // ============================================================

    public function obtenerReservaPorId(int $id): ?Reserva
    {
        /** @var Reserva|null $reserva */
        $reserva = Reserva::query()->find($id);

        return $reserva;
    }

    // ============================================================
    // Lectura - Platos
    // ============================================================

    public function obtenerPlatoConReceta(int $platoId): ?Plato
    {
        /** @var Plato|null $plato */
        $plato = Plato::with('receta')->find($platoId);

        return $plato;
    }

    /** @return Collection<int, Plato> */
    public function obtenerMenuParaLanding(): Collection
    {
        return Plato::activos()
            ->where('web', true)
            ->with(['precios.moneda', 'imagenes', 'categoria'])
            ->orderBy('categoria_id')
            ->orderBy('nombre')
            ->get();
    }

    /** @return Collection<int, Plato> */
    public function obtenerPlatosActivos(?int $categoriaId = null): Collection
    {
        $query = Plato::query()
            ->activos()
            ->with(['categoria', 'imagenes', 'precios']);

        if ($categoriaId !== null) {
            $query->where('categoria_id', $categoriaId);
        }

        return $query->get();
    }

    public function obtenerPlatoConPrecios(int $platoId): ?Plato
    {
        /** @var Plato|null $plato */
        $plato = Plato::with(['imagenes', 'precios'])->find($platoId);

        return $plato;
    }

    /** @return Collection<int, Espacio> */
    public function obtenerMesasDisponibles(): Collection
    {
        return Espacio::query()->where('tipo', 'mesa')->get();
    }

    /** @param  array<string, mixed>  $datos */
    public function crearPedidoItem(array $datos): PedidoItem
    {
        return PedidoItem::query()->create($datos);
    }

    // ============================================================
    // Lectura - Inventario / Stock
    // ============================================================

    /** @return Collection<int, ProductoKit> */
    public function obtenerIngredientesReceta(int $productoRecetaId): Collection
    {
        return ProductoKit::with(['variante.producto', 'productoPadre'])
            ->where('producto_padre_id', $productoRecetaId)
            ->get();
    }

    public function obtenerUbicacionPorNombre(string $nombre): ?Ubicacion
    {
        /** @var Ubicacion|null $ubicacion */
        $ubicacion = Ubicacion::where('nombre', $nombre)->first();

        return $ubicacion;
    }

    public function obtenerStockConLote(int $ubicacionId, int $varianteId): ?Stock
    {
        /** @var Stock|null $stock */
        $stock = Stock::with('lote')
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', $ubicacionId)
            ->where('producto_variante_id', $varianteId)
            ->where('cantidad_actual', '>', 0)
            ->first();

        return $stock;
    }

    public function obtenerStockPorVariante(int $ubicacionId, int $varianteId): ?Stock
    {
        /** @var Stock|null $stock */
        $stock = Stock::where('stockable_type', Ubicacion::class)
            ->where('stockable_id', $ubicacionId)
            ->where('producto_variante_id', $varianteId)
            ->first();

        return $stock;
    }

    // ============================================================
    // Lectura - Landing
    // ============================================================

    public function obtenerRestauranteParaLanding(): ?Espacio
    {
        /** @var Espacio|null $restaurante */
        $restaurante = Espacio::where('tipo', 'restaurante')
            ->where('estado', '!=', 0)
            ->with(['imagenes'])
            ->first();

        return $restaurante;
    }

    /** @return Collection<int, Espacio> */
    public function obtenerMesasDeRestaurante(int $restauranteId): Collection
    {
        return Espacio::where('padre_id', $restauranteId)
            ->where('tipo', 'mesa')
            ->orderBy('nombre')
            ->get();
    }

    /** @return Collection<int, Espacio> */
    public function obtenerAmbientesDeRestaurante(int $restauranteId): Collection
    {
        return Espacio::where('padre_id', $restauranteId)
            ->where('tipo', '!=', 'mesa')
            ->where('estado', 1)
            ->with(['imagenes'])
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }

    // ============================================================
    // Lectura - Imágenes
    // ============================================================

    /** @return Collection<int, string> */
    public function obtenerUrlsImagenesDeModelo(string $modeloClave, int $modeloId): Collection
    {
        return Imagen::query()
            ->where('imagenable_type', $modeloClave)
            ->where('imagenable_id', $modeloId)
            ->pluck('url')
            ->map(fn ($u): string => is_scalar($u) ? (string) $u : '')
            ->values();
    }

    // ============================================================
    // Escritura - Pedidos
    // ============================================================

    public function guardarItem(PedidoItem $item): void
    {
        $item->save();
    }

    public function guardarPedido(Pedido $pedido): void
    {
        $pedido->save();
    }

    // ============================================================
    // Escritura - Mesas / Espacios
    // ============================================================

    public function guardarMesa(Espacio $mesa): void
    {
        $mesa->save();
    }

    /** @param  array<string, mixed>  $datos */
    public function actualizarEspacio(Espacio $espacio, array $datos): void
    {
        $espacio->update($datos);
    }

    // ============================================================
    // Escritura - Procesos de cocina
    // ============================================================

    public function guardarProcesoCocina(ProcesoCocina $proceso): void
    {
        $proceso->save();
    }

    /** @param  array<string, mixed>  $datos */
    public function guardarProcesoItem(ProcesoCocina $proceso, array $datos): void
    {
        $proceso->items()->create($datos);
    }

    // ============================================================
    // Escritura - Stock
    // ============================================================

    public function guardarStock(Stock $stock): void
    {
        $stock->save();
    }

    public function registrarMovimiento(array $datos): void
    {
        MovimientoStock::query()->create($datos);
    }

    // ============================================================
    // Escritura - Limpieza
    // ============================================================

    public function crearSolicitudLimpieza(array $datos): SolicitudLimpieza
    {
        return SolicitudLimpieza::create($datos);
    }

    // ============================================================
    // Escritura - Imágenes
    // ============================================================

    /** @param  array<int, string>  $urls */
    public function eliminarImagenesPorUrls(string $modeloClave, int $modeloId, array $urls): void
    {
        Imagen::query()
            ->where('imagenable_type', $modeloClave)
            ->where('imagenable_id', $modeloId)
            ->whereIn('url', $urls)
            ->delete();
    }

    public function sincronizarImagenOrden(string $modeloClave, int $modeloId, string $url, int $orden): void
    {
        Imagen::query()
            ->where('imagenable_type', $modeloClave)
            ->where('imagenable_id', $modeloId)
            ->where('url', $url)
            ->update(['orden' => $orden]);
    }

    // ============================================================
    // Escritura - Auditoría
    // ============================================================

    public function registrarAuditoria(array $datos): AuditoriaRestaurante
    {
        return AuditoriaRestaurante::create($datos);
    }

    // ============================================================
    // Lectura - Capacidad
    // ============================================================

    public function contarMesasEnRestaurante(int $restauranteId, ?int $ignorarId = null): int
    {
        $query = Espacio::query()
            ->where('padre_id', $restauranteId)
            ->where('tipo', 'mesa');

        if ($ignorarId !== null) {
            $query->where('id', '!=', $ignorarId);
        }

        return $query->count();
    }

    // ============================================================
    // Lectura - Cuentas
    // ============================================================

    public function obtenerCuentaPorId(int $id): ?Cuenta
    {
        /** @var Cuenta|null $cuenta */
        $cuenta = Cuenta::query()->find($id);

        return $cuenta;
    }

    /** @return Collection<int, Cuenta> */
    public function obtenerCuentasActivas(): Collection
    {
        return Cuenta::query()
            ->where('estado', 2) // EstadoCuenta::ABIERTA
            ->with(['cliente', 'detalles', 'pagos'])
            ->get();
    }

    // ============================================================
    // Escritura - Cuentas
    // ============================================================

    public function guardarCuenta(Cuenta $cuenta): void
    {
        $cuenta->save();
    }

    public function guardarPago(PagoCuenta $pago): void
    {
        $pago->save();
    }
}
