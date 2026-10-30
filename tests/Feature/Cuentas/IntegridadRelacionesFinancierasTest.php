<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/** @return array{estancias: int, cuentas: int, ventas: int, pedidos: int, pagos_cuenta: int} */
function crearRelacionesFinancierasNormalizacion(): array
{
    $reserva = DB::table('reservas')->insertGetId([
        'codigo_reserva' => 'RES-NORMALIZACION',
        'nombre_cliente' => 'Cliente de prueba',
        'tipo_reserva' => 1,
        'fecha_check_in' => '2026-11-01',
        'fecha_check_out' => '2026-11-02',
    ]);
    $estancia = DB::table('estancias')->insertGetId([
        'reserva_id' => $reserva,
        'check_in_at' => now(),
    ]);
    $cuenta = DB::table('cuentas')->insertGetId([
        'numero_cuenta' => 'CTA-NORMALIZACION',
        'estancia_id' => $estancia,
        'abierta_at' => now(),
    ]);
    $venta = DB::table('ventas')->insertGetId([
        'numero_venta' => 'VEN-NORMALIZACION',
        'cuenta_id' => $cuenta,
    ]);
    $pedido = DB::table('pedidos')->insertGetId([
        'codigo' => 'PED-NORMALIZACION',
        'cuenta_id' => $cuenta,
    ]);
    $pago = DB::table('pagos_cuenta')->insertGetId([
        'cuenta_id' => $cuenta,
        'venta_id' => $venta,
        'forma_pago' => 1,
        'monto' => 10,
    ]);

    return ['estancias' => $estancia, 'cuentas' => $cuenta, 'ventas' => $venta, 'pedidos' => $pedido, 'pagos_cuenta' => $pago];
}

dataset('relaciones financieras directas', [
    'cuenta y estancia' => ['cuentas', 'estancia_id', 'estancias'],
    'pedido y cuenta' => ['pedidos', 'cuenta_id', 'cuentas'],
    'pago y venta' => ['pagos_cuenta', 'venta_id', 'ventas'],
]);

test('rechaza referencias financieras inexistentes', function (string $tabla, string $columna, string $destino): void {
    $ids = crearRelacionesFinancierasNormalizacion();
    $inexistente = $ids[$destino] + 1000000;

    expect(fn () => DB::transaction(fn () => DB::table($tabla)->where('id', $ids[$tabla])->update([$columna => $inexistente])))
        ->toThrow(QueryException::class);

    expect(DB::table($tabla)->where('id', $ids[$tabla])->value($columna))->toBe($ids[$destino]);
})->with('relaciones financieras directas');

test('conserva relaciones opcionales sin crear entidades artificiales', function (string $tabla, string $columna, string $destino): void {
    $ids = crearRelacionesFinancierasNormalizacion();
    DB::table($tabla)->where('id', $ids[$tabla])->update([$columna => null]);

    expect(DB::table($tabla)->where('id', $ids[$tabla])->value($columna))->toBeNull()
        ->and(DB::table($destino)->where('id', $ids[$destino])->exists())->toBeTrue();
})->with('relaciones financieras directas');

test('impide borrar fisicamente el origen vinculado al historial financiero', function (string $tabla, string $columna, string $destino): void {
    $ids = crearRelacionesFinancierasNormalizacion();

    if ($destino === 'cuentas') {
        // Aislar la proteccion del pedido de la FK ya existente del pago.
        DB::table('pagos_cuenta')->where('id', $ids['pagos_cuenta'])->delete();
    }

    expect(fn () => DB::transaction(fn () => DB::table($destino)->where('id', $ids[$destino])->delete()))
        ->toThrow(QueryException::class);

    expect(DB::table($destino)->where('id', $ids[$destino])->exists())->toBeTrue()
        ->and(DB::table($tabla)->where('id', $ids[$tabla])->value($columna))->toBe($ids[$destino]);
})->with('relaciones financieras directas');

test('mantiene la vinculacion historica al dar de baja logicamente una cuenta', function (): void {
    $ids = crearRelacionesFinancierasNormalizacion();
    DB::table('cuentas')->where('id', $ids['cuentas'])->update(['deleted_at' => now()]);

    expect(DB::table('pedidos')->where('id', $ids['pedidos'])->value('cuenta_id'))->toBe($ids['cuentas']);
});
