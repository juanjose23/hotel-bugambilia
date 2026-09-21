<?php

declare(strict_types=1);

namespace Database\Seeders\Servicios;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use Illuminate\Database\Seeder;

class ServicioCatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $tipoCategoriaServicio = CatalogoTipo::where('codigo', 'CATEGORIA_SERVICIO')->first();
        $tipoServicio = CatalogoTipo::where('codigo', 'TIPO_SERVICIO')->first();

        if (! $tipoCategoriaServicio) {
            $tipoCategoriaServicio = CatalogoTipo::create([
                'codigo' => 'CATEGORIA_SERVICIO',
                'nombre' => 'Categorías de Servicio',
                'descripcion' => 'Clasificación principal de los servicios ofrecidos por el hotel',
                'estado' => 1,
            ]);
        }

        if (! $tipoServicio) {
            $tipoServicio = CatalogoTipo::create([
                'codigo' => 'TIPO_SERVICIO',
                'nombre' => 'Tipos de Servicio',
                'descripcion' => 'Modalidades de contratación de servicios del hotel',
                'estado' => 1,
            ]);
        }

        $categorias = [
            [
                'codigo' => 'CAT_SERV_ALOJAMIENTO',
                'nombre' => 'Alojamiento y Estancia',
                'prefijo' => 'EST',
                'descripcion' => 'Servicios adicionales directos para la estancia en habitaciones y confort del huésped.',
            ],
            [
                'codigo' => 'CAT_SERV_BIENESTAR',
                'nombre' => 'Bienestar, Spa & Salud',
                'prefijo' => 'SPA',
                'descripcion' => 'Terapias corporales, masajes, tratamientos faciales, hidroterapia y sauna.',
            ],
            [
                'codigo' => 'CAT_SERV_TRANSPORTE',
                'nombre' => 'Transporte y Logística',
                'prefijo' => 'TRA',
                'descripcion' => 'Shuttle aeropuerto, traslados privados, valet parking y custodia de equipaje.',
            ],
            [
                'codigo' => 'CAT_SERV_LAVANDERIA',
                'nombre' => 'Lavandería y Tintorería',
                'prefijo' => 'LAV',
                'descripcion' => 'Lavado, planchado profesional, tintorería y limpieza profunda especializada.',
            ],
            [
                'codigo' => 'CAT_SERV_NEGOCIOS',
                'nombre' => 'Negocios, Salones y Eventos',
                'prefijo' => 'EVE',
                'descripcion' => 'Alquiler de salones ejecutivos, equipos audiovisuales y coffee breaks corporativos.',
            ],
            [
                'codigo' => 'CAT_SERV_RECREACION',
                'nombre' => 'Recreación y Entretenimiento',
                'prefijo' => 'REC',
                'descripcion' => 'Tours guiados locales, alquiler de bicicletas, senderismo y actividades recreativas.',
            ],
            [
                'codigo' => 'CAT_SERV_VIP',
                'nombre' => 'Servicios VIP & Experiencias',
                'prefijo' => 'VIP',
                'descripcion' => 'Cenas románticas, decoraciones personalizadas, mayordomía y amenidades premium.',
            ],
            [
                'codigo' => 'CAT_SERV_TECNOLOGIA',
                'nombre' => 'Tecnología y Conectividad',
                'prefijo' => 'TEC',
                'descripcion' => 'Conexión a internet dedicada, soporte técnico para huéspedes y alquiler de dispositivos.',
            ],
        ];

        foreach ($categorias as $index => $cat) {
            Catalogo::updateOrCreate(
                [
                    'catalogo_tipo_id' => $tipoCategoriaServicio->id,
                    'codigo' => $cat['codigo'],
                ],
                [
                    'nombre' => $cat['nombre'],
                    'prefijo' => $cat['prefijo'],
                    'descripcion' => $cat['descripcion'],
                    'orden' => $index + 1,
                    'estado' => 1,
                ]
            );
        }

        $tipos = [
            ['codigo' => 'TIP_SERV_SOLO_ALOJAMIENTO', 'nombre' => 'Solo Alojamiento', 'prefijo' => 'SA'],
            ['codigo' => 'TIP_SERV_ALOJAMIENTO_DESAYUNO', 'nombre' => 'Alojamiento y Desayuno', 'prefijo' => 'AD'],
            ['codigo' => 'TIP_SERV_MEDIA_PENSION', 'nombre' => 'Media Pensión', 'prefijo' => 'MP'],
            ['codigo' => 'TIP_SERV_PENSION_COMPLETA', 'nombre' => 'Pensión Completa', 'prefijo' => 'PC'],
            ['codigo' => 'TIP_SERV_TRASLADO_AEROPUERTO', 'nombre' => 'Traslado Aeropuerto', 'prefijo' => 'TA'],
            ['codigo' => 'TIP_SERV_SPA_MASAJE', 'nombre' => 'Masajes y Spa', 'prefijo' => 'SP'],
            ['codigo' => 'TIP_SERV_LAVANDERIA', 'nombre' => 'Lavandería y Planchado', 'prefijo' => 'LP'],
            ['codigo' => 'TIP_SERV_TRABAJO_EVENTOS', 'nombre' => 'Trabajo y Eventos', 'prefijo' => 'TE'],
        ];

        foreach ($tipos as $index => $tipo) {
            Catalogo::updateOrCreate(
                [
                    'catalogo_tipo_id' => $tipoServicio->id,
                    'codigo' => $tipo['codigo'],
                ],
                [
                    'nombre' => $tipo['nombre'],
                    'prefijo' => $tipo['prefijo'],
                    'orden' => $index + 1,
                    'estado' => 1,
                ]
            );
        }
    }
}
