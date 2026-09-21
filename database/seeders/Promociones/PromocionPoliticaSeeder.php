<?php

declare(strict_types=1);

namespace Database\Seeders\Promociones;

use App\Repository\Models\Politicas\Politica;
use Illuminate\Database\Seeder;

class PromocionPoliticaSeeder extends Seeder
{
    public function run(): void
    {
        $politicas = [
            [
                'titulo' => 'Política de Cancelación y Reprogramación de Paquetes',
                'descripcion' => 'Las tarifas y paquetes promocionales permiten cambios de fecha sin penalización hasta 48 horas previas al check-in original. Cancelaciones con menos de 48 horas conllevarán la retención del depósito de garantía.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Garantía y Anticipo para Promociones Exclusivas',
                'descripcion' => 'Para asegurar la tarifa especial y los servicios complementarios (spa, cenas gourmet, tours), se requiere un anticipo del 30% al momento de realizar la reserva.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Términos de Paquetes Románticos y Cenas Privadas',
                'descripcion' => 'Los servicios adicionales incluidos (decoración especial, botella de vino, masajes y cenas de varios tiempos) deben coordinarse con recepción y concierge con al menos 24 horas de anticipación.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de No Reembolso en Temporada Alta / Early Bird',
                'descripcion' => 'Las promociones con descuento anticipado (Early Bird) son tarifas especiales no reembolsables. En caso de fuerza mayor comprobable, el saldo se acreditará como voucher válido por 12 meses.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Uso de Instalaciones y Acceso a Piscina & Spa',
                'descripcion' => 'Los pases de cortesía y paquetes de día incluyen toallas, batas y amenidades. Se requiere respetar el reglamento de seguridad acuática y horarios de silencio en las áreas de relajación.',
                'estado' => 1,
            ],
        ];

        foreach ($politicas as $pData) {
            Politica::query()->updateOrCreate(
                ['titulo' => $pData['titulo']],
                [
                    'descripcion' => $pData['descripcion'],
                    'estado' => $pData['estado'],
                ]
            );
        }
    }
}
