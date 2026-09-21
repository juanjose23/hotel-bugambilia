<?php

declare(strict_types=1);

namespace Database\Seeders\Compras\Cotizaciones;

use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\User;
use Database\Seeders\Compras\Solicitudes\SolicitudesSeeder;
use Illuminate\Database\Seeder;

/**
 * Crea las cotizaciones de los escenarios del módulo Compras.
 *
 * @phpstan-import-type ContextoCompras from SolicitudesSeeder
 */
class CotizacionesSeeder extends Seeder
{
    public function run(): void
    {
        $this->ejecutar(app(SolicitudesSeeder::class)->ejecutar());
    }

    /**
     * @param  ContextoCompras  $ctx
     * @return ContextoCompras
     */
    public function ejecutar(array $ctx): array
    {
        $admin = User::where('email', 'admin@hotel.com')->first() ?? User::first();
        if (! $admin) {
            return $ctx;
        }

        $solicitudComp = $ctx['solicitudes']['SOL-COMP-2026'];
        $solicitudFull = $ctx['solicitudes']['SOL-STOCK-2026'];
        $solicitudManto = $ctx['solicitudes']['SOL-INFRA-2026'];

        $proveedores = $ctx['proveedores'];
        $condicionPago = $ctx['condicionPago'];
        $usdId = 1;

        // --- ESCENARIO 2: COMPARATIVA DE PRECIOS (COTIZACIONES PARA SOL-COMP-2026) ---
        if (Cotizacion::where('solicitud_id', $solicitudComp->id)->doesntExist()) {
            $precios = [100.00, 115.00, 150.00, 130.00, 105.00];
            $diasEntrega = [15, 5, 1, 3, 10];
            $observaciones = [
                'Precio más bajo garantizado, pero tiempo de entrega extendido.',
                'Balance ideal entre costo y tiempo de respuesta.',
                'Entrega inmediata. Stock garantizado.',
                'Proveedor local con soporte técnico incluido.',
                'Precio especial por apertura de cuenta corporativa.',
            ];

            foreach ($precios as $i => $precio) {
                $proveedor = $proveedores->get($i) ?? $proveedores->first();

                $sub = 0;
                $cot = Cotizacion::create([
                    'solicitud_id' => $solicitudComp->id,
                    'proveedor_id' => $proveedor?->id,
                    'fecha_cotizacion' => now()->subDays(10 - $i),
                    'dias_entrega' => $diasEntrega[$i],
                    'condicion_pago_id' => $condicionPago?->id,
                    'observaciones' => $observaciones[$i],
                    'creada_por' => $admin->id,
                    'moneda_id' => $usdId,
                    'subtotal' => 0,
                    'total' => 0,
                ]);

                foreach ($solicitudComp->items as $item) {
                    $sub += ($item->cantidad_aprobada * $precio);
                    $cot->items()->create([
                        'producto_id' => $item->producto_id,
                        'producto_variante_id' => $item->producto_variante_id,
                        'cantidad' => $item->cantidad_aprobada,
                        'precio_unitario' => $precio,
                        'subtotal' => $item->cantidad_aprobada * $precio,
                    ]);
                }
                $cot->update(['subtotal' => $sub, 'total' => $sub * 1.15]);
            }
        }

        // --- ESCENARIO 3: COTIZACIÓN ELEGIDA PARA EL FLUJO COMPLETO ---
        $cotWin = null;
        if (Cotizacion::where('solicitud_id', $solicitudFull->id)->doesntExist()) {
            $proveedorWin = $proveedores->get(0);

            $cotWin = Cotizacion::create([
                'solicitud_id' => $solicitudFull->id,
                'proveedor_id' => $proveedorWin?->id,
                'fecha_cotizacion' => now()->subDays(18),
                'dias_entrega' => 2,
                'condicion_pago_id' => $condicionPago?->id,
                'es_elegida' => true,
                'elegida_por' => $admin->id,
                'elegida_en' => now()->subDays(17),
                'subtotal' => 600,
                'total' => 690,
            ]);

            foreach ($solicitudFull->items as $item) {
                $cotWin->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'cantidad' => $item->cantidad_aprobada,
                    'precio_unitario' => 10.00,
                    'subtotal' => 200,
                    'es_elegido' => true,
                ]);
            }
        } else {
            $cotWin = Cotizacion::where('solicitud_id', $solicitudFull->id)->where('es_elegida', true)->first();
        }

        // --- ESCENARIO 4: COTIZACIONES DE INFRAESTRUCTURA (SOL-INFRA-2026) ---
        if (Cotizacion::where('solicitud_id', $solicitudManto->id)->doesntExist()) {
            for ($i = 0; $i < 3; $i++) {
                $cot = Cotizacion::create([
                    'solicitud_id' => $solicitudManto->id,
                    'proveedor_id' => $proveedores->random()->id,
                    'fecha_cotizacion' => now()->subDays(2),
                    'dias_entrega' => rand(2, 8),
                    'condicion_pago_id' => $condicionPago?->id,
                    'observaciones' => 'Propuesta técnica '.($i + 1),
                    'creada_por' => $admin->id,
                    'moneda_id' => $usdId,
                    'subtotal' => 0,
                    'total' => 0,
                ]);
                $subTotal = 0;
                foreach ($solicitudManto->items as $item) {
                    $precio = rand(50, 200);
                    $subTotal += ($item->cantidad_aprobada * $precio);
                    $cot->items()->create([
                        'producto_id' => $item->producto_id,
                        'producto_variante_id' => $item->producto_variante_id,
                        'cantidad' => $item->cantidad_aprobada,
                        'precio_unitario' => $precio,
                        'subtotal' => $item->cantidad_aprobada * $precio,
                    ]);
                }
                $cot->update(['subtotal' => $subTotal, 'total' => $subTotal * 1.15]);
            }
        }

        $ctx['cotizaciones'] = [
            'SOL-WIN' => $cotWin,
        ];

        return $ctx;
    }
}
