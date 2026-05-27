<?php

namespace Database\Seeders;

use App\Enums\CatalogoTipo;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Enums\Compras\EstadoSolicitud;
use App\Enums\EstadoCatalogo;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Pais;
use App\Models\Catalogos\Producto;
use App\Models\Colaboradores\Colaborador;
use App\Models\Colaboradores\ColaboradorCargoHistorial;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Models\Personas\Persona;
use App\Models\Personas\PersonaNatural;
use App\Models\User;
use App\UseCases\Inventario\Recepciones\Mutations\RegistrarEntradaRecepcion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ProcurementFlowSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hotel.com')->first();

        if (! $admin) {
            $pais = Pais::first() ?? Pais::create(['nombre' => 'Nicaragua', 'codigo' => 'NI']);

            $persona = Persona::create([
                'primer_nombre' => 'Admin',
                'segundo_nombre' => 'Compras',
                'pais_id' => $pais->id,
                'tipo_persona' => 'natural',
                'telefono' => '12345678',
                'direccion' => 'Dirección de prueba',
            ]);

            PersonaNatural::create([
                'persona_id' => $persona->id,
                'primer_apellido' => 'Sistema',
                'segundo_apellido' => 'Hotel',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => 'ADMIN-001',
                'sexo' => 'M',
                'fecha_nacimiento' => '1990-01-01',
            ]);

            $colaborador = Colaborador::create([
                'persona_id' => $persona->id,
                'codigo' => 'COLAB-001',
                'fecha_ingreso' => now(),
                'estado' => EstadoCatalogo::Activo->value,
            ]);

            // Asignar cargo y departamento por defecto
            $cargo = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'CARG'))->first();
            $depto = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'DEPT'))->first();

            if ($cargo && $depto) {
                ColaboradorCargoHistorial::create([
                    'colaborador_id' => $colaborador->id,
                    'cargo_id' => $cargo->id,
                    'departamento_id' => $depto->id,
                    'fecha_inicio' => now(),
                    'estado' => EstadoCatalogo::Activo->value,
                ]);
            }

            $admin = User::create([
                'name' => 'Admin Compras',
                'email' => 'admin@hotel.com',
                'password' => bcrypt('123456'),
                'email_verified_at' => now(),
                'persona_id' => $persona->id,
            ]);

            // Asignar rol super_admin para notificaciones y permisos
            try {
                $superAdminRole = Role::firstOrCreate([
                    'name' => config('filament-shield.super_admin.name', 'super_admin'),
                    'guard_name' => 'web',
                ]);
                $admin->assignRole($superAdminRole);
            } catch (\Throwable $e) {
                // Silenciar si las tablas no existen
            }
        }

        Auth::login($admin);

        DB::transaction(function () use ($admin) {
            $colaborador = Colaborador::first();
            if (! $colaborador) {
                return;
            }
            $proveedores = Proveedor::limit(5)->get();
            $productos = Producto::limit(100)->get();
            $unidadMedida = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::UNIDAD_MEDIDA->value))->first();
            $condicionPago = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::CONDICION_PAGO->value))->first();

            $catalogoIds = Catalogo::pluck('id', 'codigo')->toArray();

            // --- ESCENARIO 1: SOLICITUDES DEPARTAMENTALES EN DIFERENTES ESTADOS ---
            $solicitudesBase = [
                [
                    'codigo' => 'SOL-MANT-001',
                    'depto' => 'DEP_MANTENIMIENTO',
                    'estado' => EstadoSolicitud::Borrador,
                    'motivo' => 'Reparación urgente de calderas en zona de lavandería.',
                ],
                [
                    'codigo' => 'SOL-ALIM-002',
                    'depto' => 'DEP_OPERACIONES',
                    'estado' => EstadoSolicitud::Pendiente,
                    'motivo' => 'Reposición mensual de granos básicos y café para restaurante.',
                ],
                [
                    'codigo' => 'SOL-AMEN-003',
                    'depto' => 'DEP_AMA_LLAVES',
                    'estado' => EstadoSolicitud::Rechazada,
                    'motivo' => 'Compra de toallas de lujo (Rechazada por falta de presupuesto trimestral).',
                ],
                [
                    'codigo' => 'SOL-RECP-004',
                    'depto' => 'DEP_RECEPCION',
                    'estado' => EstadoSolicitud::Cancelada,
                    'motivo' => 'Papelería institucional (Cancelada por cambio de diseño de marca).',
                ],
                [
                    'codigo' => 'SOL-EQUIP-005',
                    'depto' => 'DEP_MANTENIMIENTO',
                    'estado' => EstadoSolicitud::Aprobada,
                    'motivo' => 'Renovación de aire acondicionado suites y proyector salón B.',
                ],
            ];

            foreach ($solicitudesBase as $sBase) {
                $sol = Solicitud::create([
                    'codigo' => $sBase['codigo'],
                    'colaborador_id' => $colaborador->id,
                    'departamento_solicitante_id' => 1, // DEP_MANTENIMIENTO usualmente
                    'fecha_solicitud' => now()->subDays(rand(1, 10)),
                    'estado' => $sBase['estado'],
                    'motivo' => $sBase['motivo'],
                ]);

                // Para mantenimiento (Escenario 1), agregamos muchos ítems (simulando pedido de stock)
                $count = ($sBase['codigo'] === 'SOL-MANT-001') ? 15 : 2;
                foreach ($productos->random($count) as $p) {
                    $variante = DB::table('producto_variantes')->where('producto_id', $p->id)->first();
                    $sol->items()->create([
                        'producto_id' => $p->id,
                        'producto_variante_id' => $variante?->id,
                        'cantidad_solicitada' => rand(5, 50),
                        'unidad_medida_id' => $p->unidad_medida_id ?? $unidadMedida->id,
                    ]);
                }
            }

            // --- ESCENARIO 2: COMPARATIVA DE PRECIOS (3 COTIZACIONES PARA 1 SOLICITUD) ---
            $solicitudComp = Solicitud::create([
                'codigo' => 'SOL-COMP-2026',
                'colaborador_id' => $colaborador->id,
                'departamento_solicitante_id' => 1,
                'fecha_solicitud' => now()->subDays(15),
                'estado' => EstadoSolicitud::Aprobada,
                'motivo' => 'Equipamiento tecnológico y mobiliario para el nuevo centro de negocios.',
            ]);

            // Seleccionamos 3 productos fijos para comparar
            $prodsComp = $productos->whereIn('categoria_id', [$catalogoIds['CAT_PRO_inv_ELECTRO'], $catalogoIds['CAT_PRO_inv_MOB']])->take(3);
            foreach ($prodsComp as $prod) {
                $solicitudComp->items()->create([
                    'producto_id' => $prod->id,
                    'producto_variante_id' => DB::table('producto_variantes')->where('producto_id', $prod->id)->value('id'),
                    'cantidad_solicitada' => 5,
                    'cantidad_aprobada' => 5,
                    'unidad_medida_id' => $prod->unidad_medida_id ?? $unidadMedida->id,
                ]);
            }

            // --- PROVEEDOR 1: EL ECONÓMICO (Lento) ---
            $cot1 = Cotizacion::create([
                'solicitud_id' => $solicitudComp->id,
                'proveedor_id' => $proveedores->get(0)->id,
                'fecha_cotizacion' => now()->subDays(10),
                'dias_entrega' => 15,
                'condicion_pago_id' => $condicionPago->id,
                'observaciones' => 'Precio más bajo garantizado, pero tiempo de entrega extendido.',
                'creada_por' => $admin->id,
                'moneda_id' => 2,
                'subtotal' => 0, 'total' => 0,
            ]);
            $sub1 = 0;
            foreach ($solicitudComp->items as $item) {
                $precio = 100.00;
                $cot1->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'cantidad' => $item->cantidad_aprobada,
                    'precio_unitario' => $precio,
                    'subtotal' => $item->cantidad_aprobada * $precio,
                ]);
                $sub1 += ($item->cantidad_aprobada * $precio);
            }
            $cot1->update(['subtotal' => $sub1, 'total' => $sub1 * 1.15]);

            // --- PROVEEDOR 2: EL EQUILIBRADO (Ganador probable) ---
            $cot2 = Cotizacion::create([
                'solicitud_id' => $solicitudComp->id,
                'proveedor_id' => $proveedores->get(1)->id,
                'fecha_cotizacion' => now()->subDays(9),
                'dias_entrega' => 5,
                'condicion_pago_id' => $condicionPago->id,
                'observaciones' => 'Balance ideal entre costo y tiempo de respuesta.',
                'creada_por' => $admin->id,
                'moneda_id' => 2,
                'subtotal' => 0, 'total' => 0,
            ]);
            $sub2 = 0;
            foreach ($solicitudComp->items as $item) {
                $precio = 115.00;
                $cot2->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'cantidad' => $item->cantidad_aprobada,
                    'precio_unitario' => $precio,
                    'subtotal' => $item->cantidad_aprobada * $precio,
                ]);
                $sub2 += ($item->cantidad_aprobada * $precio);
            }
            $cot2->update(['subtotal' => $sub2, 'total' => $sub2 * 1.15]);

            // --- PROVEEDOR 3: EL PREMIUM (Rápido pero caro) ---
            $cot3 = Cotizacion::create([
                'solicitud_id' => $solicitudComp->id,
                'proveedor_id' => $proveedores->get(2)->id,
                'fecha_cotizacion' => now()->subDays(8),
                'dias_entrega' => 1,
                'condicion_pago_id' => $condicionPago->id,
                'observaciones' => 'Entrega inmediata. Stock garantizado.',
                'creada_por' => $admin->id,
                'moneda_id' => 2,
                'subtotal' => 0, 'total' => 0,
            ]);
            $sub3 = 0;
            foreach ($solicitudComp->items as $item) {
                $precio = 150.00;
                $cot3->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'cantidad' => $item->cantidad_aprobada,
                    'precio_unitario' => $precio,
                    'subtotal' => $item->cantidad_aprobada * $precio,
                ]);
                $sub3 += ($item->cantidad_aprobada * $precio);
            }
            $cot3->update(['subtotal' => $sub3, 'total' => $sub3 * 1.15]);

            // --- PROVEEDOR 4: EL LOCAL (Flexible) ---
            $cot4 = Cotizacion::create([
                'solicitud_id' => $solicitudComp->id,
                'proveedor_id' => $proveedores->get(3)->id ?? $proveedores->first()->id,
                'fecha_cotizacion' => now()->subDays(7),
                'dias_entrega' => 3,
                'condicion_pago_id' => $condicionPago->id,
                'observaciones' => 'Proveedor local con soporte técnico incluido.',
                'creada_por' => $admin->id,
                'moneda_id' => 2,
                'subtotal' => 0, 'total' => 0,
            ]);
            $sub4 = 0;
            foreach ($solicitudComp->items as $item) {
                $precio = 130.00;
                $cot4->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'cantidad' => $item->cantidad_aprobada,
                    'precio_unitario' => $precio,
                    'subtotal' => $item->cantidad_aprobada * $precio,
                ]);
                $sub4 += ($item->cantidad_aprobada * $precio);
            }
            $cot4->update(['subtotal' => $sub4, 'total' => $sub4 * 1.15]);

            // --- PROVEEDOR 5: EL MAYORISTA (Volumen) ---
            $cot5 = Cotizacion::create([
                'solicitud_id' => $solicitudComp->id,
                'proveedor_id' => $proveedores->get(4)->id ?? $proveedores->last()->id,
                'fecha_cotizacion' => now()->subDays(6),
                'dias_entrega' => 10,
                'condicion_pago_id' => $condicionPago->id,
                'observaciones' => 'Precio especial por apertura de cuenta corporativa.',
                'creada_por' => $admin->id,
                'moneda_id' => 2,
                'subtotal' => 0, 'total' => 0,
            ]);
            $sub5 = 0;
            foreach ($solicitudComp->items as $item) {
                $precio = 105.00;
                $cot5->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'cantidad' => $item->cantidad_aprobada,
                    'precio_unitario' => $precio,
                    'subtotal' => $item->cantidad_aprobada * $precio,
                ]);
                $sub5 += ($item->cantidad_aprobada * $precio);
            }
            $cot5->update(['subtotal' => $sub5, 'total' => $sub5 * 1.15]);

            // --- ESCENARIO 3: FLUJO COMPLETO HASTA RECEPCIÓN ---
            $solicitudFull = Solicitud::create([
                'codigo' => 'SOL-STOCK-2026',
                'colaborador_id' => $colaborador->id,
                'departamento_solicitante_id' => 1,
                'fecha_solicitud' => now()->subDays(20),
                'estado' => EstadoSolicitud::Aprobada,
                'motivo' => 'Reposición de insumos de limpieza y suministros operativos.',
            ]);

            foreach ($productos->where('categoria_id', $catalogoIds['CAT_PRO_LIMP_QUIM'])->take(3) as $prod) {
                $solicitudFull->items()->create([
                    'producto_id' => $prod->id,
                    'producto_variante_id' => DB::table('producto_variantes')->where('producto_id', $prod->id)->value('id'),
                    'cantidad_solicitada' => 20,
                    'cantidad_aprobada' => 20,
                    'unidad_medida_id' => $prod->unidad_medida_id ?? $unidadMedida->id,
                ]);
            }

            $proveedorWin = $proveedores->get(0);
            $cotWin = Cotizacion::create([
                'solicitud_id' => $solicitudFull->id,
                'proveedor_id' => $proveedorWin->id,
                'fecha_cotizacion' => now()->subDays(18),
                'dias_entrega' => 2,
                'condicion_pago_id' => $condicionPago->id,
                'es_elegida' => true,
                'elegida_por' => $admin->id,
                'elegida_en' => now()->subDays(17),
                'subtotal' => 600, 'total' => 690,
            ]);

            foreach ($solicitudFull->items as $item) {
                $cotWin->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'cantidad' => $item->cantidad_aprobada,
                    'precio_unitario' => 10.00,
                    'subtotal' => 200,
                    'es_elegido' => true,
                ]);
            }

            $orden = OrdenCompra::create([
                'codigo' => 'OC-2026-WIN',
                'proveedor_id' => $proveedorWin->id,
                'solicitud_id' => $solicitudFull->id,
                'cotizacion_id' => $cotWin->id,
                'fecha_orden' => now()->subDays(15),
                'condicion_pago_id' => $condicionPago->id,
                'estado' => EstadoOrdenCompra::Recibida,
                'subtotal' => 600, 'total' => 690,
            ]);

            foreach ($cotWin->items as $cItem) {
                $orden->items()->create([
                    'producto_id' => $cItem->producto_id,
                    'producto_variante_id' => $cItem->producto_variante_id,
                    'cantidad' => $cItem->cantidad,
                    'precio_unitario' => $cItem->precio_unitario,
                    'subtotal' => $cItem->subtotal,
                    'unidad_medida_id' => $unidadMedida->id,
                ]);
            }

            $recepcion = RecepcionCompra::create([
                'codigo' => 'RC-WIN-001',
                'orden_compra_id' => $orden->id,
                'fecha_recepcion' => now()->subDays(5),
                'recibido_por_id' => $admin->id,
                'estado' => EstadoRecepcion::Completa,
                'notas' => 'Entrega perfecta según contrato.',
            ]);

            $loteProveedor = 'PROV-LOT-'.Str::upper(Str::random(5));

            $itemsData = $orden->items->map(fn ($oi) => [
                'orden_item_id' => $oi->id,
                'producto_id' => $oi->producto_id,
                'producto_variante_id' => $oi->producto_variante_id,
                'cantidad_recibida' => $oi->cantidad,
                'cantidad_rechazada' => 0.0,
                'lote_proveedor' => $loteProveedor,
                'fecha_vencimiento' => now()->addMonths(12)->format('Y-m-d'),
            ])->toArray();

            $createdItems = [];
            foreach ($itemsData as $item) {
                $createdItems[] = $recepcion->items()->create($item);
            }

            $itemsForUseCase = collect($createdItems)->map(fn ($i) => [
                'id' => $i->id,
                'producto_id' => $i->producto_id,
                'producto_variante_id' => $i->producto_variante_id,
                'cantidad_recibida' => (float) $i->cantidad_recibida,
                'cantidad_rechazada' => (float) $i->cantidad_rechazada,
                'lote_proveedor' => $i->lote_proveedor,
                'fecha_vencimiento' => $i->fecha_vencimiento?->format('Y-m-d'),
            ])->all();

            app(RegistrarEntradaRecepcion::class)->execute(
                nuevoEstado: 'Completa',
                items: $itemsForUseCase,
                proveedorId: $orden->proveedor_id,
                creadoPorId: $admin->id,
            );

            // --- ESCENARIO 4: MANTENIMIENTO DE INFRAESTRUCTURA (3 COTIZACIONES MÁS) ---
            $solicitudManto = Solicitud::create([
                'codigo' => 'SOL-INFRA-2026',
                'colaborador_id' => $colaborador->id,
                'departamento_solicitante_id' => 1,
                'fecha_solicitud' => now()->subDays(5),
                'estado' => EstadoSolicitud::Aprobada,
                'motivo' => 'Materiales para remodelación de fachada y área de alberca.',
            ]);

            foreach ($productos->where('categoria_id', $catalogoIds['CAT_PRO_inv_MANT'])->take(4) as $prod) {
                $solicitudManto->items()->create([
                    'producto_id' => $prod->id,
                    'producto_variante_id' => DB::table('producto_variantes')->where('producto_id', $prod->id)->value('id'),
                    'cantidad_solicitada' => 10,
                    'cantidad_aprobada' => 10,
                    'unidad_medida_id' => $prod->unidad_medida_id ?? $unidadMedida->id,
                ]);
            }

            // 3 Cotizaciones para Infraestructura
            for ($i = 0; $i < 3; $i++) {
                $cot = Cotizacion::create([
                    'solicitud_id' => $solicitudManto->id,
                    'proveedor_id' => $proveedores->random()->id,
                    'fecha_cotizacion' => now()->subDays(2),
                    'dias_entrega' => rand(2, 8),
                    'condicion_pago_id' => $condicionPago->id,
                    'observaciones' => 'Propuesta técnica '.($i + 1),
                    'creada_por' => $admin->id,
                    'moneda_id' => 2,
                    'subtotal' => 0, 'total' => 0,
                ]);
                $subTotal = 0;
                foreach ($solicitudManto->items as $item) {
                    $precio = rand(50, 200);
                    $cot->items()->create([
                        'producto_id' => $item->producto_id,
                        'producto_variante_id' => $item->producto_variante_id,
                        'cantidad' => $item->cantidad_aprobada,
                        'precio_unitario' => $precio,
                        'subtotal' => $item->cantidad_aprobada * $precio,
                    ]);
                    $subTotal += ($item->cantidad_aprobada * $precio);
                }
                $cot->update(['subtotal' => $subTotal, 'total' => $subTotal * 1.15]);
            }
        });
    }
}
