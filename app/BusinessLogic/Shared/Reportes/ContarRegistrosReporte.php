<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared\Reportes;

use App\Enums\Facturacion\EstadoFactura;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;

/**
 * Determina si un reporte supera el umbral de registros configurado para
 * activar la generación en segundo plano (Job), evitando timeouts HTTP.
 *
 * Solo realiza COUNT(*) eficientes — nunca carga registros completos.
 */
final class ContarRegistrosReporte
{
    /** Número de registros a partir del cual el reporte se genera en Job. */
    public const UMBRAL_SEGUNDO_PLANO = 10000;

    /** @param array<string, mixed> $params */
    public function superaUmbral(string $codigo, array $params): bool
    {
        return $this->contar($codigo, $params) > self::UMBRAL_SEGUNDO_PLANO;
    }

    /** @param array<string, mixed> $params */
    public function contar(string $codigo, array $params): int
    {
        $fechaInicioParam = $params['fecha_inicio'] ?? $params['fecha_desde'] ?? null;
        $fechaInicio = is_string($fechaInicioParam) ? $fechaInicioParam : now()->startOfMonth()->format('Y-m-d');
        $fechaFinParam = $params['fecha_fin'] ?? $params['fecha_hasta'] ?? null;
        $fechaFin = is_string($fechaFinParam) ? $fechaFinParam : now()->format('Y-m-d');
        $estado = is_string($params['estado'] ?? null) ? $params['estado'] : null;

        return match (true) {
            // ── Financiero ────────────────────────────────────────────────────
            in_array($codigo, ['HTB-FIN-001', 'cuentas_cobrar'], true) => $this->contarCuentasCobrar($fechaInicio, $fechaFin),
            in_array($codigo, ['HTB-FIN-002', 'facturacion_ventas'], true) => $this->contarFacturacion($fechaInicio, $fechaFin),
            in_array($codigo, ['HTB-FIN-003', 'resumen_ejecutivo'], true) => 1, // Resumen ejecutivo es siempre pequeño

            // ── Reservas ─────────────────────────────────────────────────────
            in_array($codigo, ['HTB-RES-001', 'ocupacion'], true) => $this->contarReservasOcupacion($fechaInicio, $fechaFin, $estado),
            in_array($codigo, ['HTB-RES-002', 'ventas_ingresos'], true) => $this->contarReservasVentas($fechaInicio, $fechaFin),
            in_array($codigo, ['HTB-RES-003', 'reservas_estado'], true) => $this->contarReservasEstado($fechaInicio, $fechaFin, $estado),
            in_array($codigo, ['HTB-RES-004', 'huespedes'], true) => $this->contarReservasHuespedes($fechaInicio, $fechaFin),
            in_array($codigo, ['HTB-RES-005', 'rendimiento_habitaciones'], true) => $this->contarReservasOcupacion($fechaInicio, $fechaFin, null),

            // ── Inventario ───────────────────────────────────────────────────
            str_starts_with($codigo, 'HTB-INV') || in_array($codigo, [
                'stock', 'movimientos', 'vencidos', 'proximos_vencer',
                'cuarentena', 'valorizacion', 'rotacion', 'mermas',
                'stock_minimo', 'ajustes', 'costo_ventas', 'trazabilidad_lote',
            ], true) => $this->contarLotesInventario($codigo, $fechaInicio, $fechaFin),

            // ── Catálogos (Productos) ────────────────────────────────────────
            in_array($codigo, ['HTB-CP001', 'HTB-CP002', 'HTB-CP003'], true) => $this->contarProductos(),

            // ── Limpieza ─────────────────────────────────────────────────────
            str_starts_with($codigo, 'HTB-LIM') || in_array($codigo, [
                'operacion_hotelera', 'tiempo_promedio_limpieza',
                'habitaciones_pendientes_bloqueadas', 'consumo_amenities_habitacion',
                'productividad_colaborador_turno',
            ], true) => $this->contarEjecucionesLimpieza($fechaInicio, $fechaFin),

            // ── Servicios ────────────────────────────────────────────────────
            in_array($codigo, ['HTB-SER-001', 'historico_precios'], true) => $this->contarPreciosServicios($estado),

            // ── Compras ──────────────────────────────────────────────────────
            in_array($codigo, ['HTB-COM-001', 'HTB-COM-002', 'HTB-COM-006', 'HTB-COM-009', 'solicitud', 'cotizacion', 'comparativa', 'trazabilidad_completa', 'orden_compra', 'recepcion', 'devolucion'], true) => 1,
            in_array($codigo, ['HTB-COM-010', 'HTB-COM-017', 'resumen_departamentos', 'solicitudes_estado'], true) => $this->contarSolicitudesCompras($fechaInicio, $fechaFin),
            in_array($codigo, ['HTB-COM-007', 'HTB-COM-008', 'HTB-COM-011', 'HTB-COM-013', 'HTB-COM-014', 'HTB-COM-015', 'rotacion_compras', 'tiempos_entrega', 'seguimiento_oc', 'analisis_precio', 'valorizacion_categoria', 'ranking_proveedores'], true) => $this->contarOrdenesCompra($fechaInicio, $fechaFin),
            in_array($codigo, ['HTB-COM-012', 'recepciones_proveedor'], true) => $this->contarRecepcionesCompra($fechaInicio, $fechaFin),
            in_array($codigo, ['HTB-COM-016', 'devoluciones'], true) => $this->contarDevolucionesCompra($fechaInicio, $fechaFin),

            // ── Activos ──────────────────────────────────────────────────────
            in_array($codigo, ['HTB-ACT-002', 'HTB-ACT-003', 'HTB-ACT-013', 'hoja_habitacion', 'ficha_activo', 'ficha_mantenimiento'], true) => 1,
            in_array($codigo, ['HTB-ACT-001', 'HTB-ACT-004', 'HTB-ACT-005', 'HTB-ACT-006', 'HTB-ACT-007', 'HTB-ACT-009', 'HTB-ACT-010', 'HTB-ACT-011', 'HTB-ACT-014', 'HTB-ACT-015', 'ficha_espacio', 'inventario_general', 'etiquetas', 'por_ubicacion', 'espacios_asignados', 'en_mantenimiento', 'bajas', 'extraviados', 'sin_asignacion', 'historial'], true) => $this->contarActivos(),
            in_array($codigo, ['HTB-ACT-008', 'HTB-ACT-012', 'garantias', 'manttos_vencidos'], true) => $this->contarMantenimientosActivos(),

            default => 0,
        };
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function contarCuentasCobrar(string $fechaInicio, string $fechaFin): int
    {
        $reservas = Reserva::query()
            ->where('saldo', '>', 0)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();

        $cuentas = Cuenta::query()
            ->where('saldo', '>', 0)
            ->count();

        return $reservas + $cuentas;
    }

    private function contarFacturacion(string $fechaInicio, string $fechaFin): int
    {
        return Factura::query()
            ->whereDate('fecha_emision', '>=', $fechaInicio)
            ->whereDate('fecha_emision', '<=', $fechaFin)
            ->where('estado', '!=', EstadoFactura::Anulada)
            ->count();
    }

    private function contarReservasOcupacion(string $fechaInicio, string $fechaFin, ?string $estado): int
    {
        return Reserva::query()
            ->whereNotNull('habitacion_id')
            ->whereDate('fecha_check_in', '>=', $fechaInicio)
            ->whereDate('fecha_check_in', '<=', $fechaFin)
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('estado', $estado))
            ->count();
    }

