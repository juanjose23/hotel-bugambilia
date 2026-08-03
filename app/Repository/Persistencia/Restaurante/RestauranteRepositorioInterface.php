<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Models\Shared\Stock;
use Illuminate\Support\Collection;

interface RestauranteRepositorioInterface
{
    // ============================================================
    // Lectura - Mesas / Espacios
    // ============================================================

    public function obtenerMesaPorId(int $id): ?Espacio;

    public function obtenerEspacioPorId(int $id): ?Espacio;

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, Espacio>
     */
    public function obtenerEspaciosPorIds(array $ids): Collection;

    public function obtenerRestaurantePrincipal(): ?Espacio;

    // ============================================================
    // Lectura - Pedidos
    // ============================================================

    public function obtenerPedidoPorId(int $id): ?Pedido;

    /** @return Collection<int, Pedido> */
    public function obtenerPedidosActivosDeMesa(int $mesaId): Collection;

    // ============================================================
    // Lectura - Reservas
    // ============================================================

    public function obtenerReservaPorId(int $id): ?Reserva;

    // ============================================================
    // Lectura - Cuentas / Caja
    // ============================================================

    public function obtenerCuentaCobro(int $cuentaId): ?Cuenta;

    public function obtenerCuentaPorId(int $id): ?Cuenta;

    public function obtenerCuentaIdDePedidoActivoEnMesa(int $mesaId): ?int;

    public function existeOtroPedidoActivoEnMesa(int $mesaId, int $exceptoId): bool;

    public function obtenerPersonaPorId(int $id): ?Persona;

    // ============================================================
    // Lectura - Platos
    // ============================================================

    public function obtenerPlatoConReceta(int $platoId): ?Plato;

    /** @return Collection<int, Plato> */
    public function obtenerMenuParaLanding(): Collection;

    /** @return Collection<int, Plato> */
    public function obtenerPlatosActivos(?int $categoriaId = null): Collection;

    public function obtenerPlatoConPrecios(int $platoId): ?Plato;

    /** @return Collection<int, Espacio> */
    public function obtenerMesasDisponibles(): Collection;

    /** @param  array<string, mixed>  $datos */
    public function crearPedidoItem(array $datos): PedidoItem;

    // ============================================================
    // Lectura - Inventario / Stock
    // ============================================================

    /** @return Collection<int, ProductoKit> */
    public function obtenerIngredientesReceta(int $productoRecetaId): Collection;

    public function obtenerUbicacionPorNombre(string $nombre): ?Ubicacion;

    public function obtenerStockConLote(int $ubicacionId, int $varianteId): ?Stock;

    public function obtenerStockPorVariante(int $ubicacionId, int $varianteId): ?Stock;

    public function obtenerProductoPorId(int $id): ?Producto;

    /** @return Collection<int, ProcesoCocina> */
    public function obtenerProcesosCocinaFiltrados(?string $fechaInicio = null, ?string $fechaFin = null): Collection;

    public function obtenerCatalogoClienteRegular(): ?Catalogo;

    // ============================================================
    // Lectura - Landing
    // ============================================================

    public function obtenerRestauranteParaLanding(): ?Espacio;

    /** @return Collection<int, Espacio> */
    public function obtenerMesasDeRestaurante(int $restauranteId): Collection;

    /** @return Collection<int, Espacio> */
    public function obtenerAmbientesDeRestaurante(int $restauranteId): Collection;

    // ============================================================
    // Lectura - Imágenes
    // ============================================================

    /** @return Collection<int, string> */
    public function obtenerUrlsImagenesDeModelo(string $modeloClave, int $modeloId): Collection;

    // ============================================================
    // Escritura - Pedidos
    // ============================================================

    public function guardarItem(PedidoItem $item): void;

    public function guardarPedido(Pedido $pedido): void;

    /** @param  array<string, mixed>  $datos */
    public function actualizarPedido(Pedido $pedido, array $datos): void;

    public function eliminarItem(PedidoItem $item): void;

    /**
     * @param  array<int, int>  $itemIds
     * @return Collection<int, PedidoItem>
     */
    public function obtenerItemsMoviblesDePedido(Pedido $pedido, array $itemIds): Collection;

    public function contarItemsNoAnuladosDePedido(Pedido $pedido): int;

    public function contarItemsDePedido(Pedido $pedido): int;

    public function subtotalDeItemsNoAnulados(Pedido $pedido): float;

    // ============================================================
    // Escritura - Mesas / Espacios
    // ============================================================

    public function guardarMesa(Espacio $mesa): void;

    /** @param  array<string, mixed>  $datos */
    public function actualizarEspacio(Espacio $espacio, array $datos): void;

    // ============================================================
    // Escritura - Procesos de cocina
    // ============================================================

    public function guardarProcesoCocina(ProcesoCocina $proceso): void;

    /** @param  array<string, mixed>  $datos */
    public function actualizarProcesoCocina(ProcesoCocina $proceso, array $datos): void;

    public function eliminarItemsDeProcesoCocina(ProcesoCocina $proceso): void;

    /** @param  array<string, mixed>  $datos */
    public function guardarProcesoItem(ProcesoCocina $proceso, array $datos): void;

    /** @param  array<string, mixed>  $datos */
    public function crearProcesoCocina(array $datos): ProcesoCocina;

    public function recalcularCostoTotalProceso(ProcesoCocina $proceso): ProcesoCocina;

    // ============================================================
    // Escritura - Compras / Abastecimiento
    // ============================================================

    /** @param  array<string, mixed>  $datos */
    public function crearSolicitudAbastecimiento(array $datos): Solicitud;

    // ============================================================
    // Escritura - Clientes
    // ============================================================

    /** @param  array<string, mixed>  $datos */
    public function crearPersona(array $datos): Persona;

    /** @param  array<string, mixed>  $datos */
    public function crearPersonaNatural(array $datos): PersonaNatural;

    /** @param  array<string, mixed>  $datos */
    public function crearCliente(array $datos): Cliente;

    // ============================================================
    // Escritura - Stock
    // ============================================================

    public function guardarStock(Stock $stock): void;

    /** @param array<string, mixed> $datos */
    public function registrarMovimiento(array $datos): void;

    // ============================================================
    // Escritura - Limpieza
    // ============================================================

    /** @param array<string, mixed> $datos */
    public function crearSolicitudLimpieza(array $datos): SolicitudLimpieza;

    // ============================================================
    // Escritura - Imágenes
    // ============================================================

    /** @param  array<int, string>  $urls */
    public function eliminarImagenesPorUrls(string $modeloClave, int $modeloId, array $urls): void;

    public function sincronizarImagenOrden(string $modeloClave, int $modeloId, string $url, int $orden): void;

    // ============================================================
    // Lectura - Capacidad
    // ============================================================

    public function contarMesasEnRestaurante(int $restauranteId, ?int $ignorarId = null): int;
}
