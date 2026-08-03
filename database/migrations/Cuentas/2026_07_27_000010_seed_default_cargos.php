<?php

declare(strict_types=1);

use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ahora = now();

        $cargos = [
            [
                'codigo' => 'IVA15',
                'nombre' => 'IVA 15%',
                'tipo' => TipoCargo::Impuesto->value,
                'modo_calculo' => ModoCargo::Porcentaje->value,
                'valor' => 15.0000,
                'base_calculo' => BaseCalculo::SubtotalNeto->value,
                'orden' => 1,
                'obligatorio' => true,
                'permite_modificacion' => false,
                'areas' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
                'estado' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'codigo' => 'SERV10',
                'nombre' => 'Servicio 10%',
                'tipo' => TipoCargo::Servicio->value,
                'modo_calculo' => ModoCargo::Porcentaje->value,
                'valor' => 10.0000,
                'base_calculo' => BaseCalculo::SubtotalNeto->value,
                'orden' => 2,
                'obligatorio' => false,
                'permite_modificacion' => true,
                'areas' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
                'estado' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'codigo' => 'PROP10',
                'nombre' => 'Propina Sugerida 10%',
                'tipo' => TipoCargo::Propina->value,
                'modo_calculo' => ModoCargo::Porcentaje->value,
                'valor' => 10.0000,
                'base_calculo' => BaseCalculo::SubtotalNeto->value,
                'orden' => 3,
                'obligatorio' => false,
                'permite_modificacion' => true,
                'areas' => null,
                'fecha_inicio' => null,
                'fecha_fin' => null,
                'estado' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ];

        foreach ($cargos as $cargo) {
            $existe = DB::table('cargos_facturacion')->where('codigo', $cargo['codigo'])->exists();
            if (! $existe) {
                DB::table('cargos_facturacion')->insert($cargo);
            }
        }
    }

    public function down(): void
    {
        DB::table('cargos_facturacion')->whereIn('codigo', ['IVA15', 'SERV10', 'PROP10'])->delete();
    }
};
