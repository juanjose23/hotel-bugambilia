<?php

declare(strict_types=1);

namespace Database\Seeders\Tesoreria;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;

final class CajasInicialesSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('is_admin', true)->value('id') ?? User::query()->value('id') ?? 1;

        $monedasIds = Moneda::query()->whereIn('codigo', ['NIO', 'USD'])->pluck('id')->all();
        if ($monedasIds === []) {
            $monedasIds = Moneda::query()->pluck('id')->all();
        }

        $tipos = Catalogo::query()
            ->whereRelation('catalogoTipo', 'codigo', 'TIPO_CAJA')
            ->pluck('id', 'codigo');

        $cajas = [
            [
                'codigo' => 'CJ-RECEP-01',
                'nombre' => 'Caja Recepción Principal',
                'ubicacion_nombre' => 'Recepción y Lobby',
                'ubicacion_busqueda' => 'Recepción',
                'tipo_codigo' => 'CAJA_FRONT_DESK',
            ],
            [
                'codigo' => 'CJ-REST-01',
                'nombre' => 'Caja Restaurante Bugambilias',
                'ubicacion_nombre' => 'Restaurante',
                'ubicacion_busqueda' => 'Restaurante',
                'tipo_codigo' => 'CAJA_POS_RESTAURANTE',
            ],
            [
                'codigo' => 'CJ-PISC-01',
                'nombre' => 'Caja Bar de Piscina',
                'ubicacion_nombre' => 'Área de Piscina / Bar',
                'ubicacion_busqueda' => 'Bar de Piscina',
                'tipo_codigo' => 'CAJA_BAR_PISCINA',
            ],
            [
                'codigo' => 'CJ-EVENT-01',
                'nombre' => 'Caja Banquetes y Eventos',
                'ubicacion_nombre' => 'Salón de Eventos',
                'ubicacion_busqueda' => 'Salón de Eventos',
                'tipo_codigo' => 'CAJA_EVENTOS',
            ],
            [
                'codigo' => 'CJ-CHICA-01',
                'nombre' => 'Caja Chica Administración',
                'ubicacion_nombre' => 'Edificio Principal',
                'ubicacion_busqueda' => 'Edificio Principal',
                'tipo_codigo' => 'CAJA_CHICA_OPERATIVA',
            ],
        ];

        foreach ($cajas as $def) {
            $ubicacion = Ubicacion::query()->where('nombre', 'ilike', "%{$def['ubicacion_busqueda']}%")->first();
            $rawTipo = $tipos->get($def['tipo_codigo']);
            $tipoId = is_numeric($rawTipo) ? (int) $rawTipo : null;

            $caja = Caja::query()->updateOrCreate(
                ['codigo' => $def['codigo']],
                [
                    'nombre' => $def['nombre'],
                    'ubicacion' => $ubicacion !== null ? $ubicacion->nombre : $def['ubicacion_nombre'],
                    'ubicacion_id' => $ubicacion?->id,
                    'tipo_caja_id' => $tipoId,
                    'activa' => true,
                    'creada_por' => $adminId,
                ]
            );

            $caja->monedas()->sync($monedasIds);
        }
    }
}
