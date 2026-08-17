<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\ServicioAsignacion;
use Illuminate\Database\Seeder;

class EspacioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener monedas y equivalencia
        $nio = Moneda::where('codigo', 'NIO')->first();
        $usd = Moneda::where('codigo', 'USD')->first();

        if (! $nio || ! $usd) {
            $this->command->error('No se encontraron las monedas NIO o USD en el sistema. Asegúrate de que TasaCambioSeeder se ejecute primero.');

            return;
        }

        $tipoCambio = 36.5;

        // 2. Obtener ubicaciones
        $plantaBaja = Ubicacion::where('nombre', 'Planta Baja')->first();
        $plantaAlta = Ubicacion::where('nombre', 'Planta Alta')->first();

        $ubicacionDefecto = $plantaBaja ?? Ubicacion::first();

        if (! $ubicacionDefecto) {
            $this->command->error('No se encontró ninguna ubicación en el sistema. Asegúrate de que UbicacionSeeder se ejecute primero.');

            return;
        }

        // 3. Obtener o Crear políticas aplicables a los espacios
        $politicaReserva = Politica::updateOrCreate(
            ['titulo' => 'Política de Cancelación de Espacios Comerciales'],
            [
                'descripcion' => 'Cancelación sin penalización hasta 48 horas antes del evento. Cancelaciones dentro de las 48 horas previas incurrirán en el cargo del 50% del precio base de reserva.',
                'aplica_penalizacion' => true,
                'estado' => 1,
            ]
        );

        $politicaReserva->penalizaciones()->delete();
        $politicaReserva->penalizaciones()->createMany([
            [
                'min_unidades' => 2,
                'max_unidades' => null,
                'unidad' => 1,
                'porcentaje' => 0.00,
                'aplica_no_show' => false,
                'orden' => 1,
            ],
            [
                'min_unidades' => 0,
                'max_unidades' => 1,
                'unidad' => 1,
                'porcentaje' => 50.00,
                'aplica_no_show' => false,
                'orden' => 2,
            ],
            [
                'min_unidades' => null,
                'max_unidades' => null,
                'unidad' => 1,
                'porcentaje' => 100.00,
                'aplica_no_show' => true,
                'orden' => 3,
            ],
        ]);

        $politicaGimnasio = Politica::updateOrCreate(
            ['titulo' => 'Reglamento Interno del Gimnasio'],
            [
                'descripcion' => 'El horario de uso es de 06:00 a 22:00 horas. Calzado deportivo, toalla personal y ropa de entrenamiento obligatorios. Prohibido el ingreso a menores de 14 años sin supervisión de un adulto.',
                'estado' => 1,
            ]
        );

        // 4. Sembrar Espacios Padres

        // --- RESTAURANTE ---
        $restaurante = Espacio::create([
            'codigo' => 'REST-001',
            'nombre' => 'Restaurante Bugambilias',
            'descripcion' => 'Principal área de buffet, almuerzos a la carta y desayunos tradicionales del hotel.',
            'tipo' => TipoEspacio::RESTAURANTE,
            'capacidad_personas' => 100,
            'ubicacion_id' => $ubicacionDefecto->id,
            'estado' => EstadoEspacio::Disponible,
            'orden' => 1,
        ]);

        // Asociar servicio de WiFi al restaurante
        $wifi = Servicio::where('nombre', 'WiFi premium')->first();
        if ($wifi) {
            ServicioAsignacion::create([
                'serviceable_type' => Espacio::class,
                'serviceable_id' => $restaurante->id,
                'servicio_id' => $wifi->id,
                'incluido' => true,
                'estado' => 1,
            ]);
        }

        // Sembrar Mesas (Hijos del Restaurante)
        $mesasData = [
            [
                'codigo' => 'MESA-01',
                'nombre' => 'Mesa 1 (VIP)',
                'capacidad' => 4,
                'meta' => ['tipo_mesa' => 'redonda', 'zona_restaurante' => 'vip'],
                'orden' => 1,
            ],
            [
                'codigo' => 'MESA-02',
                'nombre' => 'Mesa 2',
                'capacidad' => 2,
                'meta' => ['tipo_mesa' => 'cuadrada', 'zona_restaurante' => 'interior'],
                'orden' => 2,
            ],
            [
                'codigo' => 'MESA-03',
                'nombre' => 'Mesa 3 (Terraza)',
                'capacidad' => 6,
                'meta' => ['tipo_mesa' => 'rectangular', 'zona_restaurante' => 'terraza'],
                'orden' => 3,
            ],
            [
                'codigo' => 'MESA-04',
                'nombre' => 'Mesa 4 (Terraza Vista Jardín)',
                'capacidad' => 8,
                'meta' => ['tipo_mesa' => 'rectangular', 'zona_restaurante' => 'terraza'],
                'orden' => 4,
            ],
        ];

        foreach ($mesasData as $mData) {
            Espacio::create([
                'padre_id' => $restaurante->id,
                'codigo' => $mData['codigo'],
                'nombre' => $mData['nombre'],
                'tipo' => TipoEspacio::MESA,
                'capacidad_personas' => $mData['capacidad'],
                'ubicacion_id' => null, // Hereda la ubicación física del restaurante padre
                'estado' => EstadoEspacio::Disponible,
                'orden' => $mData['orden'],
                'meta_datos' => $mData['meta'],
            ]);
        }

        // --- GIMNASIO ---
        $gym = Espacio::create([
            'codigo' => 'GYM-001',
            'nombre' => 'Gimnasio Fitness Center',
            'descripcion' => 'Gimnasio equipado con caminadoras, pesas libres, bicicletas y climatización.',
            'tipo' => TipoEspacio::GYM,
            'capacidad_personas' => 25,
            'ubicacion_id' => $plantaAlta ? $plantaAlta->id : $ubicacionDefecto->id,
            'estado' => EstadoEspacio::Disponible,
            'orden' => 2,
            'meta_datos' => [
                'restricciones_gimnasio' => 'Uso obligatorio de toalla personal, vestimenta y calzado deportivo adecuado. No se permite ingresar alimentos.',
            ],
        ]);

        // Registrar tarifas históricas por hora para el Gimnasio (NIO y USD)
        Precio::create([
            'priceable_type' => Espacio::class,
            'priceable_id' => $gym->id,
            'moneda_id' => $nio->id,
            'precio' => 150.00, // C$ 150 por hora
            'tipo_precio' => 'por_hora',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => false,
        ]);
        Precio::create([
            'priceable_type' => Espacio::class,
            'priceable_id' => $gym->id,
            'moneda_id' => $usd->id,
            'precio' => round(150.00 / $tipoCambio, 2), // Eq USD
            'tipo_precio' => 'por_hora',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => false,
        ]);

        if ($wifi) {
            ServicioAsignacion::create([
                'serviceable_type' => Espacio::class,
                'serviceable_id' => $gym->id,
                'servicio_id' => $wifi->id,
                'incluido' => true,
                'estado' => 1,
            ]);
        }
        $gym->politicas()->attach($politicaGimnasio->id);

        // --- GRAN SALÓN DE EVENTOS ---
        $salon = Espacio::create([
            'codigo' => 'SALON-001',
            'nombre' => 'Salón de Eventos Real Bugambilias',
            'descripcion' => 'Majestuoso salón ideal para bodas, graduaciones, juntas corporativas de gran envergadura o banquetes.',
            'tipo' => TipoEspacio::SALON,
            'capacidad_personas' => 180,
            'ubicacion_id' => $ubicacionDefecto->id,
            'estado' => EstadoEspacio::Disponible,
            'orden' => 3,
            'meta_datos' => [
                'metros_cuadrados' => 220,
                'equipamiento_incluido' => ['proyector', 'sonido', 'clima', 'luces', 'pizarra'],
            ],
        ]);

        // Tarifas para el Salón (Base y Por Hora)
        // 1. Precios Base (Reserva Completa / Evento)
        Precio::create([
            'priceable_type' => Espacio::class,
            'priceable_id' => $salon->id,
            'moneda_id' => $nio->id,
            'precio' => 7000.00, // C$ 7,000 por reserva/evento completo
            'tipo_precio' => 'base',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => false,
        ]);
        Precio::create([
            'priceable_type' => Espacio::class,
            'priceable_id' => $salon->id,
            'moneda_id' => $usd->id,
            'precio' => 200.00, // $ 200 USD
            'tipo_precio' => 'base',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => false,
        ]);

        // 2. Precios por Hora (Renta Fraccionada)
        Precio::create([
            'priceable_type' => Espacio::class,
            'priceable_id' => $salon->id,
            'moneda_id' => $nio->id,
            'precio' => 1000.00, // C$ 1,000 por hora
            'tipo_precio' => 'por_hora',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => false,
        ]);
        Precio::create([
            'priceable_type' => Espacio::class,
            'priceable_id' => $salon->id,
            'moneda_id' => $usd->id,
            'precio' => 28.00, // $ 28 USD
            'tipo_precio' => 'por_hora',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => false,
        ]);

        // Servicios asociados al Salón
        $proyector = Servicio::where('nombre', 'Alquiler de proyector')->first();
        if ($proyector) {
            ServicioAsignacion::create([
                'serviceable_type' => Espacio::class,
                'serviceable_id' => $salon->id,
                'servicio_id' => $proyector->id,
                'incluido' => true, // Incluido en el precio base de alquiler
                'estado' => 1,
            ]);
        }
        if ($wifi) {
            ServicioAsignacion::create([
                'serviceable_type' => Espacio::class,
                'serviceable_id' => $salon->id,
                'servicio_id' => $wifi->id,
                'incluido' => true,
                'estado' => 1,
            ]);
        }

        $salon->politicas()->attach($politicaReserva->id);

        // --- SPA CABINA DE MASAJES ---
        $spa = Espacio::create([
            'codigo' => 'SPA-001',
            'nombre' => 'Cabina de Masajes Relax',
            'descripcion' => 'Cabina ambientada con aromaterapia y música relajante para terapias corporales.',
            'tipo' => TipoEspacio::SPA,
            'capacidad_personas' => 2,
            'ubicacion_id' => $ubicacionDefecto->id,
            'estado' => EstadoEspacio::Disponible,
            'orden' => 4,
        ]);

        // Tarifas base (Reserva / Sesión)
        Precio::create([
            'priceable_type' => Espacio::class,
            'priceable_id' => $spa->id,
            'moneda_id' => $nio->id,
            'precio' => 600.00, // C$ 600 la sesión base
            'tipo_precio' => 'base',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => false,
        ]);
        Precio::create([
            'priceable_type' => Espacio::class,
            'priceable_id' => $spa->id,
            'moneda_id' => $usd->id,
            'precio' => 16.50, // $ 16.50 USD
            'tipo_precio' => 'base',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 1,
            'es_oferta' => false,
        ]);

        $spa->politicas()->attach($politicaReserva->id);

        $this->command->info('Espacios, sub-espacios (mesas), tarifas históricas, servicios y políticas de espacios sembrados exitosamente.');
    }
}
