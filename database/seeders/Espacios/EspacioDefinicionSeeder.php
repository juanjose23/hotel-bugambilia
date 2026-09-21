<?php

declare(strict_types=1);

namespace Database\Seeders\Espacios;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\ServicioAsignacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspacioDefinicionSeeder extends Seeder
{
    public function run(): void
    {
        $nio = Moneda::query()->where('codigo', 'NIO')->first();
        $usd = Moneda::query()->where('codigo', 'USD')->first();
        $tipoCambio = 36.5;

        if (! $nio || ! $usd) {
            $this->command->error('No se encontraron las monedas NIO o USD en el sistema.');

            return;
        }

        $plantaBaja = Ubicacion::query()->where('nombre', 'like', '%Baja%')->first();
        $plantaAlta = Ubicacion::query()->where('nombre', 'like', '%Alta%')->first();
        $ubicacionDefecto = $plantaBaja ?? Ubicacion::query()->first();

        if (! $ubicacionDefecto) {
            $this->command->error('No se encontró ninguna ubicación en el sistema.');

            return;
        }

        $ubicacionBajaId = $plantaBaja instanceof Ubicacion ? $plantaBaja->id : $ubicacionDefecto->id;
        $ubicacionAltaId = $plantaAlta instanceof Ubicacion ? $plantaAlta->id : $ubicacionDefecto->id;

        $politicaSalon = Politica::query()->where('titulo', 'like', '%Espacios Comerciales y Salones%')->first();
        $politicaGym = Politica::query()->where('titulo', 'like', '%Gimnasio%')->first();
        $politicaPiscina = Politica::query()->where('titulo', 'like', '%Piscina%')->first();
        $politicaTerraza = Politica::query()->where('titulo', 'like', '%Terrazas%')->first();
        $politicaSpa = Politica::query()->where('titulo', 'like', '%Cabinas de Masajes%')->first();

        $servicioWifi = Servicio::query()->where('nombre', 'like', '%WiFi%')->first();
        $servicioProyector = Servicio::query()->where('nombre', 'like', '%Proyector%')->first();
        $servicioCoffeeBreak = Servicio::query()->where('nombre', 'like', '%Coffee%')->first();

        $espacios = [
            // ─── 01. RESTAURANTE BUGAMBILIAS (Principal & Web) ───
            [
                'codigo' => 'REST-001',
                'nombre' => 'Restaurante Bugambilias',
                'descripcion' => 'Espacio gastronómico insignia del hotel con ambiente climatizado y área de terraza al aire libre. Ofrecemos exquisitos desayunos tradicionales nicaragüenses, buffet ejecutivo, cortes de carne importados y platillos internacionales.',
                'tipo' => TipoEspacio::RESTAURANTE,
                'capacidad_personas' => 120,
                'ubicacion_id' => $ubicacionDefecto->id,
                'padre_id' => null,
                'estado' => EstadoEspacio::Disponible,
                'orden' => 1,
                'web' => true,
                'reservable' => true,
                'meta_datos' => [
                    'tipo_cocina' => 'internacional',
                    'tipo_servicio' => 'carta',
                    'horario_desayuno' => '06:30 - 10:30 AM',
                    'horario_almuerzo' => '12:00 - 03:30 PM',
                    'horario_cena' => '06:00 - 10:00 PM',
                    'capacidad_mesas' => 25,
                    'permite_delivery' => true,
                    'costo_delivery' => 50.00,
                    'pedido_minimo' => 150.00,
                ],
                'imagenes' => [
                    ['url' => '/images/restaurant-hero.jpg', 'orden' => 1],
                    ['url' => '/images/service-kitchen.webp', 'orden' => 2],
                    ['url' => '/images/service-bartender.webp', 'orden' => 3],
                ],
                'politicas' => [$politicaTerraza?->id],
                'servicios' => [$servicioWifi?->id],
                'precios' => [],
                'mesas' => [
                    ['codigo' => 'MESA-01', 'nombre' => 'Mesa 1 (VIP)', 'capacidad' => 4, 'orden' => 1, 'meta' => ['tipo_mesa' => 'redonda', 'zona_restaurante' => 'vip', 'permite_union' => true]],
                    ['codigo' => 'MESA-02', 'nombre' => 'Mesa 2 (Pareja)', 'capacidad' => 2, 'orden' => 2, 'meta' => ['tipo_mesa' => 'cuadrada', 'zona_restaurante' => 'interior', 'permite_union' => true]],
                    ['codigo' => 'MESA-03', 'nombre' => 'Mesa 3 (Familiar Interior)', 'capacidad' => 6, 'orden' => 3, 'meta' => ['tipo_mesa' => 'rectangular', 'zona_restaurante' => 'interior', 'permite_union' => true]],
                    ['codigo' => 'MESA-04', 'nombre' => 'Mesa 4 (Terraza Vista Piscina)', 'capacidad' => 4, 'orden' => 4, 'meta' => ['tipo_mesa' => 'cuadrada', 'zona_restaurante' => 'terraza', 'permite_union' => true]],
                    ['codigo' => 'MESA-05', 'nombre' => 'Mesa 5 (Terraza Lounge)', 'capacidad' => 8, 'orden' => 5, 'meta' => ['tipo_mesa' => 'rectangular', 'zona_restaurante' => 'terraza', 'permite_union' => false]],
                    ['codigo' => 'MESA-06', 'nombre' => 'Mesa 6 (Barra / Cocktails)', 'capacidad' => 4, 'orden' => 6, 'meta' => ['tipo_mesa' => 'barra', 'zona_restaurante' => 'bar', 'permite_union' => false]],
                ],
            ],

            // ─── 02. SALÓN DE EVENTOS REAL BUGAMBILIAS ───
            [
                'codigo' => 'SALON-001',
                'nombre' => 'Gran Salón de Eventos Real Bugambilias',
                'descripcion' => 'Espacio versátil de alta gama para convenciones, banquetes, seminarios y bodas. Cuenta con sistema de sonido profesional, proyector láser 4K, climatización central y accesos privados para proveedores y catering.',
                'tipo' => TipoEspacio::SALON,
                'capacidad_personas' => 180,
                'ubicacion_id' => $ubicacionDefecto->id,
                'padre_id' => null,
                'estado' => EstadoEspacio::Disponible,
                'orden' => 2,
                'web' => true,
                'reservable' => true,
                'meta_datos' => [
                    'metros_cuadrados' => 220,
                    'equipamiento_incluido' => ['proyector', 'sonido', 'clima', 'luces', 'pizarra'],
                ],
                'imagenes' => [
                    ['url' => '/images/service-events.webp', 'orden' => 1],
                    ['url' => '/images/hero-secondary.webp', 'orden' => 2],
                ],
                'politicas' => [$politicaSalon?->id],
                'servicios' => [$servicioWifi?->id, $servicioProyector?->id, $servicioCoffeeBreak?->id],
                'precios' => [
                    ['tipo' => 'base', 'precio_nio' => 7000.00, 'precio_usd' => 200.00],
                    ['tipo' => 'por_hora', 'precio_nio' => 1000.00, 'precio_usd' => 28.00],
                ],
                'mesas' => [],
            ],

            // ─── 03. GIMNASIO FITNESS CENTER ───
            [
                'codigo' => 'GYM-001',
                'nombre' => 'Gimnasio Fitness Center',
                'descripcion' => 'Centro de acondicionamiento físico equipado con caminadoras profesionales, bicicletas elípticas, mancuernas, bancos multiposición y vista a los jardines. Acceso libre sin costo para todos los huéspedes del hotel.',
                'tipo' => TipoEspacio::GYM,
                'capacidad_personas' => 25,
                'ubicacion_id' => $ubicacionAltaId,
                'padre_id' => null,
                'estado' => EstadoEspacio::Disponible,
                'orden' => 3,
                'web' => true,
                'reservable' => false,
                'meta_datos' => [
                    'restricciones_gimnasio' => 'Uso obligatorio de toalla personal, vestimenta y calzado deportivo adecuado. Hidratación disponible.',
                ],
                'imagenes' => [
                    ['url' => '/images/hero-main.webp', 'orden' => 1],
                    ['url' => '/images/terrace.webp', 'orden' => 2],
                ],
                'politicas' => [$politicaGym?->id],
                'servicios' => [$servicioWifi?->id],
                'precios' => [
                    ['tipo' => 'por_hora', 'precio_nio' => 150.00, 'precio_usd' => round(150.00 / $tipoCambio, 2)],
                ],
                'mesas' => [],
            ],

            // ─── 04. SPA & CABINA DE MASAJES RELAX ───
            [
                'codigo' => 'SPA-001',
                'nombre' => 'Cabina de Masajes Relax & Spa',
                'descripcion' => 'Santuario de bienestar y relajación diseñado con aromaterapia, música zen y cabinas térmicas para tratamientos faciales, masajes terapéuticos de tejido profundo y exfoliaciones botánicas.',
                'tipo' => TipoEspacio::SPA,
                'capacidad_personas' => 4,
                'ubicacion_id' => $ubicacionDefecto->id,
                'padre_id' => null,
                'estado' => EstadoEspacio::Disponible,
                'orden' => 4,
                'web' => true,
                'reservable' => true,
                'meta_datos' => [
                    'tipo_spa' => 'masajes',
                    'capacidad_simultanea' => 2,
                    'equipamiento_spa' => ['camilla', 'sauna', 'jacuzzi', 'ducha_hidro'],
                ],
                'imagenes' => [
                    ['url' => '/images/service-pool.webp', 'orden' => 1],
                    ['url' => '/images/bathroom.webp', 'orden' => 2],
                ],
                'politicas' => [$politicaSpa?->id],
                'servicios' => [$servicioWifi?->id],
                'precios' => [
                    ['tipo' => 'base', 'precio_nio' => 600.00, 'precio_usd' => 16.50],
                ],
                'mesas' => [],
            ],

            // ─── 05. PISCINA INFINITY & POOL LOUNGE ───
            [
                'codigo' => 'PISC-001',
                'nombre' => 'Piscina Infinity & Pool Lounge',
                'descripcion' => 'Hermosa piscina con borde infinito, área de hidromasaje y terraza perimetral con camastros acolchados. Servicio de bebidas refrescantes, cócteles tropicales y snacks ligeros desde el pool bar.',
                'tipo' => TipoEspacio::PISCINA,
                'capacidad_personas' => 60,
                'ubicacion_id' => $ubicacionDefecto->id,
                'padre_id' => null,
                'estado' => EstadoEspacio::Disponible,
                'orden' => 5,
                'web' => true,
                'reservable' => true,
                'meta_datos' => [
                    'tipo_piscina' => 'mixta',
                    'camastros' => 24,
                    'horario_operacion' => '07:00 - 21:00',
                    'toallas_incluidas' => true,
                ],
                'imagenes' => [
                    ['url' => '/images/pool-front-view.webp', 'orden' => 1],
                    ['url' => '/images/pool-scaled.webp', 'orden' => 2],
                    ['url' => '/images/service-bartender.webp', 'orden' => 3],
                ],
                'politicas' => [$politicaPiscina?->id],
                'servicios' => [$servicioWifi?->id],
                'precios' => [
                    ['tipo' => 'base', 'precio_nio' => 250.00, 'precio_usd' => 7.00],
                ],
                'mesas' => [],
            ],

            // ─── 06. TERRAZA PANORÁMICA LOS BALCONES ───
            [
                'codigo' => 'TERR-001',
                'nombre' => 'Terraza Panorámica Los Balcones',
                'descripcion' => 'Mirador al aire libre en planta alta con vistas privilegiadas a las montañas del norte nicaragüense. Ideal para sesiones fotográficas, cócteles al atardecer, veladas acústicas o cenas románticas privadas.',
                'tipo' => TipoEspacio::TERRAZA,
                'capacidad_personas' => 50,
                'ubicacion_id' => $ubicacionAltaId,
                'padre_id' => null,
                'estado' => EstadoEspacio::Disponible,
                'orden' => 6,
                'web' => true,
                'reservable' => true,
                'meta_datos' => [
                    'caracteristicas' => ['Vista al Jardín', 'Pérgola Iluminada', 'Música de Fondo', 'Asientos Lounge'],
                ],
                'imagenes' => [
                    ['url' => '/images/terrace.webp', 'orden' => 1],
                    ['url' => '/images/hero-secondary.webp', 'orden' => 2],
                ],
                'politicas' => [$politicaTerraza?->id],
                'servicios' => [$servicioWifi?->id],
                'precios' => [
                    ['tipo' => 'por_hora', 'precio_nio' => 800.00, 'precio_usd' => 22.00],
                ],
                'mesas' => [],
            ],

            // ─── 07. SALA DE JUNTAS & COWORKING EJECUTIVO ───
            [
                'codigo' => 'COWORK-001',
                'nombre' => 'Sala de Juntas & Coworking Ejecutivo',
                'descripcion' => 'Espacio corporativo privado equipado con mesa directiva para 12 personas, pantalla interactiva de 75 pulgadas, videollamadas con cancelación de ruido y estación de café gourmet de cortesía.',
                'tipo' => TipoEspacio::SALON,
                'capacidad_personas' => 14,
                'ubicacion_id' => $ubicacionAltaId,
                'padre_id' => null,
                'estado' => EstadoEspacio::Disponible,
                'orden' => 7,
                'web' => true,
                'reservable' => true,
                'meta_datos' => [
                    'metros_cuadrados' => 45,
                    'equipamiento_incluido' => ['proyector', 'clima', 'pizarra', 'sonido'],
                ],
                'imagenes' => [
                    ['url' => '/images/service-events.webp', 'orden' => 1],
                    ['url' => '/images/main-room.webp', 'orden' => 2],
                ],
                'politicas' => [$politicaSalon?->id],
                'servicios' => [$servicioWifi?->id, $servicioCoffeeBreak?->id],
                'precios' => [
                    ['tipo' => 'por_hora', 'precio_nio' => 400.00, 'precio_usd' => 11.00],
                ],
                'mesas' => [],
            ],
        ];

        DB::transaction(function () use ($espacios, $nio, $usd): void {
            foreach ($espacios as $data) {
                $imagenes = $data['imagenes'];
                $politicaIds = array_filter($data['politicas']);
                $servicioIds = array_filter($data['servicios']);
                $precios = $data['precios'];
                $mesas = $data['mesas'];

                unset($data['imagenes'], $data['politicas'], $data['servicios'], $data['precios'], $data['mesas']);

                // Crear o actualizar recurso reservable asociado
                $recurso = RecursoReservable::firstOrCreate(
                    ['nombre' => $data['nombre'], 'tipo' => TipoRecursoReservable::ESPACIO],
                    [
                        'capacidad' => $data['capacidad_personas'],
                        'control_disponibilidad' => ControlDisponibilidad::HORARIO,
                        'duracion_minutos' => 120,
                        'estado' => EstadoRecursoReservable::ACTIVO,
                    ]
                );

                $data['reservable_id'] = $recurso->id;

                /** @var Espacio $espacio */
                $espacio = Espacio::query()->updateOrCreate(
                    ['codigo' => $data['codigo']],
                    $data
                );

                // 1. Sincronizar Galería de Imágenes
                $espacio->imagenes()->delete();
                foreach ($imagenes as $imgData) {
                    Imagen::query()->create([
                        'imagenable_type' => Espacio::class,
                        'imagenable_id' => $espacio->id,
                        'url' => $imgData['url'],
                        'orden' => $imgData['orden'],
                    ]);
                }

                // 2. Sincronizar Políticas Asociadas
                if (! empty($politicaIds)) {
                    $espacio->politicas()->syncWithoutDetaching($politicaIds);
                }

                // 3. Sincronizar Servicios Asignados
                foreach ($servicioIds as $srvId) {
                    ServicioAsignacion::query()->updateOrCreate(
                        [
                            'serviceable_type' => Espacio::class,
                            'serviceable_id' => $espacio->id,
                            'servicio_id' => $srvId,
                        ],
                        [
                            'incluido' => true,
                            'estado' => 1,
                        ]
                    );
                }

                // 4. Sincronizar Tarifas / Precios (NIO y USD)
                foreach ($precios as $precioData) {
                    Precio::query()->updateOrCreate(
                        [
                            'priceable_type' => Espacio::class,
                            'priceable_id' => $espacio->id,
                            'moneda_id' => $nio->id,
                            'tipo_precio' => $precioData['tipo'],
                        ],
                        [
                            'precio' => $precioData['precio_nio'],
                            'fecha_inicio' => now()->toDateString(),
                            'estado' => 1,
                            'es_oferta' => false,
                        ]
                    );

                    Precio::query()->updateOrCreate(
                        [
                            'priceable_type' => Espacio::class,
                            'priceable_id' => $espacio->id,
                            'moneda_id' => $usd->id,
                            'tipo_precio' => $precioData['tipo'],
                        ],
                        [
                            'precio' => $precioData['precio_usd'],
                            'fecha_inicio' => now()->toDateString(),
                            'estado' => 1,
                            'es_oferta' => false,
                        ]
                    );
                }

                // 5. Sembrar Sub-espacios (Mesas)
                foreach ($mesas as $mData) {
                    $recursoMesa = RecursoReservable::firstOrCreate(
                        ['nombre' => $mData['nombre'], 'tipo' => TipoRecursoReservable::ESPACIO],
                        [
                            'capacidad' => $mData['capacidad'],
                            'control_disponibilidad' => ControlDisponibilidad::HORARIO,
                            'duracion_minutos' => 120,
                            'estado' => EstadoRecursoReservable::ACTIVO,
                        ]
                    );

                    Espacio::query()->updateOrCreate(
                        ['codigo' => $mData['codigo']],
                        [
                            'padre_id' => $espacio->id,
                            'nombre' => $mData['nombre'],
                            'tipo' => TipoEspacio::MESA,
                            'capacidad_personas' => $mData['capacidad'],
                            'ubicacion_id' => null,
                            'reservable_id' => $recursoMesa->id,
                            'estado' => EstadoEspacio::Disponible,
                            'orden' => $mData['orden'],
                            'web' => false, // Las mesas no son páginas individuales, sino elementos del restaurante
                            'reservable' => true,
                            'meta_datos' => $mData['meta'],
                        ]
                    );
                }
            }
        });

        $this->command->info('Definición de Espacios hoteleros, sub-espacios, imágenes y servicios sembrada con éxito.');
    }
}
