<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared\Reportes;

use App\Actions\Catalogos\GenerarEtiquetasCodigosBarrasAction;
use App\Actions\Catalogos\GenerarReporteProductosAction;
use App\BusinessLogic\Catalogos\Data\ProductoFiltrosData;
use App\Interactors\Compras\Reportes\GenerarReporteCompraUseCase;
use App\Repository\Queries\Activos\GenerarReporteActivoUseCase;
use App\Repository\Queries\Inventario\Reportes\GenerarReporteInventario;
use Barryvdh\DomPDF\PDF;
use InvalidArgumentException;

final class ReporteDispatcher
{
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

    /** @param array<string, mixed> $params */
    public function generar(string $codigo, array $params = []): PDF
    {
        return match (true) {
            str_starts_with($codigo, 'HTB-CP') => $this->generarCatalogos($codigo, $params),
            str_starts_with($codigo, 'HTB-COM') => $this->generarCompras($codigo, $params),
            str_starts_with($codigo, 'HTB-INV') => $this->generarInventario($codigo, $params),
            str_starts_with($codigo, 'HTB-ACT') => $this->generarActivos($codigo, $params),
            default => $this->generarPorClave($codigo, $params),
        };
    }

    /** @param array<string, mixed> $params */
    private function generarCatalogos(string $codigo, array $params): PDF
    {
        $dto = ProductoFiltrosData::fromArray($params);

        return match ($codigo) {
            'HTB-CP001' => app(GenerarReporteProductosAction::class)->ejecutar($dto, false),
            'HTB-CP002' => app(GenerarReporteProductosAction::class)->ejecutar($dto),
            'HTB-CP003' => app(GenerarEtiquetasCodigosBarrasAction::class)->ejecutar($dto),
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

        $useCase = app(GenerarReporteCompraUseCase::class);

        return $useCase->execute($internalName, $params);
    }

    /** @param array<string, mixed> $params */
    private function generarInventario(string $codigo, array $params): PDF
    {
        $internalName = self::INVENTARIO_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Inventario '{$codigo}' no soportado.");
        }

        $useCase = app(GenerarReporteInventario::class);

        return $useCase->execute($internalName, $params);
    }

    /** @param array<string, mixed> $params */
    private function generarActivos(string $codigo, array $params): PDF
    {
        $internalName = self::ACTIVOS_MAP[$codigo] ?? null;

        if ($internalName === null) {
            throw new InvalidArgumentException("Reporte Activos '{$codigo}' no soportado.");
        }

        $useCase = app(GenerarReporteActivoUseCase::class);

        $result = $useCase->execute($internalName, $params);

        if (! $result instanceof PDF) {
            throw new \UnexpectedValueException("El reporte '{$codigo}' no generó un PDF.");
        }

        return $result;
    }

    /** @param array<string, mixed> $params */
    private function generarPorClave(string $clave, array $params): PDF
    {
        if (isset(self::COMPRAS_MAP[$clave])) {
            return $this->generarCompras($clave, $params);
        }

        if (isset(self::INVENTARIO_MAP[$clave])) {
            return $this->generarInventario($clave, $params);
        }

        if (isset(self::ACTIVOS_MAP[$clave])) {
            return $this->generarActivos($clave, $params);
        }

        throw new InvalidArgumentException("Reporte '{$clave}' no soportado.");
    }

    /** @return array<string, array<string, string>> */
    public static function opcionesFiltro(): array
    {
        return [
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
        ];
    }
}
