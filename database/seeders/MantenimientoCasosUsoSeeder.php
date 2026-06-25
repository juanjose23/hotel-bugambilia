<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\EstadoPlanMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Models\Activos\Activo;
use App\Models\Activos\ActPlanMantenimiento;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MantenimientoCasosUsoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎬 Iniciando MantenimientoCasosUsoSeeder...');

        $admin = User::where('email', 'admin@hotel.com')->first() ?? User::first();
        if (! $admin) {
            $this->command->warn('⚠ No se encontró usuario administrador.');

            return;
        }

        // Crear un usuario técnico de pruebas para notificaciones dirigidas
        $tecnico = User::firstOrCreate(
            ['email' => 'tecnico.mantenimiento@hotel.com'],
            [
                'name' => 'Técnico de Pruebas',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $moneda = DB::table('monedas')->first();
        $proveedor = DB::table('proveedores')->first();

        // 1. Obtener o crear una categoría y producto tipo=3 para activos
        $categoria = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'CAT_PRO'))->first()
            ?? Catalogo::first();

        if (! $categoria) {
            $this->command->warn('No se encontró categoría para el producto.');

            return;
        }

        $producto = Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Equipo de Climatización Industrial',
            'tipo' => 3, // Activo Fijo
            'estado' => 1,
        ]);

        // ====================================================================
        // CASO 1: Activo con garantía próxima a vencer (10 días)
        // ====================================================================
        $activoGarantia = Activo::create([
            'codigo_inventario' => 'AF-GAR-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Climatizador Central Lobby (Próximo Vencimiento Garantía)',
            'fecha_adquisicion' => now()->subMonths(11)->toDateString(),
            'fecha_garantia_fin' => today()->addDays(10)->toDateString(),
            'estado' => EstadoActivo::Activo,
        ]);
        $this->command->info("✅ Caso Garantía Vence en 10 días: {$activoGarantia->codigo_inventario}");

        // ====================================================================
        // CASO 2: Plan preventivo vencido (debe gatillar VerificarMantenimientosPreventivosJob)
        // ====================================================================
        $activoPlan = Activo::create([
            'codigo_inventario' => 'AF-PLN-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Generador Eléctrico de Emergencia (Plan Activo)',
            'fecha_adquisicion' => now()->subMonths(6)->toDateString(),
            'estado' => EstadoActivo::Activo,
        ]);

        $plan = ActPlanMantenimiento::create([
            'nombre' => 'Plan de Mantenimiento Mensual Generador Eléctrico',
            'tipo' => 'preventivo',
            'frecuencia_dias' => 30,
            'fecha_inicio' => today()->subDays(60)->toDateString(),
            'fecha_proximo_mantenimiento' => today()->subDays(1)->toDateString(), // Vencido ayer
            'estado' => EstadoPlanMantenimiento::Activo,
            'proveedor_id' => $proveedor?->id,
        ]);
        $plan->activos()->attach($activoPlan->id);
        $this->command->info("✅ Caso Plan Preventivo Vencido creado: '{$plan->nombre}'");

        // ====================================================================
        // CASO 3: Ventana exacta: 7 días antes
        // ====================================================================
        $activoProximo7 = Activo::create([
            'codigo_inventario' => 'AF-P7D-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Aire Acondicionado Suite 101',
            'fecha_adquisicion' => now()->subMonths(6)->toDateString(),
            'estado' => EstadoActivo::Activo,
        ]);
        $activoProximo7->mantenimientos()->create([
            'tipo' => TipoMantenimiento::Preventivo,
            'fecha_programada' => today()->addDays(7)->toDateString(),
            'notas' => 'Mantenimiento Preventivo de Suite 101 (Alertará en 7 días)',
            'estado' => EstadoMantenimiento::Programado,
            'realizado_por_id' => $tecnico->id,
        ]);
        $this->command->info('✅ Caso Notificación Proxima (7 días) creado.');

        // ====================================================================
        // CASO 4: Ventana exacta: 3 días antes
        // ====================================================================
        $activoProximo3 = Activo::create([
            'codigo_inventario' => 'AF-P3D-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Aire Acondicionado Suite 102',
            'fecha_adquisicion' => now()->subMonths(6)->toDateString(),
            'estado' => EstadoActivo::Activo,
        ]);
        $activoProximo3->mantenimientos()->create([
            'tipo' => TipoMantenimiento::Preventivo,
            'fecha_programada' => today()->addDays(3)->toDateString(),
            'notas' => 'Mantenimiento Preventivo de Suite 102 (Alertará en 3 días)',
            'estado' => EstadoMantenimiento::Programado,
            'realizado_por_id' => $tecnico->id,
        ]);
        $this->command->info('✅ Caso Notificación Proxima (3 días) creado.');

        // ====================================================================
        // CASO 5: Ventana exacta: 1 día antes
        // ====================================================================
        $activoProximo1 = Activo::create([
            'codigo_inventario' => 'AF-P1D-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Aire Acondicionado Suite 103',
            'fecha_adquisicion' => now()->subMonths(6)->toDateString(),
            'estado' => EstadoActivo::Activo,
        ]);
        $activoProximo1->mantenimientos()->create([
            'tipo' => TipoMantenimiento::Preventivo,
            'fecha_programada' => today()->addDays(1)->toDateString(),
            'notas' => 'Mantenimiento Preventivo de Suite 103 (Alertará en 1 día)',
            'estado' => EstadoMantenimiento::Programado,
            'realizado_por_id' => $tecnico->id,
        ]);
        $this->command->info('✅ Caso Notificación Proxima (1 día) creado.');

        // ====================================================================
        // CASO 6: Ventana exacta: Mismo día (hoy)
        // ====================================================================
        $activoHoy = Activo::create([
            'codigo_inventario' => 'AF-HOY-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Aire Acondicionado Suite 104',
            'fecha_adquisicion' => now()->subMonths(6)->toDateString(),
            'estado' => EstadoActivo::Activo,
        ]);
        $activoHoy->mantenimientos()->create([
            'tipo' => TipoMantenimiento::Preventivo,
            'fecha_programada' => today()->toDateString(),
            'notas' => 'Mantenimiento Preventivo de Suite 104 (Programado para hoy)',
            'estado' => EstadoMantenimiento::Programado,
            'realizado_por_id' => $tecnico->id,
        ]);
        $this->command->info('✅ Caso Notificación (Hoy) creado.');

        // ====================================================================
        // CASO 7: Mantenimiento vencido por 1 día
        // ====================================================================
        $activoVencido = Activo::create([
            'codigo_inventario' => 'AF-V1D-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Filtros de Aire Lobby',
            'fecha_adquisicion' => now()->subMonths(6)->toDateString(),
            'estado' => EstadoActivo::Activo,
        ]);
        $activoVencido->mantenimientos()->create([
            'tipo' => TipoMantenimiento::Correctivo,
            'fecha_programada' => today()->subDays(1)->toDateString(),
            'notas' => 'Cambio de filtros de aire lobby (Vencido hace 1 día)',
            'estado' => EstadoMantenimiento::Programado,
            'realizado_por_id' => $tecnico->id,
        ]);
        $this->command->info('✅ Caso Retrasado (1 día) creado.');

        // ====================================================================
        // CASO 8: Mantenimiento Crítico vencido por 8 días
        // ====================================================================
        $activoCritico = Activo::create([
            'codigo_inventario' => 'AF-CRT-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Caldera Central de Agua Caliente',
            'fecha_adquisicion' => now()->subMonths(18)->toDateString(),
            'estado' => EstadoActivo::Activo,
        ]);
        $activoCritico->mantenimientos()->create([
            'tipo' => TipoMantenimiento::Correctivo,
            'fecha_programada' => today()->subDays(8)->toDateString(),
            'notas' => 'Revisión crítica de caldera central (Retrasada por más de 7 días)',
            'estado' => EstadoMantenimiento::Programado,
            'realizado_por_id' => $tecnico->id,
        ]);
        $this->command->info('✅ Caso Crítico (Vencido hace 8 días) creado.');

        // ====================================================================
        // CASO 9: Mantenimiento en curso y prolongado por 20 días
        // ====================================================================
        $activoProlongado = Activo::create([
            'codigo_inventario' => 'AF-PRL-'.Str::upper(Str::random(4)),
            'producto_id' => $producto->id,
            'nombre_descriptivo' => 'Ascensor Principal de Clientes',
            'fecha_adquisicion' => now()->subMonths(12)->toDateString(),
            'estado' => EstadoActivo::EnMantenimiento,
        ]);
        $activoProlongado->mantenimientos()->create([
            'tipo' => TipoMantenimiento::Correctivo,
            'fecha_programada' => today()->subDays(20)->toDateString(),
            'notas' => 'Mantenimiento del ascensor principal (En curso prolongado por más de 15 días)',
            'estado' => EstadoMantenimiento::EnProceso,
            'realizado_por_id' => $tecnico->id,
        ]);
        $this->command->info('✅ Caso Mantenimiento Prolongado (En Proceso, 20 días) creado.');
        $this->command->info('🎉 MantenimientoCasosUsoSeeder completado con éxito.');
    }
}
