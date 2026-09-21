<?php

declare(strict_types=1);

use App\BusinessLogic\Shared\Reportes\ContarRegistrosReporte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('determina correctamente cuando se supera el umbral de segundo plano', function (): void {
    $contador = new ContarRegistrosReporte;

    // Resumen ejecutivo nunca supera el umbral (retorna 1)
    expect($contador->superaUmbral('HTB-FIN-003', []))->toBeFalse();
    expect($contador->contar('HTB-FIN-003', []))->toBe(1);

    // Códigos desconocidos retornan 0
    expect($contador->superaUmbral('CODIGO_INEXISTENTE', []))->toBeFalse();
    expect($contador->contar('CODIGO_INEXISTENTE', []))->toBe(0);
});

test('contar reportes de reservas consulta rango de fechas', function (): void {
    $contador = new ContarRegistrosReporte;

    // Rango futuro donde no hay reservas
    $count = $contador->contar('HTB-RES-001', [
        'fecha_inicio' => '2099-01-01',
        'fecha_fin' => '2099-01-31',
    ]);

    expect($count)->toBe(0);
    expect($contador->superaUmbral('HTB-RES-001', [
        'fecha_inicio' => '2099-01-01',
        'fecha_fin' => '2099-01-31',
    ]))->toBeFalse();
});

test('supera el umbral cuando los registros superan el limite de 10000', function (): void {
    expect(ContarRegistrosReporte::UMBRAL_SEGUNDO_PLANO)->toBe(10000);

    $contador = new ContarRegistrosReporte;

    // Cuando contar() devuelve <= 10000, superaUmbral es false
    expect($contador->superaUmbral('HTB-FIN-003', []))->toBeFalse();
});

test('documentos individuales nunca pasan a segundo plano', function (): void {
    $contador = new ContarRegistrosReporte;

    expect($contador->contar('solicitud', []))->toBe(1);
    expect($contador->contar('cotizacion', []))->toBe(1);
    expect($contador->contar('orden_compra', []))->toBe(1);
    expect($contador->contar('comparativa', []))->toBe(1);
    expect($contador->contar('trazabilidad_completa', []))->toBe(1);
    expect($contador->contar('ficha_activo', []))->toBe(1);
    expect($contador->contar('hoja_habitacion', []))->toBe(1);
    expect($contador->superaUmbral('ficha_activo', []))->toBeFalse();
});

test('contar reportes de compras consulta ordenes y solicitudes por fecha', function (): void {
    $contador = new ContarRegistrosReporte;

    // Rango futuro donde no hay registros
    $params = [
        'fecha_inicio' => '2099-01-01',
        'fecha_fin' => '2099-01-31',
    ];

    expect($contador->contar('resumen_departamentos', $params))->toBe(0);
    expect($contador->contar('HTB-COM-017', $params))->toBe(0);
    expect($contador->contar('solicitudes_estado', $params))->toBe(0);
    expect($contador->contar('seguimiento_oc', $params))->toBe(0);
    expect($contador->contar('recepciones_proveedor', $params))->toBe(0);
    expect($contador->contar('devoluciones', $params))->toBe(0);
    expect($contador->superaUmbral('seguimiento_oc', $params))->toBeFalse();
});

test('contar reportes de activos, limpieza, servicios y catalogos', function (): void {
    $contador = new ContarRegistrosReporte;

    // Rango futuro donde no hay ejecuciones de limpieza
    $params = [
        'fecha_desde' => '2099-01-01',
        'fecha_hasta' => '2099-01-31',
    ];

    expect($contador->contar('operacion_hotelera', $params))->toBe(0);

    // Sin filtros y sin registros creados, los conteos no superan el umbral
    expect($contador->superaUmbral('inventario_general', []))->toBeFalse();
    expect($contador->contar('HTB-ACT-014', []))->toBe(0);
    expect($contador->contar('HTB-ACT-015', []))->toBe(0);
    expect($contador->superaUmbral('etiquetas', []))->toBeFalse();
    expect($contador->superaUmbral('manttos_vencidos', []))->toBeFalse();
    expect($contador->superaUmbral('HTB-SER-001', []))->toBeFalse();
    expect($contador->superaUmbral('HTB-CP001', []))->toBeFalse();
});
