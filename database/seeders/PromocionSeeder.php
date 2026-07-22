<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Interactors\Promociones\GenerarCodigoPromocion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Seeder;

class PromocionSeeder extends Seeder
{
    public function run(): void
    {
        $nio = Moneda::where('codigo', 'NIO')->first();
        $usd = Moneda::where('codigo', 'USD')->first();
        $tipoCambio = 36.5;

        if (! $nio || ! $usd) {
            $this->command->warn('Monedas no encontradas. Ejecute TasaCambioSeeder primero.');

            return;
        }

        $tipoPaquete = Catalogo::where('codigo', 'PROMO_PAQUETE')->first();
        $tipoEstancia = Catalogo::where('codigo', 'PROMO_ESTANCIA')->first();
        $tipoTemporada = Catalogo::where('codigo', 'PROMO_TEMPORADA')->first();

        $servicioMasaje = Servicio::where('nombre', 'Masaje relajante 60 min')->first();
        $servicioJacuzzi = Servicio::where('nombre', 'Jacuzzi privado 1 hora')->first();
        $servicioCena = Servicio::where('nombre', 'Decoración romántica')->first();
        $servicioTour = Servicio::where('nombre', 'Tour turístico local')->first();

        $habSuite = Habitacion::where('codigo', 'HAB-003')->first();
        $habDeluxe = Habitacion::where('codigo', 'HAB-002')->first();

        $generarCodigo = app(GenerarCodigoPromocion::class);

        // --- Promoción 1: Escapada Romántica ---
        $promo1 = Promocion::create([
            'codigo' => $generarCodigo->ejecutar(),
            'nombre' => 'Escapada Romántica',
            'tipo_promocion_id' => $tipoPaquete?->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonths(6)->toDateString(),
            'descuento_porcentaje' => 20,
            'descripcion' => 'Vive una experiencia inolvidable con nuestra escapada romántica. Incluye habitación suite, masaje relajante, jacuzzi privado y cena romántica con decoración especial.',
            'condiciones' => 'Válido para parejas. No acumulable con otras promociones. Sujeto a disponibilidad.',
            'estado' => 1,
            'web' => true,
            'orden' => 1,
        ]);

        if ($habSuite) {
            $promo1->items()->create([
                'item_type' => Habitacion::class,
                'item_id' => $habSuite->id,
                'precio_especial' => round(2500 / $tipoCambio, 2),
            ]);
        }
        if ($servicioMasaje) {
            $promo1->items()->create([
                'item_type' => Servicio::class,
                'item_id' => $servicioMasaje->id,
                'precio_especial' => round(900 / $tipoCambio, 2),
            ]);
        }
        if ($servicioJacuzzi) {
            $promo1->items()->create([
                'item_type' => Servicio::class,
                'item_id' => $servicioJacuzzi->id,
                'precio_especial' => round(700 / $tipoCambio, 2),
            ]);
        }
        if ($servicioCena) {
            $promo1->items()->create([
                'item_type' => Servicio::class,
                'item_id' => $servicioCena->id,
                'precio_especial' => round(1200 / $tipoCambio, 2),
            ]);
        }

        Precio::create([
            'priceable_type' => Promocion::class,
            'priceable_id' => $promo1->id,
            'moneda_id' => $nio->id,
            'precio' => 5300,
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => true,
        ]);
        Precio::create([
            'priceable_type' => Promocion::class,
            'priceable_id' => $promo1->id,
            'moneda_id' => $usd->id,
            'precio' => round(5300 / $tipoCambio, 2),
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => true,
        ]);

        // --- Promoción 2: Estancia Prolongada ---
        $promo2 = Promocion::create([
            'codigo' => $generarCodigo->ejecutar(),
            'nombre' => 'Estancia Prolongada 7x6',
            'tipo_promocion_id' => $tipoEstancia?->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addYear()->toDateString(),
            'descuento_porcentaje' => 15,
            'descripcion' => 'Disfrute de 7 noches pagando solo 6 en nuestra habitación Deluxe. Además incluimos un tour turístico local completamente gratis.',
            'condiciones' => 'Válido para estadías de 7 noches consecutivas. No aplica en temporada alta.',
            'estado' => 1,
            'web' => true,
            'orden' => 2,
        ]);

        if ($habDeluxe) {
            $promo2->items()->create([
                'item_type' => Habitacion::class,
                'item_id' => $habDeluxe->id,
            ]);
        }
        if ($servicioTour) {
            $promo2->items()->create([
                'item_type' => Servicio::class,
                'item_id' => $servicioTour->id,
                'precio_especial' => 0,
            ]);
        }

        Precio::create([
            'priceable_type' => Promocion::class,
            'priceable_id' => $promo2->id,
            'moneda_id' => $nio->id,
            'precio' => 10800,
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => true,
        ]);
        Precio::create([
            'priceable_type' => Promocion::class,
            'priceable_id' => $promo2->id,
            'moneda_id' => $usd->id,
            'precio' => round(10800 / $tipoCambio, 2),
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => true,
        ]);

        // --- Promoción 3: Reserva Anticipada ---
        $promo3 = Promocion::create([
            'codigo' => $generarCodigo->ejecutar(),
            'nombre' => 'Reserva Anticipada 15% OFF',
            'tipo_promocion_id' => $tipoTemporada?->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonths(3)->toDateString(),
            'descuento_porcentaje' => 15,
            'descripcion' => 'Reserve con al menos 30 días de anticipación y obtenga un 15% de descuento en su estancia.',
            'condiciones' => 'Reserva mínima con 30 días de anticipación. Válido para cualquier tipo de habitación.',
            'estado' => 1,
            'web' => true,
            'orden' => 3,
        ]);

        Precio::create([
            'priceable_type' => Promocion::class,
            'priceable_id' => $promo3->id,
            'moneda_id' => $nio->id,
            'precio' => 0,
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => true,
        ]);

        // --- Promoción 4: Cena Romántica + Restaurante ---
        $restaurante = Espacio::where('tipo', 'restaurante')->where('estado', 1)->first();
        $servicioCena = Servicio::where('nombre', 'Decoración romántica')->first();
        $servicioLomo = Servicio::where('nombre', 'Lomo de cerdo a la plancha')->first();
        $servicioPostre = Servicio::where('nombre', 'Tres leches')->first();

        $tipoPaqueteId = $tipoPaquete?->id;
        $promo4 = Promocion::create([
            'codigo' => $generarCodigo->ejecutar(),
            'nombre' => 'Cena Romántica en Restaurante',
            'tipo_promocion_id' => $tipoPaqueteId,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addYear()->toDateString(),
            'descuento_porcentaje' => 25,
            'descripcion' => 'Cena romántica privada en nuestro restaurante. Incluye espacio reservado, 2 platos fuertes, postre y decoración especial.',
            'condiciones' => 'Reserva con 24 horas de anticipación. Válido de lunes a sábado.',
            'estado' => 1,
            'web' => true,
            'orden' => 4,
        ]);

        if ($restaurante) {
            $promo4->items()->create(['item_type' => Espacio::class, 'item_id' => $restaurante->id]);
        }
        if ($servicioCena) {
            $promo4->items()->create(['item_type' => Servicio::class, 'item_id' => $servicioCena->id, 'precio_especial' => 800]);
        }
        if ($servicioLomo) {
            $promo4->items()->create(['item_type' => Servicio::class, 'item_id' => $servicioLomo->id, 'precio_especial' => 250]);
        }
        if ($servicioPostre) {
            $promo4->items()->create(['item_type' => Servicio::class, 'item_id' => $servicioPostre->id, 'precio_especial' => 100]);
        }

        Precio::create([
            'priceable_type' => Promocion::class,
            'priceable_id' => $promo4->id,
            'moneda_id' => $nio->id,
            'precio' => 1500,
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => true,
        ]);
        Precio::create([
            'priceable_type' => Promocion::class,
            'priceable_id' => $promo4->id,
            'moneda_id' => $usd->id,
            'precio' => round(1500 / $tipoCambio, 2),
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => true,
        ]);

        $this->command->info('Promociones sembradas exitosamente.');
    }
}
