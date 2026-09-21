<?php

declare(strict_types=1);

namespace Database\Seeders\Habitaciones;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Habitaciones\GenerarCodigoHabitacion;
use App\Interactors\Habitaciones\GenerarSlugHabitacion;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Habitaciones\DetalleHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\ServicioAsignacion;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $nio = Moneda::where('codigo', 'NIO')->first();
        $usd = Moneda::where('codigo', 'USD')->first();

        if (! $nio || ! $usd) {
            return;
        }

        $admin = User::where('email', 'admin@hotel.com')->first() ?? User::first();
        $adminId = $admin ? $admin->id : 1;

        $generarCodigo = app(GenerarCodigoHabitacion::class);
        $generarSlug = app(GenerarSlugHabitacion::class);

        // 1. Obtener Categorías
        $getCategoriaId = static function (string $codigo): int {
            $id = Catalogo::where('codigo', $codigo)->value('id');

            return is_numeric($id) ? (int) $id : 1;
        };

        $catEstandar = $getCategoriaId('CAT_HAB_ESTANDAR');
        $catDeluxe = $getCategoriaId('CAT_HAB_DELUXE');
        $catSuite = $getCategoriaId('CAT_HAB_SUITE');
        $catPresidencial = $getCategoriaId('CAT_HAB_PRESIDENCIAL');
        $catFamiliar = $getCategoriaId('CAT_HAB_FAMILIAR');

        // 2. Obtener Ubicaciones Físicas
        $alaNorte = Ubicacion::where('nombre', 'Ala Norte')->first();
        $alaSur = Ubicacion::where('nombre', 'Ala Sur')->first();
        $areaEjecutiva = Ubicacion::where('nombre', 'Área Ejecutiva')->first();
        $jardines = Ubicacion::where('nombre', 'Jardines y Exterior')->first();
        $plantaAlta = Ubicacion::where('nombre', 'Planta Alta')->first() ?? Ubicacion::first();

        $plantaAltaId = $plantaAlta ? $plantaAlta->id : 1;
        $ubicacionNorteId = $alaNorte ? $alaNorte->id : $plantaAltaId;
        $ubicacionSurId = $alaSur ? $alaSur->id : $plantaAltaId;
        $ubicacionEjecutivaId = $areaEjecutiva ? $areaEjecutiva->id : $plantaAltaId;
        $ubicacionJardinesId = $jardines ? $jardines->id : $plantaAltaId;

        // 3. Obtener Políticas
        $politicas = Politica::pluck('id')->toArray();

        // 4. Obtener Servicios para Asignación
        $servicios = Servicio::all();
        $wifiServicio = $servicios->firstWhere('nombre', 'WiFi Simétrico Dedicado Ultra Alta Velocidad') ?? $servicios->first();
        $roomService = $servicios->firstWhere('nombre', 'Servicio a la Habitación 24/7');
        $earlyCheckin = $servicios->firstWhere('nombre', 'Early Check-in');
        $lateCheckout = $servicios->firstWhere('nombre', 'Late Check-out');
        $limpiezaExtra = $servicios->firstWhere('nombre', 'Limpieza Extra y Cambio de Blancos');

        // 5. Variantes de consumibles
        /** @var Collection<int, ProductoVariante> $variantes */
        $variantes = ProductoVariante::with('producto')->get();

        // 6. Producto de activo fijo para inventario asignado
        $productoTv = Producto::where('nombre', 'like', '%TV%')->orWhere('nombre', 'like', '%Televisor%')->first() ?? Producto::first();
        $productoAc = Producto::where('nombre', 'like', '%Aire%')->orWhere('nombre', 'like', '%Clima%')->first() ?? Producto::first();
        $productoCama = Producto::where('nombre', 'like', '%Cama%')->orWhere('nombre', 'like', '%Colch%')->first() ?? Producto::first();

        // Definición de las 122 Habitaciones
        $habitacionesConfig = [];

        // 1. Ala Norte - Piso 1 (101-120: 20 Habitaciones Estándar)
        for ($i = 101; $i <= 120; $i++) {
            $habitacionesConfig[] = [
                'numero' => $i,
                'nombre' => "Habitación {$i} - Estándar Queen",
                'descripcion' => 'Habitación confortable con cama Queen size, baño privado de mármol, escritorio ejecutivo y vista a los jardines interiores.',
                'categoria_id' => $catEstandar,
                'ubicacion_id' => $ubicacionNorteId,
                'piso' => 1,
                'adultos' => 2,
                'ninos' => 1,
                'medidas' => 28.50,
                'vistas' => ['Vista al Jardín'],
                'precio_nio' => 2200.00,
                'tipo_habitacion' => 'estandar',
            ];
        }

        // 2. Ala Norte - Piso 2 (201-220: 20 Habitaciones Deluxe)
        for ($i = 201; $i <= 220; $i++) {
            $habitacionesConfig[] = [
                'numero' => $i,
                'nombre' => "Habitación {$i} - Deluxe Piscina",
                'descripcion' => 'Elegante habitación Deluxe con cama King size, balcón privado con vista directa hacia la piscina, cafetera espresso y Smart TV de 50".',
                'categoria_id' => $catDeluxe,
                'ubicacion_id' => $ubicacionNorteId,
                'piso' => 2,
                'adultos' => 2,
                'ninos' => 2,
                'medidas' => 38.00,
                'vistas' => ['Vista a la Piscina'],
                'precio_nio' => 3400.00,
                'tipo_habitacion' => 'deluxe',
            ];
        }

        // 3. Ala Norte - Piso 3 (301-315: 15 Junior Suites)
        for ($i = 301; $i <= 315; $i++) {
            $habitacionesConfig[] = [
                'numero' => $i,
                'nombre' => "Suite {$i} - Junior Suite Panorámica",
                'descripcion' => 'Espaciosa Junior Suite con área de estar independiente, sofá cama, vestidor, tina de hidromasaje y ventanales de piso a techo.',
                'categoria_id' => $catSuite,
                'ubicacion_id' => $ubicacionNorteId,
                'piso' => 3,
                'adultos' => 3,
                'ninos' => 2,
                'medidas' => 52.00,
                'vistas' => ['Vista Panorámica', 'Vista a la Piscina'],
                'precio_nio' => 5200.00,
                'tipo_habitacion' => 'suite',
            ];
        }

        // 4. Ala Sur - Piso 1 (121-140: 20 Habitaciones Estándar Dobles)
        for ($i = 121; $i <= 140; $i++) {
            $habitacionesConfig[] = [
                'numero' => $i,
                'nombre' => "Habitación {$i} - Estándar Doble",
                'descripcion' => 'Habitación espaciosa con 2 camas matrimoniales, ideal para familias o viajes de trabajo compartidos, con aire acondicionado silencioso.',
                'categoria_id' => $catEstandar,
                'ubicacion_id' => $ubicacionSurId,
                'piso' => 1,
                'adultos' => 4,
                'ninos' => 1,
                'medidas' => 34.00,
                'vistas' => ['Vista al Jardín'],
                'precio_nio' => 2600.00,
                'tipo_habitacion' => 'estandar',
            ];
        }

        // 5. Ala Sur - Piso 2 (221-240: 20 Habitaciones Deluxe Terraza)
        for ($i = 221; $i <= 240; $i++) {
            $habitacionesConfig[] = [
                'numero' => $i,
                'nombre' => "Habitación {$i} - Deluxe Terraza",
                'descripcion' => 'Habitación de categoría superior con cama King, amplia terraza privada con juego de sala exterior y vista hacia el atardecer.',
                'categoria_id' => $catDeluxe,
                'ubicacion_id' => $ubicacionSurId,
                'piso' => 2,
                'adultos' => 2,
                'ninos' => 1,
                'medidas' => 42.00,
                'vistas' => ['Vista al Mar / Montaña', 'Vista a la Terraza'],
                'precio_nio' => 3800.00,
                'tipo_habitacion' => 'deluxe',
            ];
        }

        // 6. Ala Sur - Piso 3 (321-335: 15 Master Suites)
        for ($i = 321; $i <= 335; $i++) {
            $habitacionesConfig[] = [
                'numero' => $i,
                'nombre' => "Suite {$i} - Master Suite Terraza",
                'descripcion' => 'Suite de lujo con dormitorio principal King, sala de estar formal, comedor para 4 personas, doble lavamanos y amenidades prémium de bienvenida.',
                'categoria_id' => $catSuite,
                'ubicacion_id' => $ubicacionSurId,
                'piso' => 3,
                'adultos' => 4,
                'ninos' => 2,
                'medidas' => 68.00,
                'vistas' => ['Vista al Mar / Montaña', 'Vista Panorámica'],
                'precio_nio' => 6500.00,
                'tipo_habitacion' => 'suite',
            ];
        }

        // 7. Área Ejecutiva / Penthouse (401-404: 4 Suites Presidenciales)
        for ($i = 401; $i <= 404; $i++) {
            $habitacionesConfig[] = [
                'numero' => $i,
                'nombre' => "Suite {$i} - Suite Presidencial Penthouse",
                'descripcion' => 'La máxima expresión de exclusividad. 120 m² de lujo con 2 dormitorios independientes, cocina gourmet, terraza privada con jacuzzi exterior y mayordomía.',
                'categoria_id' => $catPresidencial,
                'ubicacion_id' => $ubicacionEjecutivaId,
                'piso' => 4,
                'adultos' => 4,
                'ninos' => 3,
                'medidas' => 125.00,
                'vistas' => ['Vista 360°', 'Vista Panorámica', 'Vista al Mar'],
                'precio_nio' => 14500.00,
                'tipo_habitacion' => 'presidencial',
            ];
        }

        // 8. Jardines Exteriores (501-508: 8 Cabañas Familiares / Bungalows)
        for ($i = 501; $i <= 508; $i++) {
            $habitacionesConfig[] = [
                'numero' => $i,
                'nombre' => "Cabaña {$i} - Cabaña Familiar Bugambilias",
                'descripcion' => 'Cabaña independiente rodeada de vegetación tropical, 2 habitaciones completas, cocineta equipada, porche con hamacas y área pet-friendly.',
                'categoria_id' => $catFamiliar,
                'ubicacion_id' => $ubicacionJardinesId,
                'piso' => 1,
                'adultos' => 6,
                'ninos' => 3,
                'medidas' => 88.00,
                'vistas' => ['Vista al Jardín Tropical', 'Vista a la Naturaleza'],
                'precio_nio' => 7800.00,
                'tipo_habitacion' => 'familiar',
            ];
        }

        $galeriasPorTipo = [
            'estandar' => ['/images/main-room.jpg', '/images/room-detail.webp', '/images/bathroom.webp'],
            'deluxe' => ['/images/room-detail.webp', '/images/bathroom.webp', '/images/terrace.webp'],
            'suite' => ['/images/hero-main.webp', '/images/room-detail.webp', '/images/bathroom.webp', '/images/terrace.webp'],
            'presidencial' => ['/images/hero-main.webp', '/images/hero-secondary.webp', '/images/room-detail.webp', '/images/bathroom.webp', '/images/terrace.webp'],
            'familiar' => ['/images/group-room.webp', '/images/room-detail.webp', '/images/bathroom.webp', '/images/terrace.webp'],
        ];

        $tipoCambio = 36.5;

        // Sembrar cada una de las 122 Habitaciones
        foreach ($habitacionesConfig as $hData) {
            $numero = (int) $hData['numero'];

            // Recurso Reservable
            $recurso = RecursoReservable::firstOrCreate(
                ['nombre' => "Habitación {$numero}", 'tipo' => TipoRecursoReservable::HABITACION],
                [
                    'capacidad' => $hData['adultos'] + $hData['ninos'],
                    'control_disponibilidad' => ControlDisponibilidad::FECHAS,
                    'duracion_minutos' => 1440,
                    'estado' => EstadoRecursoReservable::ACTIVO,
                ]
            );

            $habitacion = Habitacion::where('numero', $numero)->first();

            if (! $habitacion) {
                $codigo = $generarCodigo->ejecutar();
                $slug = $generarSlug->ejecutar($hData['nombre']);

                $habitacion = Habitacion::create([
                    'codigo' => $codigo,
                    'numero' => $numero,
                    'slug' => $slug,
                    'nombre' => $hData['nombre'],
                    'descripcion' => $hData['descripcion'],
                    'categoria_id' => $hData['categoria_id'],
                    'ubicacion_id' => $hData['ubicacion_id'],
                    'reservable_id' => $recurso->id,
                    'estado' => EstadoEspacio::Activa,
                ]);
            } else {
                $habitacion->update([
                    'nombre' => $hData['nombre'],
                    'descripcion' => $hData['descripcion'],
                    'categoria_id' => $hData['categoria_id'],
                    'ubicacion_id' => $hData['ubicacion_id'],
                    'reservable_id' => $recurso->id,
                    'estado' => EstadoEspacio::Activa,
                ]);
            }

            // 1. Detalle de Habitación
            DetalleHabitacion::updateOrCreate(
                ['habitacion_id' => $habitacion->id],
                [
                    'capacidad_adultos' => $hData['adultos'],
                    'capacidad_ninos' => $hData['ninos'],
                    'medidas' => $hData['medidas'],
                    'vistas' => $hData['vistas'],
                ]
            );

            // 2. Políticas Asociadas
            if (! empty($politicas)) {
                $habitacion->politicas()->syncWithoutDetaching($politicas);
            }

            // 3. Precios y Tarifas (Histórico 2025 y Vigente 2026 en NIO y USD)
            $precioNioActual = (float) $hData['precio_nio'];
            $precioUsdActual = round($precioNioActual / $tipoCambio, 2);
            $precioNioHistorico = round($precioNioActual * 0.90, 2);
            $precioUsdHistorico = round($precioNioHistorico / 36.0, 2);

            // Histórico 2025 NIO
            Precio::updateOrCreate(
                [
                    'priceable_type' => Habitacion::class,
                    'priceable_id' => $habitacion->id,
                    'moneda_id' => $nio->id,
                    'fecha_inicio' => '2025-01-01',
                ],
                [
                    'precio' => $precioNioHistorico,
                    'fecha_fin' => '2025-12-31',
                    'estado' => EstadoGeneral::Inactivo,
                    'es_oferta' => false,
                ]
            );

            // Histórico 2025 USD
            Precio::updateOrCreate(
                [
                    'priceable_type' => Habitacion::class,
                    'priceable_id' => $habitacion->id,
                    'moneda_id' => $usd->id,
                    'fecha_inicio' => '2025-01-01',
                ],
                [
                    'precio' => $precioUsdHistorico,
                    'fecha_fin' => '2025-12-31',
                    'estado' => EstadoGeneral::Inactivo,
                    'es_oferta' => false,
                ]
            );

            // Vigente 2026 NIO
            Precio::updateOrCreate(
                [
                    'priceable_type' => Habitacion::class,
                    'priceable_id' => $habitacion->id,
                    'moneda_id' => $nio->id,
                    'fecha_inicio' => '2026-01-01',
                ],
                [
                    'precio' => $precioNioActual,
                    'fecha_fin' => null,
                    'estado' => EstadoGeneral::Activo,
                    'es_oferta' => false,
                ]
            );

            // Vigente 2026 USD
            Precio::updateOrCreate(
                [
                    'priceable_type' => Habitacion::class,
                    'priceable_id' => $habitacion->id,
                    'moneda_id' => $usd->id,
                    'fecha_inicio' => '2026-01-01',
                ],
                [
                    'precio' => $precioUsdActual,
                    'fecha_fin' => null,
                    'estado' => EstadoGeneral::Activo,
                    'es_oferta' => false,
                ]
            );

            // 4. Stock de Consumibles
            if ($variantes->isNotEmpty()) {
                $consumibles = [
                    'toalla' => ['ideal' => 4.0, 'actual' => 4.0],
                    'sabana' => ['ideal' => 2.0, 'actual' => 2.0],
                    'shampoo' => ['ideal' => 4.0, 'actual' => 3.0],
                    'jabon' => ['ideal' => 4.0, 'actual' => 4.0],
                    'agua' => ['ideal' => 2.0, 'actual' => 2.0],
                ];

                foreach ($consumibles as $key => $cant) {
                    $variante = $variantes->first(fn (ProductoVariante $v) => str_contains(strtolower(($v->producto->nombre ?? '').' '.($v->nombre_variante ?? '')), $key))
                        ?? $variantes->random();

                    Stock::updateOrCreate(
                        [
                            'stockable_type' => Habitacion::class,
                            'stockable_id' => $habitacion->id,
                            'producto_variante_id' => $variante->id,
                        ],
                        [
                            'cantidad_ideal' => $cant['ideal'],
                            'cantidad_actual' => $cant['actual'],
                            'estado' => 'NORMAL',
                            'ultima_verificacion' => now(),
                        ]
                    );
                }
            }

            // 5. Inventario Fijo (Activos Fijos Asignados)
            if ($productoTv && $productoAc && $productoCama) {
                $activosAAsignar = [
                    ['nombre' => "Smart TV LED 50\" Hab. {$numero}", 'prod' => $productoTv, 'prefijo' => 'TV'],
                    ['nombre' => "Aire Acondicionado Split Inverter Hab. {$numero}", 'prod' => $productoAc, 'prefijo' => 'AC'],
                    ['nombre' => "Cama Confort King Size Hab. {$numero}", 'prod' => $productoCama, 'prefijo' => 'CAM'],
                ];

                foreach ($activosAAsignar as $actData) {
                    $codActivo = sprintf('%s-HAB-%04d', $actData['prefijo'], $numero);
                    $activo = Activo::firstOrCreate(
                        ['codigo_inventario' => $codActivo],
                        [
                            'producto_id' => $actData['prod']->id,
                            'nombre_descriptivo' => $actData['nombre'],
                            'fecha_adquisicion' => '2025-06-01',
                            'costo_adquisicion' => 450.00,
                            'vida_util_meses' => 60,
                            'estado' => EstadoActivo::Activo,
                        ]
                    );

                    ActivoAsignacion::firstOrCreate(
                        [
                            'activo_id' => $activo->id,
                            'asignable_type' => Habitacion::class,
                            'asignable_id' => $habitacion->id,
                            'fecha_fin' => null,
                        ],
                        [
                            'fecha_inicio' => '2025-06-01',
                            'motivo' => 'Equipamiento fijo estándar de habitación',
                            'asignado_por_id' => $adminId,
                            'estado' => EstadoAsignacion::Vigente,
                        ]
                    );
                }
            }

            // 6. Servicios Asignados
            if ($wifiServicio) {
                ServicioAsignacion::updateOrCreate(
                    [
                        'serviceable_type' => Habitacion::class,
                        'serviceable_id' => $habitacion->id,
                        'servicio_id' => $wifiServicio->id,
                    ],
                    [
                        'incluido' => true,
                        'estado' => EstadoGeneral::Activo,
                    ]
                );
            }

            if ($roomService) {
                ServicioAsignacion::updateOrCreate(
                    [
                        'serviceable_type' => Habitacion::class,
                        'serviceable_id' => $habitacion->id,
                        'servicio_id' => $roomService->id,
                    ],
                    [
                        'incluido' => false,
                        'estado' => EstadoGeneral::Activo,
                    ]
                );
            }

            if ($earlyCheckin) {
                ServicioAsignacion::updateOrCreate(
                    [
                        'serviceable_type' => Habitacion::class,
                        'serviceable_id' => $habitacion->id,
                        'servicio_id' => $earlyCheckin->id,
                    ],
                    [
                        'incluido' => false,
                        'estado' => EstadoGeneral::Activo,
                    ]
                );
            }

            if ($lateCheckout) {
                ServicioAsignacion::updateOrCreate(
                    [
                        'serviceable_type' => Habitacion::class,
                        'serviceable_id' => $habitacion->id,
                        'servicio_id' => $lateCheckout->id,
                    ],
                    [
                        'incluido' => false,
                        'estado' => EstadoGeneral::Activo,
                    ]
                );
            }

            // 7. Galería de Imágenes
            $tipoImg = $hData['tipo_habitacion'];
            $imgs = $galeriasPorTipo[$tipoImg];

            foreach ($imgs as $idx => $imgUrl) {
                Imagen::firstOrCreate([
                    'imagenable_type' => Habitacion::class,
                    'imagenable_id' => $habitacion->id,
                    'url' => $imgUrl,
                ], [
                    'orden' => $idx + 1,
                ]);
            }
        }
    }
}
