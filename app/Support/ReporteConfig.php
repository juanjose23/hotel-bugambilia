<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final readonly class ReporteConfig
{
    /**
     * Retorna la configuración completa de todos los reportes estructurada por módulos.
     *
     * @return array<string, array<string, array{
     *     codigo: string,
     *     titulo: string,
     *     descripcion: string,
     *     ruta_pdf: ?string,
     *     ruta_excel: ?string
     * }>>
     */
    public static function getReportes(): array
    {
        return [
            'compras' => [
                'solicitudes_estado' => [
                    'codigo' => 'HTB-COM-010',
                    'titulo' => 'Solicitudes por Estado',
                    'descripcion' => 'Detalle de las solicitudes de compra agrupadas y filtradas por su estado actual.',
                    'ruta_pdf' => 'reporte.compras.solicitudes-estado',
                    'ruta_excel' => null,
                ],
                'seguimiento_oc' => [
                    'codigo' => 'HTB-COM-011',
                    'titulo' => 'Seguimiento de OC',
                    'descripcion' => 'Muestra el estado de avance, recepción y facturación de las Órdenes de Compra.',
                    'ruta_pdf' => 'reporte.compras.seguimiento-oc',
                    'ruta_excel' => null,
                ],
                'recepciones_proveedor' => [
                    'codigo' => 'HTB-COM-012',
                    'titulo' => 'Recepciones por Proveedor',
                    'descripcion' => 'Historial de recepciones de mercancías clasificadas por proveedor.',
                    'ruta_pdf' => 'reporte.compras.recepciones-proveedor',
                    'ruta_excel' => null,
                ],
                'resumen_departamentos' => [
                    'codigo' => 'HTB-COM-005',
                    'titulo' => 'Resumen por Departamento',
                    'descripcion' => 'Resumen acumulado del gasto en compras por departamento solicitante.',
                    'ruta_pdf' => 'reporte.compras.departamentos',
                    'ruta_excel' => null,
                ],
                'analisis_precio' => [
                    'codigo' => 'HTB-COM-013',
                    'titulo' => 'Análisis de Precios',
                    'descripcion' => 'Análisis histórico de la variación de precios de compra para insumos clave.',
                    'ruta_pdf' => 'reporte.compras.analisis-precio',
                    'ruta_excel' => null,
                ],
                'valorizacion' => [
                    'codigo' => 'HTB-COM-014',
                    'titulo' => 'Valorización por Categoría',
                    'descripcion' => 'Valorización total de las compras realizadas agrupadas por categorías.',
                    'ruta_pdf' => 'reporte.compras.valorizacion',
                    'ruta_excel' => null,
                ],
                'rotacion' => [
                    'codigo' => 'HTB-COM-007',
                    'titulo' => 'Rotación de Compras',
                    'descripcion' => 'Cálculo del índice de rotación de las compras realizadas en un periodo de tiempo.',
                    'ruta_pdf' => 'reporte.compras.rotacion',
                    'ruta_excel' => null,
                ],
                'tiempos_entrega' => [
                    'codigo' => 'HTB-COM-008',
                    'titulo' => 'Lead Time Proveedores',
                    'descripcion' => 'Lead time o tiempo promedio de entrega por cada proveedor.',
                    'ruta_pdf' => 'reporte.compras.tiempos-entrega',
                    'ruta_excel' => null,
                ],
                'ranking_proveedores' => [
                    'codigo' => 'HTB-COM-015',
                    'titulo' => 'Ranking Proveedores',
                    'descripcion' => 'Evaluación y ranking de proveedores basado en volumen de compra e incidencias.',
                    'ruta_pdf' => 'reporte.compras.ranking-proveedores',
                    'ruta_excel' => null,
                ],
                'devoluciones' => [
                    'codigo' => 'HTB-COM-016',
                    'titulo' => 'Devoluciones y Reclamos',
                    'descripcion' => 'Reporte detallado de devoluciones y reclamos a proveedores.',
                    'ruta_pdf' => 'reporte.compras.devoluciones',
                    'ruta_excel' => null,
                ],
            ],
            'inventario' => [
                'stock' => [
                    'codigo' => 'HTB-INV-001',
                    'titulo' => 'Inventario de Productos',
                    'descripcion' => 'Filtra y descarga el stock disponible actual de tus almacenes.',
                    'ruta_pdf' => 'reporte.inventario.stock-producto.pdf',
                    'ruta_excel' => 'reporte.inventario.stock-producto.excel',
                ],
                'vencidos' => [
                    'codigo' => 'HTB-INV-002',
                    'titulo' => 'Productos Vencidos',
                    'descripcion' => 'Descarga la lista de lotes cuya fecha de vencimiento ya expiró.',
                    'ruta_pdf' => 'reporte.inventario.vencidos.pdf',
                    'ruta_excel' => 'reporte.inventario.vencidos.excel',
                ],
                'proximos_vencer' => [
                    'codigo' => 'HTB-INV-003',
                    'titulo' => 'Próximos Vencimientos',
                    'descripcion' => 'Filtra los productos que expiran en los siguientes días.',
                    'ruta_pdf' => 'reporte.inventario.proximos-vencer.pdf',
                    'ruta_excel' => 'reporte.inventario.proximos-vencer.excel',
                ],
                'cuarentena' => [
                    'codigo' => 'HTB-INV-004',
                    'titulo' => 'Productos en Cuarentena',
                    'descripcion' => 'Descarga la lista de lotes retenidos por calidad en bodega.',
                    'ruta_pdf' => 'reporte.inventario.cuarentena.pdf',
                    'ruta_excel' => 'reporte.inventario.cuarentena.excel',
                ],
                'valorizacion' => [
                    'codigo' => 'HTB-INV-005',
                    'titulo' => 'Valorización de Almacén',
                    'descripcion' => 'Genera el costo acumulado de todo el stock activo en Córdobas.',
                    'ruta_pdf' => 'reporte.inventario.valorizacion.pdf',
                    'ruta_excel' => 'reporte.inventario.valorizacion.excel',
                ],
                'rotacion' => [
                    'codigo' => 'HTB-INV-006',
                    'titulo' => 'Rotación de Inventario',
                    'descripcion' => 'Analiza el movimiento promedio mensual de tus artículos.',
                    'ruta_pdf' => null,
                    'ruta_excel' => 'reporte.inventario.rotacion.excel',
                ],
                'mermas' => [
                    'codigo' => 'HTB-INV-007',
                    'titulo' => 'Mermas y Pérdidas',
                    'descripcion' => 'Filtra los productos desechados o perdidos en un rango de fechas.',
                    'ruta_pdf' => 'reporte.inventario.mermas.pdf',
                    'ruta_excel' => 'reporte.inventario.mermas.excel',
                ],
                'stock_minimo' => [
                    'codigo' => 'HTB-INV-008',
                    'titulo' => 'Stock Mínimo y Punto de Pedido',
                    'descripcion' => 'Visualiza qué productos se encuentran bajo los límites mínimos operativos.',
                    'ruta_pdf' => 'reporte.inventario.stock-minimo.pdf',
                    'ruta_excel' => 'reporte.inventario.stock-minimo.excel',
                ],
                'ajustes' => [
                    'codigo' => 'HTB-INV-009',
                    'titulo' => 'Ajustes de Inventario',
                    'descripcion' => 'Historial completo de ajustes de stock, pérdidas o diferencias.',
                    'ruta_pdf' => 'reporte.inventario.ajustes.pdf',
                    'ruta_excel' => 'reporte.inventario.ajustes.excel',
                ],
                'costo_ventas' => [
                    'codigo' => 'HTB-INV-010',
                    'titulo' => 'Análisis de Costo de Ventas',
                    'descripcion' => 'Comparativa entre compras de insumos y consumos del servicio de limpieza.',
                    'ruta_pdf' => 'reporte.inventario.costo-ventas.pdf',
                    'ruta_excel' => 'reporte.inventario.costo-ventas.excel',
                ],
            ],
            'activos' => [
                'inventario_general' => [
                    'codigo' => 'HTB-ACT-001',
                    'titulo' => 'Inventario General de Activos',
                    'descripcion' => 'Reporte completo de todos los activos fijos registrados en el sistema, con filtros por estado.',
                    'ruta_pdf' => 'reporte.activos.inventario-general.pdf',
                    'ruta_excel' => 'reporte.activos.inventario-general.excel',
                ],
                'por_ubicacion' => [
                    'codigo' => 'HTB-ACT-002',
                    'titulo' => 'Activos por Ubicación',
                    'descripcion' => 'Agrupa los activos según su asignación actual (habitaciones, áreas comunes, bodegas).',
                    'ruta_pdf' => 'reporte.activos.por-ubicacion.pdf',
                    'ruta_excel' => null,
                ],
                'hoja_habitacion' => [
                    'codigo' => 'HTB-ACT-003',
                    'titulo' => 'Hoja de Habitación o Espacio',
                    'descripcion' => 'Genera el inventario de activos fijos asignados a una habitación o espacio en particular.',
                    'ruta_pdf' => 'reporte.activos.hoja-habitacion.pdf',
                    'ruta_excel' => null,
                ],
                'espacios_asignados' => [
                    'codigo' => 'HTB-ACT-004',
                    'titulo' => 'Activos por Espacio',
                    'descripcion' => 'Lista todos los activos fijos asignados a cada espacio o área común (restaurante, salones, gimnasio, spa, etc.).',
                    'ruta_pdf' => 'reporte.activos.por-ubicacion.pdf',
                    'ruta_excel' => null,
                ],
                'ficha_espacio' => [
                    'codigo' => 'HTB-ACT-005',
                    'titulo' => 'Ficha de Espacio',
                    'descripcion' => 'Genera la hoja de inventario detallada de un espacio específico con todos sus activos asignados y firmas de control.',
                    'ruta_pdf' => 'reporte.activos.hoja-habitacion.pdf',
                    'ruta_excel' => null,
                ],
                'en_mantenimiento' => [
                    'codigo' => 'HTB-ACT-006',
                    'titulo' => 'Activos en Mantenimiento',
                    'descripcion' => 'Lista todos los activos que se encuentran actualmente en reparación o mantenimiento.',
                    'ruta_pdf' => 'reporte.activos.en-mantenimiento.pdf',
                    'ruta_excel' => null,
                ],
                'manttos_vencidos' => [
                    'codigo' => 'HTB-ACT-007',
                    'titulo' => 'Mantenimientos Vencidos',
                    'descripcion' => 'Reporte de todos los mantenimientos cuya fecha programada ya pasó y siguen pendientes.',
                    'ruta_pdf' => 'reporte.activos.mantenimientos-vencidos.pdf',
                    'ruta_excel' => null,
                ],
                'garantias' => [
                    'codigo' => 'HTB-ACT-008',
                    'titulo' => 'Garantías Próximas a Vencer',
                    'descripcion' => 'Encuentra los activos cuyas garantías están a punto de vencer en los próximos días.',
                    'ruta_pdf' => 'reporte.activos.garantias-proximas.pdf',
                    'ruta_excel' => null,
                ],
                'historial' => [
                    'codigo' => 'HTB-ACT-009',
                    'titulo' => 'Historial de Movimientos de un Activo',
                    'descripcion' => 'Línea de tiempo completa de asignaciones, mantenimientos y bajas de un activo específico.',
                    'ruta_pdf' => 'reporte.activos.historial-movimientos.pdf',
                    'ruta_excel' => null,
                ],
                'bajas' => [
                    'codigo' => 'HTB-ACT-010',
                    'titulo' => 'Activos Dados de Baja',
                    'descripcion' => 'Listado histórico de todos los activos fijos que han sido dados de baja en el hotel.',
                    'ruta_pdf' => 'reporte.activos.dados-de-baja.pdf',
                    'ruta_excel' => null,
                ],
                'extraviados' => [
                    'codigo' => 'HTB-ACT-011',
                    'titulo' => 'Activos Extraviados',
                    'descripcion' => 'Reportes rápidos para localizar activos marcados como extraviados.',
                    'ruta_pdf' => 'reporte.activos.extraviados.pdf',
                    'ruta_excel' => null,
                ],
                'sin_asignacion' => [
                    'codigo' => 'HTB-ACT-012',
                    'titulo' => 'Activos Sin Asignar',
                    'descripcion' => 'Reportes rápidos para localizar activos que no tienen ninguna asignación vigente.',
                    'ruta_pdf' => 'reporte.activos.sin-asignacion.pdf',
                    'ruta_excel' => null,
                ],
            ],
            'servicios' => [
                'historico_precios' => [
                    'codigo' => 'HTB-SER-001',
                    'titulo' => 'Histórico de Precios de Servicios',
                    'descripcion' => 'Historial completo de cambios de precios y tarifas para los servicios del hotel.',
                    'ruta_pdf' => 'reporte.servicios.historico-precios.pdf',
                    'ruta_excel' => 'reporte.servicios.historico-precios.excel',
                ],
            ],
        ];
    }

    /**
     * Retorna las opciones para el Select de Filament de un módulo.
     * Formato: ['key' => 'Código — Título']
     *
     * @return array<string, string>
     */
    public static function getSelectOptions(string $modulo): array
    {
        $reportes = self::getReportes()[$modulo] ?? [];
        $options = [];

        foreach ($reportes as $key => $reporte) {
            $options[$key] = "{$reporte['codigo']} — {$reporte['titulo']}";
        }

        return $options;
    }

    /**
     * Retorna la descripción o resumen de contenido de un reporte.
     */
    public static function getDescripcion(string $modulo, ?string $key): ?string
    {
        if ($key === null) {
            return null;
        }

        return self::getReportes()[$modulo][$key]['descripcion'] ?? null;
    }

    /**
     * Retorna el nombre de la ruta para el formato solicitado (pdf o excel).
     */
    public static function getRuta(string $modulo, string $key, string $formato = 'pdf'): string
    {
        $reporte = self::getReportes()[$modulo][$key] ?? null;

        if ($reporte === null) {
            throw new InvalidArgumentException("Reporte '{$key}' no encontrado en el módulo '{$modulo}'.");
        }

        $ruta = $formato === 'excel' ? $reporte['ruta_excel'] : $reporte['ruta_pdf'];

        if ($ruta === null) {
            throw new InvalidArgumentException("El reporte '{$key}' del módulo '{$modulo}' no cuenta con ruta para el formato '{$formato}'.");
        }

        return $ruta;
    }

    /**
     * Genera la URL para el reporte y formato especificados.
     *
     * @param  array<string, mixed>  $params
     */
    public static function getUrl(string $modulo, string $key, array $params = [], string $formato = 'pdf'): string
    {
        $ruta = self::getRuta($modulo, $key, $formato);

        return route($ruta, $params);
    }
}
