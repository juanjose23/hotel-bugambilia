<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Interactors\Servicios\GenerarCodigoServicio;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener monedas
        $nio = Moneda::where('codigo', 'NIO')->first();
        $usd = Moneda::where('codigo', 'USD')->first();

        if (! $nio || ! $usd) {
            $this->command->error('No se encontraron las monedas NIO o USD en el sistema. Asegúrate de que TasaCambioSeeder se ejecute primero.');

            return;
        }

        // Tasa de cambio aproximada para la equivalencia
        $tipoCambio = 36.5;

        // 2. Definir servicios por categoría
        $serviciosPorCategoria = [
            'CAT_SERV_ALOJAMIENTO' => [
                [
                    'nombre' => 'Early Check-in',
                    'descripcion' => 'Permite el acceso a la habitación antes de la hora estándar de entrada (sujeto a disponibilidad).',
                    'precio_nio' => 350.00,
                    'icono' => 'heroicon-o-clock',
                ],
                [
                    'nombre' => 'Late Check-out',
                    'descripcion' => 'Extiende la estancia en la habitación más allá de la hora de salida estándar.',
                    'precio_nio' => 500.00,
                    'icono' => 'heroicon-o-arrow-right-on-rectangle',
                ],
                [
                    'nombre' => 'Cama adicional',
                    'descripcion' => 'Instalación de una cama extra confortable o cuna en la habitación.',
                    'precio_nio' => 800.00,
                    'icono' => 'heroicon-o-home-modern',
                ],
            ],
            'CAT_SERV_BIENESTAR' => [
                [
                    'nombre' => 'Masaje relajante 60 min',
                    'descripcion' => 'Terapia corporal completa con aceites esenciales para aliviar la tensión y el estrés.',
                    'precio_nio' => 1200.00,
                    'icono' => 'heroicon-o-hand-raised',
                ],
                [
                    'nombre' => 'Acceso al sauna',
                    'descripcion' => 'Sesión rejuvenecedora de calor seco en nuestro spa de clase mundial.',
                    'precio_nio' => 400.00,
                    'icono' => 'heroicon-o-fire',
                ],
                [
                    'nombre' => 'Jacuzzi privado 1 hora',
                    'descripcion' => 'Uso exclusivo del jacuzzi privado climatizado con aromaterapia.',
                    'precio_nio' => 950.00,
                    'icono' => 'heroicon-o-sun',
                ],
            ],
            'CAT_SERV_TRANSPORTE' => [
                [
                    'nombre' => 'Shuttle aeropuerto',
                    'descripcion' => 'Servicio de traslado privado o compartido ida y vuelta hacia el aeropuerto internacional.',
                    'precio_nio' => 900.00,
                    'icono' => 'heroicon-o-paper-airplane',
                ],
                [
                    'nombre' => 'Valet parking',
                    'descripcion' => 'Servicio premium de recepción, estacionamiento y entrega de su vehículo.',
                    'precio_nio' => 250.00,
                    'icono' => 'heroicon-o-key',
                ],
                [
                    'nombre' => 'Custodia de equipaje',
                    'descripcion' => 'Almacenamiento seguro de sus maletas antes del check-in o después del check-out.',
                    'precio_nio' => 150.00,
                    'icono' => 'heroicon-o-briefcase',
                ],
            ],
            'CAT_SERV_LAVANDERIA' => [
                [
                    'nombre' => 'Lavado de ropa por libra',
                    'descripcion' => 'Servicio completo de lavado, secado y doblado de prendas personales por libra.',
                    'precio_nio' => 120.00,
                    'icono' => 'heroicon-o-shopping-bag',
                ],
                [
                    'nombre' => 'Planchado de prendas',
                    'descripcion' => 'Planchado profesional e impecable para camisas, vestidos u otras vestimentas.',
                    'precio_nio' => 80.00,
                    'icono' => 'heroicon-o-scissors',
                ],
                [
                    'nombre' => 'Limpieza extra de habitación',
                    'descripcion' => 'Limpieza profunda adicional y cambio completo de lencería a solicitud del cliente.',
                    'precio_nio' => 350.00,
                    'icono' => 'heroicon-o-sparkles',
                ],
            ],
            'CAT_SERV_NEGOCIOS' => [
                [
                    'nombre' => 'Uso de sala de reuniones',
                    'descripcion' => 'Alquiler de sala ejecutiva totalmente equipada para conferencias y juntas de negocios.',
                    'precio_nio' => 2500.00,
                    'icono' => 'heroicon-o-users',
                ],
                [
                    'nombre' => 'Impresión de documentos',
                    'descripcion' => 'Servicio de impresión, fotocopiado y escaneo de alta resolución por página.',
                    'precio_nio' => 10.00,
                    'icono' => 'heroicon-o-document-text',
                ],
                [
                    'nombre' => 'Alquiler de proyector',
                    'descripcion' => 'Equipamiento audiovisual de alta definición para presentaciones o eventos corporativos.',
                    'precio_nio' => 1200.00,
                    'icono' => 'heroicon-o-presentation-chart-line',
                ],
            ],
            'CAT_SERV_RECREACION' => [
                [
                    'nombre' => 'Tour turístico local',
                    'descripcion' => 'Recorrido guiado exclusivo por los puntos de interés históricos, culturales y naturales más emblemáticos.',
                    'precio_nio' => 1800.00,
                    'icono' => 'heroicon-o-map',
                ],
                [
                    'nombre' => 'Alquiler de bicicletas',
                    'descripcion' => 'Alquiler diario de bicicleta de montaña equipada con casco y mapa de rutas recomendadas.',
                    'precio_nio' => 300.00,
                    'icono' => 'heroicon-o-globe-alt',
                ],
                [
                    'nombre' => 'Clase de yoga',
                    'descripcion' => 'Sesión guiada de yoga y meditación al amanecer con instructores profesionales en nuestros jardines.',
                    'precio_nio' => 450.00,
                    'icono' => 'heroicon-o-heart',
                ],
            ],
            'CAT_SERV_VIP' => [
                [
                    'nombre' => 'Decoración romántica',
                    'descripcion' => 'Arreglo floral especial, pétalos de rosa, velas LED y botella de vino espumoso en la habitación.',
                    'precio_nio' => 1500.00,
                    'icono' => 'heroicon-o-gift',
                ],
                [
                    'nombre' => 'Servicio VIP habitación',
                    'descripcion' => 'Atención de mayordomo privado, check-in express y amenities de cortesía personalizados.',
                    'precio_nio' => 2000.00,
                    'icono' => 'heroicon-o-trophy',
                ],
                [
                    'nombre' => 'Amenidades premium',
                    'descripcion' => 'Set de bienvenida de lujo con chocolates artesanales locales, canasta de frutas exóticas y flores de temporada.',
                    'precio_nio' => 850.00,
                    'icono' => 'heroicon-o-star',
                ],
            ],
            'CAT_SERV_TECNOLOGIA' => [
                [
                    'nombre' => 'WiFi premium',
                    'descripcion' => 'Acceso a conexión a internet simétrica de ultra alta velocidad ideal para videoconferencias o streaming.',
                    'precio_nio' => 200.00,
                    'icono' => 'heroicon-o-wifi',
                ],
                [
                    'nombre' => 'Alquiler de laptop',
                    'descripcion' => 'Uso temporal de computadora portátil de última generación preconfigurada con suites de oficina.',
                    'precio_nio' => 1000.00,
                    'icono' => 'heroicon-o-computer-desktop',
                ],
                [
                    'nombre' => 'Soporte técnico privado',
                    'descripcion' => 'Asistencia técnica personalizada para la configuración de dispositivos, redes y plataformas corporativas.',
                    'precio_nio' => 600.00,
                    'icono' => 'heroicon-o-wrench-screwdriver',
                ],
            ],
        ];

        // 3. Crear los servicios y sus precios equivalentes
        foreach ($serviciosPorCategoria as $categoriaCodigo => $servicios) {
            $categoria = Catalogo::where('codigo', $categoriaCodigo)->first();
            if (! $categoria) {
                $this->command->warn("No se encontró la categoría de servicio '{$categoriaCodigo}'. Se saltarán estos servicios.");

                continue;
            }

            foreach ($servicios as $sData) {
                // Generar código autogenerado secuencial usando el caso de uso del dominio
                $codigo = app(GenerarCodigoServicio::class)->ejecutar();

                // Crear servicio
                $servicio = Servicio::create([
                    'codigo' => $codigo,
                    'nombre' => $sData['nombre'],
                    'categoria_id' => $categoria->id,
                    'descripcion' => $sData['descripcion'],
                    'icono' => $sData['icono'],
                    'estado' => 1, // Activo
                ]);

                // Precio en Córdoba Nicaragüense (C$)
                Precio::create([
                    'priceable_type' => Servicio::class,
                    'priceable_id' => $servicio->id,
                    'moneda_id' => $nio->id,
                    'precio' => $sData['precio_nio'],
                    'fecha_inicio' => now()->toDateString(),
                    'estado' => 1, // Vigente
                    'es_oferta' => false,
                ]);

                // Precio equivalente en Dólar Estadounidense ($)
                $precioUsd = round($sData['precio_nio'] / $tipoCambio, 2);
                Precio::create([
                    'priceable_type' => Servicio::class,
                    'priceable_id' => $servicio->id,
                    'moneda_id' => $usd->id,
                    'precio' => $precioUsd,
                    'fecha_inicio' => now()->toDateString(),
                    'estado' => 1, // Vigente
                    'es_oferta' => false,
                ]);
            }
        }

        $this->command->info('Servicios y precios sembrados exitosamente.');
    }
}
