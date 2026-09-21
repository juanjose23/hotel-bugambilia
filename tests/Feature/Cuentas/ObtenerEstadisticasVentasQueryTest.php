<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Queries\Cuentas\ObtenerEstadisticasVentasQuery;

test('calcula estadisticas de ventas correctamente sin errores con base vacia o con registros', function (): void {
    $query = app(ObtenerEstadisticasVentasQuery::class);
    $stats = $query->ejecutar();

    expect($stats)->toBeArray()
        ->and($stats)->toHaveKeys([
            'total_ventas',
            'ventas_mes',
            'ventas_mes_anterior',
            'variacion_ventas_porcentaje',
            'ingresos_mes',
            'ingresos_mes_anterior',
            'variacion_ingresos_porcentaje',
            'ticket_promedio',
            'sparkline_ventas',
            'sparkline_ingresos',
            'sparkline_ticket',
            'conteos_tabs',
        ])
        ->and($stats['sparkline_ventas'])->toHaveCount(7)
        ->and($stats['sparkline_ingresos'])->toHaveCount(7)
        ->and($stats['sparkline_ticket'])->toHaveCount(7)
        ->and($stats['conteos_tabs'])->toHaveKeys([
            'todas',
            'emitidas',
            'anuladas',
            'con_factura',
            'sin_factura',
        ]);

    $moneda = Moneda::query()->firstOrCreate(
        ['codigo' => 'NIO'],
        [
            'nombre' => 'Córdoba',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]
    );

    Venta::query()->create([
        'numero_venta' => 'VNT-TEST-STAT-001',
        'subtotal' => 100.00,
        'impuesto_total' => 15.00,
        'descuento_total' => 0.00,
        'servicio_total' => 0.00,
        'propina_total' => 0.00,
        'recargo_total' => 0.00,
        'total' => 115.00,
        'estado' => EstadoVenta::Emitida,
        'moneda_id' => $moneda->id,
        'created_at' => now(),
    ]);

    $statsActualizadas = $query->ejecutar();

    expect($statsActualizadas['total_ventas'])->toBeGreaterThanOrEqual(1)
        ->and($statsActualizadas['ventas_mes'])->toBeGreaterThanOrEqual(1)
        ->and($statsActualizadas['ingresos_mes'])->toBeGreaterThanOrEqual(115.00)
        ->and($statsActualizadas['ticket_promedio'])->toBeGreaterThan(0);
});
