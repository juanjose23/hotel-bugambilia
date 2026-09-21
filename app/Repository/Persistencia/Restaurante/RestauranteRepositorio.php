<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaJuridica;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Models\Restaurante\SustitucionIngrediente;
use App\Repository\Models\Restaurante\TransformacionMateriaPrima;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
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
        $restaurante = Espacio::query()->where('tipo', TipoEspacio::RESTAURANTE)->first();

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
            ->whereIn('estado', [
                EstadoPedido::ABIERTO->value,
                EstadoPedido::EN_PREPARACION->value,
                EstadoPedido::SERVIDO->value,
            ])
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
    public function obtenerMenuPublico(): Collection
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
        return Espacio::query()->where('tipo', TipoEspacio::MESA)->get();
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

    public function obtenerProductoPorId(int $id): ?Producto
    {
        /** @var Producto|null $producto */
        $producto = Producto::query()->find($id);

        return $producto;
    }

    /**
     * @return Collection<int, ProcesoCocina>
     */
    public function obtenerProcesosCocinaFiltrados(?string $fechaInicio = null, ?string $fechaFin = null): Collection
    {
        $query = ProcesoCocina::query()
            ->with(['plato', 'items.productoDestino', 'realizadoPor']);

        if (! empty($fechaInicio)) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }

        if (! empty($fechaFin)) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        /** @var Collection<int, ProcesoCocina> $procesos */
        $procesos = $query->latest()->get();

        return $procesos;
    }

    public function obtenerCatalogoClienteRegular(): ?Catalogo
    {
        $consulta = Catalogo::whereHas(
            'catalogoTipo',
            fn ($q) => $q->where('codigo', 'TIPO_CLIENTE')
        );

        /** @var Catalogo|null $catalogo */
        $catalogo = (clone $consulta)->where('codigo', 'CLI_REGULAR')->first();

        if ($catalogo instanceof Catalogo) {
            return $catalogo;
        }

        /** @var Catalogo|null $catalogo */
        $catalogo = (clone $consulta)->orderBy('orden')->first();

        return $catalogo;
    }

    // ============================================================
    // Lectura - Público / Catálogo
    // ============================================================

    public function obtenerRestaurantePublico(): ?Espacio
    {
        /** @var Espacio|null $restaurante */
        $restaurante = Espacio::activosWeb()
            ->where('tipo', TipoEspacio::RESTAURANTE)
            ->with(['imagenes'])
            ->first();

        return $restaurante;
    }

    /** @return Collection<int, Espacio> */
    public function obtenerMesasDeRestaurante(int $restauranteId): Collection
    {
        return Espacio::where('padre_id', $restauranteId)
            ->where('tipo', TipoEspacio::MESA)
            ->with(['ubicacion'])
            ->orderBy('orden')
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

    /** @param  array<string, mixed>  $datos */
    public function actualizarPedido(Pedido $pedido, array $datos): void
    {
        $pedido->update($datos);
    }

    public function eliminarItem(PedidoItem $item): void
    {
        $item->delete();
    }

    /**
     * @param  array<int, int>  $itemIds
     * @return Collection<int, PedidoItem>
     */
    public function obtenerItemsMoviblesDePedido(Pedido $pedido, array $itemIds): Collection
    {
        return $pedido->items()
            ->whereIn('id', $itemIds)
            ->whereNotIn('estado', [
                EstadoItemPedido::ANULADO->value,
                EstadoItemPedido::SERVIDO->value,
            ])
            ->get();
    }

    public function contarItemsNoAnuladosDePedido(Pedido $pedido): int
    {
        return $pedido->items()
            ->where('estado', '!=', EstadoItemPedido::ANULADO->value)
            ->count();
    }

    public function contarItemsDePedido(Pedido $pedido): int
    {
        return $pedido->items()->count();
    }

    public function subtotalDeItemsNoAnulados(Pedido $pedido): float
    {
        return (float) $pedido->items()
            ->where('estado', '!=', EstadoItemPedido::ANULADO->value)
            ->sum('subtotal');
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
    public function actualizarProcesoCocina(ProcesoCocina $proceso, array $datos): void
    {
        $proceso->update($datos);
    }

    public function eliminarItemsDeProcesoCocina(ProcesoCocina $proceso): void
    {
        $proceso->items()->delete();
    }

    /** @param  array<string, mixed>  $datos */
    public function guardarProcesoItem(ProcesoCocina $proceso, array $datos): void
    {
        $proceso->items()->create($datos);
    }

    /** @param  array<string, mixed>  $datos */
    public function crearProcesoCocina(array $datos): ProcesoCocina
    {
        return ProcesoCocina::query()->create($datos);
    }

    public function recalcularCostoTotalProceso(ProcesoCocina $proceso): ProcesoCocina
    {
        $costoTotal = (float) $proceso->items()->sum('costo_asignado');

        $proceso->update([
            'costo_total' => round($costoTotal, 2),
        ]);

        return $proceso->fresh() ?? $proceso;
    }

    // ============================================================
    // Escritura - Compras / Abastecimiento
    // ============================================================

    /** @param  array<string, mixed>  $datos */
    public function crearSolicitudAbastecimiento(array $datos): Solicitud
    {
        return Solicitud::query()->create($datos);
    }

    // ============================================================
    // Escritura - Clientes
    // ============================================================

    /** @param  array<string, mixed>  $datos */
    public function crearPersona(array $datos): Persona
    {
        return Persona::query()->create($datos);
    }

    /** @param  array<string, mixed>  $datos */
    public function crearPersonaNatural(array $datos): PersonaNatural
    {
        return PersonaNatural::query()->create($datos);
    }

    /** @param  array<string, mixed>  $datos */
    public function crearPersonaJuridica(array $datos): PersonaJuridica
    {
        return PersonaJuridica::query()->create($datos);
    }

    /** @param  array<string, mixed>  $datos */
    public function crearCliente(array $datos): Cliente
    {
        return Cliente::query()->create($datos);
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

    /** @param array<string, mixed> $datos */
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
    // Lectura - Capacidad
    // ============================================================

    public function contarMesasEnRestaurante(int $restauranteId, ?int $ignorarId = null): int
    {
        $subEspacioIds = Espacio::query()
            ->where('padre_id', $restauranteId)
            ->whereIn('tipo', [
                TipoEspacio::AMBIENTE->value,
                TipoEspacio::TERRAZA->value,
                TipoEspacio::BAR->value,
                TipoEspacio::SALON->value,
                TipoEspacio::OTRO->value,
            ])
            ->pluck('id')
            ->all();

        $query = Espacio::query()
            ->where('tipo', TipoEspacio::MESA)
            ->where(function (Builder $q) use ($restauranteId, $subEspacioIds): void {
                $q->where('padre_id', $restauranteId);
                if (! empty($subEspacioIds)) {
                    $q->orWhereIn('padre_id', $subEspacioIds);
                }
            });

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

    public function obtenerCuentaCobro(int $cuentaId): ?Cuenta
    {
        /** @var Cuenta|null $cuenta */
        $cuenta = Cuenta::query()
            ->with(['cliente.persona.personaNatural', 'cliente.persona.personaJuridica', 'moneda'])
            ->find($cuentaId);

        return $cuenta;
    }

    public function obtenerCuentaIdDePedidoActivoEnMesa(int $mesaId): ?int
    {
        $cuentaId = Pedido::query()
            ->where('mesa_id', $mesaId)
            ->whereNotNull('cuenta_id')
            ->whereHas('cuenta', fn ($q) => $q->where('estado', 2))
            ->value('cuenta_id');

        return is_numeric($cuentaId) ? (int) $cuentaId : null;
    }

    public function existeOtroPedidoActivoEnMesa(int $mesaId, int $exceptoId): bool
    {
        return Pedido::query()
            ->where('mesa_id', $mesaId)
            ->whereIn('estado', [
                EstadoPedido::ABIERTO->value,
                EstadoPedido::EN_PREPARACION->value,
                EstadoPedido::LISTO->value,
                EstadoPedido::SERVIDO->value,
            ])
            ->where('id', '!=', $exceptoId)
            ->exists();
    }

    public function obtenerPersonaPorId(int $id): ?Persona
    {
        /** @var Persona|null $persona */
        $persona = Persona::query()->find($id);

        return $persona;
    }

    public function obtenerClientePorId(int $id): ?Cliente
    {
        /** @var Cliente|null $cliente */
        $cliente = Cliente::query()->with('persona')->find($id);

        return $cliente;
    }

    /** @return Collection<int, Cuenta> */
    public function obtenerCuentasActivas(): Collection
    {
        return Cuenta::query()
            ->where('estado', 2) // EstadoCuenta::ABIERTA
            ->with(['cliente.persona', 'detalles', 'pagos'])
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

    /**
     * @param  array<int, int>  $excluirIds
     * @return Collection<int, Espacio>
     */
    public function obtenerMesasLibresExcluyendo(array $excluirIds): Collection
    {
        return Espacio::query()
            ->where('estado', EstadoEspacio::Disponible->value)
            ->whereNotIn('id', $excluirIds)
            ->get();
    }

    public function tieneSolicitudLimpiezaActiva(string $limpiableType, int $limpiableId): bool
    {
        return SolicitudLimpieza::query()
            ->where('limpiable_type', $limpiableType)
            ->where('limpiable_id', $limpiableId)
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->exists();
    }

    /** @param array<string, mixed> $datos */
    public function crearTransformacionMateriaPrima(array $datos): TransformacionMateriaPrima
    {
        return TransformacionMateriaPrima::query()->create($datos);
    }

    /** @param array<string, mixed> $datos */
    public function crearTransformacionItem(TransformacionMateriaPrima $transformacion, array $datos): void
    {
        $transformacion->items()->create($datos);
    }

    /** @param array<string, mixed> $datos */
    public function actualizarTransformacionMateriaPrima(TransformacionMateriaPrima $transformacion, array $datos): void
    {
        $transformacion->update($datos);
    }

    /** @param array<string, mixed> $datos */
    public function crearLote(array $datos): Lote
    {
        return Lote::query()->create($datos);
    }

    /** @param array<string, mixed> $datos */
    public function crearStock(array $datos): Stock
    {
        return Stock::query()->create($datos);
    }

    public function obtenerVarianteConProducto(int $varianteId): ?ProductoVariante
    {
        return ProductoVariante::query()->with('producto')->find($varianteId);
    }

    public function obtenerUsuarioPorId(int $usuarioId): ?User
    {
        return User::query()->find($usuarioId);
    }

    public function obtenerUbicacionPorId(int $id): ?Ubicacion
    {
        return Ubicacion::query()->find($id);
    }

    public function obtenerUbicacionCocinaId(): int
    {
        $id = Ubicacion::query()
            ->where('nombre', UbicacionCocina::RESTAURANTE->value)
            ->orWhere('nombre', 'Cocina')
            ->orWhere('nombre', 'like', '%Cocina%')
            ->orWhere('tipo', 'cocina')
            ->value('id');

        if (! is_numeric($id)) {
            throw new DomainException('No existe una ubicación de cocina configurada para recibir abastecimiento.');
        }

        return (int) $id;
    }

    /** @return Collection<int, Stock> */
    public function obtenerStocksDisponiblesParaTraslado(int $varianteId, int $destinoId): Collection
    {
        return Stock::query()
            ->with('lote')
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', '!=', $destinoId)
            ->where('producto_variante_id', $varianteId)
            ->where('cantidad_actual', '>', 0)
            ->orderByDesc('cantidad_actual')
            ->lockForUpdate()
            ->get();
    }

    public function obtenerSustitucionActiva(int $pedidoItemId, int $varianteOriginalId): ?SustitucionIngrediente
    {
        /** @var SustitucionIngrediente|null $sustitucion */
        $sustitucion = SustitucionIngrediente::query()
            ->with(['varianteSustituta.producto'])
            ->where('pedido_item_id', $pedidoItemId)
            ->where('variante_original_id', $varianteOriginalId)
            ->where('estado', 1)
            ->latest('id')
            ->first();

        return $sustitucion;
    }

    public function desactivarSustitucionesActivas(int $pedidoItemId, int $varianteOriginalId): void
    {
        SustitucionIngrediente::query()
            ->where('pedido_item_id', $pedidoItemId)
            ->where('variante_original_id', $varianteOriginalId)
            ->where('estado', 1)
            ->update(['estado' => 0]);
    }

    /** @param array<string, mixed> $datos */
    public function crearSustitucionIngrediente(array $datos): SustitucionIngrediente
    {
        /** @var SustitucionIngrediente $sustitucion */
        $sustitucion = SustitucionIngrediente::query()->create($datos);

        return $sustitucion;
    }

    public function guardarSolicitudAbastecimiento(Solicitud $solicitud): void
    {
        $solicitud->save();
    }

    /** @param array<string, mixed> $intentPayload */
    public function actualizarPagoStripeTransaccionYPedido(?PagoTransaccion $transaccion, Pedido $pedido, array $intentPayload, string $paymentIntentId): void
    {
        if ($transaccion !== null) {
            $transaccion->update([
                'estado' => EstadoTransaccionPago::Capturada,
                'response_payload' => $intentPayload,
            ]);
        }

        $pedido->update([
            'notas' => "✅ PAGO STRIPE CONFIRMADO ({$paymentIntentId}) | ".$pedido->notas,
        ]);
    }
}