    private function contarReservasVentas(string $fechaInicio, string $fechaFin): int
    {
        return Reserva::query()
            ->whereNotNull('habitacion_id')
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();
    }

    private function contarReservasEstado(string $fechaInicio, string $fechaFin, ?string $estado): int
    {
        return Reserva::query()
            ->whereNotNull('habitacion_id')
            ->whereDate('fecha_check_in', '>=', $fechaInicio)
            ->whereDate('fecha_check_in', '<=', $fechaFin)
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('estado', $estado))
            ->count();
    }

    private function contarReservasHuespedes(string $fechaInicio, string $fechaFin): int
    {
        return Reserva::query()
            ->whereNotNull('habitacion_id')
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();
    }

    private function contarLotesInventario(string $codigo, string $fechaInicio, string $fechaFin): int
    {
        if (in_array($codigo, ['HTB-INV-001', 'HTB-INV-007', 'HTB-INV-009', 'stock', 'valorizacion', 'stock_minimo'], true)) {
            return Producto::query()->where('estado', 1)->count();
        }

        return Lote::query()
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();
    }

    private function contarProductos(): int
    {
        return Producto::query()->count();
    }

    private function contarEjecucionesLimpieza(string $fechaInicio, string $fechaFin): int
    {
        return LimpiezaEjecucion::query()
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();
    }

    private function contarPreciosServicios(?string $estado): int
    {
        return Precio::query()
            ->where('priceable_type', Servicio::class)
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('estado', (int) $estado))
            ->count();
    }

    private function contarSolicitudesCompras(string $fechaInicio, string $fechaFin): int
    {
        return Solicitud::query()
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();
    }

    private function contarOrdenesCompra(string $fechaInicio, string $fechaFin): int
    {
        return OrdenCompra::query()
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();
    }

    private function contarRecepcionesCompra(string $fechaInicio, string $fechaFin): int
    {
        return RecepcionCompra::query()
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();
    }

    private function contarDevolucionesCompra(string $fechaInicio, string $fechaFin): int
    {
        return DevolucionCompra::query()
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->count();
    }

    private function contarActivos(): int
    {
        return Activo::query()->count();
    }

    private function contarMantenimientosActivos(): int
    {
        return ActivoMantenimiento::query()->count();
    }
}
