<?php

declare(strict_types=1);

namespace Database\Seeders\Servicios;

use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Interactors\Servicios\GenerarCodigoServicio;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;
use Illuminate\Database\Seeder;

class ServicioDefinicionSeeder extends Seeder
{
    public function run(): void
    {
        $generadorCodigo = app(GenerarCodigoServicio::class);

        $serviciosPorCategoria = [
            'CAT_SERV_ALOJAMIENTO' => [
                [
                    'nombre' => 'Early Check-in',
                    'descripcion' => 'Permite el acceso a la habitación antes de la hora estándar de entrada (sujeto a disponibilidad y preparación de habitación).',
                    'icono' => 'clock',
                    'web' => true,
                    'imagenes' => ['/images/room-detail.webp', '/images/hero-main.webp'],
                    'politicas' => ['Política de No Fumar y Convivencia'],
                ],
                [
                    'nombre' => 'Late Check-out',
                    'descripcion' => 'Extiende la estancia en la habitación más allá de la hora de salida estándar hasta las 16:00 horas.',
                    'icono' => 'log-out',
                    'web' => true,
                    'imagenes' => ['/images/room-detail.webp'],
                    'politicas' => ['Política de No Fumar y Convivencia'],
                ],
                [
                    'nombre' => 'Cama adicional / Cuna',
                    'descripcion' => 'Instalación de una cama extra confortable o cuna para bebé con lencería hipoalergénica en la habitación.',
                    'icono' => 'bed',
                    'web' => true,
                    'imagenes' => ['/images/group-room.webp'],
                    'politicas' => ['Política de No Fumar y Convivencia'],
                ],
                [
                    'nombre' => 'Servicio a la Habitación 24/7',
                    'descripcion' => 'Entrega de platillos selectos, bebidas y snacks directamente a la habitación en cualquier momento del día o la noche.',
                    'icono' => 'coffee',
                    'web' => true,
                    'imagenes' => ['/images/service-kitchen.webp'],
                    'politicas' => ['Política de No Fumar y Convivencia'],
                ],
            ],
            'CAT_SERV_BIENESTAR' => [
                [
                    'nombre' => 'Masaje Relajante Aromaterapia 60 min',
                    'descripcion' => 'Terapia corporal completa con aceites esenciales botánicos para liberar tensión muscular, mejorar la circulación y reducir el estrés.',
                    'icono' => 'sparkles',
                    'web' => true,
                    'imagenes' => ['/images/service-pool.webp', '/images/bathroom.webp'],
                    'politicas' => ['Política de Cancelación y Reprogramación de Spa'],
                ],
                [
                    'nombre' => 'Masaje Terapéutico Tejido Profundo 90 min',
                    'descripcion' => 'Terapia intensiva enfocada en aliviar contracturas crónicas y fatiga física mediante técnicas neuromusculares avanzadas.',
                    'icono' => 'heart',
                    'web' => true,
                    'imagenes' => ['/images/service-pool.webp'],
                    'politicas' => ['Política de Cancelación y Reprogramación de Spa'],
                ],
                [
                    'nombre' => 'Circuito Hidroterapia & Sauna Seco',
                    'descripcion' => 'Sesión rejuvenecedora que combina baño de vapor aromatizado, sauna finlandés y pozas de contraste térmico.',
                    'icono' => 'flame',
                    'web' => true,
                    'imagenes' => ['/images/pool-front-view.webp', '/images/service-pool.webp'],
                    'politicas' => ['Política de Cancelación y Reprogramación de Spa'],
                ],
                [
                    'nombre' => 'Jacuzzi Privado Climatizado',
                    'descripcion' => 'Uso exclusivo de tina de hidromasaje con sales minerales relajantes y vistas panorámicas hacia los jardines.',
                    'icono' => 'bath',
                    'web' => true,
                    'imagenes' => ['/images/pool-scaled.webp'],
                    'politicas' => ['Política de Cancelación y Reprogramación de Spa'],
                ],
                [
                    'nombre' => 'Clase de Yoga & Mindfulness al Amanecer',
                    'descripcion' => 'Práctica guiada en deck al aire libre con instructores certificados para conectar cuerpo y mente frente a la naturaleza.',
                    'icono' => 'sun',
                    'web' => true,
                    'imagenes' => ['/images/terrace.webp'],
                    'politicas' => ['Política de Cancelación y Reprogramación de Spa'],
                ],
            ],
            'CAT_SERV_TRANSPORTE' => [
                [
                    'nombre' => 'Shuttle Aeropuerto VIP (Ida y Vuelta)',
                    'descripcion' => 'Traslado privado en vehículo ejecutivo climatizado con conductor bilingüe, agua embotellada y asistencia con el equipaje.',
                    'icono' => 'plane',
                    'web' => true,
                    'imagenes' => ['/images/hero-secondary.webp'],
                    'politicas' => ['Política de Traslados y Transporte'],
                ],
                [
                    'nombre' => 'Valet Parking & Custodia Vehicular',
                    'descripcion' => 'Recepción personalizada de vehículos en el lobby principal con vigilancia privada 24 horas y estacionamiento cubierto.',
                    'icono' => 'car',
                    'web' => false,
                    'imagenes' => ['/images/hero-secondary.webp'],
                    'politicas' => ['Política de Traslados y Transporte'],
                ],
                [
                    'nombre' => 'Custodia de Equipaje Extendido',
                    'descripcion' => 'Guarda de equipaje en sala de seguridad climatizada antes del check-in o tras realizar el check-out.',
                    'icono' => 'briefcase',
                    'web' => true,
                    'imagenes' => ['/images/hero-main.webp'],
                    'politicas' => ['Política de Traslados y Transporte'],
                ],
            ],
            'CAT_SERV_LAVANDERIA' => [
                [
                    'nombre' => 'Lavado y Secado de Ropa por Libra',
                    'descripcion' => 'Servicio completo de lavado con detergentes hipoalergénicos biodegradables, secado a temperatura controlada y doblado perfecto.',
                    'icono' => 'shirt',
                    'web' => true,
                    'imagenes' => ['/images/bathroom.webp'],
                    'politicas' => ['Política de Lavandería y Cuidado de Prendas'],
                ],
                [
                    'nombre' => 'Planchado Profesional Express',
                    'descripcion' => 'Planchado a vapor de alta precisión para camisas, trajes, vestidos y prendas ejecutivas entregadas en gancho protector.',
                    'icono' => 'scissors',
                    'web' => true,
                    'imagenes' => ['/images/bathroom.webp'],
                    'politicas' => ['Política de Lavandería y Cuidado de Prendas'],
                ],
                [
                    'nombre' => 'Limpieza Extra y Cambio de Blancos',
                    'descripcion' => 'Servicio de mucama a solicitud para renovación total de toallas, sábanas de algodón egipcio y desinfección ambiental.',
                    'icono' => 'sparkles',
                    'web' => false,
                    'imagenes' => ['/images/room-detail.webp'],
                    'politicas' => ['Política de Lavandería y Cuidado de Prendas'],
                ],
            ],
            'CAT_SERV_NEGOCIOS' => [
                [
                    'nombre' => 'Alquiler de Sala de Juntas Ejecutiva',
                    'descripcion' => 'Salón corporativo con pantalla 4K interactiva, videoconferencia de alta fidelidad, pizarra y capacidad para 12 personas.',
                    'icono' => 'users',
                    'web' => true,
                    'imagenes' => ['/images/service-events.webp'],
                    'politicas' => ['Política de Salones y Equipos Audiovisuales'],
                ],
                [
                    'nombre' => 'Alquiler de Proyector Láser & Sonido',
                    'descripcion' => 'Proyector de 5000 lúmenes con sistema de sonido inalámbrico y microfonía para conferencias y eventos en áreas del hotel.',
                    'icono' => 'presentation',
                    'web' => false,
                    'imagenes' => ['/images/service-events.webp'],
                    'politicas' => ['Política de Salones y Equipos Audiovisuales'],
                ],
                [
                    'nombre' => 'Coffee Break Ejecutivo (por persona)',
                    'descripcion' => 'Servicio de café gourmet nicaragüense, té selecto, jugos naturales, repostería artesanal y bocadillos salados.',
                    'icono' => 'coffee',
                    'web' => true,
                    'imagenes' => ['/images/service-kitchen.webp', '/images/service-bartender.webp'],
                    'politicas' => ['Política de Salones y Equipos Audiovisuales'],
                ],
            ],
            'CAT_SERV_RECREACION' => [
                [
                    'nombre' => 'Tour Guiado Colonial & Volcanes',
                    'descripcion' => 'Excursión guiada por historiadores y guías naturalistas con transporte privado, entradas y refrigerio típico incluido.',
                    'icono' => 'map',
                    'web' => true,
                    'imagenes' => ['/images/terrace.webp'],
                    'politicas' => ['Política de Traslados y Transporte'],
                ],
                [
                    'nombre' => 'Alquiler de Bicicletas de Montaña',
                    'descripcion' => 'Bicicletas de aluminio con suspensión, casco, kit de hidratación y mapa de senderos ecológicos locales.',
                    'icono' => 'bike',
                    'web' => true,
                    'imagenes' => ['/images/terrace.webp'],
                    'politicas' => ['Política de Salones y Equipos Audiovisuales'],
                ],
            ],
            'CAT_SERV_VIP' => [
                [
                    'nombre' => 'Decoración Romántica Premium',
                    'descripcion' => 'Ambientación de la habitación con pétalos de rosa importadas, velas aromáticas LED, arreglo floral y botella de espumante.',
                    'icono' => 'gift',
                    'web' => true,
                    'imagenes' => ['/images/room-detail.webp', '/images/service-bartender.webp'],
                    'politicas' => ['Política de Experiencias VIP y Decoraciones'],
                ],
                [
                    'nombre' => 'Cena Privada a la Luz de las Velas',
                    'descripcion' => 'Menú degustación de 4 tiempos preparado por nuestro Chef Ejecutivo con maridaje de vinos en terraza privada con vista estelar.',
                    'icono' => 'utensils',
                    'web' => true,
                    'imagenes' => ['/images/restaurant-hero.jpg', '/images/service-kitchen.webp'],
                    'politicas' => ['Política de Experiencias VIP y Decoraciones'],
                ],
                [
                    'nombre' => 'Cesta de Bienvenida Gourmet',
                    'descripcion' => 'Selección de chocolates artesanales de cacao puro, quesos madurados, frutas tropicales frescas y vino reserva.',
                    'icono' => 'star',
                    'web' => true,
                    'imagenes' => ['/images/service-bartender.webp'],
                    'politicas' => ['Política de Experiencias VIP y Decoraciones'],
                ],
            ],
            'CAT_SERV_TECNOLOGIA' => [
                [
                    'nombre' => 'WiFi Simétrico Dedicado Ultra Alta Velocidad',
                    'descripcion' => 'Acceso a ancho de banda garantizado de 100 Mbps simétricos con IP pública o túnel prioritario para streaming y teletrabajo.',
                    'icono' => 'wifi',
                    'web' => true,
                    'imagenes' => ['/images/hero-secondary.webp'],
                    'politicas' => ['Política de Salones y Equipos Audiovisuales'],
                ],
                [
                    'nombre' => 'Alquiler de Laptop Corporativa',
                    'descripcion' => 'Dispositivo portátil de alto rendimiento con software de productividad, antivirus y restablecimiento seguro tras su uso.',
                    'icono' => 'laptop',
                    'web' => false,
                    'imagenes' => ['/images/service-events.webp'],
                    'politicas' => ['Política de Salones y Equipos Audiovisuales'],
                ],
            ],
        ];

        foreach ($serviciosPorCategoria as $catCodigo => $servicios) {
            $categoria = Catalogo::where('codigo', $catCodigo)->first();
            if (! $categoria) {
                continue;
            }

            foreach ($servicios as $sData) {
                $recurso = RecursoReservable::firstOrCreate(
                    ['nombre' => $sData['nombre'], 'tipo' => TipoRecursoReservable::SERVICIO],
                    [
                        'capacidad' => null,
                        'control_disponibilidad' => ControlDisponibilidad::SIN_BLOQUEO,
                        'duracion_minutos' => null,
                        'estado' => EstadoRecursoReservable::ACTIVO,
                    ]
                );

                $servicio = Servicio::where('nombre', $sData['nombre'])->first();

                if ($servicio === null) {
                    $codigo = $generadorCodigo->ejecutar();
                    $servicio = Servicio::create([
                        'codigo' => $codigo,
                        'nombre' => $sData['nombre'],
                        'categoria_id' => $categoria->id,
                        'descripcion' => $sData['descripcion'],
                        'icono' => $sData['icono'],
                        'reservable_id' => $recurso->id,
                        'web' => $sData['web'],
                        'estado' => 1,
                    ]);
                } else {
                    $servicio->update([
                        'categoria_id' => $categoria->id,
                        'descripcion' => $sData['descripcion'],
                        'icono' => $sData['icono'],
                        'reservable_id' => $recurso->id,
                        'web' => $sData['web'],
                        'estado' => 1,
                    ]);
                }

                // Sincronizar Políticas asociadas
                $politicasIds = Politica::whereIn('titulo', $sData['politicas'])->pluck('id')->toArray();
                if ($politicasIds !== []) {
                    $servicio->politicas()->syncWithoutDetaching($politicasIds);
                }

                // Sincronizar Imágenes asociadas
                foreach ($sData['imagenes'] as $index => $imgUrl) {
                    Imagen::firstOrCreate([
                        'imagenable_type' => Servicio::class,
                        'imagenable_id' => $servicio->id,
                        'url' => $imgUrl,
                    ], [
                        'orden' => $index + 1,
                    ]);
                }
            }
        }
    }
}
