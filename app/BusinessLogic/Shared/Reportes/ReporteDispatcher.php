<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared\Reportes;

use App\Actions\Limpieza\Reportes\GenerarReporteOperacionHoteleraAction;
use App\Interactors\Activos\Reportes\GenerarReporteActivo;
use App\Interactors\Catalogos\Productos\GenerarReporteProductos;
use App\Interactors\Compras\Reportes\GenerarReporteCompra;
use App\Interactors\Inventario\Reportes\GenerarReporteInventario;
use App\Interactors\Reportes\Financiero\GenerarReporteFinanciero;
use App\Interactors\Reportes\Reservas\GenerarReporteReserva;
use App\Interactors\Servicios\Reportes\GenerarReporteServicio;
use Barryvdh\DomPDF\PDF;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteDispatcher
{
    private const LIMPIEZA_MAP = [
        'HTB-LIM-001' => 'operacion_hotelera',
        'HTB-LIM-002' => 'tiempo_promedio_limpieza',
        'HTB-LIM-003' => 'habitaciones_pendientes_bloqueadas',
        'HTB-LIM-004' => 'consumo_amenities_habitacion',
        'HTB-LIM-005' => 'productividad_colaborador_turno',
        'operacion_hotelera' => 'operacion_hotelera',
        'tiempo_promedio_limpieza' => 'tiempo_promedio_limpieza',
        'habitaciones_pendientes_bloqueadas' => 'habitaciones_pendientes_bloqueadas',
        'consumo_amenities_habitacion' => 'consumo_amenities_habitacion',
        'productividad_colaborador_turno' => 'productividad_colaborador_turno',
    ];

    private const FINANCIERO_MAP = [
        'HTB-FIN-001' => 'cuentasCobrarPdf',
        'HTB-FIN-002' => 'facturacionVentasPdf',
        'HTB-FIN-003' => 'resumenEjecutivoPdf',
        'cuentas_cobrar' => 'cuentasCobrarPdf',
        'facturacion_ventas' => 'facturacionVentasPdf',
        'resumen_ejecutivo' => 'resumenEjecutivoPdf',
    ];

    private const RESERVAS_MAP = [
        'HTB-RES-001' => 'ocupacionPdf',
        'HTB-RES-002' => 'ventasIngresosPdf',
        'HTB-RES-003' => 'reservasEstadoPdf',
        'HTB-RES-004' => 'huespedesPdf',
        'HTB-RES-005' => 'rendimientoHabitacionesPdf',
        'ocupacion' => 'ocupacionPdf',
        'ventas_ingresos' => 'ventasIngresosPdf',
        'reservas_estado' => 'reservasEstadoPdf',
        'huespedes' => 'huespedesPdf',
        'rendimiento_habitaciones' => 'rendimientoHabitacionesPdf',
    ];

    private const COMPRAS_MAP = [
        'HTB-COM-001' => 'solicitud',
        'HTB-COM-002' => 'cotizacion',
        'HTB-COM-003' => 'orden_compra',
        'HTB-COM-004' => 'recepcion',
        'HTB-COM-005' => 'devolucion',
        'HTB-COM-006' => 'comparativa',
        'HTB-COM-007' => 'rotacion_compras',
        'HTB-COM-008' => 'tiempos_entrega',
        'HTB-COM-009' => 'trazabilidad_completa',
        'HTB-COM-010' => 'solicitudes_estado',
        'HTB-COM-011' => 'seguimiento_oc',
        'HTB-COM-012' => 'recepciones_proveedor',
        'HTB-COM-013' => 'analisis_precio',
        'HTB-COM-014' => 'valorizacion_categoria',
        'HTB-COM-015' => 'ranking_proveedores',
        'HTB-COM-016' => 'devoluciones_proveedor',
        'HTB-COM-017' => 'resumen_departamentos',
        'rotacion_compras' => 'rotacion_compras',
        'tiempos_entrega' => 'tiempos_entrega',
        'resumen_departamentos' => 'resumen_departamentos',
        'solicitudes_estado' => 'solicitudes_estado',
        'seguimiento_oc' => 'seguimiento_oc',
        'recepciones_proveedor' => 'recepciones_proveedor',
        'analisis_precio' => 'analisis_precio',
        'valorizacion_categoria' => 'valorizacion_categoria',
        'ranking_proveedores' => 'ranking_proveedores',
        'devoluciones' => 'devoluciones_proveedor',
        'trazabilidad_completa' => 'trazabilidad_completa',
        'cotizacion' => 'cotizacion',
        'comparativa' => 'comparativa',
        'solicitud' => 'solicitud',
        'orden_compra' => 'orden_compra',
        'recepcion' => 'recepcion',
        'devolucion' => 'devolucion',
    ];

    private const INVENTARIO_MAP = [
        'HTB-INV-001' => 'stockPorProductoPdf',
        'HTB-INV-002' => 'movimientosPdf',
        'HTB-INV-003' => 'movimientosPdf',
        'HTB-INV-004' => 'cuarentenaPdf',
        'HTB-INV-005' => 'proximosVencerPdf',
        'HTB-INV-006' => 'mermasPdf',
        'HTB-INV-007' => 'valorizacionPdf',
        'HTB-INV-008' => 'rotacionPdf',
        'HTB-INV-009' => 'stockMinimoPdf',
        'HTB-INV-010' => 'ajustesPdf',
        'HTB-INV-011' => 'trazabilidadLotePdf',
        'HTB-INV-012' => 'vencidosPdf',
        'HTB-INV-013' => 'costoVentasPdf',
        'stock' => 'stockPorProductoPdf',
        'vencidos' => 'vencidosPdf',
        'proximos_vencer' => 'proximosVencerPdf',
        'cuarentena' => 'cuarentenaPdf',
        'valorizacion' => 'valorizacionPdf',
        'rotacion' => 'rotacionPdf',
        'mermas' => 'mermasPdf',
        'stock_minimo' => 'stockMinimoPdf',
        'ajustes' => 'ajustesPdf',
        'costo_ventas' => 'costoVentasPdf',
        'movimientos' => 'movimientosPdf',
        'trazabilidad_lote' => 'trazabilidadLotePdf',
    ];

    private const ACTIVOS_MAP = [
        'HTB-ACT-001' => 'inventarioGeneralPdf',
        'HTB-ACT-002' => 'fichaActivoPdf',
        'HTB-ACT-003' => 'fichaMantenimientoPdf',
        'HTB-ACT-004' => 'etiquetasPdf',
        'HTB-ACT-005' => 'porUbicacionPdf',
        'HTB-ACT-006' => 'historialMovimientosPdf',
        'HTB-ACT-007' => 'enMantenimientoPdf',
        'HTB-ACT-008' => 'garantiasProximasPdf',
        'HTB-ACT-009' => 'dadosDeBajaPdf',
        'HTB-ACT-010' => 'extraviadosPdf',
        'HTB-ACT-011' => 'sinAsignacionPdf',
        'HTB-ACT-012' => 'mantenimientosVencidosPdf',
        'HTB-ACT-013' => 'hojaHabitacionPdf',
        'HTB-ACT-014' => 'hojaHabitacionPdf',
        'HTB-ACT-015' => 'porUbicacionPdf',
        'inventario_general' => 'inventarioGeneralPdf',
        'etiquetas' => 'etiquetasPdf',
        'por_ubicacion' => 'porUbicacionPdf',
        'hoja_habitacion' => 'hojaHabitacionPdf',
        'espacios_asignados' => 'porUbicacionPdf',
        'ficha_espacio' => 'hojaHabitacionPdf',
        'en_mantenimiento' => 'enMantenimientoPdf',
        'manttos_vencidos' => 'mantenimientosVencidosPdf',
        'garantias' => 'garantiasProximasPdf',
        'historial' => 'historialMovimientosPdf',
        'bajas' => 'dadosDeBajaPdf',
        'extraviados' => 'extraviadosPdf',
        'sin_asignacion' => 'sinAsignacionPdf',
    ];

    private const SERVICIOS_MAP = [
        'HTB-SER-001' => 'historicoPreciosPdf',
        'historico_precios' => 'historicoPreciosPdf',
    ];

    public function __construct(
        private GenerarReporteFinanciero $financiero,
        private GenerarReporteReserva $reservas,
        private GenerarReporteProductos $catalogos,
        private GenerarReporteCompra $compras,
        private GenerarReporteInventario $inventario,
        private GenerarReporteActivo $activos,
        private GenerarReporteServicio $servicios,
        private GenerarReporteOperacionHoteleraAction $limpieza,
    ) {}

    /** @param array<string, mixed> $params */
    public function generar(string $codigo, array $params = []): PDF|Response|StreamedResponse
    {
        return match (true) {
            str_starts_with($codigo, 'HTB-FIN') || isset(self::FINANCIERO_MAP[$codigo]) => $this->generarFinanciero($codigo, $params),
            str_starts_with($codigo, 'HTB-RES') || isset(self::RESERVAS_MAP[$codigo]) => $this->generarReservas($codigo, $params),
            str_starts_with($codigo, 'HTB-CP') => $this->generarCatalogos($codigo, $params),
            str_starts_with($codigo, 'HTB-COM') || isset(self::COMPRAS_MAP[$codigo]) => $this->generarCompras($codigo, $params),
            str_starts_with($codigo, 'HTB-INV') || isset(self::INVENTARIO_MAP[$codigo]) => $this->generarInventario($codigo, $params),
            str_starts_with($codigo, 'HTB-ACT') || isset(self::ACTIVOS_MAP[$codigo]) => $this->generarActivos($codigo, $params),
            str_starts_with($codigo, 'HTB-SER') || isset(self::SERVICIOS_MAP[$codigo]) => $this->generarServicios($codigo, $params),
            str_starts_with($codigo, 'HTB-LIM') || isset(self::LIMPIEZA_MAP[$codigo]) => $this->generarLimpieza($codigo, $params),
            default => throw new InvalidArgumentException("Reporte '{$codigo}' no soportado."),
        };
    }

    /** @param array<string, mixed> $params */
    private function generarFinanciero(string $codigo, array $params): Response
    {
        $internalName = self::FINANCIERO_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Financiero '{$codigo}' no soportado.");
        }

        return $this->financiero->ejecutar($internalName, $params);
    }

    /** @param array<string, mixed> $params */
    private function generarReservas(string $codigo, array $params): Response
    {
        $internalName = self::RESERVAS_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Reservas '{$codigo}' no soportado.");
        }

        return $this->reservas->ejecutar($internalName, $params);
    }

    /** @param array<string, mixed> $params */
    private function generarCatalogos(string $codigo, array $params): PDF
    {
        return match ($codigo) {
            'HTB-CP001' => $this->catalogos->simple($params),
            'HTB-CP002' => $this->catalogos->detallado($params),
            'HTB-CP003' => $this->catalogos->etiquetas($params),
            default => throw new InvalidArgumentException("Reporte Catalogos '{$codigo}' no soportado."),
        };
    }

    /** @param array<string, mixed> $params */
    private function generarCompras(string $codigo, array $params): PDF
    {
        $internalName = self::COMPRAS_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Compras '{$codigo}' no soportado.");
        }

        return $this->compras->ejecutar($internalName, $params);
    }

    /** @param array<string, mixed> $params */
    private function generarInventario(string $codigo, array $params): PDF
    {
        $internalName = self::INVENTARIO_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Inventario '{$codigo}' no soportado.");
        }

        return $this->inventario->ejecutar($internalName, $params);
    }

    /** @param array<string, mixed> $params */
    private function generarActivos(string $codigo, array $params): PDF
    {
        $internalName = self::ACTIVOS_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Activos '{$codigo}' no soportado.");
        }

        $result = $this->activos->ejecutar($internalName, $params);

        if (! $result instanceof PDF) {
            throw new \UnexpectedValueException("El reporte '{$codigo}' no generó un PDF.");
        }

        return $result;
    }

    /** @param array<string, mixed> $params */
    private function generarServicios(string $codigo, array $params): Response|StreamedResponse
    {
        $internalName = self::SERVICIOS_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Servicios '{$codigo}' no soportado.");
        }

        return $this->servicios->ejecutar($internalName, $params);
    }

    /** @param array<string, mixed> $params */
    private function generarLimpieza(string $codigo, array $params): PDF
    {
        $internalName = self::LIMPIEZA_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Limpieza '{$codigo}' no soportado.");
        }

        return $this->limpieza->pdf(array_merge($params, ['reporte' => $internalName]));
    }

    /** @return array<string, array<string, string>> */
    public static function opcionesFiltro(): array
    {
        return [
            'Limpieza' => [
                'operacion_hotelera' => 'Operación hotelera',
                'tiempo_promedio_limpieza' => 'Tiempo promedio',
                'habitaciones_pendientes_bloqueadas' => 'Habitaciones pendientes y bloqueadas',
                'consumo_amenities_habitacion' => 'Consumo de amenities',
                'productividad_colaborador_turno' => 'Productividad colaborador y turno',
            ],
            'Financiero' => [
                'HTB-FIN-001' => 'Cuentas por Cobrar',
                'HTB-FIN-002' => 'Facturación y Ventas',
                'HTB-FIN-003' => 'Resumen Ejecutivo',
            ],
            'Reservas' => [
                'HTB-RES-001' => 'Ocupación y Estadías',
                'HTB-RES-002' => 'Ventas por Canal de Pago',
                'HTB-RES-003' => 'Reservas por Estado',
                'HTB-RES-004' => 'Huéspedes',
                'HTB-RES-005' => 'Rendimiento por Categoría',
            ],
            'Catálogos' => [
                'HTB-CP001' => 'Reporte Simple',
                'HTB-CP002' => 'Reporte Detallado',
                'HTB-CP003' => 'Etiquetas',
            ],
            'Compras' => [
                'rotacion_compras' => 'Rotación de compras',
                'tiempos_entrega' => 'Tiempos de entrega',
                'resumen_departamentos' => 'Resumen por departamentos',
                'solicitudes_estado' => 'Solicitudes por estado',
                'seguimiento_oc' => 'Seguimiento O.C.',
                'recepciones_proveedor' => 'Recepciones',
                'analisis_precio' => 'Análisis de precios',
                'valorizacion_categoria' => 'Valorización por categoría',
                'ranking_proveedores' => 'Ranking proveedores',
                'devoluciones' => 'Devoluciones',
                'trazabilidad_completa' => 'Trazabilidad',
            ],
            'Inventario' => [
                'stock' => 'Stock',
                'vencidos' => 'Vencidos',
                'proximos_vencer' => 'Próximos a vencer',
                'cuarentena' => 'Cuarentena',
                'valorizacion' => 'Valorización',
                'rotacion' => 'Rotación',
                'mermas' => 'Mermas',
                'stock_minimo' => 'Stock mínimo',
                'ajustes' => 'Ajustes',
                'costo_ventas' => 'Costo de ventas',
                'movimientos' => 'Movimientos',
                'trazabilidad_lote' => 'Trazabilidad por lote',
            ],
            'Activos' => [
                'inventario_general' => 'Inventario general',
                'por_ubicacion' => 'Por ubicación',
                'hoja_habitacion' => 'Hoja habitación',
                'espacios_asignados' => 'Espacios asignados',
                'ficha_espacio' => 'Ficha espacio',
                'en_mantenimiento' => 'En mantenimiento',
                'manttos_vencidos' => 'Mantenimientos vencidos',
                'garantias' => 'Garantías',
                'historial' => 'Historial',
                'bajas' => 'Bajas',
                'extraviados' => 'Extraviados',
                'sin_asignacion' => 'Sin asignación',
            ],
            'Servicios' => [
                'HTB-SER-001' => 'Histórico de Precios',
            ],
        ];
    }
}
