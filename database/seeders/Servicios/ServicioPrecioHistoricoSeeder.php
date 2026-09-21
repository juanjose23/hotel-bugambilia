<?php

declare(strict_types=1);

namespace Database\Seeders\Servicios;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Seeder;

class ServicioPrecioHistoricoSeeder extends Seeder
{
    public function run(): void
    {
        $nio = Moneda::where('codigo', 'NIO')->first();
        $usd = Moneda::where('codigo', 'USD')->first();

        if (! $nio || ! $usd) {
            return;
        }

        $tipoCambioActual = 36.5;
        $tipoCambioHistorico = 36.0;

        $tarifasActuales = [
            'Early Check-in' => 350.00,
            'Late Check-out' => 500.00,
            'Cama adicional / Cuna' => 800.00,
            'Servicio a la Habitación 24/7' => 150.00,
            'Masaje Relajante Aromaterapia 60 min' => 1200.00,
            'Masaje Terapéutico Tejido Profundo 90 min' => 1650.00,
            'Circuito Hidroterapia & Sauna Seco' => 450.00,
            'Jacuzzi Privado Climatizado' => 950.00,
            'Clase de Yoga & Mindfulness al Amanecer' => 450.00,
            'Shuttle Aeropuerto VIP (Ida y Vuelta)' => 950.00,
            'Valet Parking & Custodia Vehicular' => 250.00,
            'Custodia de Equipaje Extendido' => 150.00,
            'Lavado y Secado de Ropa por Libra' => 120.00,
            'Planchado Profesional Express' => 80.00,
            'Limpieza Extra y Cambio de Blancos' => 350.00,
            'Alquiler de Sala de Juntas Ejecutiva' => 2500.00,
            'Alquiler de Proyector Láser & Sonido' => 1200.00,
            'Coffee Break Ejecutivo (por persona)' => 280.00,
            'Tour Guiado Colonial & Volcanes' => 1800.00,
            'Alquiler de Bicicletas de Montaña' => 300.00,
            'Decoración Romántica Premium' => 1500.00,
            'Cena Privada a la Luz de las Velas' => 2800.00,
            'Cesta de Bienvenida Gourmet' => 850.00,
            'WiFi Simétrico Dedicado Ultra Alta Velocidad' => 200.00,
            'Alquiler de Laptop Corporativa' => 1000.00,
        ];

        $servicios = Servicio::all();

        foreach ($servicios as $servicio) {
            $precioNioActual = $tarifasActuales[$servicio->nombre] ?? 500.00;
            $precioUsdActual = round($precioNioActual / $tipoCambioActual, 2);

            // 1. Tarifa Histórica (Año anterior: 2025, ~10-15% menor, estado inactivo/cerrado)
            $precioNioHistorico = round($precioNioActual * 0.88, 2);
            $precioUsdHistorico = round($precioNioHistorico / $tipoCambioHistorico, 2);

            // Histórico NIO
            Precio::updateOrCreate(
                [
                    'priceable_type' => Servicio::class,
                    'priceable_id' => $servicio->id,
                    'moneda_id' => $nio->id,
                    'fecha_inicio' => '2025-01-01',
                ],
                [
                    'precio' => $precioNioHistorico,
                    'fecha_fin' => '2025-12-31',
                    'estado' => 0, // Inactivo / Histórico
                    'es_oferta' => false,
                ]
            );

            // Histórico USD
            Precio::updateOrCreate(
                [
                    'priceable_type' => Servicio::class,
                    'priceable_id' => $servicio->id,
                    'moneda_id' => $usd->id,
                    'fecha_inicio' => '2025-01-01',
                ],
                [
                    'precio' => $precioUsdHistorico,
                    'fecha_fin' => '2025-12-31',
                    'estado' => 0, // Inactivo / Histórico
                    'es_oferta' => false,
                ]
            );

            // 2. Tarifa Vigente (Año 2026, estado activo)
            // Vigente NIO
            Precio::updateOrCreate(
                [
                    'priceable_type' => Servicio::class,
                    'priceable_id' => $servicio->id,
                    'moneda_id' => $nio->id,
                    'fecha_inicio' => '2026-01-01',
                ],
                [
                    'precio' => $precioNioActual,
                    'fecha_fin' => null,
                    'estado' => 1, // Activo / Vigente
                    'es_oferta' => false,
                ]
            );

            // Vigente USD
            Precio::updateOrCreate(
                [
                    'priceable_type' => Servicio::class,
                    'priceable_id' => $servicio->id,
                    'moneda_id' => $usd->id,
                    'fecha_inicio' => '2026-01-01',
                ],
                [
                    'precio' => $precioUsdActual,
                    'fecha_fin' => null,
                    'estado' => 1, // Activo / Vigente
                    'es_oferta' => false,
                ]
            );
        }
    }
}
