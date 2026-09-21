<?php

declare(strict_types=1);

namespace Database\Seeders\Promociones;

use App\Interactors\Promociones\GenerarCodigoPromocion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromocionDefinicionSeeder extends Seeder
{
    public function run(): void
    {
        $nio = Moneda::query()->where('codigo', 'NIO')->first();
        $usd = Moneda::query()->where('codigo', 'USD')->first();
        $tipoCambio = 36.5;

        if (! $nio || ! $usd) {
            $this->command->warn('Monedas no encontradas. Ejecute MonedaSeeder / TasaCambioSeeder primero.');

            return;
        }

        $tipoPaquete = Catalogo::query()->where('codigo', 'PROMO_PAQUETE')->first();
        $tipoEstancia = Catalogo::query()->where('codigo', 'PROMO_ESTANCIA')->first();
        $tipoTemporada = Catalogo::query()->where('codigo', 'PROMO_TEMPORADA')->first();
        $tipoAnticipada = Catalogo::query()->where('codigo', 'PROMO_ANTICIPADA')->first();
        $tipoEvento = Catalogo::query()->where('codigo', 'PROMO_EVENTO')->first();

        // Habitaciones clave
        $habSuite = Habitacion::query()->where('nombre', 'like', '%Suite%')->first()
            ?? Habitacion::query()->where('numero', 201)->first()
            ?? Habitacion::query()->first();

        $habDeluxe = Habitacion::query()->where('nombre', 'like', '%Deluxe%')->first()
            ?? Habitacion::query()->where('numero', 105)->first()
            ?? Habitacion::query()->first();

        $habFamiliar = Habitacion::query()->where('nombre', 'like', '%Familiar%')->first()
            ?? Habitacion::query()->where('numero', 205)->first()
            ?? Habitacion::query()->first();

        $habDoble = Habitacion::query()->where('nombre', 'like', '%Doble%')->first()
            ?? Habitacion::query()->where('numero', 102)->first()
            ?? Habitacion::query()->first();

        // Servicios clave
        $servicioMasaje = Servicio::query()->where('nombre', 'like', '%Masaje%')->first();
        $servicioJacuzzi = Servicio::query()->where('nombre', 'like', '%Jacuzzi%')->first();
        $servicioCena = Servicio::query()->where('nombre', 'like', '%Decoración%')->first()
            ?? Servicio::query()->where('nombre', 'like', '%Cena%')->first();
        $servicioTour = Servicio::query()->where('nombre', 'like', '%Tour%')->first();
        $servicioLavanderia = Servicio::query()->where('nombre', 'like', '%Lavado%')->first();
        $servicioDesayuno = Servicio::query()->where('nombre', 'like', '%Coffee%')->first()
            ?? Servicio::query()->where('nombre', 'like', '%Desayuno%')->first();
        $servicioPiscina = Servicio::query()->where('nombre', 'like', '%Piscina%')->first()
            ?? Servicio::query()->where('nombre', 'like', '%Hidroterapia%')->first();

        // Espacios
        $espacioRestaurante = Espacio::query()->where('tipo', 'restaurante')->first()
            ?? Espacio::query()->where('nombre', 'like', '%Restaurante%')->first();
        $espacioTerraza = Espacio::query()->where('nombre', 'like', '%Terraza%')->first();
        $espacioPiscina = Espacio::query()->where('nombre', 'like', '%Piscina%')->first();

        // Políticas
        $politicaCancelacion = Politica::query()->where('titulo', 'like', '%Cancelación y Reprogramación%')->first();
        $politicaGarantia = Politica::query()->where('titulo', 'like', '%Garantía y Anticipo%')->first();
        $politicaRomantica = Politica::query()->where('titulo', 'like', '%Paquetes Románticos%')->first();
        $politicaNoReembolso = Politica::query()->where('titulo', 'like', '%No Reembolso%')->first();
        $politicaPiscina = Politica::query()->where('titulo', 'like', '%Piscina & Spa%')->first();

        $generadorCodigo = app(GenerarCodigoPromocion::class);

        $paquetes = [
            // ─── 01. ESCAPADA ROMÁNTICA TODO INCLUIDO ───
            [
                'codigo' => 'PROM-ROMANTICA',
                'nombre' => 'Escapada Romántica Todo Incluido',
                'tipo_promocion_id' => $tipoPaquete?->id,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addMonths(12)->toDateString(),
                'descuento_porcentaje' => 20.00,
                'descuento_monto' => null,
                'precio_paquete' => 5300.00,
                'descripcion' => 'Vive una experiencia inolvidable en pareja. Incluye 1 noche en Suite de lujo, sesión de masaje relajante en pareja (60 min), acceso exclusivo a jacuzzi privado climatizado, botella de vino de cortesía y cena romántica a la luz de las velas.',
                'condiciones' => 'Válido para parejas. Requiere reserva con mínimo 48 horas de anticipación. Sujeto a disponibilidad de suite.',
                'estado' => 1,
                'web' => true,
                'orden' => 1,
                'imagenes' => [
                    ['url' => '/images/main-room.webp', 'orden' => 1],
                    ['url' => '/images/room-detail.webp', 'orden' => 2],
                    ['url' => '/images/restaurant-hero.jpg', 'orden' => 3],
                ],
                'items' => [
                    ['type' => Habitacion::class, 'id' => $habSuite?->id, 'precio' => 2500.00],
                    ['type' => Servicio::class, 'id' => $servicioMasaje?->id, 'precio' => 900.00],
                    ['type' => Servicio::class, 'id' => $servicioJacuzzi?->id, 'precio' => 700.00],
                    ['type' => Servicio::class, 'id' => $servicioCena?->id, 'precio' => 1200.00],
                ],
                'politicas' => [$politicaCancelacion?->id, $politicaGarantia?->id, $politicaRomantica?->id],
                'precio_nio' => 5300.00,
                'precio_usd' => round(5300.00 / $tipoCambio, 2),
            ],

            // ─── 02. ESTANCIA PROLONGADA 7X6 ───
            [
                'codigo' => 'PROM-ESTANCIA7X6',
                'nombre' => 'Estancia Prolongada 7x6 (Pague 6, Duerma 7)',
                'tipo_promocion_id' => $tipoEstancia?->id,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addMonths(18)->toDateString(),
                'descuento_porcentaje' => 15.00,
                'descuento_monto' => null,
                'precio_paquete' => 10800.00,
                'descripcion' => 'Disfrute de 7 noches consecutivas pagando únicamente 6 en nuestra habitación Deluxe con vista panorámica. Incluye servicio de lavado de cortesía y tour turístico guiado por los principales atractivos de la región.',
                'condiciones' => 'Válido para estadías continuas de 7 noches. No fraccionable. No acumulable con otras promociones.',
                'estado' => 1,
                'web' => true,
                'orden' => 2,
                'imagenes' => [
                    ['url' => '/images/group-room.webp', 'orden' => 1],
                    ['url' => '/images/terrace.webp', 'orden' => 2],
                    ['url' => '/images/hero-secondary.webp', 'orden' => 3],
                ],
                'items' => [
                    ['type' => Habitacion::class, 'id' => $habDeluxe?->id, 'precio' => 9000.00],
                    ['type' => Servicio::class, 'id' => $servicioTour?->id, 'precio' => 0.00],
                    ['type' => Servicio::class, 'id' => $servicioLavanderia?->id, 'precio' => 0.00],
                ],
                'politicas' => [$politicaCancelacion?->id, $politicaGarantia?->id],
                'precio_nio' => 10800.00,
                'precio_usd' => round(10800.00 / $tipoCambio, 2),
            ],

            // ─── 03. RESERVA ANTICIPADA EARLY BIRD ───
            [
                'codigo' => 'PROM-EARLYBIRD',
                'nombre' => 'Reserva Anticipada Early Bird (15% OFF)',
                'tipo_promocion_id' => $tipoAnticipada?->id,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addMonths(6)->toDateString(),
                'descuento_porcentaje' => 15.00,
                'descuento_monto' => null,
                'precio_paquete' => 0.00,
                'descripcion' => 'Planifique sus vacaciones con anticipación y ahorre. Reserve con al menos 30 días de anticipación y obtenga un 15% de descuento garantizado en cualquier categoría de habitación seleccionada.',
                'condiciones' => 'Requiere reserva confirmada con al menos 30 días de antelación. Tarifa no reembolsable en caso de cancelación voluntaria.',
                'estado' => 1,
                'web' => true,
                'orden' => 3,
                'imagenes' => [
                    ['url' => '/images/pool-front-view.webp', 'orden' => 1],
                    ['url' => '/images/hero-main.webp', 'orden' => 2],
                ],
                'items' => [],
                'politicas' => [$politicaNoReembolso?->id, $politicaGarantia?->id],
                'precio_nio' => 0.00,
                'precio_usd' => 0.00,
            ],

            // ─── 04. FIN DE SEMANA GASTRONÓMICO & RELAX ───
            [
                'codigo' => 'PROM-GASTRONOMICO',
                'nombre' => 'Fin de Semana Gastronómico & Relax',
                'tipo_promocion_id' => $tipoPaquete?->id,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addMonths(12)->toDateString(),
                'descuento_porcentaje' => 25.00,
                'descuento_monto' => null,
                'precio_paquete' => 4200.00,
                'descripcion' => 'Un fin de semana diseñado para los amantes de la buena mesa y el descanso. Incluye 2 noches en habitación Matrimonial/Doble, cena gourmet de 3 tiempos en terraza para 2 personas, 2 cócteles de autor en el pool bar y acceso ilimitado a la piscina.',
                'condiciones' => 'Válido de viernes a domingo. Se requiere reservar turno para cena en recepción.',
                'estado' => 1,
                'web' => true,
                'orden' => 4,
                'imagenes' => [
                    ['url' => '/images/restaurant-hero.jpg', 'orden' => 1],
                    ['url' => '/images/service-bartender.webp', 'orden' => 2],
                    ['url' => '/images/service-pool.webp', 'orden' => 3],
                ],
                'items' => [
                    ['type' => Habitacion::class, 'id' => $habDoble?->id, 'precio' => 2600.00],
                    ['type' => Espacio::class, 'id' => $espacioRestaurante?->id, 'precio' => 0.00],
                    ['type' => Servicio::class, 'id' => $servicioPiscina?->id, 'precio' => 0.00],
                ],
                'politicas' => [$politicaCancelacion?->id, $politicaPiscina?->id],
                'precio_nio' => 4200.00,
                'precio_usd' => round(4200.00 / $tipoCambio, 2),
            ],

            // ─── 05. PAQUETE EJECUTIVO & NEGOCIOS ───
            [
                'codigo' => 'PROM-CORPORATIVO',
                'nombre' => 'Paquete Ejecutivo & Negocios',
                'tipo_promocion_id' => $tipoEvento?->id,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addMonths(12)->toDateString(),
                'descuento_porcentaje' => 10.00,
                'descuento_monto' => null,
                'precio_paquete' => 3200.00,
                'descripcion' => 'La mejor opción para viajes corporativos y ejecutivos en Estelí. Habitación individual ejecutiva con Wi-Fi de alta velocidad, desayuno buffet matutino, coffee break continuo, planchado express y late check-out garantizado hasta las 13:00.',
                'condiciones' => 'Aplica de lunes a viernes. Facturación fiscal disponible para empresas.',
                'estado' => 1,
                'web' => true,
                'orden' => 5,
                'imagenes' => [
                    ['url' => '/images/service-events.webp', 'orden' => 1],
                    ['url' => '/images/main-room.webp', 'orden' => 2],
                ],
                'items' => [
                    ['type' => Habitacion::class, 'id' => $habDeluxe?->id, 'precio' => 2200.00],
                    ['type' => Servicio::class, 'id' => $servicioDesayuno?->id, 'precio' => 400.00],
                ],
                'politicas' => [$politicaCancelacion?->id],
                'precio_nio' => 3200.00,
                'precio_usd' => round(3200.00 / $tipoCambio, 2),
            ],

            // ─── 06. ESCAPADA FAMILIAR VERANO & PISCINA ───
            [
                'codigo' => 'PROM-FAMILIAR',
                'nombre' => 'Escapada Familiar Verano & Piscina',
                'tipo_promocion_id' => $tipoTemporada?->id,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addMonths(12)->toDateString(),
                'descuento_porcentaje' => 20.00,
                'descuento_monto' => null,
                'precio_paquete' => 5900.00,
                'descripcion' => 'Disfruta en familia de unas vacaciones inolvidables. Incluye habitación Familiar amplia para hasta 4 personas, pases para todo el día en piscina con toallas incluidas, combo de almuerzo familiar y helados para los más pequeños.',
                'condiciones' => 'Máximo 4 personas por paquete (2 adultos y 2 niños menores de 12 años). Horario de piscina de 08:00 a 20:00.',
                'estado' => 1,
                'web' => true,
                'orden' => 6,
                'imagenes' => [
                    ['url' => '/images/pool-scaled.webp', 'orden' => 1],
                    ['url' => '/images/group-room.webp', 'orden' => 2],
                    ['url' => '/images/service-kitchen.webp', 'orden' => 3],
                ],
                'items' => [
                    ['type' => Habitacion::class, 'id' => $habFamiliar?->id, 'precio' => 3500.00],
                    ['type' => Espacio::class, 'id' => $espacioPiscina?->id, 'precio' => 0.00],
                ],
                'politicas' => [$politicaCancelacion?->id, $politicaPiscina?->id],
                'precio_nio' => 5900.00,
                'precio_usd' => round(5900.00 / $tipoCambio, 2),
            ],
        ];

        DB::transaction(function () use ($paquetes, $nio, $usd): void {
            foreach ($paquetes as $data) {
                $imagenes = $data['imagenes'];
                $items = $data['items'];
                $politicaIds = array_filter($data['politicas']);
                $precioNio = $data['precio_nio'];
                $precioUsd = $data['precio_usd'];

                unset($data['imagenes'], $data['items'], $data['politicas'], $data['precio_nio'], $data['precio_usd']);

                /** @var Promocion $promocion */
                $promocion = Promocion::query()->updateOrCreate(
                    ['codigo' => $data['codigo']],
                    $data
                );

                // 1. Sincronizar Galería de Imágenes
                $promocion->imagenes()->delete();
                foreach ($imagenes as $imgData) {
                    Imagen::query()->create([
                        'imagenable_type' => Promocion::class,
                        'imagenable_id' => $promocion->id,
                        'url' => $imgData['url'],
                        'orden' => $imgData['orden'],
                    ]);
                }

                // 2. Sincronizar Elementos del Paquete
                $promocion->items()->delete();
                foreach ($items as $item) {
                    if ($item['id'] !== null) {
                        $promocion->items()->create([
                            'item_type' => $item['type'],
                            'item_id' => $item['id'],
                            'precio_especial' => $item['precio'],
                        ]);
                    }
                }

                // 3. Sincronizar Políticas Asociadas
                if (! empty($politicaIds)) {
                    $promocion->politicas()->syncWithoutDetaching($politicaIds);
                }

                // 4. Sincronizar Precios en NIO y USD
                Precio::query()->updateOrCreate(
                    [
                        'priceable_type' => Promocion::class,
                        'priceable_id' => $promocion->id,
                        'moneda_id' => $nio->id,
                    ],
                    [
                        'precio' => $precioNio,
                        'fecha_inicio' => $promocion->fecha_inicio ?? now()->toDateString(),
                        'estado' => 1,
                        'es_oferta' => true,
                    ]
                );

                Precio::query()->updateOrCreate(
                    [
                        'priceable_type' => Promocion::class,
                        'priceable_id' => $promocion->id,
                        'moneda_id' => $usd->id,
                    ],
                    [
                        'precio' => $precioUsd,
                        'fecha_inicio' => $promocion->fecha_inicio ?? now()->toDateString(),
                        'estado' => 1,
                        'es_oferta' => true,
                    ]
                );
            }
        });

        $this->command->info('Definición de paquetes promocionales, ítems, precios e imágenes sembrada con éxito.');
    }
}
