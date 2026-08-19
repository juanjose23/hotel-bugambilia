<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared\Reportes;

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
    private const FINANCIERO_MAP = [
        'cuentas_cobrar' => 'cuentasCobrarPdf',
        'facturacion_ventas' => 'facturacionVentasPdf',
        'resumen_ejecutivo' => 'resumenEjecutivoPdf',
    ];

    private const RESERVAS_MAP = [
        'ocupacion' => 'ocupacionPdf',
        'ventas_ingresos' => 'ventasIngresosPdf',
        'reservas_estado' => 'reservasEstadoPdf',
        'huespedes' => 'huespedesPdf',
        'rendimiento_habitaciones' => 'rendimientoHabitacionesPdf',
    ];

    private const COMPRAS_MAP = [
        'rotacion' => 'rotacion_compras',
        'tiempos_entrega' => 'tiempos_entrega',
        'resumen_departamentos' => 'resumen_departamentos',
        'solicitudes_estado' => 'solicitudes_estado',
        'seguimiento_oc' => 'seguimiento_oc',
        'recepciones_proveedor' => 'recepciones_proveedor',
        'analisis_precio' => 'analisis_precio',
        'valorizacion' => 'valorizacion_categoria',
        'ranking_proveedores' => 'ranking_proveedores',
        'devoluciones' => 'devoluciones_proveedor',
        'trazabilidad_completa' => 'trazabilidad_completa',
    ];

    private const INVENTARIO_MAP = [
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
        'inventario_general' => 'inventarioGeneralPdf',
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
    ) {}

    /** @param array<string, mixed> $params */
    public function generar(string $codigo, array $params = []): PDF|Response|StreamedResponse
    {
        return match (true) {
            str_starts_with($codigo, 'HTB-FIN') => $this->generarFinanciero($codigo, $params),
            str_starts_with($codigo, 'HTB-RES') => $this->generarReservas($codigo, $params),
            str_starts_with($codigo, 'HTB-CP') => $this->generarCatalogos($codigo, $params),
            str_starts_with($codigo, 'HTB-COM') => $this->generarCompras($codigo, $params),
            str_starts_with($codigo, 'HTB-INV') => $this->generarInventario($codigo, $params),
            str_starts_with($codigo, 'HTB-ACT') => $this->generarActivos($codigo, $params),
            str_starts_with($codigo, 'HTB-SER') => $this->generarServicios($codigo, $params),
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

    /** @return array<string, array<string, string>> */
    public static function opcionesFiltro(): array
    {
        return [
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
                'rotacion' => 'Rotación',
                'tiempos_entrega' => 'Tiempos de entrega',
                'resumen_departamentos' => 'Resumen por departamentos',
                'solicitudes_estado' => 'Solicitudes por estado',
                'seguimiento_oc' => 'Seguimiento O.C.',
                'recepciones_proveedor' => 'Recepciones',
                'analisis_precio' => 'Análisis de precios',
                'valorizacion' => 'Valorización',
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
