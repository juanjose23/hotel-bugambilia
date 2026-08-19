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
            'financiero' => [
                'cuentas_cobrar' => [
                    'codigo' => 'HTB-FIN-001',
                    'titulo' => 'Reporte de Cuentas por Cobrar',
                    'descripcion' => 'Informe detallado de saldos pendientes de reservaciones y cuentas corporativas por cobrar.',
                    'ruta_pdf' => 'admin.financiero.reportes.cuentas-cobrar.pdf',
                    'ruta_excel' => null,
                ],
                'facturacion_ventas' => [
                    'codigo' => 'HTB-FIN-002',
                    'titulo' => 'Reporte de Facturación Fiscal y Ventas',
                    'descripcion' => 'Recaudación total de ventas con desglose subtotal e impuestos fiscales.',
                    'ruta_pdf' => 'admin.financiero.reportes.facturacion-ventas.pdf',
                    'ruta_excel' => null,
                ],
                'resumen_ejecutivo' => [
                    'codigo' => 'HTB-FIN-003',
                    'titulo' => 'Resumen Ejecutivo y Estado de Resultados',
                    'descripcion' => 'Informe consolidado de ingresos brutos, recaudaciones y facturación para la toma de decisiones.',
                    'ruta_pdf' => 'admin.financiero.reportes.resumen-ejecutivo.pdf',
                    'ruta_excel' => null,
                ],
            ],
            'reservas' => [
                'ocupacion' => [
                    'codigo' => 'HTB-RES-001',
                    'titulo' => 'Reporte de Ocupación y Estadías',
                    'descripcion' => 'Informe detallado de porcentaje de ocupación, check-in, check-out y noches reservadas por rango de fecha.',
                    'ruta_pdf' => 'admin.reservas.reportes.ocupacion.pdf',
                    'ruta_excel' => null,
                ],
                'ventas_ingresos' => [
                    'codigo' => 'HTB-RES-002',
                    'titulo' => 'Ventas e Ingresos por Canal de Pago',
                    'descripcion' => 'Recaudación total de ventas desglosada por pasarela (Stripe, Transferencia, Efectivo) y saldos cobrados.',
                    'ruta_pdf' => 'admin.reservas.reportes.ventas-ingresos.pdf',
                    'ruta_excel' => null,
                ],
                'reservas_estado' => [
                    'codigo' => 'HTB-RES-003',
                    'titulo' => 'Reservas Agrupadas por Estado',
                    'descripcion' => 'Listado y totales acumulados de reservas clasificadas por estado (Confirmada, Pendiente, Cancelada, Finalizada).',
                    'ruta_pdf' => 'admin.reservas.reportes.reservas-estado.pdf',
                    'ruta_excel' => null,
                ],
                'huespedes' => [
                    'codigo' => 'HTB-RES-004',
                    'titulo' => 'Listado y Fichas de Huéspedes',
                    'descripcion' => 'Directorio de clientes titulares, historial de reservas y montos acumulados de estadía.',
                    'ruta_pdf' => 'admin.reservas.reportes.huespedes.pdf',
                    'ruta_excel' => null,
                ],
                'rendimiento_habitaciones' => [
                    'codigo' => 'HTB-RES-005',
                    'titulo' => 'Rendimiento por Categoría de Habitación',
                    'descripcion' => 'Estadísticas de demanda, ingresos promedio por noche (ADR) y categoría más reservada.',
                    'ruta_pdf' => 'admin.reservas.reportes.rendimiento-habitaciones.pdf',
                    'ruta_excel' => null,
                ],
            ],
            'compras' => [
                'solicitudes_estado' => [
                    'codigo' => 'HTB-COM-010',
                    'titulo' => 'Solicitudes por Estado',
                    'descripcion' => 'Detalle de las solicitudes de compra agrupadas y filtradas por su estado actual.',
                    'ruta_pdf' => 'admin.compras.reportes.solicitudes-estado',
                    'ruta_excel' => null,
                ],
                'seguimiento_oc' => [
                    'codigo' => 'HTB-COM-011',
                    'titulo' => 'Seguimiento de OC',
                    'descripcion' => 'Muestra el estado de avance, recepción y facturación de las Órdenes de Compra.',
                    'ruta_pdf' => 'admin.compras.reportes.seguimiento-oc',
                    'ruta_excel' => null,
                ],
                'recepciones_proveedor' => [
                    'codigo' => 'HTB-COM-012',
                    'titulo' => 'Recepciones por Proveedor',
                    'descripcion' => 'Historial de recepciones de mercancías clasificadas por proveedor.',
                    'ruta_pdf' => 'admin.compras.reportes.recepciones-proveedor',
                    'ruta_excel' => null,
                ],
                'resumen_departamentos' => [
                    'codigo' => 'HTB-COM-017',
                    'titulo' => 'Resumen por Departamento',
                    'descripcion' => 'Resumen acumulado del gasto en compras por departamento solicitante.',
                    'ruta_pdf' => 'admin.compras.reportes.resumen-departamentos',
                    'ruta_excel' => null,
                ],
                'analisis_precio' => [
                    'codigo' => 'HTB-COM-013',
                    'titulo' => 'Análisis de Precios',
                    'descripcion' => 'Análisis histórico de la variación de precios de compra para insumos clave.',
                    'ruta_pdf' => 'admin.compras.reportes.analisis-precio',
                    'ruta_excel' => null,
                ],
                'valorizacion' => [
                    'codigo' => 'HTB-COM-014',
                    'titulo' => 'Valorización por Categoría',
                    'descripcion' => 'Valorización total de las compras realizadas agrupadas por categorías.',
                    'ruta_pdf' => 'admin.compras.reportes.valorizacion',
                    'ruta_excel' => null,
                ],
                'rotacion' => [
                    'codigo' => 'HTB-COM-007',
                    'titulo' => 'Rotación de Compras',
                    'descripcion' => 'Cálculo del índice de rotación de las compras realizadas en un periodo de tiempo.',
                    'ruta_pdf' => 'admin.compras.reportes.rotacion',
                    'ruta_excel' => null,
                ],
                'tiempos_entrega' => [
                    'codigo' => 'HTB-COM-008',
                    'titulo' => 'Lead Time Proveedores',
                    'descripcion' => 'Lead time o tiempo promedio de entrega por cada proveedor.',
                    'ruta_pdf' => 'admin.compras.reportes.tiempos-entrega',
                    'ruta_excel' => null,
                ],
                'ranking_proveedores' => [
                    'codigo' => 'HTB-COM-015',
                    'titulo' => 'Ranking Proveedores',
                    'descripcion' => 'Evaluación y ranking de proveedores basado en volumen de compra e incidencias.',
                    'ruta_pdf' => 'admin.compras.reportes.ranking-proveedores',
                    'ruta_excel' => null,
                ],
                'devoluciones' => [
                    'codigo' => 'HTB-COM-016',
                    'titulo' => 'Devoluciones y Reclamos',
                    'descripcion' => 'Reporte detallado de devoluciones y reclamos a proveedores.',
                    'ruta_pdf' => 'admin.compras.reportes.devoluciones',
                    'ruta_excel' => null,
                ],
            ],
            'inventario' => [
                'stock' => [
                    'codigo' => 'HTB-INV-001',
                    'titulo' => 'Inventario de Productos',
                    'descripcion' => 'Filtra y descarga el stock disponible actual de tus almacenes.',
                    'ruta_pdf' => 'admin.inventario.reportes.stock-producto.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.stock-producto.excel',
                ],
                'movimientos' => [
                    'codigo' => 'HTB-INV-002',
                    'titulo' => 'Movimientos de Inventario',
                    'descripcion' => 'Historial de todos los movimientos de entradas, salidas, traslados y devoluciones.',
                    'ruta_pdf' => 'admin.inventario.reportes.movimientos.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.movimientos.excel',
                ],
                'vencidos' => [
                    'codigo' => 'HTB-INV-012',
                    'titulo' => 'Productos Vencidos',
                    'descripcion' => 'Descarga la lista de lotes cuya fecha de vencimiento ya expiró.',
                    'ruta_pdf' => 'admin.inventario.reportes.vencidos.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.vencidos.excel',
                ],
                'proximos_vencer' => [
                    'codigo' => 'HTB-INV-005',
                    'titulo' => 'Próximos Vencimientos',
                    'descripcion' => 'Filtra los productos que expiran en los siguientes días.',
                    'ruta_pdf' => 'admin.inventario.reportes.proximos-vencer.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.proximos-vencer.excel',
                ],
                'cuarentena' => [
                    'codigo' => 'HTB-INV-004',
                    'titulo' => 'Productos en Cuarentena',
                    'descripcion' => 'Descarga la lista de lotes retenidos por calidad en bodega.',
                    'ruta_pdf' => 'admin.inventario.reportes.cuarentena.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.cuarentena.excel',
                ],
                'valorizacion' => [
                    'codigo' => 'HTB-INV-007',
                    'titulo' => 'Valorización de Almacén',
                    'descripcion' => 'Genera el costo acumulado de todo el stock activo en Córdobas.',
                    'ruta_pdf' => 'admin.inventario.reportes.valorizacion.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.valorizacion.excel',
                ],
                'rotacion' => [
                    'codigo' => 'HTB-INV-008',
                    'titulo' => 'Rotación de Inventario',
                    'descripcion' => 'Analiza el movimiento promedio mensual de tus artículos.',
                    'ruta_pdf' => 'admin.inventario.reportes.rotacion.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.rotacion.excel',
                ],
                'mermas' => [
                    'codigo' => 'HTB-INV-006',
                    'titulo' => 'Mermas y Pérdidas',
                    'descripcion' => 'Filtra los productos desechados o perdidos en un rango de fechas.',
                    'ruta_pdf' => 'admin.inventario.reportes.mermas.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.mermas.excel',
                ],
                'stock_minimo' => [
                    'codigo' => 'HTB-INV-009',
                    'titulo' => 'Stock Mínimo y Punto de Pedido',
                    'descripcion' => 'Visualiza qué productos se encuentran bajo los límites mínimos operativos.',
                    'ruta_pdf' => 'admin.inventario.reportes.stock-minimo.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.stock-minimo.excel',
                ],
                'ajustes' => [
                    'codigo' => 'HTB-INV-010',
                    'titulo' => 'Ajustes de Inventario',
                    'descripcion' => 'Historial completo de ajustes de stock, pérdidas o diferencias.',
                    'ruta_pdf' => 'admin.inventario.reportes.ajustes.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.ajustes.excel',
                ],
                'costo_ventas' => [
                    'codigo' => 'HTB-INV-013',
                    'titulo' => 'Análisis de Costo de Ventas',
                    'descripcion' => 'Comparativa entre compras de insumos y consumos del servicio de limpieza.',
                    'ruta_pdf' => 'admin.inventario.reportes.costo-ventas.pdf',
                    'ruta_excel' => 'admin.inventario.reportes.costo-ventas.excel',
                ],
            ],
            'activos' => [
                'inventario_general' => [
                    'codigo' => 'HTB-ACT-001',
                    'titulo' => 'Inventario General de Activos',
                    'descripcion' => 'Reporte completo de todos los activos fijos registrados en el sistema, con filtros por estado.',
                    'ruta_pdf' => 'admin.activos.reportes.inventario-general.pdf',
                    'ruta_excel' => 'admin.activos.reportes.inventario-general.excel',
                ],
                'por_ubicacion' => [
                    'codigo' => 'HTB-ACT-005',
                    'titulo' => 'Activos por Ubicación',
                    'descripcion' => 'Agrupa los activos según su asignación actual (habitaciones, áreas comunes, bodegas).',
                    'ruta_pdf' => 'admin.activos.reportes.por-ubicacion.pdf',
                    'ruta_excel' => null,
                ],
                'hoja_habitacion' => [
                    'codigo' => 'HTB-ACT-013',
                    'titulo' => 'Hoja de Habitación o Espacio',
                    'descripcion' => 'Genera el inventario de activos fijos asignados a una habitación o espacio en particular.',
                    'ruta_pdf' => 'admin.activos.reportes.hoja-habitacion.pdf',
                    'ruta_excel' => null,
                ],
                'espacios_asignados' => [
                    'codigo' => 'HTB-ACT-015',
                    'titulo' => 'Activos por Espacio',
                    'descripcion' => 'Lista todos los activos fijos asignados a cada espacio o área común (restaurante, salones, gimnasio, spa, etc.).',
                    'ruta_pdf' => 'admin.activos.reportes.por-ubicacion.pdf',
                    'ruta_excel' => null,
                ],
                'ficha_espacio' => [
                    'codigo' => 'HTB-ACT-014',
                    'titulo' => 'Ficha de Espacio',
                    'descripcion' => 'Genera la hoja de inventario detallada de un espacio específico con todos sus activos asignados y firmas de control.',
                    'ruta_pdf' => 'admin.activos.reportes.hoja-habitacion.pdf',
                    'ruta_excel' => null,
                ],
                'en_mantenimiento' => [
                    'codigo' => 'HTB-ACT-007',
                    'titulo' => 'Activos en Mantenimiento',
                    'descripcion' => 'Lista todos los activos que se encuentran actualmente en reparación o mantenimiento.',
                    'ruta_pdf' => 'admin.activos.reportes.en-mantenimiento.pdf',
                    'ruta_excel' => null,
                ],
                'manttos_vencidos' => [
                    'codigo' => 'HTB-ACT-012',
                    'titulo' => 'Mantenimientos Vencidos',
                    'descripcion' => 'Reporte de todos los mantenimientos cuya fecha programada ya pasó y siguen pendientes.',
                    'ruta_pdf' => 'admin.activos.reportes.mantenimientos-vencidos.pdf',
                    'ruta_excel' => null,
                ],
                'garantias' => [
                    'codigo' => 'HTB-ACT-008',
                    'titulo' => 'Garantías Próximas a Vencer',
                    'descripcion' => 'Encuentra los activos cuyas garantías están a punto de vencer en los próximos días.',
                    'ruta_pdf' => 'admin.activos.reportes.garantias-proximas.pdf',
                    'ruta_excel' => null,
                ],
                'historial' => [
                    'codigo' => 'HTB-ACT-006',
                    'titulo' => 'Historial de Movimientos de un Activo',
                    'descripcion' => 'Línea de tiempo completa de asignaciones, mantenimientos y bajas de un activo específico.',
                    'ruta_pdf' => 'admin.activos.reportes.historial-movimientos.pdf',
                    'ruta_excel' => null,
                ],
                'bajas' => [
                    'codigo' => 'HTB-ACT-009',
                    'titulo' => 'Activos Dados de Baja',
                    'descripcion' => 'Listado histórico de todos los activos fijos que han sido dados de baja en el hotel.',
                    'ruta_pdf' => 'admin.activos.reportes.dados-de-baja.pdf',
                    'ruta_excel' => null,
                ],
                'extraviados' => [
                    'codigo' => 'HTB-ACT-010',
                    'titulo' => 'Activos Extraviados',
                    'descripcion' => 'Reportes rápidos para localizar activos marcados como extraviados.',
                    'ruta_pdf' => 'admin.activos.reportes.extraviados.pdf',
                    'ruta_excel' => null,
                ],
                'sin_asignacion' => [
                    'codigo' => 'HTB-ACT-011',
                    'titulo' => 'Activos Sin Asignar',
                    'descripcion' => 'Reportes rápidos para localizar activos que no tienen ninguna asignación vigente.',
                    'ruta_pdf' => 'admin.activos.reportes.sin-asignacion.pdf',
                    'ruta_excel' => null,
                ],
            ],
            'servicios' => [
                'historico_precios' => [
                    'codigo' => 'HTB-SER-001',
                    'titulo' => 'Histórico de Precios de Servicios',
                    'descripcion' => 'Historial completo de cambios de precios y tarifas para los servicios del hotel.',
                    'ruta_pdf' => 'admin.servicios.reportes.historico-precios.pdf',
                    'ruta_excel' => 'admin.servicios.reportes.historico-precios.excel',
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
     * Verifica si un reporte soporta exportación a Excel.
     */
    public static function tieneFormatoExcel(string $modulo, ?string $key): bool
    {
        if ($key === null) {
            return false;
        }

        return ! empty(self::getReportes()[$modulo][$key]['ruta_excel'] ?? null);
    }

    /**
     * Retorna los formatos disponibles ('pdf', 'excel') para un reporte.
     *
     * @return array<string, string>
     */
    public static function getFormatosDisponibles(string $modulo, ?string $key): array
    {
        $formatos = ['pdf' => 'Documento PDF (.pdf)'];

        if (self::tieneFormatoExcel($modulo, $key)) {
            $formatos['excel'] = 'Hoja de Cálculo Excel (.xlsx)';
        }

        return $formatos;
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
