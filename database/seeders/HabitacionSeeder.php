<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoServicioAsignacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\DetalleHabitacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Habitaciones\PrecioHabitacion;
use App\Models\Habitaciones\ServicioHabitacion;
use App\Models\Monedas\Moneda;
use App\Models\Politicas\Politica;
use App\Models\Servicios\Servicio;
use App\UseCases\Habitaciones\Mutations\GenerarCodigoHabitacion;
use App\UseCases\Habitaciones\Mutations\GenerarSlugHabitacion;
use Illuminate\Database\Seeder;

class HabitacionSeeder extends Seeder
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

        $tipoCambio = 36.5;

        $politicasData = [
            [
                'titulo' => 'Política de Cancelación',
                'descripcion' => 'Cancelación gratuita hasta 24 horas antes de la llegada. En caso de no presentarse o cancelar fuera de este plazo, se penalizará con el cobro de la primera noche de estancia.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de No Fumar',
                'descripcion' => 'Todas nuestras habitaciones y áreas cerradas son 100% libres de humo de tabaco o cigarrillos electrónicos. Se aplicará una penalización de $100 USD para cubrir costos de limpieza profunda si se detecta humo.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Mascotas',
                'descripcion' => 'No se admiten mascotas en esta habitación para garantizar el bienestar de todos nuestros huéspedes, a excepción de animales de asistencia certificados por razones médicas.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Check-in y Check-out',
                'descripcion' => 'La hora de entrada (Check-in) es a las 15:00 horas. La hora de salida (Check-out) es a las 11:00 horas. Cargos adicionales pueden aplicar por Early Check-in o Late Check-out según disponibilidad.',
                'estado' => 1,
            ],
        ];

        $politicasModelos = [];
        foreach ($politicasData as $pData) {
            $politicasModelos[$pData['titulo']] = Politica::updateOrCreate(
                ['titulo' => $pData['titulo']],
                $pData
            );
        }

        // 3. Datos de las 12 habitaciones premium
        $roomsData = [
            [
                'nombre' => 'Habitación 101 - Estándar Sencilla',
                'numero' => 101,
                'categoria_codigo' => 'CAT_HAB_ESTANDAR',
                'ubicacion_nombre' => 'Ala Norte',
                'descripcion' => 'Habitación acogedora de estilo clásico con cama individual, perfecta para viajeros de negocios o estancias cortas. Cuenta con escritorio de trabajo, iluminación LED cálida y minibar completo.',
                'capacidad_adultos' => 1,
                'capacidad_ninos' => 0,
                'medidas' => 20.00,
                'vistas_codigos' => ['VISTA_INTERIOR'],
                'precio_usd' => 45.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => false],
                    ['nombre' => 'Early Check-in', 'incluido' => false],
                    ['nombre' => 'Late Check-out', 'incluido' => false],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 102 - Estándar Doble',
                'numero' => 102,
                'categoria_codigo' => 'CAT_HAB_ESTANDAR',
                'ubicacion_nombre' => 'Ala Norte',
                'descripcion' => 'Espaciosa habitación con dos camas matrimoniales ideal para familias pequeñas o parejas. Equipada con smart TV, aire acondicionado silencioso y baño privado con amenidades básicas.',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 1,
                'medidas' => 24.00,
                'vistas_codigos' => ['VISTA_JARDIN'],
                'precio_usd' => 55.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => false],
                    ['nombre' => 'Cama adicional', 'incluido' => false],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 103 - Estándar Matrimonial',
                'numero' => 103,
                'categoria_codigo' => 'CAT_HAB_ESTANDAR',
                'ubicacion_nombre' => 'Ala Norte',
                'descripcion' => 'Perfecta para parejas, cuenta con una cómoda cama Queen Size, grandes ventanales con iluminación natural y una decoración minimalista y relajante.',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 0,
                'medidas' => 22.00,
                'vistas_codigos' => ['VISTA_INTERIOR'],
                'precio_usd' => 50.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => false],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 104 - Deluxe Vista Jardín',
                'numero' => 104,
                'categoria_codigo' => 'CAT_HAB_DELUXE',
                'ubicacion_nombre' => 'Ala Sur',
                'descripcion' => 'Hermosa habitación Deluxe con cama King Size y balcón privado con vista directa a nuestros exuberantes jardines tropicales. Incluye cafetera premium y batas de algodón de cortesía.',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 1,
                'medidas' => 28.00,
                'vistas_codigos' => ['VISTA_JARDIN'],
                'precio_usd' => 75.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Amenidades premium', 'incluido' => false],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 105 - Deluxe Vista Piscina',
                'numero' => 105,
                'categoria_codigo' => 'CAT_HAB_DELUXE',
                'ubicacion_nombre' => 'Ala Sur',
                'descripcion' => 'Excelente habitación de categoría superior con dos camas matrimoniales y terraza privada con acceso o vista directa al área de la piscina climatizada.',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 2,
                'medidas' => 30.00,
                'vistas_codigos' => ['VISTA_PISCINA'],
                'precio_usd' => 85.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Cama adicional', 'incluido' => false],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 106 - Deluxe King',
                'numero' => 106,
                'categoria_codigo' => 'CAT_HAB_DELUXE',
                'ubicacion_nombre' => 'Ala Sur',
                'descripcion' => 'Confort supremo con cama California King, decoración moderna con detalles locales en madera preciosa y baño de mármol equipado con ducha tipo lluvia.',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 0,
                'medidas' => 32.00,
                'vistas_codigos' => ['VISTA_PISCINA', 'VISTA_JARDIN'],
                'precio_usd' => 80.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Decoración romántica', 'incluido' => false],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 201 - Junior Suite',
                'numero' => 201,
                'categoria_codigo' => 'CAT_HAB_SUITE',
                'ubicacion_nombre' => 'Ala Norte',
                'descripcion' => 'Suite de concepto abierto que combina sala de estar con sillones confortables y dormitorio de lujo con cama King. Balcón con vista panorámica y tina de baño de diseño.',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 2,
                'medidas' => 45.00,
                'vistas_codigos' => ['VISTA_CIUDAD'],
                'precio_usd' => 120.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Amenidades premium', 'incluido' => true],
                    ['nombre' => 'Servicio VIP habitación', 'incluido' => false],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out', 'Política de Mascotas'],
            ],
            [
                'nombre' => 'Habitación 202 - Executive Suite',
                'numero' => 202,
                'categoria_codigo' => 'CAT_HAB_SUITE',
                'ubicacion_nombre' => 'Ala Norte',
                'descripcion' => 'Diseñada para ejecutivos exigentes o estancias prolongadas. Cuenta con área de oficina independiente, sala de reuniones pequeña, baño con tina de hidromasaje y minibar VIP gratis de cortesía.',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 1,
                'medidas' => 50.00,
                'vistas_codigos' => ['VISTA_JARDIN', 'VISTA_MONTANA'],
                'precio_usd' => 140.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Amenidades premium', 'incluido' => true],
                    ['nombre' => 'Soporte técnico privado', 'incluido' => true],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 203 - Family Suite Premium',
                'numero' => 203,
                'categoria_codigo' => 'CAT_HAB_FAMILIAR',
                'ubicacion_nombre' => 'Ala Sur',
                'descripcion' => 'La opción ideal para familias grandes. Dos dormitorios independientes, sala de estar amplia con comedor, cocina básica equipada con microondas y minibar, y tina de baño grande.',
                'capacidad_adultos' => 4,
                'capacidad_ninos' => 2,
                'medidas' => 60.00,
                'vistas_codigos' => ['VISTA_PISCINA'],
                'precio_usd' => 160.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Amenidades premium', 'incluido' => true],
                    ['nombre' => 'Limpieza extra de habitación', 'incluido' => true],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 204 - Suite Nupcial Romance',
                'numero' => 204,
                'categoria_codigo' => 'CAT_HAB_SUITE',
                'ubicacion_nombre' => 'Ala Sur',
                'descripcion' => 'Atmósfera de romance absoluto. Cama dosel King Size, balcón con hamaca y vista espectacular al atardecer sobre el mar, jacuzzi privado integrado y decoración romántica de cortesía.',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 0,
                'medidas' => 55.00,
                'vistas_codigos' => ['VISTA_MAR'],
                'precio_usd' => 180.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Amenidades premium', 'incluido' => true],
                    ['nombre' => 'Decoración romántica', 'incluido' => true],
                    ['nombre' => 'Servicio VIP habitación', 'incluido' => true],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 301 - Penthouse Presidencial',
                'numero' => 301,
                'categoria_codigo' => 'CAT_HAB_PRESIDENCIAL',
                'ubicacion_nombre' => 'Ala Norte',
                'descripcion' => 'Nuestra máxima expresión de opulencia. Ubicado en el último piso con terraza de 40m², piscina infinity privada, cocina gourmet completa, mesa de comedor formal y acabados de lujo mundial.',
                'capacidad_adultos' => 4,
                'capacidad_ninos' => 2,
                'medidas' => 120.00,
                'vistas_codigos' => ['VISTA_MAR', 'VISTA_PISCINA', 'VISTA_MONTANA'],
                'precio_usd' => 350.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Amenidades premium', 'incluido' => true],
                    ['nombre' => 'Servicio VIP habitación', 'incluido' => true],
                    ['nombre' => 'Jacuzzi privado 1 hora', 'incluido' => true],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
            [
                'nombre' => 'Habitación 302 - Royal Presidencial Suite',
                'numero' => 302,
                'categoria_codigo' => 'CAT_HAB_PRESIDENCIAL',
                'ubicacion_nombre' => 'Ala Sur',
                'descripcion' => 'Refugio real incomparable. Lujosa suite de tres habitaciones, spa privado integrado con sauna y jacuzzi, mayordomo exclusivo las 24 horas y traslados al aeropuerto de cortesía.',
                'capacidad_adultos' => 4,
                'capacidad_ninos' => 4,
                'medidas' => 135.00,
                'vistas_codigos' => ['VISTA_MAR', 'VISTA_PISCINA', 'VISTA_JARDIN'],
                'precio_usd' => 450.00,
                'servicios' => [
                    ['nombre' => 'WiFi premium', 'incluido' => true],
                    ['nombre' => 'Amenidades premium', 'incluido' => true],
                    ['nombre' => 'Servicio VIP habitación', 'incluido' => true],
                    ['nombre' => 'Acceso al sauna', 'incluido' => true],
                    ['nombre' => 'Shuttle aeropuerto', 'incluido' => true],
                ],
                'politicas' => ['Política de Cancelación', 'Política de No Fumar', 'Política de Check-in y Check-out'],
            ],
        ];

        // 4. Sembrar habitaciones, detalles, precios y relaciones
        foreach ($roomsData as $rData) {
            // Verificar si ya existe
            $habitacionExistente = Habitacion::where('numero', $rData['numero'])->first();
            if ($habitacionExistente) {
                $this->command->warn("La habitación con número {$rData['numero']} ya existe. Se omitirá.");

                continue;
            }

            // Obtener categoría
            $categoria = Catalogo::where('codigo', $rData['categoria_codigo'])->first();
            if (! $categoria) {
                $this->command->error("No se encontró la categoría con código '{$rData['categoria_codigo']}'. Se omitirá la habitación {$rData['numero']}.");

                continue;
            }

            // Obtener ubicación
            $ubicacion = Ubicacion::where('nombre', $rData['ubicacion_nombre'])->first();
            if (! $ubicacion) {
                $this->command->error("No se encontró la ubicación con nombre '{$rData['ubicacion_nombre']}'. Se omitirá la habitación {$rData['numero']}.");

                continue;
            }

            // Generar código autotraducido
            $codigo = app(GenerarCodigoHabitacion::class)->execute();
            $slug = app(GenerarSlugHabitacion::class)->execute($rData['nombre']);

            // Crear Habitación
            $habitacion = Habitacion::create([
                'codigo' => $codigo,
                'numero' => $rData['numero'],
                'slug' => $slug,
                'nombre' => $rData['nombre'],
                'descripcion' => $rData['descripcion'],
                'categoria_id' => $categoria->id,
                'ubicacion_id' => $ubicacion->id,
                'estado' => EstadoHabitacion::Activa,
            ]);

            // Mapear códigos de vistas a IDs
            $vistasIds = [];
            foreach ($rData['vistas_codigos'] as $vCodigo) {
                $catalogoVista = Catalogo::where('codigo', $vCodigo)->first();
                if ($catalogoVista) {
                    $vistasIds[] = $catalogoVista->id;
                }
            }

            // Crear Detalles de la Habitación
            DetalleHabitacion::create([
                'habitacion_id' => $habitacion->id,
                'capacidad_adultos' => $rData['capacidad_adultos'],
                'capacidad_ninos' => $rData['capacidad_ninos'],
                'medidas' => $rData['medidas'],
                'vistas' => $vistasIds,
            ]);

            // Precio en Córdoba Nicaragüense (C$)
            $precioNio = round($rData['precio_usd'] * $tipoCambio, 2);
            PrecioHabitacion::create([
                'habitacion_id' => $habitacion->id,
                'moneda_id' => $nio->id,
                'precio' => $precioNio,
                'fecha_inicio' => now()->toDateString(),
                'estado' => 1, // Vigente
                'es_oferta' => false,
            ]);

            // Precio en Dólar Estadounidense ($)
            PrecioHabitacion::create([
                'habitacion_id' => $habitacion->id,
                'moneda_id' => $usd->id,
                'precio' => $rData['precio_usd'],
                'fecha_inicio' => now()->toDateString(),
                'estado' => 1, // Vigente
                'es_oferta' => false,
            ]);

            // Asignar servicios a la habitación
            foreach ($rData['servicios'] as $sData) {
                $servicio = Servicio::where('nombre', $sData['nombre'])->first();
                if ($servicio) {
                    ServicioHabitacion::create([
                        'servicio_id' => $servicio->id,
                        'habitacion_id' => $habitacion->id,
                        'incluido' => $sData['incluido'],
                        'estado' => EstadoServicioAsignacion::Activo,
                    ]);
                }
            }

            // Asignar políticas a la habitación
            foreach ($rData['politicas'] as $pTitulo) {
                $habitacion->politicas()->attach($politicasModelos[$pTitulo]->id);
            }
        }

        $this->command->info('12 Habitaciones, detalles, precios y políticas sembrados exitosamente.');
    }
}
